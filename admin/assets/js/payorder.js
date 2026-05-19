(function (window, $) {
  'use strict';

  var payOrderState = {
    pagesize: 30,
    request: null
  };

  function normalizeQuery(query) {
    if (!query || query === 'start') {
      return '';
    }
    return String(query).replace(/^\?/, '').replace(/^&+/, '');
  }

  function parseQuery(query) {
    var result = {};
    var normalized = normalizeQuery(query);

    if (!normalized) {
      return result;
    }

    $.each(normalized.split('&'), function (_, part) {
      var pair;
      var key;
      var value;

      if (!part) {
        return;
      }
      pair = part.split('=');
      key = decodeURIComponent((pair[0] || '').replace(/\+/g, ' '));
      value = decodeURIComponent((pair.slice(1).join('=') || '').replace(/\+/g, ' '));
      result[key] = value;
    });

    return result;
  }

  function buildQuery(params) {
    var items = [];

    $.each(params, function (key, value) {
      if (value === null || value === undefined || value === '') {
        return;
      }
      items.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
    });

    return items.join('&');
  }

  function getCurrentQuery() {
    return normalizeQuery(window.location.search ? window.location.search.slice(1) : '');
  }

  function updateHistory(query) {
    var normalized = normalizeQuery(query);
    var target = normalized ? ('./payorder.php?' + normalized) : './payorder.php';
    if (window.history && window.history.replaceState) {
      window.history.replaceState({}, '', target);
    }
  }

  function showErrorMessage(message) {
    layer.msg(message || '\u670d\u52a1\u5668\u9519\u8bef');
  }

  function openLoading() {
    return layer.load(2, { shade: [0.1, '#fff'] });
  }

  function getModalArea(size) {
    if ($(window).width() <= 767) {
      return ['94%', 'auto'];
    }
    return size === 'compact' ? ['520px', 'auto'] : ['620px', 'auto'];
  }

  function escapeHtml(value) {
    return $('<div/>').text(value === null || value === undefined ? '' : String(value)).html();
  }

  function renderPartyLink(label, value, href) {
    var tag = href ? 'a' : 'span';
    var attrs = href ? ' href="' + escapeHtml(href) + '" target="_blank" rel="noreferrer"' : '';
    return '<' + tag + ' class="admin-payorder-party__link' + (href ? '' : ' is-static') + '"' + attrs + '><span class="admin-payorder-party__label">' + escapeHtml(label) + '</span><strong>' + escapeHtml(value) + '</strong></' + tag + '>';
  }

  function readPartyMeta($node) {
    var siteId = $.trim($node.attr('data-party-site-id') || $node.data('siteId') || '');
    var siteHref = $.trim($node.attr('data-party-site-href') || '');
    var userText = $.trim($node.attr('data-party-user-text') || $node.data('userId') || '');
    var userHref = $.trim($node.attr('data-party-user-href') || '');
    var isGuest = String($node.attr('data-party-is-guest') || $node.data('isGuest') || '0') === '1';

    return {
      siteId: siteId || '--',
      siteHref: siteHref,
      userText: userText,
      userHref: userHref,
      isGuest: isGuest
    };
  }

  function buildPartyHtml(meta) {
    var html = '<div class="admin-payorder-card__party">';

    html += renderPartyLink('\u7ad9\u70b9', meta.siteId || '--', meta.siteHref || '');
    if (meta.isGuest || !meta.userText) {
      html += renderPartyLink('\u7528\u6237', '\u6e38\u5ba2', '');
    } else {
      html += renderPartyLink('\u7528\u6237', meta.userText, meta.userHref || '');
    }
    html += '</div>';

    return html;
  }

  function decoratePartyRows() {
    $('tr.admin-payorder-row').each(function () {
      var $row = $(this);
      var meta = readPartyMeta($row);
      var $cell = $row.find('.admin-payorder-text--party');

      if (!$cell.length) {
        return;
      }

      $cell.find('.admin-payorder-party').html(buildPartyHtml(meta));
    });
  }

  function decoratePartyCards() {
    $('.admin-payorder-card').each(function () {
      var $card = $(this);
      var meta = readPartyMeta($card);
      var $cells = $card.find('.admin-payorder-card__grid > div');
      var $partyCell = $cells.eq(4);

      if (!$partyCell.length) {
        return;
      }

      $partyCell.html('<span>\u7ad9\u70b9 / \u7528\u6237</span>' + buildPartyHtml(meta));
    });
  }

  function renderConfirmDialog(config) {
    return [
      '<div class="admin-payorder-dialog">',
      '<p class="admin-payorder-dialog__eyebrow">Payment Action</p>',
      '<h3 class="admin-payorder-dialog__title">', config.heading, '</h3>',
      '<p class="admin-payorder-dialog__desc">', config.description, '</p>',
      '<div class="admin-payorder-dialog__meta">',
      '<div class="admin-payorder-dialog__meta-item"><span>', config.metaLabel, '</span><strong>', escapeHtml(config.metaValue), '</strong></div>',
      '</div>',
      '<div class="admin-payorder-dialog__footer">',
      '<button type="button" class="btn btn-default js-dialog-cancel"><i class="fa fa-times"></i> \u53d6\u6d88</button>',
      '<button type="button" class="btn ', config.confirmClass || 'btn-primary', ' js-dialog-confirm"><i class="fa ', config.confirmIcon || 'fa-check', '"></i> ', config.confirmText, '</button>',
      '</div>',
      '</div>'
    ].join('');
  }

  function openConfirmDialog(config) {
    return layer.open({
      type: 1,
      title: config.title || '\u64cd\u4f5c\u786e\u8ba4',
      skin: 'layui-layer-rim admin-payorder-layer',
      area: getModalArea('compact'),
      shadeClose: config.shadeClose !== false,
      content: '<div class="admin-payorder-modal-shell">' + renderConfirmDialog(config) + '</div>',
      success: function (layero, index) {
        $(layero).on('click', '.js-dialog-cancel', function () {
          layer.close(index);
        });
        $(layero).on('click', '.js-dialog-confirm', function () {
          if (typeof config.onConfirm === 'function') {
            config.onConfirm(layero, index);
          }
        });
      }
    });
  }

  function requestJson(options) {
    var loadingIndex = openLoading();
    var ajaxOptions = $.extend(true, {
      type: 'POST',
      dataType: 'json',
      cache: false
    }, options || {});

    return $.ajax(ajaxOptions).always(function () {
      layer.close(loadingIndex);
    });
  }

  function syncFilterForm(query) {
    var params = parseQuery(query);
    var $form = $('#payOrderFilterForm');

    if (!$form.length) {
      return;
    }

    $form.find('select[name="column"]').val(params.column || 'trade_no');
    $form.find('input[name="kw"]').val(params.kw || '');
    $form.find('select[name="type"]').val(params.type || 'all');
    $form.find('select[name="dstatus"]').val(params.dstatus || '0');
    $form.find('input[name="starttime"]').val(params.starttime || '');
    $form.find('input[name="endtime"]').val(params.endtime || '');
  }

  function buildFilterQuery() {
    var $form = $('#payOrderFilterForm');
    var params = {};

    if (!$form.length) {
      return '';
    }

    $.each($form.serializeArray(), function (_, item) {
      if (!item.name || item.value === '') {
        return;
      }
      if (item.name === 'type' && item.value === 'all') {
        return;
      }
      if (item.name === 'dstatus' && String(item.value) === '0') {
        return;
      }
      if (item.name === 'column' && $.trim($form.find('input[name="kw"]').val()) === '') {
        return;
      }
      params[item.name] = item.value;
    });

    delete params.page;
    return buildQuery(params);
  }

  function loadTable(query) {
    var normalized = normalizeQuery(query);
    var requestUrl = 'payorder-table.php?num=' + encodeURIComponent(payOrderState.pagesize);
    var loadingIndex;

    updateHistory(normalized);
    syncFilterForm(normalized);

    if (normalized) {
      requestUrl += '&' + normalized;
    }

    if (payOrderState.request && payOrderState.request.readyState !== 4) {
      payOrderState.request.abort();
    }

    loadingIndex = openLoading();
    payOrderState.request = $.ajax({
      type: 'GET',
      url: requestUrl,
      dataType: 'html',
      cache: false
    }).done(function (html) {
      $('#listTable').html(html);
      decoratePartyRows();
      decoratePartyCards();
    }).fail(function (xhr, status) {
      if (status !== 'abort') {
        showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
      }
    }).always(function () {
      layer.close(loadingIndex);
    });
  }

  function fillOrder(tradeNo) {
    openConfirmDialog({
      title: '\u8865\u5355\u786e\u8ba4',
      heading: '\u5c06\u8be5\u652f\u4ed8\u8bb0\u5f55\u6807\u8bb0\u4e3a\u5df2\u652f\u4ed8\u5e76\u8865\u5355\uff1f',
      description: '\u7cfb\u7edf\u4f1a\u8c03\u7528\u73b0\u6709\u8865\u5355\u903b\u8f91\uff0c\u751f\u6210\u5bf9\u5e94\u4e1a\u52a1\u8ba2\u5355\u3002\u8bf7\u786e\u8ba4\u8fd9\u7b14\u652f\u4ed8\u786e\u5b9e\u5df2\u5b8c\u6210\u3002',
      metaLabel: '\u652f\u4ed8\u8ba2\u5355\u53f7',
      metaValue: tradeNo,
      confirmText: '\u786e\u8ba4\u8865\u5355',
      confirmIcon: 'fa-magic',
      onConfirm: function (layero, index) {
        requestJson({
          url: 'ajax_order.php?act=fillPayOrder',
          data: { trade_no: tradeNo }
        }).done(function (data) {
          if (data.code === 0) {
            layer.close(index);
            layer.alert(data.msg || '\u8865\u5355\u6210\u529f', { icon: 1 }, function () {
              layer.closeAll();
              loadTable(getCurrentQuery());
            });
          } else {
            layer.alert(data.msg || '\u8865\u5355\u5931\u8d25');
          }
        }).fail(function () {
          showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
        });
      }
    });
  }

  function delOrder(tradeNo) {
    openConfirmDialog({
      title: '\u5220\u9664\u786e\u8ba4',
      heading: '\u786e\u5b9a\u5220\u9664\u8fd9\u6761\u652f\u4ed8\u8bb0\u5f55\u5417\uff1f',
      description: '\u5220\u9664\u652f\u4ed8\u8bb0\u5f55\u4f1a\u5f71\u54cd\u540e\u7eed\u5bf9\u8d26\u548c\u6392\u67e5\uff0c\u8bf7\u4ec5\u5728\u786e\u8ba4\u8fd9\u662f\u65e0\u6548\u8bb0\u5f55\u65f6\u6267\u884c\u3002',
      metaLabel: '\u652f\u4ed8\u8ba2\u5355\u53f7',
      metaValue: tradeNo,
      confirmText: '\u5220\u9664\u8bb0\u5f55',
      confirmIcon: 'fa-trash',
      confirmClass: 'btn-danger',
      onConfirm: function (layero, index) {
        requestJson({
          url: 'ajax_order.php?act=delPayOrder',
          data: { trade_no: tradeNo }
        }).done(function (data) {
          if (data.code === 0) {
            layer.close(index);
            layer.msg('\u5220\u9664\u6210\u529f');
            loadTable(getCurrentQuery());
          } else {
            layer.alert(data.msg || '\u5220\u9664\u5931\u8d25');
          }
        }).fail(function () {
          showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
        });
      }
    });
  }

  function bindEvents() {
    $(document).on('submit', '#payOrderFilterForm', function (event) {
      event.preventDefault();
      loadTable(buildFilterQuery());
    });

    $(document).on('click', '#resetPayOrderFilters', function () {
      var $form = $('#payOrderFilterForm');
      $form.find('select[name="column"]').val('trade_no');
      $form.find('input[name="kw"]').val('');
      $form.find('select[name="type"]').val('all');
      $form.find('select[name="dstatus"]').val('0');
      $form.find('input[name="starttime"]').val('');
      $form.find('input[name="endtime"]').val('');
      loadTable('');
    });

    $(document).on('click', '#refreshPayOrderList', function () {
      loadTable(getCurrentQuery());
    });

    $(document).on('change', '#pagesize', function () {
      payOrderState.pagesize = $(this).val() || '30';
      loadTable(getCurrentQuery());
    });

    $(document).on('change', '#dstatus, #payOrderFilterForm select[name="type"]', function () {
      loadTable(buildFilterQuery());
    });

    $(document).on('click', '[data-filter-query]', function () {
      loadTable($(this).data('filterQuery') || '');
    });

    $(document).on('click', '[data-page-query]', function () {
      if ($(this).closest('li').hasClass('disabled') || $(this).closest('li').hasClass('active')) {
        return;
      }
      loadTable($(this).data('pageQuery') || '');
    });

    $(document).on('click', '.js-pay-fill', function () {
      fillOrder($(this).data('tradeNo'));
    });

    $(document).on('click', '.js-pay-delete', function () {
      delOrder($(this).data('tradeNo'));
    });
  }

  $(function () {
    payOrderState.pagesize = $('#pagesize').val() || '30';
    if ($.fn.datepicker) {
      $('.input-datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        clearBtn: true,
        language: 'zh-CN'
      });
    }
    bindEvents();
    decoratePartyRows();
    decoratePartyCards();
    loadTable(getCurrentQuery());
  });

  window.listTable = loadTable;
  window.searchItem = function () { loadTable(buildFilterQuery()); return false; };
  window.clearItem = function () { loadTable(''); return false; };
  window.fillOrder = fillOrder;
  window.delOrder = delOrder;
})(window, jQuery);

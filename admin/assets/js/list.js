(function (window, $) {
  'use strict';

  var orderListState = {
    pagesize: 30,
    request: null,
    ordersucc: 0,
    orderfail: 0,
    resubmitTotal: 0
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

  function getCurrentListQuery() {
    return normalizeQuery(window.location.search ? window.location.search.slice(1) : '');
  }

  function updateHistory(query) {
    var target = normalizeQuery(query) ? ('./list.php?' + normalizeQuery(query)) : './list.php';
    if (window.history && window.history.replaceState) {
      window.history.replaceState({}, '', target);
    }
  }

  function escapeHtml(value) {
    return $('<div/>').text(value === null || value === undefined ? '' : String(value)).html();
  }

  function showErrorMessage(message) {
    layer.msg(message || '\u670d\u52a1\u5668\u9519\u8bef');
  }

  function openLoading() {
    return layer.load(2, { shade: [0.1, '#fff'] });
  }

  function getModalArea(size) {
    var width = $(window).width();
    if (width <= 767) {
      return ['94%', 'auto'];
    }
    if (size === 'large') {
      return ['820px', 'auto'];
    }
    if (size === 'compact') {
      return ['520px', 'auto'];
    }
    return ['620px', 'auto'];
  }

  function renderDialogMeta(metaItems) {
    var html = [];

    if (!metaItems || !metaItems.length) {
      return '';
    }

    html.push('<div class="admin-order-dialog__meta">');
    $.each(metaItems, function (_, item) {
      html.push('<div class="admin-order-dialog__meta-item">');
      html.push('<span>' + escapeHtml(item.label || '') + '</span>');
      html.push('<strong>' + escapeHtml(item.value || '') + '</strong>');
      html.push('</div>');
    });
    html.push('</div>');

    return html.join('');
  }

  function renderDialog(config) {
    var html = [];

    html.push('<div class="admin-order-dialog' + (config.modifier ? ' ' + config.modifier : '') + '">');

    if (config.eyebrow) {
      html.push('<p class="admin-order-dialog__eyebrow">' + config.eyebrow + '</p>');
    }
    if (config.heading) {
      html.push('<h3 class="admin-order-dialog__title">' + config.heading + '</h3>');
    }
    if (config.description) {
      html.push('<p class="admin-order-dialog__desc">' + config.description + '</p>');
    }
    html.push(renderDialogMeta(config.meta));

    if (config.body) {
      html.push('<div class="admin-order-dialog__body">' + config.body + '</div>');
    }
    if (config.footer) {
      html.push('<div class="admin-order-dialog__footer">' + config.footer + '</div>');
    }

    html.push('</div>');
    return html.join('');
  }

  function openHtmlModal(title, content, size, options) {
    options = options || {};

    return layer.open({
      type: 1,
      title: title,
      skin: 'layui-layer-rim admin-order-layer' + (options.skinClass ? (' ' + options.skinClass) : ''),
      area: getModalArea(size),
      shadeClose: options.shadeClose !== false,
      closeBtn: options.closeBtn === false ? 0 : 1,
      maxHeight: '90vh',
      content: '<div class="admin-order-modal-shell">' + content + '</div>',
      success: function (layero, index) {
        if (typeof options.onSuccess === 'function') {
          options.onSuccess(layero, index);
        }
      }
    });
  }

  function openConfirmDialog(config) {
    var content = renderDialog({
      modifier: config.modifier || 'admin-order-dialog--confirm',
      eyebrow: config.eyebrow || 'Action Confirm',
      heading: config.heading,
      description: config.description,
      meta: config.meta || [],
      footer: [
        '<button type="button" class="btn btn-default js-dialog-cancel"><i class="fa fa-times"></i> ',
        config.cancelText || '\u53d6\u6d88',
        '</button>',
        '<button type="button" class="btn ',
        config.confirmClass || 'btn-primary',
        ' js-dialog-confirm"><i class="fa ',
        config.confirmIcon || 'fa-check',
        '"></i> ',
        config.confirmText || '\u786e\u8ba4',
        '</button>'
      ].join('')
    });

    return openHtmlModal(config.title || '\u64cd\u4f5c\u786e\u8ba4', content, config.size || 'compact', {
      shadeClose: config.shadeClose !== false,
      onSuccess: function (layero, index) {
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
      type: 'GET',
      dataType: 'json',
      cache: false
    }, options || {});

    return $.ajax(ajaxOptions).always(function () {
      layer.close(loadingIndex);
    });
  }

  function sendResubmitRequest(id, options) {
    options = options || {};

    return $.ajax({
      type: 'GET',
      url: 'ajax_order.php?act=djOrder&id=' + encodeURIComponent(id),
      dataType: 'json',
      cache: false
    }).done(function (data) {
      if (typeof options.onSuccess === 'function') {
        options.onSuccess(data);
      }
    }).fail(function () {
      if (typeof options.onError === 'function') {
        options.onError();
      }
    });
  }

  function syncFilterForm(query) {
    var params = parseQuery(query);
    var $form = $('#orderFilterForm');

    if (!$form.length) {
      return;
    }

    $form.find('input[name="kw"]').val(params.kw || params.id || '');
    $form.find('input[name="starttime"]').val(params.starttime || '');
    $form.find('input[name="endtime"]').val(params.endtime || '');
    $form.find('select[name="type"]').val(params.type !== undefined ? params.type : '-1');
  }

  function buildFilterQuery() {
    var $form = $('#orderFilterForm');
    var params = {};

    if (!$form.length) {
      return '';
    }

    $.each($form.serializeArray(), function (_, item) {
      if (!item.name) {
        return;
      }
      if (item.name === 'type' && String(item.value) === '-1') {
        return;
      }
      if (item.value === '') {
        return;
      }
      params[item.name] = item.value;
    });

    delete params.page;
    return buildQuery(params);
  }

  function getContextQuery() {
    var $form = $('#orderFilterForm');
    var params = {};

    if (!$form.length) {
      return '';
    }

    ['tid', 'cid', 'zid', 'uid'].forEach(function (name) {
      var value = $form.find('input[name="' + name + '"]').val();
      if (value) {
        params[name] = value;
      }
    });

    return buildQuery(params);
  }

  function syncCheckAllState() {
    var $items = $('.js-order-check');
    var total = $items.length;
    var checked = $items.filter(':checked').length;
    $('#orderCheckAll').prop('checked', total > 0 && total === checked);
  }

  function loadTable(query) {
    var normalized = normalizeQuery(query);
    var requestUrl = 'list-table.php?num=' + encodeURIComponent(orderListState.pagesize);
    var loadingIndex;

    updateHistory(normalized);
    syncFilterForm(normalized);

    if (normalized) {
      requestUrl += '&' + normalized;
    }

    if (orderListState.request && orderListState.request.readyState !== 4) {
      orderListState.request.abort();
    }

    loadingIndex = openLoading();
    orderListState.request = $.ajax({
      type: 'GET',
      url: requestUrl,
      dataType: 'html',
      cache: false
    }).done(function (html) {
      $('#listTable').html(html);
      syncCheckAllState();
    }).fail(function (xhr, status) {
      if (status !== 'abort') {
        showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
      }
    }).always(function () {
      layer.close(loadingIndex);
    });
  }

  function renderProgressRows(data) {
    var rows = [];
    var list = data.list || {};
    var shopUrl = data.shopurl ? ' href="' + escapeHtml(data.shopurl) + '" target="_blank" rel="noreferrer"' : '';

    rows.push('<div class="admin-order-progress-head">\u4ee5\u4e0b\u6570\u636e\u6765\u81ea <strong>' + escapeHtml(data.domain) + '</strong> <span>\u5546\u54c1ID\uff1a<a' + shopUrl + '>' + escapeHtml(data.shopid) + '</a></span></div>');

    if (typeof list.order_state !== 'undefined' && typeof list.now_num !== 'undefined') {
      rows.push('<table class="table table-bordered admin-order-progress-table"><tbody>');
      rows.push('<tr><th>\u8ba2\u5355ID</th><td>' + escapeHtml(list.orderid) + '</td><th>\u8ba2\u5355\u72b6\u6001</th><td>' + escapeHtml(list.order_state) + '</td></tr>');
      rows.push('<tr><th>\u4e0b\u5355\u6570\u91cf</th><td>' + escapeHtml(list.num) + '</td><th>\u4e0b\u5355\u65f6\u95f4</th><td>' + escapeHtml(list.add_time) + '</td></tr>');
      rows.push('<tr><th>\u521d\u59cb\u6570\u91cf</th><td>' + escapeHtml(list.start_num) + '</td><th>\u5f53\u524d\u6570\u91cf</th><td>' + escapeHtml(list.now_num) + '</td></tr>');
      rows.push('</tbody></table>');
      return rows.join('');
    }

    rows.push('<table class="table table-bordered admin-order-progress-table"><tbody>');
    $.each(list, function (key, value) {
      rows.push('<tr><th>' + escapeHtml(key) + '</th><td>' + escapeHtml(value) + '</td></tr>');
    });
    rows.push('</tbody></table>');
    return rows.join('');
  }

  function renderOrderDetailDialog(id, detailHtml) {
    return renderDialog({
      modifier: 'admin-order-dialog--detail',
      eyebrow: 'Order Detail',
      heading: '\u8ba2\u5355\u8be6\u60c5',
      description: '\u67e5\u770b\u8ba2\u5355\u57fa\u7840\u4fe1\u606f\uff0c\u5e76\u4ece\u8fd9\u91cc\u5feb\u901f\u8df3\u8f6c\u5230\u7f16\u8f91\u3001\u6539\u6570\u91cf\u6216\u5bf9\u63a5\u8fdb\u5ea6\u3002',
      meta: [{ label: '\u8ba2\u5355 ID', value: '#' + id }],
      body: '<ul class="list-group admin-order-detail-list">' + detailHtml + '</ul>',
      footer: [
        '<button type="button" class="btn btn-default js-dialog-order-edit" data-order-id="', id, '"><i class="fa fa-pencil"></i> \u7f16\u8f91\u6570\u636e</button>',
        '<button type="button" class="btn btn-default js-dialog-order-num" data-order-id="', id, '"><i class="fa fa-sort-numeric-asc"></i> \u4fee\u6539\u6570\u91cf</button>',
        '<button type="button" class="btn btn-primary js-dialog-order-progress" data-order-id="', id, '"><i class="fa fa-line-chart"></i> \u67e5\u770b\u8fdb\u5ea6</button>'
      ].join('')
    });
  }

  function renderRefundDialog(id, money) {
    return renderDialog({
      modifier: 'admin-order-dialog--refund',
      eyebrow: 'Refund',
      heading: '\u9000\u6b3e\u5904\u7406',
      description: '\u8bf7\u786e\u8ba4\u672c\u6b21\u9000\u6b3e\u91d1\u989d\uff0c\u7cfb\u7edf\u4f1a\u6309\u5f53\u524d\u8ba2\u5355\u72b6\u6001\u6267\u884c\u9000\u6b3e\u903b\u8f91\u3002',
      meta: [{ label: '\u8ba2\u5355 ID', value: '#' + id }, { label: '\u5efa\u8bae\u91d1\u989d', value: '\uffe5' + money }],
      body: [
        '<div class="admin-order-dialog__field">',
        '<label for="refundMoneyInput">\u9000\u6b3e\u91d1\u989d</label>',
        '<div class="admin-order-dialog__money-input">',
        '<span>\uffe5</span>',
        '<input type="text" id="refundMoneyInput" class="form-control js-refund-money" value="', escapeHtml(money), '" autocomplete="off">',
        '</div>',
        '<p class="admin-order-dialog__hint">\u5982\u679c\u9700\u8981\u5168\u989d\u9000\u6b3e\uff0c\u4fdd\u6301\u9ed8\u8ba4\u91d1\u989d\u5373\u53ef\u3002</p>',
        '</div>'
      ].join(''),
      footer: [
        '<button type="button" class="btn btn-default js-dialog-cancel"><i class="fa fa-times"></i> \u53d6\u6d88</button>',
        '<button type="button" class="btn btn-danger js-dialog-submit-refund" data-order-id="', id, '"><i class="fa fa-credit-card"></i> \u786e\u8ba4\u9000\u6b3e</button>'
      ].join('')
    });
  }

  function renderResultDialog(id, title, value) {
    return renderDialog({
      modifier: 'admin-order-dialog--result',
      eyebrow: 'Order Note',
      heading: title,
      description: '\u586b\u5199\u8ba2\u5355\u7ed3\u679c\u6216\u5907\u6ce8\uff0c\u4fbf\u4e8e\u540e\u7eed\u6392\u67e5\u548c\u4eba\u5de5\u5904\u7406\u3002',
      meta: [{ label: '\u8ba2\u5355 ID', value: '#' + id }],
      body: [
        '<div class="admin-order-dialog__field">',
        '<label for="orderResultInput">', title, '</label>',
        '<textarea id="orderResultInput" class="form-control js-order-result-input" rows="6">', escapeHtml(value || ''), '</textarea>',
        '<p class="admin-order-dialog__hint">\u652f\u6301\u7b80\u77ed\u8bf4\u660e\uff0c\u4f1a\u4fdd\u5b58\u4e3a\u5f53\u524d\u8ba2\u5355\u7684\u5907\u6ce8\u6216\u7ed3\u679c\u3002</p>',
        '</div>'
      ].join(''),
      footer: [
        '<button type="button" class="btn btn-default js-dialog-cancel"><i class="fa fa-times"></i> \u53d6\u6d88</button>',
        '<button type="button" class="btn btn-primary js-dialog-save-result" data-order-id="', id, '" data-result-title="', escapeHtml(title), '"><i class="fa fa-save"></i> \u4fdd\u5b58\u5185\u5bb9</button>'
      ].join('')
    });
  }

  function renderBatchDialog(actionLabel, selectedCount) {
    return renderDialog({
      modifier: 'admin-order-dialog--batch',
      eyebrow: 'Batch Action',
      heading: '\u6279\u91cf\u64cd\u4f5c\u786e\u8ba4',
      description: '\u5373\u5c06\u5bf9\u5df2\u9009\u7684\u8ba2\u5355\u6267\u884c\u7edf\u4e00\u5904\u7406\uff0c\u8bf7\u5148\u786e\u8ba4\u64cd\u4f5c\u7c7b\u578b\u548c\u9009\u4e2d\u6570\u91cf\u3002',
      meta: [{ label: '\u64cd\u4f5c\u5185\u5bb9', value: actionLabel }, { label: '\u9009\u4e2d\u8ba2\u5355', value: String(selectedCount) + ' \u6761' }],
      footer: [
        '<button type="button" class="btn btn-default js-dialog-cancel"><i class="fa fa-times"></i> \u53d6\u6d88</button>',
        '<button type="button" class="btn btn-primary js-dialog-submit-batch"><i class="fa fa-check"></i> \u786e\u8ba4\u6267\u884c</button>'
      ].join('')
    });
  }

  function renderResubmitConfirmDialog(id) {
    return renderDialog({
      modifier: 'admin-order-dialog--resubmit',
      eyebrow: 'Resubmit',
      heading: '\u8865\u5355\u786e\u8ba4',
      description: '\u7cfb\u7edf\u4f1a\u5c1d\u8bd5\u91cd\u65b0\u5411\u5bf9\u63a5\u65b9\u63d0\u4ea4\u5f53\u524d\u8ba2\u5355\uff0c\u9002\u7528\u4e8e\u5bf9\u63a5\u5931\u8d25\u6216\u672a\u53d1\u5361\u7684\u60c5\u51b5\u3002',
      meta: [{ label: '\u8ba2\u5355 ID', value: '#' + id }],
      footer: [
        '<button type="button" class="btn btn-default js-dialog-cancel"><i class="fa fa-times"></i> \u53d6\u6d88</button>',
        '<button type="button" class="btn btn-primary js-dialog-confirm-resubmit" data-order-id="', id, '"><i class="fa fa-repeat"></i> \u7acb\u5373\u8865\u5355</button>'
      ].join('')
    });
  }

  function renderResubmitProgressDialog(total) {
    return renderDialog({
      modifier: 'admin-order-dialog--progress',
      eyebrow: 'Bulk Resubmit',
      heading: '\u4e00\u952e\u8865\u5355\u8fdb\u5ea6',
      description: '\u7cfb\u7edf\u4f1a\u4f9d\u6b21\u91cd\u65b0\u63d0\u4ea4\u5f53\u524d\u9875\u53ef\u8865\u5355\u7684\u8ba2\u5355\uff0c\u5904\u7406\u8fc7\u7a0b\u4e2d\u8bf7\u4e0d\u8981\u79bb\u5f00\u672c\u9875\u3002',
      meta: [
        { label: '\u603b\u4efb\u52a1\u6570', value: String(total) + ' \u6761' },
        { label: '\u5df2\u6210\u529f', value: '0 \u6761' },
        { label: '\u5df2\u5931\u8d25', value: '0 \u6761' }
      ],
      body: [
        '<div class="admin-order-progress-live">',
        '<div class="admin-order-progress-live__bar"><span class="js-resubmit-progress-bar" style="width:0%"></span></div>',
        '<div class="admin-order-progress-live__stats">',
        '<div><span>\u5f53\u524d\u8ba2\u5355</span><strong class="js-resubmit-current">--</strong></div>',
        '<div><span>\u5904\u7406\u8fdb\u5ea6</span><strong><span class="js-resubmit-done">0</span> / <span class="js-resubmit-total">', total, '</span></strong></div>',
        '</div>',
        '</div>'
      ].join(''),
      footer: '<button type="button" class="btn btn-default js-dialog-cancel-progress" disabled><i class="fa fa-spinner fa-spin"></i> \u6b63\u5728\u5904\u7406</button>'
    });
  }

  function getLayerContainer(index) {
    return $('#layui-layer' + index);
  }

  function updateResubmitProgress(index, currentId) {
    var $layer = getLayerContainer(index);
    var done = orderListState.ordersucc + orderListState.orderfail;
    var percent = orderListState.resubmitTotal > 0 ? Math.round((done / orderListState.resubmitTotal) * 100) : 0;

    if (!$layer.length) {
      return;
    }

    $layer.find('.admin-order-dialog__meta-item').eq(1).find('strong').text(orderListState.ordersucc + ' \u6761');
    $layer.find('.admin-order-dialog__meta-item').eq(2).find('strong').text(orderListState.orderfail + ' \u6761');
    $layer.find('.js-resubmit-current').text(currentId ? ('#' + currentId) : '--');
    $layer.find('.js-resubmit-done').text(done);
    $layer.find('.js-resubmit-progress-bar').css('width', percent + '%');
  }

  function finishResubmitProgress(index) {
    var $layer = getLayerContainer(index);

    if (!$layer.length) {
      loadTable(getCurrentListQuery());
      return;
    }

    updateResubmitProgress(index, '');
    $layer.find('.admin-order-dialog__desc').text('\u4e00\u952e\u8865\u5355\u5df2\u5b8c\u6210\uff0c\u4f60\u53ef\u4ee5\u5173\u95ed\u7a97\u53e3\u540e\u5237\u65b0\u5217\u8868\u67e5\u770b\u6700\u65b0\u7ed3\u679c\u3002');
    $layer.find('.js-dialog-cancel-progress')
      .prop('disabled', false)
      .removeClass('btn-default')
      .addClass('btn-primary')
      .html('<i class="fa fa-check"></i> \u5173\u95ed\u5e76\u5237\u65b0')
      .off('click')
      .on('click', function () {
        layer.close(index);
        loadTable(getCurrentListQuery());
      });
  }

  function showStatus(id) {
    requestJson({
      url: 'ajax_order.php?act=showStatus&id=' + encodeURIComponent(id)
    }).done(function (data) {
      if (data.code === 0) {
        openHtmlModal('\u8ba2\u5355\u8fdb\u5ea6\u67e5\u8be2', renderProgressRows(data), 'large');
      } else {
        layer.alert(data.msg || '\u83b7\u53d6\u6570\u636e\u5931\u8d25', { shadeClose: true });
      }
    }).fail(function () {
      showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
    });
  }

  function confirmResubmit(id) {
    openHtmlModal('\u8865\u5355\u786e\u8ba4', renderResubmitConfirmDialog(id), 'compact', {
      onSuccess: function (layero, index) {
        $(layero).on('click', '.js-dialog-cancel', function () {
          layer.close(index);
        });
        $(layero).on('click', '.js-dialog-confirm-resubmit', function () {
          var loadingIndex = openLoading();
          sendResubmitRequest(id, {
            onSuccess: function (data) {
              layer.close(loadingIndex);
              if (data.code === 0) {
                layer.close(index);
                layer.msg(data.msg || '\u8865\u5355\u6210\u529f', { shadeClose: true });
                loadTable(getCurrentListQuery());
              } else {
                layer.alert(data.msg || '\u8865\u5355\u5931\u8d25', { shadeClose: true });
              }
            },
            onError: function () {
              layer.close(loadingIndex);
              showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
            }
          });
        });
      }
    });
  }

  function showOrder(id) {
    requestJson({
      url: 'ajax_order.php?act=order&id=' + encodeURIComponent(id)
    }).done(function (data) {
      if (data.code === 0) {
        openHtmlModal('\u8ba2\u5355\u8be6\u60c5', renderOrderDetailDialog(id, data.data), 'large', {
          onSuccess: function (layero, index) {
            $(layero).on('click', '.js-dialog-order-edit', function () {
              layer.close(index);
              inputOrder(id);
            });
            $(layero).on('click', '.js-dialog-order-num', function () {
              layer.close(index);
              inputNum(id);
            });
            $(layero).on('click', '.js-dialog-order-progress', function () {
              layer.close(index);
              showStatus(id);
            });
          }
        });
      } else {
        layer.alert(data.msg || '\u83b7\u53d6\u8ba2\u5355\u5931\u8d25');
      }
    }).fail(function () {
      showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
    });
  }

  function inputOrder(id) {
    requestJson({
      url: 'ajax_order.php?act=order2&id=' + encodeURIComponent(id)
    }).done(function (data) {
      if (data.code === 0) {
        openHtmlModal('\u4fee\u6539\u4e0b\u5355\u6570\u636e', '<div class="admin-order-form-dialog">' + data.data + '</div>', 'compact');
      } else {
        layer.alert(data.msg || '\u83b7\u53d6\u8868\u5355\u5931\u8d25');
      }
    }).fail(function () {
      showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
    });
  }

  function inputNum(id) {
    requestJson({
      url: 'ajax_order.php?act=order3&id=' + encodeURIComponent(id)
    }).done(function (data) {
      if (data.code === 0) {
        openHtmlModal('\u4fee\u6539\u4efd\u6570', '<div class="admin-order-form-dialog">' + data.data + '</div>', 'compact');
      } else {
        layer.alert(data.msg || '\u83b7\u53d6\u8868\u5355\u5931\u8d25');
      }
    }).fail(function () {
      showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
    });
  }

  function refund(id) {
    requestJson({
      type: 'POST',
      url: 'ajax_order.php?act=getmoney',
      data: { id: id }
    }).done(function (data) {
      if (data.code === 0) {
        openHtmlModal('\u9000\u6b3e\u5904\u7406', renderRefundDialog(id, data.money), 'compact', {
          onSuccess: function (layero, index) {
            var $input = $(layero).find('.js-refund-money');
            $input.focus().select();

            $(layero).on('click', '.js-dialog-cancel', function () {
              layer.close(index);
            });
            $(layero).on('click', '.js-dialog-submit-refund', function () {
              var money = $.trim($input.val());
              if (!money) {
                layer.msg('\u8bf7\u5148\u586b\u5199\u9000\u6b3e\u91d1\u989d');
                $input.focus();
                return;
              }

              requestJson({
                type: 'POST',
                url: 'ajax_order.php?act=refund',
                data: { id: id, money: money }
              }).done(function (response) {
                if (response.code === 0) {
                  layer.close(index);
                  layer.alert(response.msg || '\u9000\u6b3e\u6210\u529f', { icon: 1, shadeClose: true }, function () {
                    layer.closeAll();
                    loadTable(getCurrentListQuery());
                  });
                } else {
                  layer.alert(response.msg || '\u9000\u6b3e\u5931\u8d25', { shadeClose: true });
                }
              }).fail(function () {
                showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
              });
            });
            $(layero).on('keydown', '.js-refund-money', function (event) {
              if (event.keyCode === 13) {
                $(layero).find('.js-dialog-submit-refund').trigger('click');
              }
            });
          }
        });
      } else {
        layer.alert(data.msg || '\u83b7\u53d6\u9000\u6b3e\u91d1\u989d\u5931\u8d25');
      }
    }).fail(function () {
      showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
    });
  }

  function submitStatusChange(id, status) {
    requestJson({
      type: 'GET',
      url: 'ajax_order.php',
      data: {
        act: 'setStatus',
        name: id,
        status: status
      }
    }).done(function (ret) {
      if (ret.code !== 200) {
        layer.alert(ret.msg || '\u64cd\u4f5c\u5931\u8d25');
        return;
      }
      layer.msg('\u64cd\u4f5c\u5df2\u6267\u884c');
      loadTable(getCurrentListQuery());
    }).fail(function () {
      showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
    });
  }

  function setStatus(id, status) {
    var confirmMap;

    if (!status) {
      return;
    }
    if (String(status) === '6') {
      refund(id);
      return;
    }
    if (String(status) === '5') {
      openConfirmDialog({
        title: '\u5220\u9664\u8ba2\u5355',
        heading: '\u786e\u5b9a\u5220\u9664\u5f53\u524d\u8ba2\u5355\u5417\uff1f',
        description: '\u5220\u9664\u540e\u5c06\u4ece\u8ba2\u5355\u5217\u8868\u79fb\u9664\uff0c\u8bf7\u5148\u786e\u8ba4\u5df2\u4e0d\u518d\u9700\u8981\u4fdd\u7559\u8fd9\u6761\u8bb0\u5f55\u3002',
        meta: [{ label: '\u8ba2\u5355 ID', value: '#' + id }],
        confirmText: '\u786e\u8ba4\u5220\u9664',
        confirmClass: 'btn-danger',
        confirmIcon: 'fa-trash',
        onConfirm: function (layero, index) {
          layer.close(index);
          submitStatusChange(id, status);
        }
      });
      return;
    }

    confirmMap = {
      '0': '\u5f85\u5904\u7406',
      '1': '\u5df2\u5b8c\u6210',
      '2': '\u6b63\u5728\u5904\u7406',
      '3': '\u5f02\u5e38',
      '4': '\u5df2\u9000\u5355'
    };

    submitStatusChange(id, status);
  }

  function setResult(id, title) {
    requestJson({
      type: 'POST',
      url: 'ajax_order.php?act=result',
      data: { id: id }
    }).done(function (data) {
      var dialogTitle = title || '\u8ba2\u5355\u5907\u6ce8';

      if (data.code !== 0) {
        layer.alert(data.msg || '\u83b7\u53d6\u8ba2\u5355\u7ed3\u679c\u5931\u8d25');
        return;
      }

      openHtmlModal(dialogTitle, renderResultDialog(id, dialogTitle, data.result || ''), 'compact', {
        onSuccess: function (layero, index) {
          var $textarea = $(layero).find('.js-order-result-input');
          $textarea.focus();

          $(layero).on('click', '.js-dialog-cancel', function () {
            layer.close(index);
          });
          $(layero).on('click', '.js-dialog-save-result', function () {
            var text = $textarea.val();
            requestJson({
              type: 'POST',
              url: 'ajax_order.php?act=setresult',
              data: { id: id, result: text }
            }).done(function (response) {
              if (response.code === 0) {
                layer.close(index);
                layer.msg('\u4fdd\u5b58\u6210\u529f', { time: 800, icon: 1 });
                loadTable(getCurrentListQuery());
              } else {
                layer.alert(response.msg || '\u4fdd\u5b58\u5931\u8d25', { shadeClose: true });
              }
            }).fail(function () {
              showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
            });
          });
        }
      });
    }).fail(function () {
      showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
    });
  }

  function collectOrderInputs() {
    var payload = {};

    $('[id^="inputvalue"]').each(function () {
      var id = $(this).attr('id');
      payload[id] = $(this).val();
    });

    return payload;
  }

  function saveOrder(id) {
    var payload = collectOrderInputs();
    var hasError = false;

    $.each(payload, function (_, value) {
      if (value === '') {
        hasError = true;
        return false;
      }
    });

    if (hasError || !payload.inputvalue) {
      layer.alert('\u8bf7\u786e\u4fdd\u5fc5\u586b\u9879\u4e0d\u4e3a\u7a7a');
      return false;
    }

    $('#save').val('Loading');
    requestJson({
      type: 'POST',
      url: 'ajax_order.php?act=editOrder',
      data: {
        id: id,
        inputvalue: payload.inputvalue || '',
        inputvalue2: payload.inputvalue2 || '',
        inputvalue3: payload.inputvalue3 || '',
        inputvalue4: payload.inputvalue4 || '',
        inputvalue5: payload.inputvalue5 || ''
      }
    }).done(function (data) {
      $('#save').val('\u4fdd\u5b58');
      if (data.code === 0) {
        layer.msg('\u4fdd\u5b58\u6210\u529f');
        layer.closeAll();
        loadTable(getCurrentListQuery());
      } else {
        layer.alert(data.msg || '\u4fdd\u5b58\u5931\u8d25');
      }
    }).fail(function () {
      $('#save').val('\u4fdd\u5b58');
      showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
    });

    return false;
  }

  function saveOrderNum(id) {
    var num = $.trim($('#num').val());
    if (!num) {
      layer.alert('\u8bf7\u586b\u5199\u6709\u6548\u7684\u6570\u91cf');
      return false;
    }

    $('#save').val('Loading');
    requestJson({
      type: 'POST',
      url: 'ajax_order.php?act=editOrderNum',
      data: { id: id, num: num }
    }).done(function (data) {
      $('#save').val('\u4fdd\u5b58');
      if (data.code === 0) {
        layer.msg('\u4fdd\u5b58\u6210\u529f');
        layer.closeAll();
        loadTable(getCurrentListQuery());
      } else {
        layer.alert(data.msg || '\u4fdd\u5b58\u5931\u8d25');
      }
    }).fail(function () {
      $('#save').val('\u4fdd\u5b58');
      showErrorMessage('\u670d\u52a1\u5668\u9519\u8bef');
    });

    return false;
  }

  function operation() {
    var $form = $('#orderBatchForm');
    var action = $.trim($form.find('select[name="status"]').val());
    var actionLabel = $.trim($form.find('select[name="status"] option:selected').text());
    var selectedCount = $form.find('.js-order-check:checked').length;

    if (!$form.length) {
      return false;
    }
    if (!action) {
      layer.alert('\u8bf7\u5148\u9009\u62e9\u6279\u91cf\u64cd\u4f5c');
      return false;
    }
    if (selectedCount <= 0) {
      layer.alert('\u8bf7\u5148\u9009\u62e9\u8981\u5904\u7406\u7684\u8ba2\u5355');
      return false;
    }

    openHtmlModal('\u6279\u91cf\u64cd\u4f5c\u786e\u8ba4', renderBatchDialog(actionLabel, selectedCount), 'compact', {
      onSuccess: function (layero, index) {
        $(layero).on('click', '.js-dialog-cancel', function () {
          layer.close(index);
        });
        $(layero).on('click', '.js-dialog-submit-batch', function () {
          requestJson({
            type: 'POST',
            url: 'ajax_order.php?act=operation',
            data: $form.serialize()
          }).done(function (data) {
            if (data.code === 0) {
              layer.close(index);
              layer.alert(data.msg || '\u64cd\u4f5c\u6210\u529f', { icon: 1 }, function () {
                layer.closeAll();
                loadTable(getCurrentListQuery());
              });
            } else {
              layer.alert(data.msg || '\u64cd\u4f5c\u5931\u8d25');
            }
          }).fail(function () {
            showErrorMessage('\u8bf7\u6c42\u8d85\u65f6');
            loadTable(getCurrentListQuery());
          });
        });
      }
    });

    return false;
  }

  function runBulkResubmit(progressIndex) {
    var $next = $('.js-order-resubmit').first();
    var orderId;

    if ($next.length <= 0) {
      finishResubmitProgress(progressIndex);
      return;
    }

    orderId = $next.data('orderId') || $next.data('orderResubmit');
    updateResubmitProgress(progressIndex, orderId);

    sendResubmitRequest(orderId, {
      onSuccess: function (data) {
        if (data.code === 0) {
          orderListState.ordersucc += 1;
        } else {
          orderListState.orderfail += 1;
        }
      },
      onError: function () {
        orderListState.orderfail += 1;
      }
    }).always(function () {
      $next.removeClass('js-order-resubmit');
      updateResubmitProgress(progressIndex, orderId);
      runBulkResubmit(progressIndex);
    });
  }

  function startBulkResubmit() {
    var total = $('.js-order-resubmit').length;
    var progressIndex;

    orderListState.ordersucc = 0;
    orderListState.orderfail = 0;
    orderListState.resubmitTotal = total;

    if (total <= 0) {
      layer.alert('\u5f53\u524d\u9875\u6ca1\u6709\u53ef\u8865\u5355\u7684\u5bf9\u63a5\u5931\u8d25\u8ba2\u5355', { icon: 0 });
      return;
    }

    openConfirmDialog({
      title: '\u4e00\u952e\u8865\u5355',
      heading: '\u786e\u5b9a\u5f00\u59cb\u6279\u91cf\u8865\u5355\u5417\uff1f',
      description: '\u7cfb\u7edf\u4f1a\u91cd\u65b0\u63d0\u4ea4\u5f53\u524d\u9875\u9762\u6240\u6709\u5bf9\u63a5\u5931\u8d25\u6216\u672a\u53d1\u5361\u7684\u8ba2\u5355\u3002',
      meta: [{ label: '\u53ef\u8865\u5355\u8ba2\u5355', value: String(total) + ' \u6761' }],
      confirmText: '\u5f00\u59cb\u8865\u5355',
      confirmIcon: 'fa-repeat',
      onConfirm: function (layero, index) {
        layer.close(index);
        progressIndex = openHtmlModal('\u4e00\u952e\u8865\u5355', renderResubmitProgressDialog(total), 'compact', {
          shadeClose: false,
          closeBtn: false,
          onSuccess: function (progressLayer, progressLayerIndex) {
            $(progressLayer).on('click', '.js-dialog-cancel-progress', function () {
              if (!$(this).prop('disabled')) {
                layer.close(progressLayerIndex);
                loadTable(getCurrentListQuery());
              }
            });
          }
        });
        runBulkResubmit(progressIndex);
      }
    });
  }

  function bindEvents() {
    $(document).on('submit', '#orderFilterForm', function (event) {
      event.preventDefault();
      loadTable(buildFilterQuery());
    });

    $(document).on('click', '#resetOrderFilters', function () {
      var $form = $('#orderFilterForm');
      $form.find('input[name="kw"]').val('');
      $form.find('input[name="starttime"]').val('');
      $form.find('input[name="endtime"]').val('');
      $form.find('select[name="type"]').val('-1');
      loadTable(getContextQuery());
    });

    $(document).on('click', '#refreshOrderList', function () {
      loadTable(getCurrentListQuery());
    });

    $(document).on('change', '#pagesize', function () {
      var params = parseQuery(getCurrentListQuery());
      orderListState.pagesize = $(this).val() || '30';
      delete params.page;
      loadTable(buildQuery(params));
    });

    $(document).on('click', '[data-page-query]', function () {
      if ($(this).closest('li').hasClass('disabled') || $(this).closest('li').hasClass('active')) {
        return;
      }
      loadTable($(this).data('pageQuery') || '');
    });

    $(document).on('click', '[data-filter-query]', function () {
      loadTable($(this).data('filterQuery') || '');
    });

    $(document).on('change', '#orderCheckAll', function () {
      $('.js-order-check').prop('checked', $(this).prop('checked'));
    });

    $(document).on('change', '.js-order-check', function () {
      syncCheckAllState();
    });

    $(document).on('click', '#orderBatchSubmit', function () {
      operation();
    });

    $(document).on('change', '.js-row-action', function () {
      var value = $(this).val();
      var id = $(this).data('orderAction');
      if (!value) {
        return;
      }
      setStatus(id, value);
      $(this).val('');
    });

    $(document).on('click', '.js-order-detail', function () {
      showOrder($(this).data('orderDetail'));
    });

    $(document).on('click', '.js-order-edit', function () {
      inputOrder($(this).data('orderEdit'));
    });

    $(document).on('click', '.js-order-num', function () {
      inputNum($(this).data('orderNum'));
    });

    $(document).on('click', '.js-order-progress', function () {
      showStatus($(this).data('orderProgress'));
    });

    $(document).on('click', '.js-order-resubmit', function () {
      confirmResubmit($(this).data('orderResubmit'));
    });

    $(document).on('click', '.js-order-result', function () {
      setResult($(this).data('orderResult'), $(this).data('resultTitle'));
    });

    $(document).on('click', '#onekeyResubmit', function () {
      startBulkResubmit();
    });
  }

  $(function () {
    orderListState.pagesize = $('#pagesize').val() || '30';

    $('.input-datepicker, .input-daterange').datepicker({
      format: 'yyyy-mm-dd',
      autoclose: true,
      clearBtn: true,
      language: 'zh-CN'
    });

    bindEvents();
    loadTable(getCurrentListQuery());
  });

  window.listTable = loadTable;
  window.searchOrder = function () { loadTable(buildFilterQuery()); return false; };
  window.clearOrder = function () { loadTable(getContextQuery()); return false; };
  window.operation = operation;
  window.showStatus = showStatus;
  window.djOrder = confirmResubmit;
  window.showOrder = showOrder;
  window.inputOrder = inputOrder;
  window.inputNum = inputNum;
  window.refund = refund;
  window.setStatus = setStatus;
  window.setResult = setResult;
  window.saveOrder = saveOrder;
  window.saveOrderNum = saveOrderNum;
  window.onekeyDj = startBulkResubmit;
})(window, jQuery);

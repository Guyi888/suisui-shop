(function (window, $) {
  'use strict';

  var config = window.adminShopListConfig || {};
  var endpoints = config.endpoints || {};
  var shopListState = {
    pagesize: parseInt(config.defaultPageSize, 10) || 30,
    request: null
  };

  var MSG = {
    loading: '\u6b63\u5728\u52a0\u8f7d\u5546\u54c1\u5217\u8868',
    loadFail: '\u5546\u54c1\u5217\u8868\u52a0\u8f7d\u5931\u8d25\uff0c\u8bf7\u5237\u65b0\u91cd\u8bd5',
    serverError: '\u670d\u52a1\u5668\u8bf7\u6c42\u5931\u8d25\uff0c\u8bf7\u7a0d\u540e\u91cd\u8bd5',
    selectGoods: '\u8bf7\u81f3\u5c11\u9009\u62e9\u4e00\u4e2a\u5546\u54c1',
    selectAction: '\u8bf7\u5148\u9009\u62e9\u6279\u91cf\u64cd\u4f5c',
    selectCategory: '\u8bf7\u5148\u9009\u62e9\u76ee\u6807\u5206\u7c7b',
    needOldName: '\u8bf7\u5148\u586b\u5199\u8981\u66ff\u6362\u7684\u65e7\u5185\u5bb9',
    needNewName: '\u8bf7\u5148\u586b\u5199\u66ff\u6362\u540e\u7684\u65b0\u5185\u5bb9',
    needPrice: '\u5546\u54c1\u552e\u4ef7\u4e0d\u80fd\u4e3a\u7a7a',
    needBasePrice: '\u542f\u7528\u6a21\u677f\u65f6\u8bf7\u5148\u8f93\u5165\u6210\u672c\u4ef7',
    invalidStock: '\u5e93\u5b58\u53ea\u80fd\u4e3a\u7a7a\u6216\u975e\u8d1f\u6574\u6570',
    saveSuccess: '\u4fdd\u5b58\u6210\u529f',
    actionSuccess: '\u64cd\u4f5c\u6210\u529f',
    manualMode: '\u4e0d\u4f7f\u7528\u52a0\u4ef7\u6a21\u677f',
    cancel: '\u53d6\u6d88',
    confirm: '\u786e\u8ba4',
    delete: '\u786e\u8ba4\u5220\u9664',
    deleteDesc: '\u5220\u9664\u540e\u4f1a\u4ece\u5546\u54c1\u5217\u8868\u548c\u5173\u8054\u8ba2\u5355\u8bb0\u5f55\u4e2d\u79fb\u9664\uff0c\u6b64\u64cd\u4f5c\u65e0\u6cd5\u64a4\u56de\u3002',
    bulkDeleteDesc: '\u6279\u91cf\u5220\u9664\u4f1a\u540c\u65f6\u79fb\u9664\u9009\u4e2d\u5546\u54c1\u53ca\u5176\u76f8\u5173\u8ba2\u5355\u8bb0\u5f55\uff0c\u8bf7\u5148\u786e\u8ba4\u65e0\u8bef\u3002',
    moveDesc: '\u7cfb\u7edf\u4f1a\u628a\u9009\u4e2d\u5546\u54c1\u79fb\u52a8\u5230\u65b0\u5206\u7c7b\uff0c\u524d\u53f0\u5206\u7c7b\u5f52\u5c5e\u4e5f\u4f1a\u540c\u6b65\u53d8\u66f4\u3002',
    resetSortDesc: '\u91cd\u7f6e\u540e\u8be5\u5206\u7c7b\u4e0b\u7684\u5546\u54c1\u4f1a\u56de\u5230\u521b\u5efa\u65f6\u95f4\u5012\u5e8f\u6392\u5217\uff0c\u9002\u7528\u4e8e\u6392\u5e8f\u9519\u4f4d\u65f6\u5feb\u901f\u6062\u590d\u3002',
    priceHeading: '\u8c03\u6574\u5546\u54c1\u4ef7\u683c',
    priceDesc: '\u53ef\u4ee5\u5728\u8fd9\u91cc\u5207\u6362\u52a0\u4ef7\u6a21\u677f\uff0c\u6216\u76f4\u63a5\u7ef4\u62a4\u96f6\u552e\u3001\u666e\u53ca\u3001\u4e13\u4e1a\u4e09\u6863\u4ef7\u683c\u3002',
    stockHeading: '\u8bbe\u7f6e\u5546\u54c1\u5e93\u5b58',
    stockDesc: '\u7559\u7a7a\u8868\u793a\u65e0\u9650\u5e93\u5b58\uff0c\u53d1\u5361\u5546\u54c1\u4f1a\u8df3\u8f6c\u5230\u5361\u5bc6\u5e93\u5b58\u9875\u9762\u3002',
    detailHeading: '\u5546\u54c1\u8be6\u60c5',
    detailDesc: '\u8fd9\u91cc\u663e\u793a\u5f53\u524d\u5546\u54c1\u7684\u57fa\u7840\u8bbe\u7f6e\u3001\u4ef7\u683c\u4e0e\u524d\u53f0\u5165\u53e3\u4fe1\u606f\uff0c\u65b9\u4fbf\u4e0d\u8df3\u9875\u5feb\u901f\u786e\u8ba4\u3002',
    replaceNameHeading: '\u6279\u91cf\u66ff\u6362\u5546\u54c1\u540d\u79f0',
    replaceNameDesc: '\u7cfb\u7edf\u4f1a\u5728\u6240\u6709\u5546\u54c1\u4e2d\u6279\u91cf\u66ff\u6362\u5339\u914d\u5185\u5bb9\uff0c\u9002\u5408\u7edf\u4e00\u54c1\u724c\u8bcd\u3001\u7248\u672c\u8bcd\u6216\u98ce\u683c\u8bcd\u3002',
    replaceInputsHeading: '\u6279\u91cf\u66ff\u6362\u8f93\u5165\u6846\u6807\u9898',
    replaceInputsDesc: '\u7cfb\u7edf\u4f1a\u540c\u65f6\u5904\u7406 input \u4e0e inputs \u5b57\u6bb5\uff0c\u9002\u7528\u4e8e\u524d\u53f0\u4e0b\u5355\u9879\u540d\u7edf\u4e00\u8c03\u6574\u3002',
    bulkPriceHeading: '\u6279\u91cf\u4fee\u6539\u52a0\u4ef7\u6a21\u677f',
    bulkPriceDesc: '\u4e3a\u9009\u4e2d\u5546\u54c1\u7edf\u4e00\u66ff\u6362\u52a0\u4ef7\u6a21\u677f\uff0c\u5df2\u9009\u4e2d\u7684\u5546\u54c1\u4f1a\u540c\u65f6\u66f4\u65b0\u4ef7\u683c\u7b56\u7565\u3002',
    bulkStockHeading: '\u6279\u91cf\u8bbe\u7f6e\u5e93\u5b58',
    bulkStockDesc: '\u8be5\u64cd\u4f5c\u4f1a\u5bf9\u9009\u4e2d\u7684\u666e\u901a\u5546\u54c1\u7edf\u4e00\u751f\u6548\uff0c\u53d1\u5361\u5546\u54c1\u4ecd\u7531\u5361\u5bc6\u5e93\u5b58\u63a7\u5236\u3002',
    batchHeading: '\u6279\u91cf\u64cd\u4f5c\u786e\u8ba4',
    moveHeading: '\u79fb\u52a8\u5206\u7c7b',
    resetSortHeading: '\u91cd\u7f6e\u5206\u7c7b\u6392\u5e8f',
    openCardStock: '\u53d1\u5361\u5546\u54c1\u8bf7\u5728\u5361\u5bc6\u5e93\u5b58\u9875\u9762\u4fee\u6539\u6570\u91cf',
    noneText: '\u6682\u65e0',
    visible: '\u524d\u53f0\u663e\u793a',
    hidden: '\u524d\u53f0\u9690\u85cf',
    online: '\u4e0a\u67b6\u4e2d',
    offline: '\u5df2\u4e0b\u67b6',
    retail: '\u96f6\u552e',
    standard: '\u666e\u53ca',
    premium: '\u4e13\u4e1a',
    goodsCount: '\u5546\u54c1\u6570',
    goodsId: '\u5546\u54c1 ID',
    selected: '\u5df2\u9009',
    preview: '\u524d\u53f0\u9884\u89c8',
    edit: '\u7f16\u8f91\u5546\u54c1',
    orders: '\u67e5\u770b\u8ba2\u5355',
    changePrice: '\u8c03\u6574\u4ef7\u683c',
    changeStock: '\u4fee\u6539\u5e93\u5b58',
    save: '\u4fdd\u5b58',
    currentRule: '\u5f53\u524d\u6a21\u677f',
    basePrice: '\u6210\u672c\u4ef7',
    emptyState: '\u6ca1\u6709\u627e\u5230\u5339\u914d\u5546\u54c1'
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
    var target = normalizeQuery(query) ? ('./shoplist.php?' + normalizeQuery(query)) : './shoplist.php';
    if (window.history && window.history.replaceState) {
      window.history.replaceState({}, '', target);
    }
  }

  function escapeHtml(value) {
    return $('<div/>').text(value === null || value === undefined ? '' : String(value)).html();
  }

  function showMessage(message, icon) {
    layer.msg(message || MSG.serverError, { icon: icon || 0, time: 1600 });
  }

  function showAlert(message, options, callback) {
    layer.alert(message, $.extend({ shadeClose: true }, options || {}), callback);
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
      return ['860px', 'auto'];
    }
    if (size === 'compact') {
      return ['540px', 'auto'];
    }
    return ['680px', 'auto'];
  }

  function renderDialogMeta(metaItems) {
    var html = [];

    if (!metaItems || !metaItems.length) {
      return '';
    }

    html.push('<div class="admin-shop-dialog__meta">');
    $.each(metaItems, function (_, item) {
      html.push('<div class="admin-shop-dialog__meta-item">');
      html.push('<span>' + escapeHtml(item.label || '') + '</span>');
      html.push('<strong>' + escapeHtml(item.value || '') + '</strong>');
      html.push('</div>');
    });
    html.push('</div>');
    return html.join('');
  }

  function renderDialog(configDialog) {
    var html = [];

    html.push('<div class="admin-shop-dialog' + (configDialog.modifier ? (' ' + configDialog.modifier) : '') + '">');
    if (configDialog.eyebrow) {
      html.push('<p class="admin-shop-dialog__eyebrow">' + configDialog.eyebrow + '</p>');
    }
    if (configDialog.heading) {
      html.push('<h3 class="admin-shop-dialog__title">' + configDialog.heading + '</h3>');
    }
    if (configDialog.description) {
      html.push('<p class="admin-shop-dialog__desc">' + configDialog.description + '</p>');
    }
    html.push(renderDialogMeta(configDialog.meta));
    if (configDialog.body) {
      html.push('<div class="admin-shop-dialog__body">' + configDialog.body + '</div>');
    }
    if (configDialog.footer) {
      html.push('<div class="admin-shop-dialog__footer">' + configDialog.footer + '</div>');
    }
    html.push('</div>');

    return html.join('');
  }

  function openHtmlModal(title, content, size, options) {
    options = options || {};

    return layer.open({
      type: 1,
      title: title,
      skin: 'layui-layer-rim admin-shop-layer' + (options.skinClass ? (' ' + options.skinClass) : ''),
      area: getModalArea(size),
      shadeClose: options.shadeClose !== false,
      closeBtn: options.closeBtn === false ? 0 : 1,
      maxHeight: '90vh',
      content: '<div class="admin-shop-modal-shell">' + content + '</div>',
      success: function (layero, index) {
        if (typeof options.onSuccess === 'function') {
          options.onSuccess(layero, index);
        }
      }
    });
  }

  function openConfirmDialog(configDialog) {
    return openHtmlModal(configDialog.title || MSG.confirm, renderDialog({
      eyebrow: configDialog.eyebrow || 'Action Confirm',
      heading: configDialog.heading,
      description: configDialog.description,
      meta: configDialog.meta || [],
      footer: [
        '<button type="button" class="btn btn-default js-dialog-cancel"><i class="fa fa-times"></i> ',
        configDialog.cancelText || MSG.cancel,
        '</button>',
        '<button type="button" class="btn ',
        configDialog.confirmClass || 'btn-primary',
        ' js-dialog-confirm"><i class="fa ',
        configDialog.confirmIcon || 'fa-check',
        '"></i> ',
        configDialog.confirmText || MSG.confirm,
        '</button>'
      ].join('')
    }), configDialog.size || 'compact', {
      shadeClose: configDialog.shadeClose !== false,
      onSuccess: function (layero, index) {
        $(layero).on('click', '.js-dialog-cancel', function () {
          layer.close(index);
        });
        $(layero).on('click', '.js-dialog-confirm', function () {
          if (typeof configDialog.onConfirm === 'function') {
            configDialog.onConfirm(layero, index);
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

  function syncFilterForm(query) {
    var params = parseQuery(query);
    var $form = $('#shopFilterForm');

    if (!$form.length) {
      return;
    }

    $form.find('input[name="kw"]').val(params.kw || '');
    $form.find('input[name="tid"]').val(params.tid || '');
    $form.find('select[name="cid"]').val(params.cid || '');
    $form.find('select[name="type"]').val(params.type || '');
    $form.find('select[name="status"]').val(params.status || '');
    $form.find('select[name="prid"]').val(params.prid || '0');
    $form.find('select[name="num"]').val(params.num || String(shopListState.pagesize));
  }

  function buildFilterQuery(resetPage) {
    var $form = $('#shopFilterForm');
    var params = {};

    if (!$form.length) {
      return '';
    }

    $.each($form.serializeArray(), function (_, item) {
      if (!item.name) {
        return;
      }
      if (item.name === 'cid' && item.value === '') {
        return;
      }
      if (item.name === 'type' && item.value === '') {
        return;
      }
      if (item.name === 'status' && item.value === '') {
        return;
      }
      if (item.name === 'prid' && (item.value === '' || item.value === '0')) {
        return;
      }
      if (item.value === '') {
        return;
      }
      params[item.name] = item.value;
    });

    params.num = String(shopListState.pagesize);

    if (resetPage) {
      delete params.page;
    }

    return buildQuery(params);
  }

  function renderLoadingState() {
    return '<div class="admin-shop-loading"><i class="fa fa-spinner fa-spin"></i><span>' + MSG.loading + '</span></div>';
  }

  function renderEmptyState(message) {
    return '<div class="admin-shop-empty"><i class="fa fa-cubes"></i><strong>' + escapeHtml(message || MSG.emptyState) + '</strong></div>';
  }

  function loadTable(query) {
    var params = parseQuery(query !== undefined ? query : getCurrentListQuery());
    var normalized;
    var requestUrl = endpoints.table || './shoplist-table.php';

    params.num = String(shopListState.pagesize);
    normalized = buildQuery(params);
    updateHistory(normalized);
    syncFilterForm(normalized);

    if (shopListState.request && shopListState.request.readyState !== 4) {
      shopListState.request.abort();
    }

    $('#shopListTable').html(renderLoadingState());
    shopListState.request = $.ajax({
      type: 'GET',
      url: requestUrl + (normalized ? ('?' + normalized) : ''),
      dataType: 'html',
      cache: false
    }).done(function (html) {
      $('#shopListTable').html(html || renderEmptyState(MSG.emptyState));
      syncCheckAllState();
      updateSelectedCount();
    }).fail(function (xhr, status) {
      if (status !== 'abort') {
        $('#shopListTable').html(renderEmptyState(MSG.loadFail));
        showMessage(MSG.loadFail, 2);
      }
    });
  }

  function getCheckedIds() {
    var ids = [];
    $('#shopListForm').find('[data-shop-checkbox]:checked').each(function () {
      ids.push($(this).val());
    });
    return ids;
  }

  function updateSelectedCount() {
    var count = getCheckedIds().length;
    $('[data-shop-selected-count]').text('\u5df2\u9009 ' + count + ' \u9879');
  }

  function syncCheckAllState() {
    var $all = $('[data-shop-check-all]');
    var $items = $('#shopListForm').find('[data-shop-checkbox]');
    var checked = $items.filter(':checked').length;

    if (!$all.length) {
      return;
    }

    $all.prop('checked', $items.length > 0 && checked === $items.length);
    $all.prop('indeterminate', checked > 0 && checked < $items.length);
  }

  function getRow(tid) {
    return $('[data-shop-row][data-tid="' + tid + '"]');
  }

  function getRowData(tid) {
    var $row = getRow(tid);
    if (!$row.length) {
      return null;
    }

    return {
      tid: String(tid),
      cid: parseInt($row.attr('data-cid'), 10) || 0,
      name: $row.attr('data-name') || '',
      price: $row.attr('data-price') || '',
      cost: $row.attr('data-cost') || '',
      cost2: $row.attr('data-cost2') || '',
      prid: parseInt($row.attr('data-prid'), 10) || 0,
      priceName: $row.attr('data-price-name') || '',
      stock: $row.attr('data-stock') || '',
      stockLabel: $row.attr('data-stock-label') || '',
      type: parseInt($row.attr('data-type'), 10) || 0,
      typeLabel: $row.attr('data-type-label') || '',
      typeDetail: $row.attr('data-type-detail') || '',
      active: String($row.attr('data-active')) === '1',
      close: String($row.attr('data-close')) === '1',
      sales: parseInt($row.attr('data-sales'), 10) || 0,
      addtime: $row.attr('data-addtime') || '',
      frontUrl: $row.attr('data-front-url') || '',
      classPath: $row.attr('data-class-path') || ''
    };
  }

  function findRule(prid) {
    var result = null;
    $.each(config.priceRules || [], function (_, rule) {
      if (parseInt(rule.id, 10) === parseInt(prid, 10)) {
        result = rule;
        return false;
      }
      return true;
    });
    return result;
  }

  function toNumber(value) {
    var number = parseFloat(value);
    return isNaN(number) ? 0 : number;
  }

  function roundMoney(value) {
    return Math.round(toNumber(value) * 100) / 100;
  }

  function formatMoney(value) {
    var number = roundMoney(value);
    return number.toFixed(2);
  }

  function calculateRulePrices(basePrice, rule) {
    var base = toNumber(basePrice);
    var kind = parseInt(rule.kind, 10) || 0;
    var p0 = toNumber(rule.p_0);
    var p1 = toNumber(rule.p_1);
    var p2 = toNumber(rule.p_2);

    return {
      price: kind === 1 ? base + p0 : base * p0,
      cost: kind === 1 ? base + p1 : base * p1,
      cost2: kind === 1 ? base + p2 : base * p2
    };
  }

  function renderRuleOptions(selectedId) {
    var html = ['<option value="0">' + MSG.manualMode + '</option>'];

    $.each(config.priceRules || [], function (_, rule) {
      var kindLabel = parseInt(rule.kind, 10) === 1 ? '\u5143' : '\u500d';
      html.push('<option value="' + escapeHtml(rule.id) + '"' + (parseInt(selectedId, 10) === parseInt(rule.id, 10) ? ' selected' : '') + '>');
      html.push(escapeHtml(rule.name) + ' (' + escapeHtml(rule.p_2) + kindLabel + ' / ' + escapeHtml(rule.p_1) + kindLabel + ' / ' + escapeHtml(rule.p_0) + kindLabel + ')');
      html.push('</option>');
    });

    return html.join('');
  }

  function buildPricePreviewHtml(price, cost, cost2) {
    return [
      '<div class="admin-shop-price-preview">',
      '<article><span>', MSG.retail, '</span><strong>&#165; ', escapeHtml(formatMoney(price)), '</strong></article>',
      '<article><span>', MSG.standard, '</span><strong>&#165; ', escapeHtml(formatMoney(cost)), '</strong></article>',
      '<article><span>', MSG.premium, '</span><strong>&#165; ', escapeHtml(formatMoney(cost2)), '</strong></article>',
      '</div>'
    ].join('');
  }

  function renderPriceDialog(data) {
    return [
      '<form class="admin-shop-modal-form" data-shop-price-form>',
      '<div class="admin-shop-modal-form__group">',
      '<label>', MSG.currentRule, '</label>',
      '<select class="form-control js-shop-price-rule" name="prid">', renderRuleOptions(data.prid), '</select>',
      '<span class="admin-shop-modal-form__help">\u4e0d\u4f7f\u7528\u6a21\u677f\u65f6\u53ef\u76f4\u63a5\u586b\u5199\u4e09\u6863\u4ef7\u683c\uff1b\u4f7f\u7528\u6a21\u677f\u65f6\u4ec5\u9700\u8f93\u5165\u6210\u672c\u4ef7\u3002</span>',
      '</div>',
      '<div class="admin-shop-modal-form__group">',
      '<label>', MSG.basePrice, '</label>',
      '<input type="text" class="form-control js-shop-price-base" value="', escapeHtml(data.price || ''), '" placeholder="\u8f93\u5165\u6210\u672c\u4ef7">',
      '</div>',
      '<div class="admin-shop-modal-form__grid">',
      '<div class="admin-shop-modal-form__group">',
      '<label>', MSG.retail, '</label>',
      '<input type="text" class="form-control js-shop-price-retail" value="', escapeHtml(data.price || ''), '" placeholder="\u96f6\u552e\u4ef7">',
      '</div>',
      '<div class="admin-shop-modal-form__group">',
      '<label>', MSG.standard, '</label>',
      '<input type="text" class="form-control js-shop-price-standard" value="', escapeHtml(data.cost || ''), '" placeholder="\u666e\u53ca\u4ef7">',
      '</div>',
      '<div class="admin-shop-modal-form__group">',
      '<label>', MSG.premium, '</label>',
      '<input type="text" class="form-control js-shop-price-premium" value="', escapeHtml(data.cost2 || ''), '" placeholder="\u4e13\u4e1a\u4ef7">',
      '</div>',
      '</div>',
      '<div class="js-shop-price-preview"></div>',
      '</form>'
    ].join('');
  }

  function updatePriceDialogState($layer) {
    var $rule = $layer.find('.js-shop-price-rule');
    var $base = $layer.find('.js-shop-price-base');
    var $retail = $layer.find('.js-shop-price-retail');
    var $standard = $layer.find('.js-shop-price-standard');
    var $premium = $layer.find('.js-shop-price-premium');
    var $preview = $layer.find('.js-shop-price-preview');
    var rule = findRule($rule.val());
    var preview;

    if (rule) {
      $base.prop('disabled', false);
      $retail.prop('disabled', true);
      $standard.prop('disabled', true);
      $premium.prop('disabled', true);
      if ($.trim($base.val()) !== '') {
        preview = calculateRulePrices($base.val(), rule);
        $retail.val(formatMoney(preview.price));
        $standard.val(formatMoney(preview.cost));
        $premium.val(formatMoney(preview.cost2));
        $preview.html(buildPricePreviewHtml(preview.price, preview.cost, preview.cost2));
      } else {
        $retail.val('');
        $standard.val('');
        $premium.val('');
        $preview.empty();
      }
    } else {
      $base.prop('disabled', true);
      $retail.prop('disabled', false);
      $standard.prop('disabled', false);
      $premium.prop('disabled', false);
      $preview.html(buildPricePreviewHtml($retail.val() || 0, $standard.val() || 0, $premium.val() || 0));
    }
  }

  function reloadCurrentList() {
    loadTable(getCurrentListQuery());
  }

  function handleJsonResult(response, successCallback) {
    if (response && response.code === 0) {
      if (typeof successCallback === 'function') {
        successCallback(response);
      } else {
        showMessage(response.msg || MSG.actionSuccess, 1);
      }
      return;
    }

    showAlert(response && response.msg ? response.msg : MSG.serverError, { icon: 2 });
  }

  function openPriceEditor(tid) {
    var rowData = getRowData(tid);
    if (!rowData) {
      showMessage(MSG.serverError, 2);
      return;
    }

    openHtmlModal(MSG.priceHeading, renderDialog({
      eyebrow: 'Price',
      heading: MSG.priceHeading,
      description: MSG.priceDesc,
      meta: [
        { label: MSG.goodsId, value: '#' + rowData.tid },
        { label: '\u5546\u54c1\u540d\u79f0', value: rowData.name }
      ],
      body: renderPriceDialog(rowData),
      footer: [
        '<button type="button" class="btn btn-default js-dialog-cancel"><i class="fa fa-times"></i> ', MSG.cancel, '</button>',
        '<button type="button" class="btn btn-primary js-shop-price-save"><i class="fa fa-save"></i> ', MSG.save, '</button>'
      ].join('')
    }), 'compact', {
      onSuccess: function (layero, index) {
        var $layer = $(layero);

        updatePriceDialogState($layer);

        $layer.on('click', '.js-dialog-cancel', function () {
          layer.close(index);
        });
        $layer.on('change keyup', '.js-shop-price-rule, .js-shop-price-base, .js-shop-price-retail, .js-shop-price-standard, .js-shop-price-premium', function () {
          updatePriceDialogState($layer);
        });
        $layer.on('click', '.js-shop-price-save', function () {
          var prid = parseInt($layer.find('.js-shop-price-rule').val(), 10) || 0;
          var base = $.trim($layer.find('.js-shop-price-base').val());
          var retail = $.trim($layer.find('.js-shop-price-retail').val());
          var standard = $.trim($layer.find('.js-shop-price-standard').val());
          var premium = $.trim($layer.find('.js-shop-price-premium').val());

          if (prid > 0 && base === '') {
            showMessage(MSG.needBasePrice, 2);
            return;
          }
          if (prid === 0 && retail === '') {
            showMessage(MSG.needPrice, 2);
            return;
          }

          requestJson({
            type: 'POST',
            url: (endpoints.ajax || './ajax_shop.php') + '?act=editPrice',
            data: {
              tid: tid,
              price: base,
              prid: prid,
              price_s: retail,
              cost_s: standard,
              cost2_s: premium
            }
          }).done(function (response) {
            handleJsonResult(response, function (res) {
              layer.close(index);
              showMessage(res.msg || MSG.saveSuccess, 1);
              reloadCurrentList();
            });
          }).fail(function () {
            showMessage(MSG.serverError, 2);
          });
        });
      }
    });
  }

  function openStockEditor(tid) {
    var rowData = getRowData(tid);
    var initialValue;

    if (!rowData) {
      showMessage(MSG.serverError, 2);
      return;
    }

    if (rowData.type === 4) {
      window.open('./fakalist.php?tid=' + encodeURIComponent(tid));
      return;
    }

    initialValue = rowData.stock || '';

    openHtmlModal(MSG.stockHeading, renderDialog({
      eyebrow: 'Stock',
      heading: MSG.stockHeading,
      description: MSG.stockDesc,
      meta: [
        { label: MSG.goodsId, value: '#' + rowData.tid },
        { label: '\u5546\u54c1\u540d\u79f0', value: rowData.name }
      ],
      body: [
        '<div class="admin-shop-modal-form">',
        '<div class="admin-shop-modal-form__group">',
        '<label>', '\u5e93\u5b58\u6570\u91cf', '</label>',
        '<input type="text" class="form-control js-shop-stock-input" value="', escapeHtml(initialValue), '" placeholder="\u7559\u7a7a\u4e3a\u65e0\u9650\u5e93\u5b58">',
        '<span class="admin-shop-modal-form__help">\u8f93\u5165 0 \u8868\u793a\u7a7a\u5e93\u5b58\uff0c\u7559\u7a7a\u8868\u793a\u65e0\u9650\u3002</span>',
        '</div>',
        '</div>'
      ].join(''),
      footer: [
        '<button type="button" class="btn btn-default js-dialog-cancel"><i class="fa fa-times"></i> ', MSG.cancel, '</button>',
        '<button type="button" class="btn btn-primary js-shop-stock-save"><i class="fa fa-save"></i> ', MSG.save, '</button>'
      ].join('')
    }), 'compact', {
      onSuccess: function (layero, index) {
        var $layer = $(layero);
        var $input = $layer.find('.js-shop-stock-input');

        $input.focus().select();

        $layer.on('click', '.js-dialog-cancel', function () {
          layer.close(index);
        });
        $layer.on('click', '.js-shop-stock-save', function () {
          var value = $.trim($input.val());

          if (value !== '' && !/^\d+$/.test(value)) {
            showMessage(MSG.invalidStock, 2);
            return;
          }

          requestJson({
            type: 'POST',
            url: (endpoints.ajax || './ajax_shop.php') + '?act=setStock',
            data: { tid: tid, num: value }
          }).done(function (response) {
            handleJsonResult(response, function (res) {
              layer.close(index);
              showMessage(res.msg || MSG.saveSuccess, 1);
              reloadCurrentList();
            });
          }).fail(function () {
            showMessage(MSG.serverError, 2);
          });
        });
      }
    });
  }

  function safeText(value) {
    return value === null || value === undefined || value === '' ? MSG.noneText : String(value);
  }

  function buildDetailBody(data, extra) {
    var visibleText = data.active ? MSG.visible : MSG.hidden;
    var closeText = data.close ? MSG.offline : MSG.online;
    var inputText = extra && extra.input ? extra.input : '';
    var inputsText = extra && extra.inputs ? extra.inputs : '';
    var descText = extra && extra.desc ? extra.desc : '';
    var alertText = extra && extra.alert ? extra.alert : '';
    var minText = extra && extra.min ? extra.min : '';
    var maxText = extra && extra.max ? extra.max : '';
    var goodsIdText = extra && extra.goods_id ? extra.goods_id : '';
    var repeatText = extra && extra.repeat ? extra.repeat : '';

    return [
      '<div class="admin-shop-detail-card"><dl>',
      '<dt>\u5206\u7c7b</dt><dd>', escapeHtml(safeText(data.classPath)), '</dd>',
      '<dt>\u6765\u6e90</dt><dd>', escapeHtml(safeText(data.typeLabel)), ' / ', escapeHtml(safeText(data.typeDetail)), '</dd>',
      '<dt>\u524d\u53f0\u72b6\u6001</dt><dd>', escapeHtml(visibleText), '</dd>',
      '<dt>\u4e0a\u4e0b\u67b6</dt><dd>', escapeHtml(closeText), '</dd>',
      '<dt>\u9500\u91cf</dt><dd>', escapeHtml(String(data.sales)), '</dd>',
      '<dt>\u6dfb\u52a0\u65f6\u95f4</dt><dd>', escapeHtml(safeText(data.addtime)), '</dd>',
      '<dt>\u524d\u53f0\u94fe\u63a5</dt><dd><a href="', escapeHtml(data.frontUrl), '" target="_blank" rel="noopener">', escapeHtml(data.frontUrl), '</a></dd>',
      '</dl></div>',
      '<div class="admin-shop-detail-card"><dl>',
      '<dt>\u4ef7\u683c\u7b56\u7565</dt><dd>', escapeHtml(data.prid > 0 ? (data.priceName || MSG.manualMode) : MSG.manualMode), '</dd>',
      '<dt>\u96f6\u552e / \u666e\u53ca / \u4e13\u4e1a</dt><dd>&#165; ', escapeHtml(safeText(data.price)), ' / &#165; ', escapeHtml(safeText(data.cost)), ' / &#165; ', escapeHtml(safeText(data.cost2)), '</dd>',
      '<dt>\u5e93\u5b58</dt><dd>', escapeHtml(safeText(data.stockLabel)), '</dd>',
      '<dt>\u4e0b\u5355\u6807\u9898</dt><dd>', escapeHtml(safeText(inputText)), '</dd>',
      '<dt>\u6269\u5c55\u8f93\u5165\u9879</dt><dd>', escapeHtml(safeText(inputsText)), '</dd>',
      '<dt>\u5bf9\u63a5\u5546\u54c1 ID</dt><dd>', escapeHtml(safeText(goodsIdText)), '</dd>',
      '<dt>\u6700\u5c0f / \u6700\u5927\u8d2d\u4e70</dt><dd>', escapeHtml(safeText(minText)), ' / ', escapeHtml(safeText(maxText)), '</dd>',
      '<dt>\u53ef\u91cd\u590d\u8d2d\u4e70</dt><dd>', escapeHtml(safeText(repeatText)), '</dd>',
      '<dt>\u524d\u53f0\u63d0\u793a</dt><dd>', escapeHtml(safeText(alertText)), '</dd>',
      '<dt>\u5546\u54c1\u8bf4\u660e</dt><dd>', escapeHtml(safeText(descText)), '</dd>',
      '</dl></div>',
      '<div class="admin-shop-dialog__actions">',
      '<a class="btn btn-default" href="./shopedit.php?my=edit&amp;tid=', escapeHtml(data.tid), '"><i class="fa fa-pencil"></i> ', MSG.edit, '</a>',
      '<a class="btn btn-default" href="./list.php?tid=', escapeHtml(data.tid), '"><i class="fa fa-list-alt"></i> ', MSG.orders, '</a>',
      '<a class="btn btn-default" href="', escapeHtml(data.frontUrl), '" target="_blank" rel="noopener"><i class="fa fa-external-link"></i> ', MSG.preview, '</a>',
      '<button type="button" class="btn btn-primary js-shop-detail-price" data-tid="', escapeHtml(data.tid), '"><i class="fa fa-jpy"></i> ', MSG.changePrice, '</button>',
      '<button type="button" class="btn btn-default js-shop-detail-stock" data-tid="', escapeHtml(data.tid), '"><i class="fa fa-cubes"></i> ', MSG.changeStock, '</button>',
      '</div>'
    ].join('');
  }

  function openDetail(tid) {
    var rowData = getRowData(tid);

    if (!rowData) {
      showMessage(MSG.serverError, 2);
      return;
    }

    requestJson({
      url: (endpoints.ajax || './ajax_shop.php') + '?act=getTool&tid=' + encodeURIComponent(tid)
    }).done(function (response) {
      var extra = response && response.code === 0 ? (response.data || {}) : {};

      openHtmlModal(MSG.detailHeading, renderDialog({
        eyebrow: 'Detail',
        heading: MSG.detailHeading,
        description: MSG.detailDesc,
        meta: [
          { label: MSG.goodsId, value: '#' + rowData.tid },
          { label: '\u5546\u54c1\u540d\u79f0', value: rowData.name }
        ],
        body: buildDetailBody(rowData, extra)
      }), 'large', {
        onSuccess: function (layero, index) {
          $(layero).on('click', '.js-shop-detail-price', function () {
            layer.close(index);
            openPriceEditor($(this).data('tid'));
          });
          $(layero).on('click', '.js-shop-detail-stock', function () {
            layer.close(index);
            openStockEditor($(this).data('tid'));
          });
        }
      });
    }).fail(function () {
      openHtmlModal(MSG.detailHeading, renderDialog({
        eyebrow: 'Detail',
        heading: MSG.detailHeading,
        description: MSG.detailDesc,
        meta: [
          { label: MSG.goodsId, value: '#' + rowData.tid },
          { label: '\u5546\u54c1\u540d\u79f0', value: rowData.name }
        ],
        body: buildDetailBody(rowData, {})
      }), 'large');
    });
  }

  function submitStatusToggle(tid, mode, value) {
    var data = { tid: tid };

    data[mode] = value;

    requestJson({
      url: (endpoints.ajax || './ajax_shop.php') + '?act=setTools',
      data: data
    }).done(function (response) {
      handleJsonResult(response, function (res) {
        showMessage(res.msg || MSG.actionSuccess, 1);
        reloadCurrentList();
      });
    }).fail(function () {
      showMessage(MSG.serverError, 2);
    });
  }

  function submitSort(cid, tid, sortType) {
    requestJson({
      url: (endpoints.ajax || './ajax_shop.php') + '?act=setToolSort',
      data: { cid: cid, tid: tid, sort: sortType }
    }).done(function (response) {
      handleJsonResult(response, function () {
        reloadCurrentList();
      });
    }).fail(function () {
      showMessage(MSG.serverError, 2);
    });
  }

  function confirmDelete(tid, name) {
    openConfirmDialog({
      title: MSG.delete,
      eyebrow: 'Delete',
      heading: '\u786e\u5b9a\u5220\u9664\u8fd9\u4e2a\u5546\u54c1\u5417\uff1f',
      description: MSG.deleteDesc,
      meta: [
        { label: MSG.goodsId, value: '#' + tid },
        { label: '\u5546\u54c1\u540d\u79f0', value: name || '' }
      ],
      confirmText: MSG.delete,
      confirmClass: 'btn-danger',
      confirmIcon: 'fa-trash',
      onConfirm: function (layero, index) {
        layer.close(index);
        requestJson({
          url: (endpoints.ajax || './ajax_shop.php') + '?act=delTool&tid=' + encodeURIComponent(tid)
        }).done(function (response) {
          handleJsonResult(response, function (res) {
            showMessage(res.msg || MSG.actionSuccess, 1);
            reloadCurrentList();
          });
        }).fail(function () {
          showMessage(MSG.serverError, 2);
        });
      }
    });
  }

  function renderReplaceDialog(mode) {
    var isInputs = mode === 'inputs';

    return renderDialog({
      eyebrow: isInputs ? 'Inputs' : 'Rename',
      heading: isInputs ? MSG.replaceInputsHeading : MSG.replaceNameHeading,
      description: isInputs ? MSG.replaceInputsDesc : MSG.replaceNameDesc,
      body: [
        '<div class="admin-shop-modal-form">',
        '<div class="admin-shop-modal-form__group">',
        '<label>\u539f\u5185\u5bb9</label>',
        '<input type="text" class="form-control js-shop-replace-old" placeholder="\u8f93\u5165\u9700\u8981\u88ab\u66ff\u6362\u7684\u5185\u5bb9">',
        '</div>',
        '<div class="admin-shop-modal-form__group">',
        '<label>\u65b0\u5185\u5bb9</label>',
        '<input type="text" class="form-control js-shop-replace-new" placeholder="\u8f93\u5165\u66ff\u6362\u540e\u7684\u5185\u5bb9">',
        '</div>',
        '</div>'
      ].join(''),
      footer: [
        '<button type="button" class="btn btn-default js-dialog-cancel"><i class="fa fa-times"></i> ', MSG.cancel, '</button>',
        '<button type="button" class="btn btn-primary js-shop-replace-save" data-replace-mode="', escapeHtml(mode), '"><i class="fa fa-save"></i> ', MSG.save, '</button>'
      ].join('')
    });
  }

  function openReplaceDialog(mode) {
    var action = mode === 'inputs' ? 'change_inputs' : 'change_shopname';

    openHtmlModal(mode === 'inputs' ? MSG.replaceInputsHeading : MSG.replaceNameHeading, renderReplaceDialog(mode), 'compact', {
      onSuccess: function (layero, index) {
        var $layer = $(layero);

        $layer.on('click', '.js-dialog-cancel', function () {
          layer.close(index);
        });
        $layer.on('click', '.js-shop-replace-save', function () {
          var oldName = $.trim($layer.find('.js-shop-replace-old').val());
          var newName = $.trim($layer.find('.js-shop-replace-new').val());

          if (!oldName) {
            showMessage(MSG.needOldName, 2);
            return;
          }
          if (!newName) {
            showMessage(MSG.needNewName, 2);
            return;
          }

          requestJson({
            type: 'POST',
            url: (endpoints.ajax || './ajax_shop.php') + '?act=' + action,
            data: { oldName: oldName, newName: newName }
          }).done(function (response) {
            handleJsonResult(response, function (res) {
              layer.close(index);
              showMessage(res.msg || MSG.saveSuccess, 1);
              reloadCurrentList();
            });
          }).fail(function () {
            showMessage(MSG.serverError, 2);
          });
        });
      }
    });
  }

  function buildBatchMeta(ids) {
    return [
      { label: MSG.goodsCount, value: String(ids.length) + ' \u4e2a' }
    ];
  }

  function submitBulkAction(action, ids) {
    requestJson({
      type: 'POST',
      url: (endpoints.ajax || './ajax_shop.php') + '?act=shop_change',
      data: { aid: action, checkbox: ids }
    }).done(function (response) {
      handleJsonResult(response, function (res) {
        showMessage(res.msg || MSG.actionSuccess, 1);
        reloadCurrentList();
      });
    }).fail(function () {
      showMessage(MSG.serverError, 2);
    });
  }

  function openBulkPriceDialog(ids) {
    if (!ids || !ids.length) {
      showMessage(MSG.selectGoods, 2);
      return;
    }

    openHtmlModal(MSG.bulkPriceHeading, renderDialog({
      eyebrow: 'Batch Price',
      heading: MSG.bulkPriceHeading,
      description: MSG.bulkPriceDesc,
      meta: buildBatchMeta(ids),
      body: [
        '<div class="admin-shop-modal-form">',
        '<div class="admin-shop-modal-form__group">',
        '<label>', MSG.currentRule, '</label>',
        '<select class="form-control js-shop-bulk-price-rule">', renderRuleOptions(0), '</select>',
        '</div>',
        '</div>'
      ].join(''),
      footer: [
        '<button type="button" class="btn btn-default js-dialog-cancel"><i class="fa fa-times"></i> ', MSG.cancel, '</button>',
        '<button type="button" class="btn btn-primary js-shop-bulk-price-save"><i class="fa fa-save"></i> ', MSG.save, '</button>'
      ].join('')
    }), 'compact', {
      onSuccess: function (layero, index) {
        var $layer = $(layero);

        $layer.on('click', '.js-dialog-cancel', function () {
          layer.close(index);
        });
        $layer.on('click', '.js-shop-bulk-price-save', function () {
          var prid = parseInt($layer.find('.js-shop-bulk-price-rule').val(), 10) || 0;

          requestJson({
            type: 'POST',
            url: (endpoints.ajax || './ajax_shop.php') + '?act=editAllPrice',
            data: { prid: prid, checkbox: ids }
          }).done(function (response) {
            handleJsonResult(response, function (res) {
              layer.close(index);
              showMessage(res.msg || MSG.actionSuccess, 1);
              reloadCurrentList();
            });
          }).fail(function () {
            showMessage(MSG.serverError, 2);
          });
        });
      }
    });
  }

  function openBulkStockDialog(ids) {
    if (!ids || !ids.length) {
      showMessage(MSG.selectGoods, 2);
      return;
    }

    openHtmlModal(MSG.bulkStockHeading, renderDialog({
      eyebrow: 'Batch Stock',
      heading: MSG.bulkStockHeading,
      description: MSG.bulkStockDesc,
      meta: buildBatchMeta(ids),
      body: [
        '<div class="admin-shop-modal-form">',
        '<div class="admin-shop-modal-form__group">',
        '<label>\u5e93\u5b58\u6570\u91cf</label>',
        '<input type="text" class="form-control js-shop-bulk-stock-input" placeholder="\u7559\u7a7a\u4e3a\u65e0\u9650\u5e93\u5b58">',
        '<span class="admin-shop-modal-form__help">\u8be5\u503c\u4f1a\u7edf\u4e00\u5e94\u7528\u5230\u9009\u4e2d\u7684\u666e\u901a\u5546\u54c1\u3002</span>',
        '</div>',
        '</div>'
      ].join(''),
      footer: [
        '<button type="button" class="btn btn-default js-dialog-cancel"><i class="fa fa-times"></i> ', MSG.cancel, '</button>',
        '<button type="button" class="btn btn-primary js-shop-bulk-stock-save"><i class="fa fa-save"></i> ', MSG.save, '</button>'
      ].join('')
    }), 'compact', {
      onSuccess: function (layero, index) {
        var $layer = $(layero);
        var $input = $layer.find('.js-shop-bulk-stock-input');

        $input.focus();

        $layer.on('click', '.js-dialog-cancel', function () {
          layer.close(index);
        });
        $layer.on('click', '.js-shop-bulk-stock-save', function () {
          var value = $.trim($input.val());

          if (value !== '' && !/^\d+$/.test(value)) {
            showMessage(MSG.invalidStock, 2);
            return;
          }

          requestJson({
            type: 'POST',
            url: (endpoints.ajax || './ajax_shop.php') + '?act=editAllStock',
            data: { stock: value, checkbox: ids }
          }).done(function (response) {
            handleJsonResult(response, function (res) {
              layer.close(index);
              showMessage(res.msg || MSG.actionSuccess, 1);
              reloadCurrentList();
            });
          }).fail(function () {
            showMessage(MSG.serverError, 2);
          });
        });
      }
    });
  }

  function runBulkAction() {
    var ids = getCheckedIds();
    var action = $('#shopBulkAction').val();
    var labels = {
      '1': '\u6539\u4e3a\u524d\u53f0\u663e\u793a',
      '2': '\u6539\u4e3a\u524d\u53f0\u9690\u85cf',
      '3': '\u6539\u4e3a\u4e0a\u67b6\u4e2d',
      '4': '\u6539\u4e3a\u5df2\u4e0b\u67b6',
      '5': '\u5220\u9664\u9009\u4e2d\u5546\u54c1',
      '6': '\u590d\u5236\u9009\u4e2d\u5546\u54c1'
    };

    if (!ids.length) {
      showMessage(MSG.selectGoods, 2);
      return;
    }
    if (!action) {
      showMessage(MSG.selectAction, 2);
      return;
    }
    if (String(action) === '10') {
      openBulkPriceDialog(ids);
      return;
    }
    if (String(action) === '11') {
      openBulkStockDialog(ids);
      return;
    }

    openConfirmDialog({
      title: MSG.batchHeading,
      eyebrow: 'Batch Action',
      heading: labels[action] || MSG.batchHeading,
      description: String(action) === '5' ? MSG.bulkDeleteDesc : '\u7cfb\u7edf\u4f1a\u5bf9\u9009\u4e2d\u5546\u54c1\u7edf\u4e00\u6267\u884c\u8fd9\u6b21\u6279\u91cf\u64cd\u4f5c\u3002',
      meta: buildBatchMeta(ids),
      confirmText: MSG.confirm,
      confirmClass: String(action) === '5' ? 'btn-danger' : 'btn-primary',
      confirmIcon: String(action) === '5' ? 'fa-trash' : 'fa-check',
      onConfirm: function (layero, index) {
        layer.close(index);
        submitBulkAction(action, ids);
      }
    });
  }

  function runMoveAction() {
    var ids = getCheckedIds();
    var categoryId = $('#shopMoveCategory').val();
    var categoryText = $('#shopMoveCategory option:selected').text();

    if (!ids.length) {
      showMessage(MSG.selectGoods, 2);
      return;
    }
    if (!categoryId) {
      showMessage(MSG.selectCategory, 2);
      return;
    }

    openConfirmDialog({
      title: MSG.moveHeading,
      eyebrow: 'Move',
      heading: MSG.moveHeading,
      description: MSG.moveDesc,
      meta: [
        { label: MSG.goodsCount, value: String(ids.length) + ' \u4e2a' },
        { label: '\u76ee\u6807\u5206\u7c7b', value: categoryText }
      ],
      onConfirm: function (layero, index) {
        layer.close(index);
        requestJson({
          type: 'POST',
          url: (endpoints.ajax || './ajax_shop.php') + '?act=shop_move',
          data: { cid: categoryId, checkbox: ids }
        }).done(function (response) {
          handleJsonResult(response, function (res) {
            showMessage(res.msg || MSG.actionSuccess, 1);
            reloadCurrentList();
          });
        }).fail(function () {
          showMessage(MSG.serverError, 2);
        });
      }
    });
  }

  function resetSort(cid) {
    openConfirmDialog({
      title: MSG.resetSortHeading,
      eyebrow: 'Sort Reset',
      heading: MSG.resetSortHeading,
      description: MSG.resetSortDesc,
      confirmText: MSG.confirm,
      onConfirm: function (layero, index) {
        layer.close(index);
        requestJson({
          type: 'POST',
          url: (endpoints.ajax || './ajax_shop.php') + '?act=reset_sort',
          data: { cid: cid }
        }).done(function (response) {
          handleJsonResult(response, function (res) {
            showMessage(res.msg || MSG.actionSuccess, 1);
            reloadCurrentList();
          });
        }).fail(function () {
          showMessage(MSG.serverError, 2);
        });
      }
    });
  }

  function handleAction(action, $trigger) {
    var tid = $trigger.data('tid');

    switch (action) {
      case 'refresh':
        reloadCurrentList();
        break;
      case 'detail':
        openDetail(tid);
        break;
      case 'price':
        openPriceEditor(tid);
        break;
      case 'stock':
        openStockEditor(tid);
        break;
      case 'toggle-active':
        submitStatusToggle(tid, 'active', $trigger.data('active'));
        break;
      case 'toggle-close':
        submitStatusToggle(tid, 'close', $trigger.data('close'));
        break;
      case 'sort':
        submitSort($trigger.data('cid'), tid, $trigger.data('sort'));
        break;
      case 'delete':
        confirmDelete(tid, $trigger.data('name'));
        break;
      case 'bulk':
        runBulkAction();
        break;
      case 'move':
        runMoveAction();
        break;
      case 'replace-name':
        openReplaceDialog('name');
        break;
      case 'replace-inputs':
        openReplaceDialog('inputs');
        break;
      case 'reset-sort':
        resetSort($trigger.data('cid'));
        break;
      default:
        break;
    }
  }

  function bindEvents() {
    $(document).on('submit', '#shopFilterForm', function (event) {
      event.preventDefault();
      shopListState.pagesize = parseInt($(this).find('select[name="num"]').val(), 10) || shopListState.pagesize;
      loadTable(buildFilterQuery(true));
    });

    $(document).on('change', '#shopPageSize', function () {
      shopListState.pagesize = parseInt($(this).val(), 10) || shopListState.pagesize;
      loadTable(buildFilterQuery(true));
    });

    $(document).on('click', '[data-shop-query]', function (event) {
      event.preventDefault();
      loadTable($(this).attr('data-shop-query') || '');
    });

    $(document).on('change', '[data-shop-check-all]', function () {
      var checked = $(this).is(':checked');
      $('#shopListForm').find('[data-shop-checkbox]').prop('checked', checked);
      syncCheckAllState();
      updateSelectedCount();
    });

    $(document).on('change', '[data-shop-checkbox]', function () {
      syncCheckAllState();
      updateSelectedCount();
    });

    $(document).on('click', '[data-shop-action]', function (event) {
      var action = $(this).attr('data-shop-action');
      if ($(this).is('a')) {
        event.preventDefault();
      }
      handleAction(action, $(this));
    });
  }

  function init() {
    syncFilterForm(getCurrentListQuery());
    loadTable(getCurrentListQuery());
    bindEvents();
  }

  window.listTable = loadTable;
  window.show = openDetail;
  window.getPrice = openPriceEditor;
  window.editPrice = function () {
    var $button = $('.admin-shop-layer').last().find('.js-shop-price-save');
    if ($button.length) {
      $button.trigger('click');
    }
  };
  window.changePrice = function () {
    var $layer = $('.admin-shop-layer').last();
    if ($layer.length) {
      updatePriceDialogState($layer);
    }
  };
  window.setStock = function (tid) {
    openStockEditor(tid);
  };
  window.setActive = function (tid, active) {
    submitStatusToggle(tid, 'active', active);
  };
  window.setClose = function (tid, closeValue) {
    submitStatusToggle(tid, 'close', closeValue);
  };
  window.delTool = function (tid) {
    var rowData = getRowData(tid);
    confirmDelete(tid, rowData ? rowData.name : '');
  };
  window.sort = submitSort;
  window.change_shopname = function () {
    openReplaceDialog('name');
  };
  window.change_inputs = function () {
    openReplaceDialog('inputs');
  };
  window.reset_sort = resetSort;
  window.change = runBulkAction;
  window.move = runMoveAction;
  window.editAllPrice = function () {
    openBulkPriceDialog(getCheckedIds());
  };
  window.editAllStock = function () {
    openBulkStockDialog(getCheckedIds());
  };
  window.checkAll = function () {
    $('[data-shop-check-all]').trigger('change');
  };
  window.unselectall1 = function () {
    syncCheckAllState();
  };
  window.check1 = function () {
    var $all = $('[data-shop-check-all]');
    $all.prop('checked', !$all.is(':checked')).trigger('change');
    return String($all.is(':checked'));
  };
  window.searchItem = function () {
    loadTable(buildFilterQuery(true));
    return false;
  };

  $(init);
})(window, jQuery);

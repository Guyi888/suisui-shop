(function (window, $) {
  'use strict';

  var config = window.batchGoodsConfig || {};
  var endpoints = config.endpoints || {
    batchDocking: './ajax_batch_docking.php',
    shopAjax: './ajax_shop.php'
  };
  var traditionalTypes = Array.isArray(config.traditionalTypes) ? config.traditionalTypes : ['jiuwu', 'yile', 'zhike', 'shangzhanwl'];

  var MSG = {
    loading: '\u6b63\u5728\u52a0\u8f7d\u5546\u54c1\u5217\u8868',
    loadFail: '\u52a0\u8f7d\u5546\u54c1\u5217\u8868\u5931\u8d25\uff0c\u8bf7\u5237\u65b0\u91cd\u8bd5',
    reqFail: '\u8bf7\u6c42\u51fa\u9519',
    noGoods: '\u8be5\u5206\u7c7b\u4e0b\u6ca1\u6709\u5546\u54c1',
    noneSelected: '\u8bf7\u81f3\u5c11\u9009\u4e2d\u4e00\u4e2a\u5546\u54c1',
    needCategory: '\u8bf7\u9009\u62e9\u4fdd\u5b58\u5230\u672c\u7ad9\u7684\u5206\u7c7b',
    needPriceRule: '\u8bf7\u9009\u62e9\u4f7f\u7528\u7684\u52a0\u4ef7\u6a21\u677f',
    needGoodsCid: '\u8bf7\u8f93\u5165\u5546\u54c1\u76ee\u5f55',
    fetchCategoryFail: '\u83b7\u53d6\u5206\u7c7b\u5931\u8d25',
    refreshRetry: '\u52a0\u8f7d\u5931\u8d25\uff0c\u8bf7\u5237\u65b0\u91cd\u8bd5',
    offline: '\u5df2\u4e0b\u67b6',
    online: '\u4e0a\u67b6\u4e2d',
    unlimited: '\u65e0\u9650\u5236',
    unknownGoods: '\u672a\u77e5\u5546\u54c1',
    partialFail: '\u90e8\u5206\u5206\u7c7b\u52a0\u8f7d\u5931\u8d25\uff1a',
    categoryLoadFail: '\u5206\u7c7b',
    loadFailColon: '\u52a0\u8f7d\u5931\u8d25\uff1a',
    checkNetwork: '\u52a0\u8f7d\u5546\u54c1\u5931\u8d25\uff0c\u8bf7\u68c0\u67e5\u7f51\u7edc\u8fde\u63a5\u6216 API \u914d\u7f6e',
    selectAll: '\u5168\u9009',
    colGoods: '\u5546\u54c1',
    colGoodsId: '\u5546\u54c1 ID',
    colGoodsName: '\u5546\u54c1\u540d\u79f0',
    colPrice: '\u6210\u672c\u4ef7',
    colCost: '\u8ba1\u7b97\u540e\u7684\u4ef7\u94b1',
    colMinNum: '\u6700\u5c11\u4e0b\u5355\u6570\u91cf',
    colMaxNum: '\u6700\u5927\u4e0b\u5355\u6570\u91cf',
    colDefaultNum: '\u9ed8\u8ba4\u4e0b\u5355\u6570\u91cf',
    colStatus: '\u72b6\u6001',
    metaWaiting: '\u7b49\u5f85\u52a0\u8f7d',
    metaTotal: '\u5171 ',
    metaGoods: ' \u4e2a\u5546\u54c1',
    metaChecked: '\uff0c\u5df2\u9009 '
  };

  function isTraditionalType(type) {
    return traditionalTypes.indexOf(type) !== -1;
  }

  function toFloat(number, n) {
    n = n ? parseInt(n, 10) : 0;
    if (!isFinite(number)) return 0;
    if (n <= 0) return Math.ceil(number);
    var pow = Math.pow(10, n);
    return Math.round(number * pow) / pow;
  }

  function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function renderStatus(close) {
    if (parseInt(close, 10) === 1) {
      return '<span class="admin-batchgoods-status admin-batchgoods-status--offline">' + MSG.offline + '</span>';
    }
    return '<span class="admin-batchgoods-status admin-batchgoods-status--online">' + MSG.online + '</span>';
  }

  function renderTraditionalHead() {
    return '<tr>'
      + '<th class="admin-batchgoods-col-check">'
      + '<label class="admin-batchgoods-check"><input type="checkbox" id="batchGoodsSelectAll"><span>' + MSG.selectAll + '</span></label>'
      + '</th>'
      + '<th>' + MSG.colGoodsName + '</th>'
      + '<th>' + MSG.colPrice + '</th>'
      + '<th>' + MSG.colCost + '</th>'
      + '<th>' + MSG.colMinNum + '</th>'
      + '<th>' + MSG.colMaxNum + '</th>'
      + '<th>' + MSG.colDefaultNum + '</th>'
      + '<th>' + MSG.colStatus + '</th>'
      + '</tr>';
  }

  function renderPluginHead() {
    return '<tr>'
      + '<th class="admin-batchgoods-col-check">'
      + '<label class="admin-batchgoods-check"><input type="checkbox" id="batchGoodsSelectAll"><span>' + MSG.selectAll + '</span></label>'
      + '</th>'
      + '<th>' + MSG.colGoodsId + '</th>'
      + '<th>' + MSG.colGoodsName + '</th>'
      + '<th>' + MSG.colPrice + '</th>'
      + '<th>' + MSG.colStatus + '</th>'
      + '</tr>';
  }

  function updateMeta(total, checked) {
    var $meta = $('#batchGoodsMeta');
    if (!$meta.length) return;
    if (typeof total !== 'number') {
      $meta.text(MSG.metaWaiting);
      return;
    }
    var text = MSG.metaTotal + total + MSG.metaGoods;
    if (typeof checked === 'number' && total > 0) {
      text += MSG.metaChecked + checked;
    }
    $meta.text(text);
  }

  function refreshCheckedCount() {
    var $items = $('#shoplist .admin-batchgoods-row-check');
    var total = $items.length;
    var checked = $items.filter(':checked').length;
    if (total === 0) {
      updateMeta();
    } else {
      updateMeta(total, checked);
    }
  }

  var state = {
    shoplist: [],
    loading: false
  };

  window.batchGoodsState = state;

  function resetTable(headHtml) {
    var $table = $('#shoptable');
    if (headHtml) {
      $table.find('thead').html(headHtml);
    }
    $table.find('tbody#shoplist').empty();
    state.shoplist = [];
  }

  function showLoader() {
    return layer.load(2, { shade: [0.1, '#fff'] });
  }

  function fetchTraditionalGoods() {
    var shequ = $('input[name="shequ"]').val();
    var type = $('input[name="type"]').val();
    var goodscid = $('#goodscid').val() || '';
    var is = $('#is').val() || 0;

    var categoryIds = [];
    $('#category_id option:selected').each(function () {
      var val = $(this).val();
      if (val && val !== '-1') categoryIds.push(val);
    });
    var categoryId = categoryIds.join(',');

    if (type === 'shangzhanwl' && goodscid === '') {
      layer.msg(MSG.needGoodsCid, { icon: 5 });
      return false;
    }

    var loader = showLoader();
    resetTable(renderTraditionalHead());
    updateMeta();
    state.loading = true;

    $.ajax({
      type: 'POST',
      url: endpoints.batchDocking + '?act=getGoodsList',
      data: { shequ: shequ, is: is, type: type, goodscid: goodscid, category_id: categoryId },
      dataType: 'json',
      success: function (data) {
        layer.close(loader);
        state.loading = false;
        if (data && data.code === 0) {
          renderTraditionalRows(data.data || [], type);
        } else {
          layer.alert((data && data.msg) || MSG.loadFail, { icon: 2 });
        }
      },
      error: function (xhr) {
        layer.close(loader);
        state.loading = false;
        layer.alert(MSG.reqFail, { icon: 2 });
      }
    });
    return true;
  }

  function renderTraditionalRows(list, type) {
    var $tbody = $('#shoplist');
    $tbody.empty();
    state.shoplist = [];

    if (!list || list.length === 0) {
      layer.msg(MSG.noGoods, { icon: 0, time: 800 });
      updateMeta(0);
      return;
    }

    var priceFields = ['price', 'user_unitprice', 'goodsPrice', 'unitPrice'];
    var valueFields = ['value', 'default_num', 'def_num', 'defaultnum', 'defnum', 'minnum', 'minbuynum_0'];

    var fragments = [];
    for (var i = 0; i < list.length; i++) {
      var item = list[i];
      var id = item.id || item.gid || 0;
      var name = item.name || item.title || MSG.unknownGoods;

      var originalCategoryName = item.original_cname || item.category_name || item.cname || '';

      var price = 0;
      for (var p = 0; p < priceFields.length; p++) {
        var pf = item[priceFields[p]];
        if (pf !== null && pf !== undefined && !isNaN(parseFloat(pf)) && parseFloat(pf) > 0) {
          price = parseFloat(pf);
          break;
        }
      }

      var minnum = 1;
      var maxnum = 0;
      if (item.maxnum !== null && item.maxnum !== undefined && !isNaN(parseInt(item.maxnum, 10))) {
        maxnum = parseInt(item.maxnum, 10);
      } else if (item.maxbuynum_0 !== null && item.maxbuynum_0 !== undefined && !isNaN(parseInt(item.maxbuynum_0, 10))) {
        maxnum = parseInt(item.maxbuynum_0, 10);
      } else if (item.limit_max !== null && item.limit_max !== undefined && !isNaN(parseInt(item.limit_max, 10))) {
        maxnum = parseInt(item.limit_max, 10);
      }

      var close = parseInt(item.close || item.goods_status || 0, 10);
      var shopimg = item.shopimg || item.thumb || '';

      var defaultNum = 1;
      for (var v = 0; v < valueFields.length; v++) {
        var vv = item[valueFields[v]];
        if (vv !== null && vv !== undefined && !isNaN(parseInt(vv, 10)) && parseInt(vv, 10) > 0) {
          defaultNum = parseInt(vv, 10);
          break;
        }
      }
      if (defaultNum <= 0) defaultNum = 500;

      var cost = isNaN(price * defaultNum) ? 0 : toFloat(price * defaultNum, 2);
      var iterations = 0;
      while (cost <= 0 && iterations < 5) {
        defaultNum *= 10;
        cost = isNaN(price * defaultNum) ? 0 : toFloat(price * defaultNum, 2);
        iterations++;
      }

      var shopItem = {
        id: id,
        name: name,
        price: price,
        minnum: minnum,
        maxnum: maxnum,
        close: close,
        shopimg: shopimg,
        type: type,
        value: item.value || defaultNum,
        original_cname: originalCategoryName
      };
      state.shoplist[id] = JSON.stringify(shopItem);

      fragments.push(
        '<tr data-batchgoods-id="' + escapeHtml(id) + '">'
        + '<td><label class="admin-batchgoods-check">'
        + '<input type="checkbox" class="admin-batchgoods-row-check" name="tid[]" value="' + escapeHtml(id) + '">'
        + '<span></span></label></td>'
        + '<td>' + escapeHtml(name) + '</td>'
        + '<td><span class="admin-batchgoods-price" data-batchgoods-price="' + escapeHtml(id) + '">' + price + '</span></td>'
        + '<td><span class="admin-batchgoods-price admin-batchgoods-price--cost" data-batchgoods-cost="' + escapeHtml(id) + '">' + cost + '</span></td>'
        + '<td>' + minnum + '</td>'
        + '<td>' + (maxnum > 0 ? maxnum : MSG.unlimited) + '</td>'
        + '<td><input type="text" class="form-control input-sm admin-batchgoods-numinput" data-batchgoods-numinput="' + escapeHtml(id) + '" value="' + escapeHtml(defaultNum) + '" required></td>'
        + '<td>' + renderStatus(close) + '</td>'
        + '</tr>'
      );
    }
    $tbody.html(fragments.join(''));
    updateMeta(list.length, 0);
  }

  function fetchPluginGoods(cids) {
    if (!cids || cids.length === 0) return;
    var shequ = $('input[name="shequ"]').val();
    var validCids = [];
    for (var i = 0; i < cids.length; i++) {
      if (cids[i] && cids[i] !== '-1') validCids.push(cids[i]);
    }
    if (validCids.length === 0) return;

    var loader = showLoader();
    resetTable(renderPluginHead());
    updateMeta();
    state.loading = true;

    var loadedCids = 0;
    var totalItems = 0;
    var errorMessages = [];
    var $tbody = $('#shoplist');

    $.each(validCids, function (index, cid) {
      $.ajax({
        type: 'POST',
        url: endpoints.shopAjax + '?act=goodslistbycid',
        dataType: 'json',
        data: { shequ: shequ, cid: cid },
        success: function (data) {
          loadedCids++;
          if (data && data.code === 0) {
            var originalCid = cid;
            var originalCname = $('#cid option[value="' + cid + '"]').text();
            $.each(data.data || [], function (i, item) {
              item.original_cid = originalCid;
              item.original_cname = originalCname;
              state.shoplist[item.tid] = JSON.stringify(item);
              $tbody.append(
                '<tr data-batchgoods-id="' + escapeHtml(item.tid) + '">'
                + '<td><label class="admin-batchgoods-check">'
                + '<input type="checkbox" class="admin-batchgoods-row-check" name="tid[]" value="' + escapeHtml(item.tid) + '">'
                + '<span></span></label></td>'
                + '<td>' + escapeHtml(item.tid) + '</td>'
                + '<td>' + escapeHtml(item.name) + '</td>'
                + '<td><span class="admin-batchgoods-price">' + escapeHtml(item.price) + '</span></td>'
                + '<td>' + renderStatus(item.close) + '</td>'
                + '</tr>'
              );
              totalItems++;
            });
          } else {
            errorMessages.push(MSG.categoryLoadFail + ' ' + $('#cid option[value="' + cid + '"]').text() + ' ' + MSG.loadFailColon + ((data && data.msg) || ''));
          }
          if (loadedCids === validCids.length) finalizePluginLoad(loader, totalItems, errorMessages);
        },
        error: function (xhr, status, error) {
          loadedCids++;
          errorMessages.push(MSG.categoryLoadFail + ' ' + $('#cid option[value="' + cid + '"]').text() + ' ' + MSG.loadFailColon + status + ' ' + error);
          if (loadedCids === validCids.length) finalizePluginLoad(loader, totalItems, errorMessages);
        }
      });
    });
  }

  function finalizePluginLoad(loader, totalItems, errorMessages) {
    layer.close(loader);
    state.loading = false;
    updateMeta(totalItems, 0);
    if (totalItems === 0) {
      if (errorMessages.length > 0) {
        layer.alert(MSG.partialFail + '\n' + errorMessages.join('\n'), { icon: 2 });
      } else {
        layer.msg(MSG.noGoods, { icon: 0, time: 800 });
      }
    } else if (errorMessages.length > 0) {
      layer.alert(MSG.partialFail + '\n' + errorMessages.join('\n'), { icon: 2 });
    }
  }

  function fetchTraditionalCategories() {
    var shequ = $('input[name="shequ"]').val();
    $.ajax({
      type: 'POST',
      url: endpoints.batchDocking + '?act=getCategoryList',
      data: { shequ: shequ },
      dataType: 'json',
      success: function (data) {
        var $sel = $('#category_id');
        if (data && data.code === 0) {
          $sel.empty();
          $sel.append('<option value="-1">--\u8bf7\u9009\u62e9\u5206\u7c7b\uff08\u53ef\u591a\u9009\uff09--</option>');
          $.each(data.data || [], function (i, item) {
            $sel.append('<option value="' + escapeHtml(item.cid) + '">' + escapeHtml(item.name) + '</option>');
          });
          $('#categoryGroup').show();
        } else {
          layer.msg(MSG.fetchCategoryFail + ': ' + ((data && data.msg) || ''), { icon: 2 });
        }
      },
      error: function () {
        layer.msg(MSG.refreshRetry, { icon: 2 });
      }
    });
  }

  function collectSelectedGoods() {
    var list = [];
    var numList = [];
    $('#shoplist .admin-batchgoods-row-check:checked').each(function () {
      var tid = $(this).val();
      if (state.shoplist[tid] !== undefined) {
        list.push(state.shoplist[tid]);
      }
      var $num = $('[data-batchgoods-numinput="' + tid + '"]');
      if ($num.length) {
        numList.push('{"id":"' + tid + '","value":"' + $num.val() + '"}');
      }
    });
    return { list: list, numList: numList };
  }

  function submitBatch() {
    var shequ = $('input[name="shequ"]').val();
    var type = $('input[name="type"]').val();
    var mcid = $('#mcid').val();
    var parentCid = $('#parent_cid').val();
    var prid = $('#prid').val();

    if (mcid === '-1') {
      layer.alert(MSG.needCategory);
      return false;
    }
    if (prid === '-1') {
      layer.alert(MSG.needPriceRule);
      return false;
    }

    var selected = collectSelectedGoods();
    if (selected.list.length === 0) {
      layer.alert(MSG.noneSelected);
      return false;
    }

    var loader = showLoader();
    var ajaxUrl;
    var ajaxData;
    if (isTraditionalType(type)) {
      ajaxUrl = endpoints.batchDocking + '?act=batchaddgoods';
      ajaxData = { shequ: shequ, mcid: mcid, parent_cid: parentCid, prid: prid, list: selected.list, numlist: selected.numList };
    } else {
      ajaxUrl = endpoints.shopAjax + '?act=batchaddgoods';
      var cnames = [];
      $('#cid option:selected').each(function () {
        if ($(this).val() !== '-1') cnames.push($(this).text());
      });
      ajaxData = {
        shequ: shequ,
        mcid: mcid,
        parent_cid: parentCid,
        prid: prid,
        list: selected.list,
        cname: cnames.join(', '),
        cimg: $('#cid option:selected:first').attr('data-shopimg')
      };
    }

    $.ajax({
      type: 'POST',
      url: ajaxUrl,
      dataType: 'json',
      data: ajaxData,
      success: function (data) {
        layer.close(loader);
        if (data && data.code === 0) {
          layer.alert(data.msg, { icon: 1 }, function () { window.location.reload(); });
        } else {
          layer.alert((data && data.msg) || MSG.loadFail, { icon: 2 });
        }
      },
      error: function () {
        layer.close(loader);
        layer.msg(MSG.refreshRetry);
      }
    });
    return true;
  }

  function bindEvents() {
    $('#mcid').on('change', function () {
      $('#parentClassGroup').prop('hidden', $(this).val() !== 'new');
    });

    $(document).on('change', '#batchGoodsSelectAll', function () {
      var checked = this.checked;
      $('#shoplist .admin-batchgoods-row-check').prop('checked', checked);
      refreshCheckedCount();
    });

    $(document).on('change', '#shoplist .admin-batchgoods-row-check', function () {
      refreshCheckedCount();
    });

    $(document).on('input', '[data-batchgoods-numinput]', function () {
      var id = $(this).data('batchgoods-numinput');
      var price = parseFloat($('[data-batchgoods-price="' + id + '"]').text());
      var num = parseInt($(this).val(), 10);
      if (isNaN(price) || price <= 0) price = 9999;
      if (isNaN(num) || num <= 0) num = 1;
      var cost = toFloat(price * num, 2);
      $('[data-batchgoods-cost="' + id + '"]').text(cost);
      if (num <= 0) $(this).val(1);
    });

    $(document).on('click', '[data-batchgoods-action="fetch-goods"]', function () {
      fetchTraditionalGoods();
    });

    $('#category_id').on('change', function () {
      var type = $('input[name="type"]').val();
      if (!isTraditionalType(type)) return;
      var ids = [];
      $('#category_id option:selected').each(function () {
        if ($(this).val() !== '-1') ids.push($(this).val());
      });
      if (ids.length === 0) return;
      fetchTraditionalGoods();
    });

    $('#cid').on('change', function () {
      var type = $('input[name="type"]').val();
      if (isTraditionalType(type)) return;
      var cids = $(this).val();
      if (!cids || cids.length === 0) return;
      if (cids.length === 1 && cids[0] === '-1') return;
      fetchPluginGoods(cids);
    });

    $('#batchGoodsSubmit').on('click', submitBatch);

    $('#batchGoodsForm').on('submit', function (e) {
      e.preventDefault();
      submitBatch();
    });
  }

  $(function () {
    if (config.act !== 'data') return;
    bindEvents();
    updateMeta();
    if (config.isTraditional) {
      fetchTraditionalCategories();
    }
  });
})(window, jQuery);

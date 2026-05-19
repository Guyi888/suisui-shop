(function ($) {
  'use strict';

  var config = window.toolLogsPageConfig || {};
  var endpoints = config.endpoints || {};
  var state = {
    type: config.initialType === 'offline' ? 'offline' : 'online',
    page: 1,
    pageSize: 30,
    keyword: ''
  };

  var MSG = {
    loading: '\u6b63\u5728\u52a0\u8f7d\u5546\u54c1\u52a8\u6001...',
    serverError: '\u670d\u52a1\u5668\u9519\u8bef',
    noSelection: '\u8bf7\u81f3\u5c11\u9009\u62e9\u4e00\u6761\u8bb0\u5f55',
    deleteConfirm: '\u786e\u5b9a\u8981\u5220\u9664\u8fd9\u6761\u52a8\u6001\u5417\uff1f\u5220\u9664\u540e\u65e0\u6cd5\u6062\u590d\u3002',
    batchDeletePrefix: '\u786e\u5b9a\u8981\u5220\u9664\u9009\u4e2d\u7684 ',
    batchDeleteSuffix: ' \u6761\u52a8\u6001\u5417\uff1f\u5220\u9664\u540e\u65e0\u6cd5\u6062\u590d\u3002',
    copyDone: '\u5df2\u590d\u5236\u52a8\u6001\u5185\u5bb9',
    copyFail: '\u590d\u5236\u5931\u8d25\uff0c\u8bf7\u624b\u52a8\u590d\u5236',
    editTitleSuffix: '\u7f16\u8f91',
    editDateLabel: '\u52a8\u6001\u65e5\u671f',
    editItemsLabel: '\u5546\u54c1\u9879',
    editNameLabel: '\u5c55\u793a\u540d\u79f0',
    editTidLabel: '\u7ed1\u5b9a\u5546\u54c1ID',
    editHint: '\u7ed1\u5b9a\u5546\u54c1ID\u540e\uff0c\u524d\u53f0\u5546\u54c1\u52a8\u6001\u4f1a\u4f18\u5148\u6309\u8fd9\u4e2aID\u8df3\u8f6c\uff1b\u7559\u7a7a\u65f6\u624d\u4f1a\u56de\u9000\u5230\u540c\u540d\u5546\u54c1\u81ea\u52a8\u5339\u914d\u3002',
    editAddRow: '\u65b0\u589e\u4e00\u884c',
    editRemoveRow: '\u5220\u9664\u8fd9\u884c',
    editLookup: '\u8bfb\u53d6\u5546\u54c1',
    editLookupLoading: '\u6b63\u5728\u8bfb\u53d6\u5546\u54c1...',
    editLookupNeedTid: '\u8bf7\u5148\u8f93\u5165\u5546\u54c1ID',
    editLookupFail: '\u5546\u54c1\u4e0d\u5b58\u5728\u6216\u5df2\u5931\u6548',
    editLookupOkPrefix: '\u5df2\u7ed1\u5b9a',
    editEmptyRow: '\u8bf7\u81f3\u5c11\u4fdd\u7559\u4e00\u884c\u5546\u54c1\u52a8\u6001',
    invalidDate: '\u8bf7\u8f93\u5165\u6b63\u786e\u7684\u65e5\u671f',
    emptyContent: '\u8bf7\u81f3\u5c11\u8f93\u5165\u4e00\u6761\u5546\u54c1\u52a8\u6001',
    saveSuccess: '\u4fdd\u5b58\u6210\u529f',
    saveButton: '\u4fdd\u5b58',
    cancelButton: '\u53d6\u6d88',
    selectedPrefix: '\u5f53\u524d\u5df2\u9009 ',
    selectedSuffix: ' \u6761\u8bb0\u5f55',
    onlineLabel: '\u4e0a\u67b6\u65e5\u5fd7',
    offlineLabel: '\u4e0b\u67b6\u65e5\u5fd7'
  };

  function setSummary(text) {
    $('#toolLogSummary').text(text);
  }

  function escapeHtml(text) {
    return String(text || '').replace(/[&<>"']/g, function (char) {
      return {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      }[char];
    });
  }

  function getCurrentLabel() {
    return state.type === 'offline' ? MSG.offlineLabel : MSG.onlineLabel;
  }

  function getAdminCsrfToken() {
    return window.ADMIN_CSRF_TOKEN || '';
  }

  function getModalWidth() {
    var viewportWidth = window.innerWidth || document.documentElement.clientWidth || 768;
    if (viewportWidth <= 767) {
      return Math.max(300, viewportWidth - 24) + 'px';
    }
    return '760px';
  }

  function getModalHeight() {
    var viewportHeight = window.innerHeight || document.documentElement.clientHeight || 820;
    if (viewportHeight <= 767) {
      return Math.max(420, viewportHeight - 24) + 'px';
    }
    return Math.max(520, viewportHeight - 72) + 'px';
  }

  function normalizeEntries(entries) {
    var list = [];
    if ($.isArray(entries)) {
      $.each(entries, function (_, entry) {
        var name = $.trim(entry && entry.name ? String(entry.name) : '');
        var tid = parseInt(entry && entry.tid ? entry.tid : 0, 10);
        if (!isFinite(tid)) {
          tid = 0;
        }
        if (name || tid > 0) {
          list.push({ name: name, tid: tid });
        }
      });
    }
    if (!list.length) {
      list.push({ name: '', tid: 0 });
    }
    return list;
  }

  function buildEntryRowHtml(entry) {
    var tidValue = entry && entry.tid ? String(entry.tid) : '';
    return '' +
      '<div class="admin-toollogs-modal__item" data-toollog-item-row>' +
        '<div class="admin-toollogs-modal__field">' +
          '<label class="admin-toollogs-modal__label">' + MSG.editNameLabel + '</label>' +
          '<input type="text" class="form-control" data-toollog-entry-name value="' + escapeHtml(entry && entry.name ? entry.name : '') + '">' +
        '</div>' +
        '<div class="admin-toollogs-modal__bind">' +
          '<div class="admin-toollogs-modal__field admin-toollogs-modal__field--tid">' +
            '<label class="admin-toollogs-modal__label">' + MSG.editTidLabel + '</label>' +
            '<input type="number" min="0" step="1" class="form-control" data-toollog-entry-tid value="' + escapeHtml(tidValue) + '">' +
          '</div>' +
          '<div class="admin-toollogs-modal__buttons">' +
            '<button type="button" class="btn btn-default btn-sm" data-toollog-item-lookup><i class="fa fa-search"></i> ' + MSG.editLookup + '</button>' +
            '<button type="button" class="btn btn-danger btn-sm" data-toollog-item-remove><i class="fa fa-trash"></i> ' + MSG.editRemoveRow + '</button>' +
          '</div>' +
        '</div>' +
        '<div class="admin-toollogs-modal__status" data-toollog-entry-status></div>' +
      '</div>';
  }

  function buildEditModalHtml(dateValue, entries) {
    var rows = '';
    $.each(normalizeEntries(entries), function (_, entry) {
      rows += buildEntryRowHtml(entry);
    });
    return '' +
      '<div class="admin-toollogs-modal">' +
        '<div class="admin-toollogs-modal__grid">' +
          '<div class="admin-toollogs-modal__field">' +
            '<label class="admin-toollogs-modal__label" for="toolLogEditDate">' + MSG.editDateLabel + '</label>' +
            '<input type="date" class="form-control" id="toolLogEditDate" data-toollog-modal="date" value="' + escapeHtml(dateValue) + '">' +
            '<p class="admin-toollogs-modal__hint">' + MSG.editHint + '</p>' +
          '</div>' +
          '<div class="admin-toollogs-modal__field">' +
            '<label class="admin-toollogs-modal__label">' + MSG.editItemsLabel + '</label>' +
            '<div class="admin-toollogs-modal__items" data-toollog-items>' + rows + '</div>' +
            '<button type="button" class="btn btn-primary btn-sm admin-toollogs-modal__add" data-toollog-item-add><i class="fa fa-plus"></i> ' + MSG.editAddRow + '</button>' +
          '</div>' +
        '</div>' +
      '</div>';
  }

  function renderLoading() {
    $('#toolLogTable').html(
      '<div class="admin-toollogs-loading"><i class="fa fa-circle-o-notch fa-spin"></i><span>' +
        MSG.loading +
      '</span></div>'
    );
  }

  function updateTabState() {
    $('[data-toollog-type]').removeClass('is-active');
    $('[data-toollog-type="' + state.type + '"]').addClass('is-active');
  }

  function updateSelectedCount() {
    var count = $('[data-toollog-checkbox]:checked').length;
    $('[data-toollog-selected-count]').text(count);
    setSummary(getCurrentLabel() + '\uff1a' + MSG.selectedPrefix + count + MSG.selectedSuffix);
  }

  function syncHistory() {
    var query = [];
    query.push('log_type=' + encodeURIComponent(state.type));
    if (state.page > 1) {
      query.push('page=' + encodeURIComponent(String(state.page)));
    }
    if (state.keyword) {
      query.push('keyword=' + encodeURIComponent(state.keyword));
    }
    if (state.pageSize !== 30) {
      query.push('num=' + encodeURIComponent(String(state.pageSize)));
    }
    var nextUrl = './toollogs.php' + (query.length ? '?' + query.join('&') : '');
    if (window.history && typeof window.history.replaceState === 'function') {
      window.history.replaceState({}, document.title, nextUrl);
    }
  }

  function loadTable() {
    updateTabState();
    syncHistory();
    renderLoading();
    $.ajax({
      type: 'GET',
      url: endpoints.table,
      cache: false,
      data: {
        log_type: state.type,
        page: state.page,
        num: state.pageSize,
        keyword: state.keyword
      },
      dataType: 'html'
    }).done(function (html) {
      $('#toolLogTable').html(html);
      updateSelectedCount();
      var summaryMeta = $('.admin-toollogs-summary__meta').text().replace(/\s+/g, ' ').trim();
      if (summaryMeta) {
        setSummary(summaryMeta);
      }
    }).fail(function () {
      $('#toolLogTable').html(
        '<div class="admin-toollogs-empty"><i class="fa fa-warning"></i><strong>' +
          MSG.serverError +
        '</strong><p>\u9875\u9762\u6ca1\u6709\u52a0\u8f7d\u6210\u529f\uff0c\u53ef\u4ee5\u8bd5\u4e00\u6b21\u5237\u65b0\u6216\u7a0d\u540e\u518d\u8bd5\u3002</p></div>'
      );
      setSummary(MSG.serverError);
    });
  }

  function deleteLogs(ids) {
    var normalizedIds = $.map(ids || [], function (id) {
      var value = parseInt(id, 10);
      return isFinite(value) && value > 0 ? String(value) : null;
    });
    if (!normalizedIds.length) {
      layer.msg(MSG.noSelection, { icon: 2, time: 2200 });
      return;
    }

    var requestUrl = normalizedIds.length === 1 ? endpoints.delete : endpoints.batch;
    var requestData = normalizedIds.length === 1 ? {
      id: normalizedIds[0],
      log_type: state.type
    } : {
      aid: 1,
      log_type: state.type,
      checkbox: normalizedIds
    };
    var loadingIndex = layer.load(2, { shade: [0.08, '#fff'] });
    $.ajax({
      type: 'POST',
      url: requestUrl,
      dataType: 'json',
      data: $.extend({ csrf_token: getAdminCsrfToken() }, requestData)
    }).done(function (data) {
      if (data && Number(data.code) === 0) {
        layer.msg(data.msg || '\u5220\u9664\u6210\u529f', { icon: 1, time: 1800 });
        loadTable();
      } else {
        layer.alert((data && data.msg) || MSG.serverError, { icon: 2 });
      }
    }).fail(function () {
      layer.msg(MSG.serverError, { icon: 2, time: 2200 });
    }).always(function () {
      layer.close(loadingIndex);
    });
  }

  function saveLog(payload, modalIndex) {
    var loadingIndex = layer.load(2, { shade: [0.08, '#fff'] });
    $.ajax({
      type: 'POST',
      url: endpoints.save,
      dataType: 'json',
      data: $.extend({ csrf_token: getAdminCsrfToken() }, payload)
    }).done(function (data) {
      if (data && Number(data.code) === 0) {
        if (typeof modalIndex === 'number') {
          layer.close(modalIndex);
        }
        layer.msg(data.msg || MSG.saveSuccess, { icon: 1, time: 1800 });
        loadTable();
      } else {
        layer.alert((data && data.msg) || MSG.serverError, { icon: 2 });
      }
    }).fail(function () {
      layer.msg(MSG.serverError, { icon: 2, time: 2200 });
    }).always(function () {
      layer.close(loadingIndex);
    });
  }

  function copyText(text) {
    var value = String(text || '');
    if (!value) {
      layer.msg(MSG.copyFail, { icon: 2, time: 2200 });
      return;
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(value).then(function () {
        layer.msg(MSG.copyDone, { icon: 1, time: 1600 });
      }).catch(function () {
        layer.msg(MSG.copyFail, { icon: 2, time: 2200 });
      });
      return;
    }

    var $temp = $('<textarea></textarea>').val(value).css({
      position: 'fixed',
      top: '-9999px',
      left: '-9999px'
    }).appendTo('body');
    $temp.trigger('focus').trigger('select');
    try {
      document.execCommand('copy');
      layer.msg(MSG.copyDone, { icon: 1, time: 1600 });
    } catch (error) {
      layer.msg(MSG.copyFail, { icon: 2, time: 2200 });
    }
    $temp.remove();
  }

  function collectEditEntries(layero) {
    var entries = [];
    layero.find('[data-toollog-item-row]').each(function () {
      var name = $.trim($(this).find('[data-toollog-entry-name]').val() || '');
      var tid = parseInt($(this).find('[data-toollog-entry-tid]').val() || '0', 10);
      if (!isFinite(tid)) {
        tid = 0;
      }
      if (name || tid > 0) {
        entries.push({
          name: name,
          tid: tid > 0 ? tid : 0
        });
      }
    });
    return entries;
  }

  function setEntryStatus(row, text, isError) {
    var $status = $(row).find('[data-toollog-entry-status]');
    $status.text(text || '');
    $status.toggleClass('is-error', !!isError);
    $status.toggleClass('is-success', !isError && !!text);
  }

  function lookupTool(row) {
    var $row = $(row);
    var tid = parseInt($row.find('[data-toollog-entry-tid]').val() || '0', 10);
    if (!isFinite(tid) || tid < 1) {
      layer.msg(MSG.editLookupNeedTid, { icon: 2, time: 2200 });
      return;
    }
    setEntryStatus($row, MSG.editLookupLoading, false);
    $.ajax({
      type: 'GET',
      url: endpoints.lookup,
      cache: false,
      dataType: 'json',
      data: { tid: tid }
    }).done(function (data) {
      if (data && Number(data.code) === 0 && data.data) {
        $row.find('[data-toollog-entry-name]').val(data.data.name || '');
        setEntryStatus($row, MSG.editLookupOkPrefix + ' #' + tid + ' ' + (data.data.name || ''), false);
      } else {
        setEntryStatus($row, (data && data.msg) || MSG.editLookupFail, true);
      }
    }).fail(function () {
      setEntryStatus($row, MSG.serverError, true);
    });
  }

  function openEditDialog(button) {
    var $button = $(button);
    var id = $button.attr('data-id');
    var $row = $button.closest('[data-toollog-row]');
    var dateValue = $.trim($button.attr('data-date') || '');
    var entryText = String($row.find('.admin-toollogs-row__items-json').val() || '[]');
    var entries = [];
    try {
      entries = JSON.parse(entryText);
    } catch (error) {
      entries = [];
    }
    if (!id) {
      layer.msg(MSG.serverError, { icon: 2, time: 2200 });
      return;
    }

    var modalIndex = layer.open({
      type: 1,
      title: getCurrentLabel() + MSG.editTitleSuffix,
      area: [getModalWidth(), getModalHeight()],
      skin: 'admin-toollogs-layer',
      shadeClose: true,
      closeBtn: 1,
      resize: false,
      move: false,
      btn: [MSG.saveButton, MSG.cancelButton],
      content: buildEditModalHtml(dateValue, entries),
      success: function (layero) {
        layero.find('.layui-layer-content').css('overflow', 'auto');
        layero.find('[data-toollog-entry-name]').first().trigger('focus');
      },
      yes: function (index, layero) {
        var dateText = $.trim(layero.find('[data-toollog-modal="date"]').val() || '');
        var editEntries = collectEditEntries(layero);
        if (!/^\d{4}-\d{2}-\d{2}$/.test(dateText)) {
          layer.msg(MSG.invalidDate, { icon: 2, time: 2200 });
          return;
        }
        if (!editEntries.length) {
          layer.msg(MSG.emptyContent, { icon: 2, time: 2200 });
          return;
        }
        saveLog({
          id: id,
          log_type: state.type,
          date: dateText,
          items_json: JSON.stringify(editEntries)
        }, index);
      }
    });

    return modalIndex;
  }

  $(document).on('click', '[data-toollog-type]', function () {
    var nextType = $(this).attr('data-toollog-type') === 'offline' ? 'offline' : 'online';
    if (state.type === nextType) {
      return;
    }
    state.type = nextType;
    state.page = 1;
    loadTable();
  });

  $(document).on('click', '[data-toollog-action="search"]', function () {
    state.keyword = $.trim($('#toolLogKeyword').val() || '');
    state.page = 1;
    loadTable();
  });

  $(document).on('click', '[data-toollog-action="reset"]', function () {
    $('#toolLogKeyword').val('');
    $('#toolLogPageSize').val('30');
    state.keyword = '';
    state.pageSize = 30;
    state.page = 1;
    loadTable();
  });

  $(document).on('change', '#toolLogPageSize', function () {
    var nextSize = parseInt($(this).val() || '30', 10);
    if (!isFinite(nextSize)) {
      nextSize = 30;
    }
    state.pageSize = nextSize;
    state.page = 1;
    loadTable();
  });

  $(document).on('keydown', '#toolLogKeyword', function (event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      state.keyword = $.trim($('#toolLogKeyword').val() || '');
      state.page = 1;
      loadTable();
    }
  });

  $(document).on('change', '[data-toollog-check-all]', function () {
    var checked = $(this).prop('checked');
    $('[data-toollog-checkbox]').prop('checked', checked);
    updateSelectedCount();
  });

  $(document).on('change', '[data-toollog-checkbox]', function () {
    var total = $('[data-toollog-checkbox]').length;
    var selected = $('[data-toollog-checkbox]:checked').length;
    $('[data-toollog-check-all]').prop('checked', total > 0 && total === selected);
    updateSelectedCount();
  });

  $(document).on('click', '[data-toollog-action="clear-selection"]', function () {
    $('[data-toollog-check-all], [data-toollog-checkbox]').prop('checked', false);
    updateSelectedCount();
  });

  $(document).on('click', '[data-toollog-action="batch-delete"]', function () {
    var ids = $('[data-toollog-checkbox]:checked').map(function () {
      return $(this).val();
    }).get();
    if (!ids.length) {
      layer.msg(MSG.noSelection, { icon: 2, time: 2200 });
      return;
    }
    layer.confirm(MSG.batchDeletePrefix + ids.length + MSG.batchDeleteSuffix, {
      btn: ['\u786e\u5b9a', '\u53d6\u6d88'],
      icon: 3
    }, function (index) {
      layer.close(index);
      deleteLogs(ids);
    });
  });

  $(document).on('click', '[data-toollog-action="delete"]', function () {
    var id = $(this).attr('data-id');
    if (!id) {
      return;
    }
    layer.confirm(MSG.deleteConfirm, {
      btn: ['\u786e\u5b9a', '\u53d6\u6d88'],
      icon: 3
    }, function (index) {
      layer.close(index);
      deleteLogs([id]);
    });
  });

  $(document).on('click', '[data-toollog-action="copy"]', function () {
    copyText($(this).attr('data-content') || '');
  });

  $(document).on('click', '[data-toollog-action="edit"]', function () {
    openEditDialog(this);
  });

  $(document).on('click', '[data-toollog-item-add]', function () {
    var $items = $(this).closest('.admin-toollogs-modal').find('[data-toollog-items]');
    $items.append(buildEntryRowHtml({ name: '', tid: 0 }));
    $items.find('[data-toollog-item-row]').last().find('[data-toollog-entry-name]').trigger('focus');
  });

  $(document).on('click', '[data-toollog-item-remove]', function () {
    var $items = $(this).closest('.admin-toollogs-modal').find('[data-toollog-items]');
    if ($items.find('[data-toollog-item-row]').length <= 1) {
      layer.msg(MSG.editEmptyRow, { icon: 2, time: 2200 });
      return;
    }
    $(this).closest('[data-toollog-item-row]').remove();
  });

  $(document).on('click', '[data-toollog-item-lookup]', function () {
    lookupTool($(this).closest('[data-toollog-item-row]'));
  });

  $(document).on('click', '[data-toollog-page]', function () {
    var nextPage = parseInt($(this).attr('data-toollog-page') || '1', 10);
    if (!isFinite(nextPage) || nextPage < 1) {
      nextPage = 1;
    }
    state.page = nextPage;
    loadTable();
  });

  $(function () {
    var initialKeyword = '';
    try {
      var search = new URLSearchParams(window.location.search || '');
      if (search.has('keyword')) {
        initialKeyword = $.trim(search.get('keyword') || '');
      }
      if (search.has('num')) {
        var initialSize = parseInt(search.get('num') || '30', 10);
        if (isFinite(initialSize)) {
          state.pageSize = initialSize;
          $('#toolLogPageSize').val(String(initialSize));
        }
      }
      if (search.has('page')) {
        var initialPage = parseInt(search.get('page') || '1', 10);
        if (isFinite(initialPage) && initialPage > 0) {
          state.page = initialPage;
        }
      }
    } catch (error) {}

    if (initialKeyword) {
      state.keyword = initialKeyword;
      $('#toolLogKeyword').val(initialKeyword);
    }

    loadTable();
  });
})(jQuery);

(function ($) {
  'use strict';

  var config = window.messageCenterConfig || {};
  var endpoints = config.endpoints || {};
  var runLocks = {};
  var activeDetailTaskId = 0;
  var activeDetailLayerIndex = null;
  var lookupTimer = null;
  var lastSyncNoticeChecked = true;

  var MSG = {
    composeBusy: '\u6b63\u5728\u521b\u5efa\u53d1\u9001\u4efb\u52a1...',
    subjectRequired: '\u8bf7\u5148\u8f93\u5165\u90ae\u4ef6\u4e3b\u9898',
    contentRequired: '\u8bf7\u5148\u8f93\u5165\u90ae\u4ef6\u5185\u5bb9',
    targetRequired: '\u8bf7\u5148\u8f93\u5165\u8981\u53d1\u9001\u7684\u7528\u6237 UID\u3001\u8d26\u53f7\u6216 QQ',
    serverError: '\u670d\u52a1\u5668\u9519\u8bef',
    taskCreated: '\u53d1\u9001\u4efb\u52a1\u5df2\u521b\u5efa',
    taskSending: '\u4efb\u52a1\u6b63\u5728\u53d1\u9001...',
    taskCompleted: '\u4efb\u52a1\u53d1\u9001\u5b8c\u6210',
    taskRunning: '\u8fd9\u4e2a\u4efb\u52a1\u5df2\u5728\u53d1\u9001\u4e2d',
    retryDone: '\u5931\u8d25\u9879\u5df2\u91cd\u7f6e\uff0c\u6b63\u5728\u91cd\u65b0\u53d1\u9001',
    noTaskDetail: '\u53d1\u9001\u660e\u7ec6\u4e0d\u5b58\u5728',
    previewLoading: '\u6b63\u5728\u52a0\u8f7d\u901a\u77e5...',
    noticeShow: '\u5df2\u8bbe\u4e3a\u663e\u793a',
    noticeHide: '\u5df2\u8bbe\u4e3a\u9690\u85cf',
    noNotice: '\u8fd9\u6761\u901a\u77e5\u4e0d\u5b58\u5728',
    detailTitle: '\u53d1\u9001\u660e\u7ec6',
    detailScope: '\u53d1\u9001\u8303\u56f4',
    detailStatus: '\u4efb\u52a1\u72b6\u6001',
    detailProgress: '\u53d1\u9001\u8fdb\u5ea6',
    detailTime: '\u65f6\u95f4',
    detailCreator: '\u521b\u5efa\u4eba',
    detailNotice: '\u901a\u77e5',
    detailNoticeYes: '\u5df2\u540c\u6b65',
    detailNoticeNo: '\u672a\u540c\u6b65',
    detailPending: '\u5f85\u53d1',
    detailSuccess: '\u6210\u529f',
    detailFail: '\u5931\u8d25',
    detailRun: '\u7ee7\u7eed\u53d1\u9001',
    detailRetry: '\u91cd\u8bd5\u5931\u8d25',
    detailEmpty: '\u6682\u65f6\u6ca1\u6709\u6536\u4ef6\u4eba\u660e\u7ec6',
    detailClose: '\u5173\u95ed',
    viewNotice: '\u901a\u77e5\u9884\u89c8',
    syncNoticeLabel: '\u662f',
    noSyncNoticeLabel: '\u5426',
    pendingLabel: '\u5f85\u53d1\u9001',
    successLabel: '\u5df2\u53d1\u9001',
    failLabel: '\u53d1\u9001\u5931\u8d25',
    composeReady: '\u90ae\u7bb1\u901a\u9053\u53ef\u7528\uff0c\u53ef\u76f4\u63a5\u521b\u5efa\u5e76\u53d1\u9001\u4efb\u52a1\u3002',
    composeSaved: '\u4efb\u52a1\u5df2\u521b\u5efa\uff0c\u53ef\u4ee5\u7a0d\u540e\u518d\u7ee7\u7eed\u53d1\u9001\u3002',
    composeSending: '\u6b63\u5728\u53d1\u9001\u4efb\u52a1\uff0c\u53ef\u4ee5\u7559\u5728\u8fd9\u4e2a\u9875\u9762\u7b49\u5019\u7ed3\u679c\u3002',
    taskRowEmptyTitle: '\u8fd8\u6ca1\u6709\u521b\u5efa\u8fc7\u90ae\u4ef6\u4efb\u52a1',
    taskRowEmptyBody: '\u5148\u5728\u5de6\u4fa7\u586b\u597d\u4e3b\u9898\u548c\u5185\u5bb9\uff0c\u7cfb\u7edf\u4f1a\u81ea\u52a8\u751f\u6210\u53d1\u9001\u660e\u7ec6\u5e76\u8bb0\u5f55\u7ed3\u679c\u3002',
    taskMetaRecent: '\u6700\u8fd1\u4fdd\u7559 12 \u4e2a\u4efb\u52a1\u6982\u89c8',
    taskMetaRunningPrefix: '\u6b63\u5728\u53d1\u9001\u4efb\u52a1 #',
    taskMetaRunningSuffix: '\uff0c\u5f53\u524d\u6709 ',
    taskMetaRunningTail: ' \u4e2a\u6536\u4ef6\u4eba\u7b49\u5f85\u5904\u7406\u3002',
    progressPrefix: '\u6210\u529f ',
    progressMiddle: ' / \u5931\u8d25 ',
    progressTail: ' / \u5f85\u53d1 ',
    noticeContentEmpty: '\u6682\u65e0\u901a\u77e5\u5185\u5bb9',
    lookupIdleTitle: '\u8f93\u5165\u7528\u6237 UID\u3001\u8d26\u53f7\u6216 QQ \u540e\u4f1a\u5728\u8fd9\u91cc\u663e\u793a\u5339\u914d\u7ed3\u679c',
    lookupIdleBody: '\u4f8b\u5982\uff1a206554\u3001wx1337\u30012081779218',
    lookupLoading: '\u6b63\u5728\u5339\u914d\u6307\u5b9a\u7528\u6237...',
    lookupFoundPrefix: '\u5df2\u5339\u914d\u5230\uff1a',
    lookupInvalid: '\u672a\u627e\u5230\u53ef\u53d1\u9001\u7684\u76ee\u6807\u7528\u6237',
    syncNoticeDisabled: '\u6307\u5b9a\u5355\u4e2a\u7528\u6237\u65f6\uff0c\u7ad9\u5185\u901a\u77e5\u4f1a\u81ea\u52a8\u5173\u95ed\uff0c\u907f\u514d\u8bef\u53d1\u5168\u7ad9\u3002',
    syncNoticeEnabled: '\u52fe\u9009\u540e\u4f1a\u540c\u6b65\u751f\u6210\u4e00\u6761\u540c\u8303\u56f4\u7684\u7ad9\u5185\u901a\u77e5\uff0c\u4e3b\u7ad9\u666e\u901a\u7528\u6237\u4e0e\u5206\u7ad9\u4e0b\u7ea7\u7528\u6237\u5df2\u62c6\u5206\u53ef\u907f\u514d\u4e32\u7ebf\u3002',
    runButton: '\u7ee7\u7eed\u53d1\u9001',
    retryButton: '\u91cd\u8bd5\u5931\u8d25',
    noticeButton: '\u901a\u77e5',
    detailButton: '\u660e\u7ec6',
    hiddenLabel: '\u5df2\u9690\u85cf',
    visibleLabel: '\u663e\u793a\u4e2d',
    noticeEdit: '\u7f16\u8f91\u901a\u77e5',
    noticeSave: '\u4fdd\u5b58\u901a\u77e5',
    noticeSaved: '\u901a\u77e5\u5df2\u4fdd\u5b58',
    noticeTitleRequired: '\u8bf7\u5148\u8f93\u5165\u901a\u77e5\u6807\u9898',
    noticeContentRequired: '\u8bf7\u5148\u8f93\u5165\u901a\u77e5\u5185\u5bb9',
    noticeScopeRequired: '\u8bf7\u5148\u9009\u62e9\u901a\u77e5\u53ef\u89c1\u8303\u56f4',
    noticeScope: '\u53ef\u89c1\u8303\u56f4',
    noticeStatus: '\u663e\u793a\u72b6\u6001',
    noticeReadCount: '\u5df2\u8bfb\u6b21\u6570',
    noticeShown: '\u663e\u793a\u4e2d',
    noticeHidden: '\u5df2\u9690\u85cf'
  };

  var SCOPE_LABELS = {
    0: '\u5168\u90e8\u7528\u6237',
    1: '\u5168\u90e8\u666e\u901a\u7528\u6237',
    2: '\u5168\u90e8\u5206\u7ad9\u7ad9\u957f',
    3: '\u666e\u53ca\u7248\u5206\u7ad9\u7ad9\u957f',
    4: '\u4e13\u4e1a\u7248\u5206\u7ad9\u7ad9\u957f',
    5: '\u4e3b\u7ad9\u666e\u901a\u7528\u6237',
    6: '\u5206\u7ad9\u4e0b\u7ea7\u666e\u901a\u7528\u6237'
  };

  function escapeHtml(text) {
    return String(text == null ? '' : text).replace(/[&<>"']/g, function (char) {
      return {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      }[char];
    });
  }

  function getCsrfToken() {
    return window.ADMIN_CSRF_TOKEN || '';
  }

  function setComposeMeta(text) {
    $('#messageComposeMeta').text(text);
  }

  function buildScopeOptions(selectedValue) {
    var html = '';
    var selected = Number(selectedValue || 0);
    $.each(SCOPE_LABELS, function (scopeValue, scopeLabel) {
      var numericValue = Number(scopeValue);
      html += '<option value="' + numericValue + '"' + (numericValue === selected ? ' selected' : '') + '>' + escapeHtml(scopeLabel) + '</option>';
    });
    return html;
  }

  function setTaskMeta(text) {
    $('#messageTaskMeta').text(text);
  }

  function hydrateProgress(root) {
    $(root || document).find('[data-progress-fill]').each(function () {
      var percent = parseInt($(this).attr('data-progress-fill'), 10);
      if (!isFinite(percent) || percent < 0) {
        percent = 0;
      }
      if (percent > 100) {
        percent = 100;
      }
      this.style.width = percent + '%';
    });
  }

  function statusClass(task) {
    var fail = Number(task.fail_count || 0);
    var pending = Number(task.pending_count || 0);
    var status = Number(task.status || 0);
    if (status === 1 || pending > 0) {
      return 'is-running';
    }
    if (fail > 0) {
      return 'is-warning';
    }
    return 'is-success';
  }

  function normalizeTask(task) {
    var data = $.extend({}, task || {});
    var scope = Number(data.scope || 0);
    var targetMode = Number(data.target_mode || 0);
    var targetLabel = $.trim(data.target_label || '');
    var targetValue = $.trim(data.target_value || '');
    var status = Number(data.status || 0);

    if (targetMode === 1) {
      data.scope_text = targetLabel ? ('\u6307\u5b9a\u7528\u6237 \u00b7 ' + targetLabel) : (targetValue ? ('\u6307\u5b9a\u7528\u6237 \u00b7 ' + targetValue) : '\u6307\u5b9a\u7528\u6237');
    } else {
      data.scope_text = SCOPE_LABELS.hasOwnProperty(scope) ? SCOPE_LABELS[scope] : SCOPE_LABELS[0];
    }

    if (status === 1) {
      data.status_text = '\u53d1\u9001\u4e2d';
    } else if (status === 2) {
      data.status_text = '\u5df2\u5b8c\u6210';
    } else {
      data.status_text = '\u5f85\u53d1\u9001';
    }

    return data;
  }

  function progressPercent(task) {
    var total = Number(task.total_count || 0);
    if (!total) {
      return 0;
    }
    var finished = Number(task.success_count || 0) + Number(task.fail_count || 0);
    var percent = Math.round(finished * 100 / total);
    if (percent < 0) {
      return 0;
    }
    if (percent > 100) {
      return 100;
    }
    return percent;
  }

  function buildTaskActions(task) {
    var html = '';
    var taskId = Number(task.id || 0);
    html += '<button type="button" class="admin-message-mini-button" data-message-action="view" data-task-id="' + taskId + '"><i class="fa fa-eye"></i> ' + MSG.detailButton + '</button>';
    if (Number(task.pending_count || 0) > 0) {
      html += '<button type="button" class="admin-message-mini-button admin-message-mini-button--primary" data-message-action="run" data-task-id="' + taskId + '"><i class="fa fa-play"></i> ' + MSG.runButton + '</button>';
    }
    if (Number(task.fail_count || 0) > 0) {
      html += '<button type="button" class="admin-message-mini-button admin-message-mini-button--warning" data-message-action="retry" data-task-id="' + taskId + '"><i class="fa fa-refresh"></i> ' + MSG.retryButton + '</button>';
    }
    if (Number(task.notice_id || 0) > 0) {
      html += '<button type="button" class="admin-message-mini-button" data-message-action="notice" data-notice-id="' + Number(task.notice_id || 0) + '"><i class="fa fa-commenting"></i> ' + MSG.noticeButton + '</button>';
    }
    return html;
  }

  function buildTaskRow(task) {
    task = normalizeTask(task);
    var subject = escapeHtml(task.subject || '');
    var scopeText = escapeHtml(task.scope_text || '');
    var total = Number(task.total_count || 0);
    var success = Number(task.success_count || 0);
    var fail = Number(task.fail_count || 0);
    var pending = Number(task.pending_count || 0);
    var statusText = escapeHtml(task.status_text || '');
    var errorText = escapeHtml(task.last_error || '');
    var addtime = escapeHtml(task.addtime || '');
    var endtime = task.endtime ? '\u5b8c\u6210\uff1a' + escapeHtml(task.endtime) : (task.starttime ? '\u5f00\u59cb\uff1a' + escapeHtml(task.starttime) : '\u5c1a\u672a\u5f00\u59cb');
    var percent = progressPercent(task);
    var subline = '<span>' + scopeText + '</span><span>' + total + ' \u4eba</span>';

    if (Number(task.sync_notice || 0) === 1) {
      subline += '<span>\u5df2\u540c\u6b65\u901a\u77e5</span>';
    }

    return '' +
      '<tr class="admin-message-task-row" data-mail-task-id="' + Number(task.id || 0) + '">' +
        '<td>' +
          '<div class="admin-message-task__subject">' +
            '<strong>' + subject + '</strong>' +
            '<div class="admin-message-task__subline">' + subline + '</div>' +
          '</div>' +
        '</td>' +
        '<td>' +
          '<div class="admin-message-task__progress">' +
            '<div class="admin-message-task__progress-track">' +
              '<div class="admin-message-task__progress-fill" data-progress-fill="' + percent + '"></div>' +
            '</div>' +
            '<div class="admin-message-task__progress-meta">' +
              '<span>\u6210\u529f ' + success + '</span>' +
              '<span>\u5931\u8d25 ' + fail + '</span>' +
              '<span>\u5f85\u53d1 ' + pending + '</span>' +
            '</div>' +
          '</div>' +
        '</td>' +
        '<td>' +
          '<span class="admin-message-task__status ' + statusClass(task) + '">' + statusText + '</span>' +
          (errorText ? '<div class="admin-message-task__error">' + errorText + '</div>' : '') +
        '</td>' +
        '<td>' +
          '<div class="admin-message-task__time">' +
            '<span>\u521b\u5efa\uff1a' + addtime + '</span>' +
            '<span>' + endtime + '</span>' +
          '</div>' +
        '</td>' +
        '<td>' +
          '<div class="admin-message-task__actions">' + buildTaskActions(task) + '</div>' +
        '</td>' +
      '</tr>';
  }

  function renderEmptyRow() {
    return '' +
      '<tr class="admin-message-empty-row" data-message-empty-row>' +
        '<td colspan="5">' +
          '<div class="admin-message-empty">' +
            '<i class="fa fa-inbox"></i>' +
            '<strong>' + MSG.taskRowEmptyTitle + '</strong>' +
            '<p>' + MSG.taskRowEmptyBody + '</p>' +
          '</div>' +
        '</td>' +
      '</tr>';
  }

  function upsertTaskRow(task) {
    task = normalizeTask(task);
    var $body = $('#messageTaskTableBody');
    var $row = $body.find('[data-mail-task-id="' + Number(task.id || 0) + '"]');

    $body.find('[data-message-empty-row]').remove();

    if ($row.length) {
      $row.replaceWith(buildTaskRow(task));
    } else {
      $body.prepend(buildTaskRow(task));
    }

    hydrateProgress($body);
  }

  function setComposeBusy(isBusy) {
    $('[data-message-submit]').prop('disabled', !!isBusy || Number(config.mailReady || 0) !== 1);
  }

  function getRecipientMode() {
    return $('#messageRecipientModeInput').val() === 'single' ? 'single' : 'scope';
  }

  function setTargetPreview(state, title, body) {
    var $preview = $('#messageTargetPreview');
    if (!$preview.length) {
      return;
    }

    $preview
      .removeClass('is-loading is-success is-error is-muted')
      .addClass(state ? ('is-' + state) : '');

    $preview.html('' +
      '<div class="admin-message-target-preview__icon"><i class="fa ' +
      (state === 'success' ? 'fa-check-circle' : (state === 'error' ? 'fa-exclamation-circle' : (state === 'loading' ? 'fa-spinner fa-spin' : 'fa-search'))) +
      '"></i></div>' +
      '<div class="admin-message-target-preview__body">' +
        '<strong>' + escapeHtml(title) + '</strong>' +
        '<p>' + escapeHtml(body) + '</p>' +
      '</div>');
  }

  function setRecipientMode(mode) {
    mode = mode === 'single' ? 'single' : 'scope';

    $('#messageRecipientModeInput').val(mode);
    $('[data-message-mode]')
      .removeClass('is-active')
      .filter('[data-message-mode="' + mode + '"]')
      .addClass('is-active');

    $('#messageScopeWrap').toggleClass('admin-message-field--hidden', mode !== 'scope');
    $('#messageTargetWrap').toggleClass('admin-message-field--hidden', mode !== 'single');

    var $sync = $('#messageSyncNotice');
    var $hint = $('#messageSyncNoticeHint');

    if (mode === 'single') {
      lastSyncNoticeChecked = $sync.is(':checked');
      $sync.prop('checked', false).prop('disabled', true);
      $hint.text(MSG.syncNoticeDisabled);
      $('#messageTargetPreview').prop('hidden', false);

      var currentTarget = $.trim($('#messageTargetValue').val());
      if (currentTarget) {
        lookupTarget(true);
      } else {
        setTargetPreview('muted', MSG.lookupIdleTitle, MSG.lookupIdleBody);
      }
    } else {
      $sync.prop('disabled', false).prop('checked', lastSyncNoticeChecked);
      $hint.text(MSG.syncNoticeEnabled);
      $('#messageTargetPreview').prop('hidden', true);
    }
  }

  function lookupTarget(force) {
    if (getRecipientMode() !== 'single' || !endpoints.lookup) {
      return;
    }

    var targetValue = $.trim($('#messageTargetValue').val());
    if (!targetValue) {
      setTargetPreview('muted', MSG.lookupIdleTitle, MSG.lookupIdleBody);
      return;
    }

    if (!force && targetValue.length < 2) {
      return;
    }

    setTargetPreview('loading', MSG.lookupLoading, targetValue);

    $.ajax({
      type: 'GET',
      url: endpoints.lookup,
      dataType: 'json',
      data: { target_value: targetValue }
    }).done(function (data) {
      if (!data || Number(data.code) !== 0 || !data.recipient) {
        setTargetPreview('error', (data && data.msg) || MSG.lookupInvalid, targetValue);
        return;
      }

      var recipient = data.recipient;
      setTargetPreview(
        'success',
        MSG.lookupFoundPrefix + ' ' + (recipient.username || '--'),
        'UID ' + Number(recipient.zid || 0) + ' / ' + (recipient.qq || '--') + '@qq.com'
      );
    }).fail(function () {
      setTargetPreview('error', MSG.serverError, targetValue);
    });
  }

  function resetComposeForm() {
    var $form = $('#messageComposeForm');
    if ($form.length && $form[0]) {
      $form[0].reset();
    }
    lastSyncNoticeChecked = true;
    setRecipientMode('scope');
    setComposeMeta(Number(config.mailReady || 0) === 1 ? MSG.composeReady : MSG.serverError);
  }

  function collectFormData() {
    var $form = $('#messageComposeForm');
    var subject = $.trim($form.find('[name="subject"]').val());
    var content = $.trim($form.find('[name="content"]').val());
    var scope = parseInt($form.find('[name="scope"]').val(), 10);
    var recipientMode = getRecipientMode();
    var targetValue = $.trim($form.find('[name="target_value"]').val());
    var syncNotice = $form.find('[name="sync_notice"]').is(':checked') ? 1 : 0;

    if (!subject) {
      layer.msg(MSG.subjectRequired, { icon: 2, time: 2200 });
      return null;
    }
    if (!content) {
      layer.msg(MSG.contentRequired, { icon: 2, time: 2200 });
      return null;
    }
    if (!isFinite(scope)) {
      scope = 0;
    }
    if (recipientMode === 'single' && !targetValue) {
      layer.msg(MSG.targetRequired, { icon: 2, time: 2200 });
      return null;
    }

    return {
      subject: subject,
      content: content,
      scope: scope,
      sync_notice: syncNotice,
      recipient_mode: recipientMode,
      target_value: targetValue
    };
  }

  function createTask(autoRun) {
    if (Number(config.mailReady || 0) !== 1) {
      layer.msg(MSG.serverError, { icon: 2, time: 2200 });
      return;
    }

    var payload = collectFormData();
    if (!payload) {
      return;
    }

    setComposeBusy(true);
    setComposeMeta(autoRun ? MSG.composeSending : MSG.composeBusy);

    $.ajax({
      type: 'POST',
      url: endpoints.create,
      dataType: 'json',
      data: $.extend({ csrf_token: getCsrfToken() }, payload)
    }).done(function (data) {
      if (!data || Number(data.code) !== 0 || !data.task) {
        layer.msg((data && data.msg) || MSG.serverError, { icon: 2, time: 2400 });
        setComposeMeta(MSG.composeReady);
        return;
      }

      data.task = normalizeTask(data.task);
      upsertTaskRow(data.task);
      resetComposeForm();
      if (autoRun) {
        layer.msg(MSG.taskCreated, { icon: 1, time: 1500 });
        runTaskLoop(data.task.id);
      } else {
        layer.msg((data.msg || MSG.taskCreated), { icon: 1, time: 1800 });
        setComposeMeta(MSG.composeSaved);
        setTaskMeta(MSG.taskMetaRecent);
      }
    }).fail(function () {
      layer.msg(MSG.serverError, { icon: 2, time: 2200 });
      setComposeMeta(MSG.composeReady);
    }).always(function () {
      setComposeBusy(false);
    });
  }

  function runTaskLoop(taskId, afterCallback) {
    taskId = Number(taskId || 0);
    if (!taskId) {
      return;
    }

    if (runLocks[taskId]) {
      layer.msg(MSG.taskRunning, { icon: 0, time: 1800 });
      return;
    }

    runLocks[taskId] = true;

    function finalize(success, response) {
      delete runLocks[taskId];
      if (Number(config.mailReady || 0) === 1) {
        setComposeMeta(MSG.composeReady);
      }
      if (typeof afterCallback === 'function') {
        afterCallback(success, response);
      }
    }

    function step() {
      $.ajax({
        type: 'POST',
        url: endpoints.run,
        dataType: 'json',
        data: {
          csrf_token: getCsrfToken(),
          task_id: taskId,
          limit: 20
        }
      }).done(function (data) {
        if (!data || Number(data.code) !== 0 || !data.task) {
          layer.msg((data && data.msg) || MSG.serverError, { icon: 2, time: 2600 });
          finalize(false, data);
          return;
        }

        data.task = normalizeTask(data.task);
        upsertTaskRow(data.task);
        setTaskMeta(MSG.taskMetaRunningPrefix + taskId + MSG.taskMetaRunningSuffix + Number(data.task.pending_count || 0) + MSG.taskMetaRunningTail);

        if (Number(data.task.pending_count || 0) > 0) {
          window.setTimeout(step, 240);
          return;
        }

        setTaskMeta(MSG.taskMetaRecent);
        layer.msg(data.msg || MSG.taskCompleted, { icon: 1, time: 1800 });
        finalize(true, data);
      }).fail(function () {
        layer.msg(MSG.serverError, { icon: 2, time: 2200 });
        setTaskMeta(MSG.taskMetaRecent);
        finalize(false, null);
      });
    }

    setComposeMeta(MSG.composeSending);
    step();
  }

  function badgeClass(status) {
    status = Number(status || 0);
    if (status === 1) {
      return 'is-success';
    }
    if (status === 2) {
      return 'is-fail';
    }
    return 'is-pending';
  }

  function buildDetailHtml(task, items) {
    var percent = progressPercent(task);
    var rows = '';

    if ($.isArray(items) && items.length) {
      $.each(items, function (_, item) {
        rows += '' +
          '<tr>' +
            '<td>' + escapeHtml(item.username || '--') + '</td>' +
            '<td>' + escapeHtml(item.qq || '--') + '</td>' +
            '<td>' + escapeHtml(item.email || '--') + '</td>' +
            '<td><span class="admin-message-modal__badge ' + badgeClass(item.status) + '">' + escapeHtml(item.status_text || '') + '</span></td>' +
            '<td>' + escapeHtml(item.sent_at || '--') + '</td>' +
            '<td>' + escapeHtml(item.result || '--') + '</td>' +
          '</tr>';
      });
    } else {
      rows = '<tr><td colspan="6">' + MSG.detailEmpty + '</td></tr>';
    }

    return '' +
      '<div class="admin-message-modal admin-message-modal-host">' +
        '<div class="admin-message-modal__meta">' +
          '<div class="admin-message-modal__meta-item"><strong>' + escapeHtml(task.subject || '') + '</strong></div>' +
          '<div class="admin-message-modal__meta-item"><span>' + MSG.detailScope + '\uff1a' + escapeHtml(task.scope_text || '') + '</span><span>' + MSG.detailStatus + '\uff1a' + escapeHtml(task.status_text || '') + '</span><span>' + MSG.detailCreator + '\uff1a' + escapeHtml(task.creator || '--') + '</span></div>' +
          '<div class="admin-message-modal__meta-item"><span>' + MSG.detailTime + '\uff1a' + escapeHtml(task.addtime || '--') + '</span><span>' + MSG.detailNotice + '\uff1a' + (Number(task.sync_notice || 0) === 1 ? MSG.detailNoticeYes : MSG.detailNoticeNo) + '</span></div>' +
        '</div>' +
        '<div class="admin-message-modal__progress">' +
          '<div class="admin-message-modal__progress-track"><div class="admin-message-modal__progress-fill" data-progress-fill="' + percent + '"></div></div>' +
          '<div class="admin-message-modal__progress-text">' + MSG.progressPrefix + Number(task.success_count || 0) + MSG.progressMiddle + Number(task.fail_count || 0) + MSG.progressTail + Number(task.pending_count || 0) + '</div>' +
        '</div>' +
        '<div class="admin-message-task__actions admin-message-modal__actions">' +
          (Number(task.pending_count || 0) > 0 ? '<button type="button" class="admin-message-mini-button admin-message-mini-button--primary" data-message-detail-run="' + Number(task.id || 0) + '"><i class="fa fa-play"></i> ' + MSG.detailRun + '</button>' : '') +
          (Number(task.fail_count || 0) > 0 ? '<button type="button" class="admin-message-mini-button admin-message-mini-button--warning" data-message-detail-retry="' + Number(task.id || 0) + '"><i class="fa fa-refresh"></i> ' + MSG.detailRetry + '</button>' : '') +
        '</div>' +
        '<div class="admin-message-modal__table-wrap">' +
          '<table class="admin-message-modal__table">' +
            '<thead><tr><th>\u7528\u6237\u540d</th><th>QQ</th><th>\u90ae\u7bb1</th><th>\u72b6\u6001</th><th>\u53d1\u9001\u65f6\u95f4</th><th>\u7ed3\u679c</th></tr></thead>' +
            '<tbody>' + rows + '</tbody>' +
          '</table>' +
        '</div>' +
      '</div>';
  }

  function openTaskDetail(taskId) {
    taskId = Number(taskId || 0);
    if (!taskId) {
      return;
    }

    $.ajax({
      type: 'GET',
      url: endpoints.items,
      dataType: 'json',
      data: { id: taskId }
    }).done(function (data) {
      if (!data || Number(data.code) !== 0 || !data.task) {
        layer.msg((data && data.msg) || MSG.noTaskDetail, { icon: 2, time: 2200 });
        return;
      }

      data.task = normalizeTask(data.task);
      activeDetailTaskId = taskId;
      activeDetailLayerIndex = layer.open({
        type: 1,
        title: MSG.detailTitle + ' #' + taskId,
        area: [(window.innerWidth > 820 ? 820 : Math.max(window.innerWidth - 24, 300)) + 'px', '80vh'],
        shadeClose: true,
        content: buildDetailHtml(data.task, data.items || []),
        success: function (layero) {
          hydrateProgress(layero);
        }
      });
    }).fail(function () {
      layer.msg(MSG.serverError, { icon: 2, time: 2200 });
    });
  }

  function refreshActiveDetail(taskId) {
    if (Number(activeDetailTaskId || 0) !== Number(taskId || 0)) {
      return;
    }
    if (activeDetailLayerIndex !== null) {
      layer.close(activeDetailLayerIndex);
      activeDetailLayerIndex = null;
    }
    window.setTimeout(function () {
      openTaskDetail(taskId);
    }, 180);
  }

  function retryTask(taskId, autoRun) {
    taskId = Number(taskId || 0);
    if (!taskId) {
      return;
    }

    $.ajax({
      type: 'POST',
      url: endpoints.retry,
      dataType: 'json',
      data: {
        csrf_token: getCsrfToken(),
        task_id: taskId
      }
    }).done(function (data) {
      if (!data || Number(data.code) !== 0 || !data.task) {
        layer.msg((data && data.msg) || MSG.serverError, { icon: 2, time: 2200 });
        return;
      }

      data.task = normalizeTask(data.task);
      upsertTaskRow(data.task);

      if (autoRun) {
        layer.msg(MSG.retryDone, { icon: 1, time: 1800 });
        runTaskLoop(taskId, function () {
          refreshActiveDetail(taskId);
        });
      } else {
        layer.msg(data.msg || MSG.retryDone, { icon: 1, time: 1800 });
      }
    }).fail(function () {
      layer.msg(MSG.serverError, { icon: 2, time: 2200 });
    });
  }

  function openNoticePreview(noticeId) {
    noticeId = Number(noticeId || 0);
    if (!noticeId) {
      return;
    }

    $.ajax({
      type: 'GET',
      url: endpoints.getNotice,
      dataType: 'json',
      data: { id: noticeId }
    }).done(function (data) {
      var notice = data && data.notice ? data.notice : data;
      if (!data || Number(data.code) !== 0 || !notice) {
        layer.msg((data && data.msg) || MSG.noNotice, { icon: 2, time: 2200 });
        return;
      }

      var html = '' +
        '<div class="admin-message-notice-preview">' +
          '<div class="admin-message-notice-preview__meta">' +
            '<span>#' + noticeId + '</span>' +
            '<span>' + escapeHtml(notice.date || '--') + '</span>' +
            '<span>' + escapeHtml(notice.scope_text || SCOPE_LABELS[0]) + '</span>' +
          '</div>' +
          '<div class="admin-message-notice-preview__content">' + (notice.content || MSG.noticeContentEmpty) + '</div>' +
        '</div>';

      layer.open({
        type: 1,
        title: escapeHtml(notice.title || MSG.viewNotice),
        area: [(window.innerWidth > 760 ? 760 : Math.max(window.innerWidth - 24, 300)) + 'px', '75vh'],
        shadeClose: true,
        content: html
      });
    }).fail(function () {
      layer.msg(MSG.serverError, { icon: 2, time: 2200 });
    });
  }

  function buildNoticeSummary(content) {
    var text = $('<div>').html(content || '').text();
    text = $.trim(text).replace(/\s+/g, ' ');
    if (!text) {
      return MSG.noticeContentEmpty;
    }
    if (text.length > 56) {
      return text.slice(0, 56) + '...';
    }
    return text;
  }

  function updateNoticeCard(noticeId, active) {
    var $card = $('[data-message-notice-id="' + noticeId + '"]');
    var $status = $card.find('[data-message-notice-status]');
    var $toggle = $card.find('[data-message-action="notice-toggle"]');
    var isActive = Number(active) === 1;

    $card.attr('data-message-notice-active', isActive ? '1' : '0');
    $status
      .toggleClass('is-success', isActive)
      .toggleClass('is-muted', !isActive)
      .text(isActive ? MSG.visibleLabel : MSG.hiddenLabel);
    $toggle
      .attr('data-next-active', isActive ? '0' : '1')
      .toggleClass('admin-message-mini-button--warning', isActive)
      .toggleClass('admin-message-mini-button--primary', !isActive)
      .html('<i class="fa ' + (isActive ? 'fa-eye-slash' : 'fa-eye') + '"></i> ' + (isActive ? '\u9690\u85cf' : '\u663e\u793a'));
  }

  function updateNoticeCardContent(notice) {
    var noticeId = Number(notice && notice.id ? notice.id : 0);
    if (!noticeId) {
      return;
    }

    var $card = $('[data-message-notice-id="' + noticeId + '"]');
    if (!$card.length) {
      return;
    }

    $card.find('h4').text(notice.title || '');
    $card.find('.admin-message-notice-card__head p').text((notice.scope_text || SCOPE_LABELS[0]) + ' · ' + Number(notice.count || 0) + ' · ' + (notice.date || '--'));
    $card.find('.admin-message-notice-card__summary').text(notice.summary || buildNoticeSummary(notice.content || ''));
    updateNoticeCard(noticeId, Number(notice.active || 0));
  }

  function openNoticeEditor(noticeId) {
    noticeId = Number(noticeId || 0);
    if (!noticeId) {
      return;
    }

    $.ajax({
      type: 'GET',
      url: endpoints.getNotice,
      dataType: 'json',
      data: { id: noticeId }
    }).done(function (data) {
      var notice = data && data.notice ? data.notice : data;
      if (!data || Number(data.code) !== 0 || !notice) {
        layer.msg((data && data.msg) || MSG.noNotice, { icon: 2, time: 2200 });
        return;
      }

      var html = '' +
        '<div class="admin-message-notice-editor">' +
          '<label class="admin-message-notice-editor__field">' +
            '<span>\u901a\u77e5\u6807\u9898</span>' +
            '<input type="text" class="form-control" id="messageNoticeEditTitle" value="' + escapeHtml(notice.title || '') + '">' +
          '</label>' +
          '<div class="admin-message-notice-editor__meta">' +
            '<label class="admin-message-notice-editor__meta-item admin-message-notice-editor__meta-item--select"><span>' + MSG.noticeScope + '</span><select class="form-control admin-message-notice-editor__select" id="messageNoticeEditScope">' + buildScopeOptions(notice.scope || notice.type || 0) + '</select></label>' +
            '<label class="admin-message-notice-editor__toggle"><input type="checkbox" id="messageNoticeEditActive" ' + (Number(notice.active || 0) === 1 ? 'checked' : '') + '><span>' + MSG.noticeStatus + '</span><em>' + (Number(notice.active || 0) === 1 ? MSG.noticeShown : MSG.noticeHidden) + '</em></label>' +
            '<div class="admin-message-notice-editor__meta-item"><span>' + MSG.noticeReadCount + '</span><strong>' + Number(notice.count || 0) + '</strong></div>' +
          '</div>' +
          '<label class="admin-message-notice-editor__field">' +
          '<span>\u901a\u77e5\u5185\u5bb9</span>' +
            '<textarea class="form-control admin-message-notice-editor__textarea" id="messageNoticeEditContent">' + escapeHtml(notice.content || '') + '</textarea>' +
          '</label>' +
        '</div>';

      layer.open({
        type: 1,
        title: MSG.noticeEdit + ' #' + noticeId,
        area: [(window.innerWidth > 820 ? 820 : Math.max(window.innerWidth - 24, 300)) + 'px', '78vh'],
        shadeClose: true,
        btn: [MSG.noticeSave, MSG.detailClose],
        content: html,
        success: function (layero) {
          $(layero).on('change', '#messageNoticeEditActive', function () {
            $(layero).find('.admin-message-notice-editor__toggle em').text(this.checked ? MSG.noticeShown : MSG.noticeHidden);
          });
        },
        yes: function (index, layero) {
          var title = $.trim($(layero).find('#messageNoticeEditTitle').val());
          var content = $.trim($(layero).find('#messageNoticeEditContent').val());
          var scope = Number($(layero).find('#messageNoticeEditScope').val() || 0);
          var active = $(layero).find('#messageNoticeEditActive').is(':checked') ? 1 : 0;

          if (!title) {
            layer.msg(MSG.noticeTitleRequired, { icon: 2, time: 2200 });
            return false;
          }
          if (!content) {
            layer.msg(MSG.noticeContentRequired, { icon: 2, time: 2200 });
            return false;
          }
          if (!SCOPE_LABELS.hasOwnProperty(scope)) {
            layer.msg(MSG.noticeScopeRequired, { icon: 2, time: 2200 });
            return false;
          }

          $.ajax({
            type: 'POST',
            url: endpoints.saveNotice,
            dataType: 'json',
            data: {
              csrf_token: getCsrfToken(),
              id: noticeId,
              title: title,
              content: content,
              scope: scope,
              active: active
            }
          }).done(function (saveData) {
            if (!saveData || Number(saveData.code) !== 0 || !saveData.notice) {
              layer.msg((saveData && saveData.msg) || MSG.serverError, { icon: 2, time: 2200 });
              return;
            }
            updateNoticeCardContent(saveData.notice);
            layer.close(index);
            layer.msg(MSG.noticeSaved, { icon: 1, time: 1600 });
          }).fail(function () {
            layer.msg(MSG.serverError, { icon: 2, time: 2200 });
          });

          return false;
        }
      });
    }).fail(function () {
      layer.msg(MSG.serverError, { icon: 2, time: 2200 });
    });
  }

  function toggleNotice(noticeId, nextActive) {
    noticeId = Number(noticeId || 0);
    nextActive = Number(nextActive || 0);
    if (!noticeId) {
      return;
    }

    $.ajax({
      type: 'POST',
      url: endpoints.toggleNotice,
      dataType: 'json',
      data: {
        csrf_token: getCsrfToken(),
        id: noticeId,
        active: nextActive
      }
    }).done(function (data) {
      if (!data || Number(data.code) !== 0) {
        layer.msg((data && data.msg) || MSG.serverError, { icon: 2, time: 2200 });
        return;
      }
      updateNoticeCard(noticeId, nextActive);
      layer.msg(nextActive === 1 ? MSG.noticeShow : MSG.noticeHide, { icon: 1, time: 1600 });
    }).fail(function () {
      layer.msg(MSG.serverError, { icon: 2, time: 2200 });
    });
  }

  $(document).on('click', '[data-message-submit]', function () {
    createTask($(this).attr('data-message-submit') === 'send');
  });

  $(document).on('click', '[data-message-mode]', function () {
    setRecipientMode($(this).attr('data-message-mode'));
  });

  $('#messageTargetValue').on('input', function () {
    if (lookupTimer) {
      window.clearTimeout(lookupTimer);
    }
    lookupTimer = window.setTimeout(function () {
      lookupTarget(false);
    }, 260);
  });

  $('#messageTargetValue').on('blur', function () {
    lookupTarget(true);
  });

  $(document).on('click', '[data-message-action="view"]', function () {
    openTaskDetail($(this).attr('data-task-id'));
  });

  $(document).on('click', '[data-message-action="run"]', function () {
    runTaskLoop($(this).attr('data-task-id'));
  });

  $(document).on('click', '[data-message-action="retry"]', function () {
    retryTask($(this).attr('data-task-id'), true);
  });

  $(document).on('click', '[data-message-action="notice"], [data-message-action="notice-view"]', function () {
    openNoticePreview($(this).attr('data-notice-id'));
  });

  $(document).on('click', '[data-message-action="notice-edit"]', function () {
    openNoticeEditor($(this).attr('data-notice-id'));
  });

  $(document).on('click', '[data-message-action="notice-toggle"]', function () {
    toggleNotice($(this).attr('data-notice-id'), $(this).attr('data-next-active'));
  });

  $(document).on('click', '[data-message-detail-run]', function () {
    var taskId = $(this).attr('data-message-detail-run');
    if (activeDetailLayerIndex !== null) {
      layer.close(activeDetailLayerIndex);
      activeDetailLayerIndex = null;
    }
    runTaskLoop(taskId, function () {
      refreshActiveDetail(taskId);
    });
  });

  $(document).on('click', '[data-message-detail-retry]', function () {
    var taskId = $(this).attr('data-message-detail-retry');
    if (activeDetailLayerIndex !== null) {
      layer.close(activeDetailLayerIndex);
      activeDetailLayerIndex = null;
    }
    retryTask(taskId, true);
  });

  $('#messageComposeReset').on('click', function () {
    window.setTimeout(function () {
      resetComposeForm();
    }, 0);
  });

  hydrateProgress(document);
  setTaskMeta(MSG.taskMetaRecent);
  setRecipientMode('scope');
  if (Number(config.mailReady || 0) === 1) {
    setComposeMeta(MSG.composeReady);
  }
})(jQuery);

(function (window, document, $) {
  'use strict';

  var config = window.adminSyncConfig || {};
  var pageUrl = config.pageUrl || window.location.href;
  var monitorPath = config.monitorPath || './cx-api-synchronization.php';
  var monitorBaseUrl = config.monitorBaseUrl || '';
  var monitorUrl = config.monitorUrl || '';

  var MSG = {
    saveSuccess: '\u540c\u6b65\u89c4\u5219\u5df2\u4fdd\u5b58',
    saveFail: '\u4fdd\u5b58\u5931\u8d25\uff0c\u8bf7\u7a0d\u540e\u91cd\u8bd5',
    requestError: '\u670d\u52a1\u8bf7\u6c42\u5931\u8d25\uff0c\u8bf7\u7a0d\u540e\u91cd\u8bd5',
    runConfirm: '\u786e\u5b9a\u7acb\u5373\u6267\u884c\u4e00\u6b21\u81ea\u52a8\u540c\u6b65\u5417\uff1f',
    runTitle: '\u624b\u52a8\u540c\u6b65',
    runSuccess: '\u540c\u6b65\u4efb\u52a1\u5df2\u521b\u5efa\uff0c\u53ef\u5728\u4efb\u52a1\u8bb0\u5f55\u67e5\u770b\u8fdb\u5ea6',
    runFail: '\u540c\u6b65\u8bf7\u6c42\u5931\u8d25',
    runTimeout: '\u8bf7\u6c42\u8d85\u65f6\uff0c\u8bf7\u7a0d\u540e\u53bb\u7ad9\u70b9\u65e5\u5fd7\u91cc\u7ee7\u7eed\u786e\u8ba4\u7ed3\u679c',
    taskEmpty: '\u6682\u65e0\u540c\u6b65\u4efb\u52a1\u8bb0\u5f55',
    copied: '\u5df2\u590d\u5236\u5230\u526a\u8d34\u677f',
    copyFail: '\u590d\u5236\u5931\u8d25\uff0c\u8bf7\u624b\u52a8\u590d\u5236',
    noSiteSelected: '\u5f53\u524d\u6ca1\u6709\u542f\u7528\u4efb\u4f55\u7ad9\u70b9\uff0c\u4f9d\u7136\u4f1a\u4fdd\u5b58\u5168\u5c40\u8bbe\u7f6e',
    saving: '\u6b63\u5728\u4fdd\u5b58\u540c\u6b65\u89c4\u5219',
    running: '\u6b63\u5728\u6267\u884c\u540c\u6b65\u8bf7\u6c42',
    searchEmpty: '\u6ca1\u6709\u627e\u5230\u5339\u914d\u7684\u7ad9\u70b9',
    expand: '\u5c55\u5f00\u8bbe\u7f6e',
    collapse: '\u6536\u8d77\u8bbe\u7f6e',
    copyMonitor: '\u590d\u5236\u76d1\u63a7\u5730\u5740',
    invalidMonitor: '\u76d1\u63a7\u5730\u5740\u4e3a\u7a7a\uff0c\u8bf7\u5148\u4fdd\u5b58\u8bbe\u7f6e'
  };
  var activeTaskTimer = null;

  function showMessage(message, icon) {
    if (window.layer && typeof window.layer.msg === 'function') {
      window.layer.msg(message || MSG.requestError, { icon: typeof icon === 'number' ? icon : 0, time: 1600 });
      return;
    }
    window.alert(message || MSG.requestError);
  }

  function showAlert(message, callback) {
    if (window.layer && typeof window.layer.alert === 'function') {
      window.layer.alert(message || MSG.requestError, { shadeClose: true }, function (index) {
        window.layer.close(index);
        if (typeof callback === 'function') {
          callback();
        }
      });
      return;
    }
    window.alert(message || MSG.requestError);
    if (typeof callback === 'function') {
      callback();
    }
  }

  function showConfirm(message, callback) {
    if (window.layer && typeof window.layer.confirm === 'function') {
      window.layer.confirm(message, { icon: 3, title: MSG.runTitle }, function (index) {
        window.layer.close(index);
        callback();
      });
      return;
    }

    if (window.confirm(message)) {
      callback();
    }
  }

  function openLoading(message) {
    if (window.layer && typeof window.layer.load === 'function') {
      return window.layer.load(2, { shade: [0.12, '#fff'], content: message || '' });
    }
    return null;
  }

  function closeLoading(index) {
    if (index !== null && index !== undefined && window.layer && typeof window.layer.close === 'function') {
      window.layer.close(index);
    }
  }

  function getSiteChecks() {
    return $('input[name="shequ_ids[]"]');
  }

  function getVisibleSiteChecks() {
    return $('[data-sync-site]:not([hidden]) input[name="shequ_ids[]"]');
  }

  function getSiteCard(siteId) {
    return $('[data-sync-site-id="' + siteId + '"]');
  }

  function getConfigPanel(siteId) {
    return $('#config_' + siteId);
  }

  function setSiteExpanded($checkbox, expanded) {
    var siteId = $checkbox.val();
    var $card = getSiteCard(siteId);
    var $panel = getConfigPanel(siteId);
    var $button = $card.find('[data-sync-site-toggle="' + siteId + '"]');
    var buttonText = expanded ? MSG.collapse : MSG.expand;
    var iconClass = expanded ? 'fa-angle-up' : 'fa-angle-down';

    $card.toggleClass('is-active', expanded);
    $panel.prop('hidden', !expanded);
    $button.find('span').text(buttonText);
    $button.find('i').attr('class', 'fa ' + iconClass);
    $card.find('.admin-sync-status').toggleClass('admin-sync-status--success', expanded).text(expanded ? '\u5df2\u542f\u7528' : '\u672a\u542f\u7528');
  }

  function updateSelectedCount() {
    var $checks = getSiteChecks();
    var $visibleChecks = getVisibleSiteChecks();
    var total = $checks.length;
    var checked = $checks.filter(':checked').length;
    var masterChecks = $visibleChecks.length ? $visibleChecks : $checks;
    var masterTotal = masterChecks.length;
    var masterChecked = masterChecks.filter(':checked').length;
    var $count = $('#syncEnabledCount');
    var $master = $('#syncSelectAll');

    $('#selected_count').text(checked);
    if ($count.length) {
      $count.text(checked);
    }

    if (!$master.length) {
      return checked;
    }

    if (masterChecked === 0) {
      $master.prop('checked', false).prop('indeterminate', false);
    } else if (masterChecked === masterTotal) {
      $master.prop('checked', true).prop('indeterminate', false);
    } else {
      $master.prop('checked', false).prop('indeterminate', true);
    }

    return checked;
  }

  function cronFromInterval(minutes) {
    var value = parseInt(minutes, 10);

    if (!value || value <= 1) {
      return '* * * * *';
    }
    if (value >= 60) {
      return '0 * * * *';
    }

    return '*/' + value + ' * * * *';
  }

  function normalizeMonitorUrl(url) {
    var normalized = '';
    var urlObject;

    if (!url) {
      return '';
    }

    try {
      urlObject = new URL(url, window.location.href);
      normalized = urlObject.href;
    } catch (error) {
      normalized = String(url || '');
    }

    return normalized;
  }

  function getCurrentMonitorUrl() {
    var key = $.trim($('#syncMonitorKey').val() || '');
    var baseHref = '';

    try {
      baseHref = normalizeMonitorUrl(new URL(monitorPath, window.location.href).href);
    } catch (error) {
      baseHref = normalizeMonitorUrl(monitorBaseUrl || monitorUrl || '');
    }

    if (!baseHref) {
      return '';
    }
    if (key === '') {
      return baseHref;
    }

    return normalizeMonitorUrl(baseHref + '?key=' + encodeURIComponent(key));
  }

  function updateCronPreview() {
    var schedule = cronFromInterval($('#syncInterval').val());
    var currentMonitorUrl = getCurrentMonitorUrl();
    var command = schedule + ' curl -m 120 --silent ' + currentMonitorUrl;

    $('#syncMonitorUrl').val(currentMonitorUrl);
    $('#syncCronSchedule').text(schedule);
    $('#syncCronCommand').val(command);
  }

  function filterSites() {
    var keyword = $.trim($('#syncSiteSearch').val() || '').toLowerCase();
    var visibleCount = 0;
    var $grid = $('#syncSiteGrid');
    var $empty = $('#syncSearchEmpty');

    $('[data-sync-site]').each(function () {
      var $card = $(this);
      var haystack = String($card.data('syncSearch') || '').toLowerCase();
      var matched = keyword === '' || haystack.indexOf(keyword) !== -1;

      $card.prop('hidden', !matched);
      if (matched) {
        visibleCount += 1;
      }
    });

    if (!$empty.length) {
      return;
    }
    $empty.toggleClass('is-visible', visibleCount === 0 && keyword !== '');
    $grid.toggleClass('is-filter-empty', visibleCount === 0 && keyword !== '');
  }

  function generateKey() {
    var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    var value = '';
    var i;

    for (i = 0; i < 32; i += 1) {
      value += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    $('#syncMonitorKey').val(value).trigger('change');
  }

  function fallbackCopy(text) {
    var $temp = $('<textarea></textarea>').val(text).css({
      position: 'fixed',
      top: '-9999px',
      left: '-9999px'
    });

    $('body').append($temp);
    $temp[0].focus();
    $temp[0].select();

    try {
      document.execCommand('copy');
      showMessage(MSG.copied, 1);
    } catch (error) {
      showAlert(MSG.copyFail);
    }

    $temp.remove();
  }

  function copyText(text) {
    text = normalizeMonitorUrl(text);

    if (!text) {
      showAlert(MSG.invalidMonitor);
      return;
    }

    if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
      navigator.clipboard.writeText(text).then(function () {
        showMessage(MSG.copied, 1);
      }).catch(function () {
        fallbackCopy(text);
      });
      return;
    }

    fallbackCopy(text);
  }

  function escapeHtml(value) {
    return String(value === null || value === undefined ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function taskIsFinished(task) {
    return task && (task.status === 'success' || task.status === 'failed');
  }

  function renderTask(task) {
    var status = task.status || 'queued';
    var updatedAt = task.updated_at || task.finished_at || task.started_at || task.addtime || '';
    var lines = '';

    if (task.error_reason) {
      lines += '<small>' + escapeHtml(task.error_reason) + '</small>';
    }
    if (task.upstream_summary) {
      lines += '<small>' + escapeHtml(task.upstream_summary) + '</small>';
    }

    return [
      '<article class="admin-sync-task is-' + escapeHtml(status) + '" data-sync-task="' + escapeHtml(task.task_key) + '">',
      '<div class="admin-sync-task__main">',
      '<strong>' + escapeHtml(task.status_text || status) + ' \u00b7 ' + escapeHtml(task.task_key || '') + '</strong>',
      '<p>' + escapeHtml(task.summary || '') + '</p>',
      lines,
      '</div>',
      '<div class="admin-sync-task__side">',
      '<span>' + parseInt(task.progress || 0, 10) + '%</span>',
      '<time>' + escapeHtml(updatedAt) + '</time>',
      '</div>',
      '</article>'
    ].join('');
  }

  function renderTaskList(tasks) {
    var $list = $('#syncTaskList');
    var html = '';

    if (!$list.length) {
      return;
    }

    if (!tasks || !tasks.length) {
      $list.html('<div class="admin-sync-task-empty">' + MSG.taskEmpty + '</div>');
      return;
    }

    $.each(tasks, function (_, task) {
      html += renderTask(task);
    });
    $list.html(html);
  }

  function fetchTasks(taskId, callback) {
    $.ajax({
      type: 'GET',
      url: pageUrl,
      data: {
        action: 'sync_task_status',
        task_id: taskId || ''
      },
      dataType: 'json',
      timeout: 15000
    }).done(function (response) {
      if (response && response.task) {
        renderTaskList([response.task]);
        if (typeof callback === 'function') {
          callback(response.task);
        }
        return;
      }
      if (response && response.tasks) {
        renderTaskList(response.tasks);
      }
    });
  }

  function pollTask(taskId) {
    if (activeTaskTimer) {
      window.clearTimeout(activeTaskTimer);
      activeTaskTimer = null;
    }
    if (!taskId) {
      fetchTasks();
      return;
    }

    fetchTasks(taskId, function (task) {
      if (taskIsFinished(task)) {
        showMessage(task.status === 'success' ? '\u540c\u6b65\u4efb\u52a1\u5df2\u5b8c\u6210' : '\u540c\u6b65\u4efb\u52a1\u5931\u8d25\uff0c\u8bf7\u67e5\u770b\u4efb\u52a1\u8bb0\u5f55', task.status === 'success' ? 1 : 2);
        return;
      }
      activeTaskTimer = window.setTimeout(function () {
        pollTask(taskId);
      }, 5000);
    });
  }

  function submitForm() {
    var $form = $('#syncConfigForm');
    var $submit = $('#syncConfigSubmit');
    var loading = null;
    var selectedCount;

    if (!$form.length) {
      return false;
    }

    selectedCount = updateSelectedCount();
    if (selectedCount === 0) {
      showMessage(MSG.noSiteSelected, 0);
    }

    $submit.prop('disabled', true);
    loading = openLoading(MSG.saving);

    $.ajax({
      type: 'POST',
      url: pageUrl,
      data: $form.serialize() + '&submit=1',
      dataType: 'json',
      timeout: 30000
    }).done(function (response) {
      closeLoading(loading);
      $submit.prop('disabled', false);

      if (response && parseInt(response.code, 10) === 1) {
        showAlert(response.msg || MSG.saveSuccess, function () {
          window.location.reload();
        });
        return;
      }

      showAlert((response && response.msg) || MSG.saveFail);
    }).fail(function (xhr, status) {
      closeLoading(loading);
      $submit.prop('disabled', false);

      if (status === 'timeout') {
        showAlert(MSG.runTimeout);
        return;
      }

      showAlert(MSG.requestError);
    });

    return false;
  }

  function runSyncNow() {
    var currentMonitorUrl = normalizeMonitorUrl($('#syncMonitorUrl').val() || getCurrentMonitorUrl());
    var runUrl = pageUrl || './cx-synchronization.php';

    if (!runUrl) {
      showAlert(MSG.invalidMonitor);
      return false;
    }

    showConfirm(MSG.runConfirm, function () {
      var loading = openLoading(MSG.running);

      $.ajax({
        type: 'POST',
        url: runUrl,
        data: {
          action: 'run_sync_now',
          monitor_url: currentMonitorUrl
        },
        dataType: 'json',
        timeout: 30000
      }).done(function (response) {
        closeLoading(loading);
        if (response && parseInt(response.code, 10) === 1) {
          showMessage(response.msg || MSG.runSuccess, 1);
          pollTask(response.task_id);
          return;
        }
        showAlert((response && response.msg) || MSG.runFail);
      }).fail(function (xhr, status) {
        closeLoading(loading);
        if (status === 'timeout') {
          showAlert(MSG.runTimeout);
          return;
        }
        showAlert(MSG.runFail);
      });
    });

    return false;
  }

  function bindEvents() {
    $('#syncConfigForm').on('submit', function (event) {
      event.preventDefault();
      submitForm();
    });

    $('#syncConfigForm').on('change', 'input[name="shequ_ids[]"]', function () {
      setSiteExpanded($(this), $(this).prop('checked'));
      updateSelectedCount();
    });

    $('#syncConfigForm').on('click', '[data-sync-site-toggle]', function () {
      var siteId = $(this).attr('data-sync-site-toggle');
      var $checkbox = $('input[name="shequ_ids[]"][value="' + siteId + '"]');
      var nextState = !$checkbox.prop('checked');

      $checkbox.prop('checked', nextState).trigger('change');
    });

    $('#syncSelectAll').on('change', function () {
      var checked = $(this).prop('checked');
      var $targets = getVisibleSiteChecks();

      if (!$targets.length) {
        $targets = getSiteChecks();
      }

      $targets.each(function () {
        $(this).prop('checked', checked);
        setSiteExpanded($(this), checked);
      });
      updateSelectedCount();
    });

    $('#syncSiteSearch').on('input', function () {
      filterSites();
    });

    $('#syncGenerateKey').on('click', function () {
      generateKey();
      updateCronPreview();
    });

    $('#syncRunNow').on('click', function () {
      runSyncNow();
    });

    $('#syncCopyMonitor').on('click', function () {
      copyText($('#syncMonitorUrl').val());
    });

    $('#syncRefreshTasks').on('click', function () {
      fetchTasks();
    });

    $('[data-sync-copy="monitor-url"]').on('click', function () {
      copyText($('#syncMonitorUrl').val());
    });

    $('#syncInterval').on('change', function () {
      updateCronPreview();
    });

    $('#syncMonitorKey').on('input change', function () {
      updateCronPreview();
    });
  }

  function initCompatibilityGlobals() {
    window.toggleSiteConfig = function (checkbox) {
      var $checkbox = $(checkbox);
      setSiteExpanded($checkbox, $checkbox.prop('checked'));
      updateSelectedCount();
    };

    window.checkAll = function (element) {
      var checked = $(element).prop('checked');
      $('#syncSelectAll').prop('checked', checked).trigger('change');
    };

    window.save = function () {
      return submitForm();
    };

    window.generateKey = generateKey;
    window.runSyncNow = runSyncNow;
  }

  function init() {
    getSiteChecks().each(function () {
      setSiteExpanded($(this), $(this).prop('checked'));
    });

    updateSelectedCount();
    updateCronPreview();
    filterSites();
    bindEvents();
    initCompatibilityGlobals();
  }

  $(init);
})(window, document, jQuery);

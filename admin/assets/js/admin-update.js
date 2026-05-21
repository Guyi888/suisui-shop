(function ($) {
  'use strict';

  var config = window.adminUpdateConfig || {};
  var steps = ['precheck', 'remote', 'download', 'verify', 'backup', 'extract', 'apply', 'migrate', 'cleanup', 'selfcheck'];
  var checkOnlySteps = ['precheck', 'remote'];
  var dryRunSteps = ['precheck', 'remote', 'download', 'verify', 'backup', 'extract', 'dryrun', 'cleanup', 'selfcheck'];
  var labels = {
    precheck: '\u73af\u5883\u9884\u68c0',
    remote: '\u7248\u672c\u68c0\u67e5',
    download: '\u4e0b\u8f7d\u66f4\u65b0\u5305',
    verify: '\u5b8c\u6574\u6027\u6821\u9a8c',
    backup: '\u81ea\u52a8\u5907\u4efd',
    extract: '\u89e3\u538b\u66f4\u65b0\u5305',
    dryrun: '\u6d41\u7a0b\u6f14\u7ec3',
    apply: '\u8986\u76d6\u7a0b\u5e8f\u6587\u4ef6',
    migrate: '\u6570\u636e\u5e93\u5347\u7ea7',
    cleanup: '\u6e05\u7406\u7f13\u5b58',
    selfcheck: '\u6700\u7ec8\u81ea\u68c0'
  };

  function setStatus(text, hint) {
    $('#updateStatus').text(text);
    $('#updateStatusHint').text(hint || '');
  }

  function setProgress(index, total) {
    var percent = total ? Math.round((index / total) * 100) : 0;
    $('#updateProgressBar').css('width', percent + '%');
  }

  function setStepState(step, state, message) {
    var $item = $('#updateSteps [data-step="' + step + '"]');
    $item.removeClass('is-running is-done is-error');
    if (state) $item.addClass('is-' + state);
    var icon = 'fa-circle-o';
    if (state === 'running') icon = 'fa-spinner fa-spin';
    if (state === 'done') icon = 'fa-check';
    if (state === 'error') icon = 'fa-times';
    $item.find('> span i').attr('class', 'fa ' + icon);
    if (message) $item.find('p').text(message);
  }

  function resetSteps(activeSteps) {
    $('#updateSteps li').each(function () {
      var step = $(this).data('step');
      $(this).removeClass('is-running is-done is-error');
      $(this).find('> span i').attr('class', 'fa fa-circle-o');
      $(this).toggle(activeSteps.indexOf(step) !== -1);
    });
    setProgress(0, activeSteps.length);
  }

  function renderChecks(checks) {
    var $box = $('#updateChecks').empty();
    if (!checks || !checks.length) {
      $box.html('<div class="admin-update-empty"><i class="fa fa-info-circle"></i> \u6682\u65e0\u68c0\u67e5\u7ed3\u679c</div>');
      return;
    }
    checks.forEach(function (item) {
      var cls = item.ok ? 'is-ok' : 'is-error';
      var icon = item.ok ? 'fa-check-circle' : 'fa-times-circle';
      $box.append(
        '<div class="admin-update-check ' + cls + '">' +
          '<i class="fa ' + icon + '"></i>' +
          '<div><b>' + escapeHtml(item.name) + '</b><small>' + escapeHtml(item.detail || '') + '</small></div>' +
        '</div>'
      );
    });
  }

  function escapeHtml(text) {
    return String(text == null ? '' : text).replace(/[&<>"']/g, function (ch) {
      return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[ch];
    });
  }

  function postStep(step) {
    return $.ajax({
      url: config.endpoint || './update.php',
      type: 'POST',
      dataType: 'json',
      data: {
        act: 'online_update_step',
        step: step,
        csrf_token: config.csrfToken || window.ADMIN_CSRF_TOKEN || ''
      }
    });
  }

  function runFlow(activeSteps, options) {
    var current = 0;
    options = options || {};
    resetSteps(activeSteps);
    setStatus(options.checkOnly ? '\u6b63\u5728\u68c0\u67e5' : '\u6b63\u5728\u66f4\u65b0', '\u8bf7\u4e0d\u8981\u5173\u95ed\u9875\u9762');
    $('#startOnlineUpdate, #dryRunOnlineUpdate, #checkOnlineUpdate').prop('disabled', true);

    function next() {
      if (current >= activeSteps.length) {
        setProgress(activeSteps.length, activeSteps.length);
        setStatus(options.checkOnly ? '\u68c0\u67e5\u5b8c\u6210' : '\u66f4\u65b0\u5b8c\u6210', options.checkOnly ? '\u53ef\u6839\u636e\u7ed3\u679c\u51b3\u5b9a\u662f\u5426\u66f4\u65b0' : '\u6700\u7ec8\u81ea\u68c0\u5df2\u901a\u8fc7');
        $('#startOnlineUpdate, #dryRunOnlineUpdate, #checkOnlineUpdate').prop('disabled', false);
        if (window.layer) layer.msg(options.checkOnly ? '\u68c0\u67e5\u5b8c\u6210' : (options.dryRun ? '\u6f14\u7ec3\u5b8c\u6210' : '\u66f4\u65b0\u5b8c\u6210'), {icon: 1});
        return;
      }

      var step = activeSteps[current];
      setStepState(step, 'running', labels[step] + '\u4e2d...');
      setProgress(current, activeSteps.length);

      postStep(step).done(function (res) {
        if (!res || Number(res.code) !== 1) {
          var msg = res && res.msg ? res.msg : '\u8bf7\u6c42\u5931\u8d25';
          setStepState(step, 'error', msg);
          setStatus('\u66f4\u65b0\u5931\u8d25', msg);
          $('#startOnlineUpdate, #dryRunOnlineUpdate, #checkOnlineUpdate').prop('disabled', false);
          if (window.layer) layer.alert(msg, {icon: 2, title: '\u64cd\u4f5c\u5931\u8d25'});
          return;
        }

        setStepState(step, 'done', res.msg || (labels[step] + '\u5b8c\u6210'));
        if (step === 'precheck') renderChecks(res.checks || []);
        if (step === 'remote' && res.remote) {
          $('#remoteVersion').text(res.remote.version || '\u672a\u77e5');
          $('#remoteHint').text(res.has_update ? '\u53d1\u73b0\u65b0\u7248\u672c' : '\u5df2\u662f\u6700\u65b0');
          $('#releaseNotes').text(res.remote.body || res.remote.name || '\u6682\u65e0\u66f4\u65b0\u8bf4\u660e');
          if (options.checkOnly || !res.has_update) {
            setProgress(activeSteps.length, activeSteps.length);
          }
          if (!options.checkOnly && !options.allowCurrent && !res.has_update) {
            setStatus('\u5df2\u662f\u6700\u65b0', '\u65e0\u9700\u6267\u884c\u66f4\u65b0');
            $('#startOnlineUpdate, #dryRunOnlineUpdate, #checkOnlineUpdate').prop('disabled', false);
            if (window.layer) layer.msg('\u5f53\u524d\u5df2\u662f\u6700\u65b0\u7248\u672c', {icon: 1});
            return;
          }
        }
        current += 1;
        next();
      }).fail(function () {
        var msg = '\u670d\u52a1\u5668\u8bf7\u6c42\u5931\u8d25\uff0c\u8bf7\u68c0\u67e5\u7f51\u7edc\u6216\u767b\u5f55\u72b6\u6001';
        setStepState(step, 'error', msg);
        setStatus('\u66f4\u65b0\u5931\u8d25', msg);
        $('#startOnlineUpdate, #dryRunOnlineUpdate, #checkOnlineUpdate').prop('disabled', false);
        if (window.layer) layer.alert(msg, {icon: 2, title: '\u8bf7\u6c42\u5931\u8d25'});
      });
    }

    next();
  }

  $('#checkOnlineUpdate').on('click', function () {
    runFlow(checkOnlySteps, {checkOnly: true});
  });

  $('#startOnlineUpdate').on('click', function () {
    var start = function () {
      runFlow(steps, {checkOnly: false});
    };
    if (window.layer) {
      layer.confirm('\u7cfb\u7edf\u5c06\u81ea\u52a8\u5907\u4efd\u5e76\u6267\u884c\u66f4\u65b0\uff0c\u8bf7\u786e\u8ba4\u5f53\u524d\u7ad9\u70b9\u6ca1\u6709\u6b63\u5728\u8fdb\u884c\u5176\u4ed6\u6587\u4ef6\u64cd\u4f5c\u3002', {
        icon: 3,
        title: '\u786e\u8ba4\u7acb\u5373\u66f4\u65b0',
        btn: ['\u7acb\u5373\u66f4\u65b0', '\u53d6\u6d88']
      }, start);
    } else if (window.confirm('\u786e\u8ba4\u7acb\u5373\u66f4\u65b0\uff1f')) {
      start();
    }
  });

  $('#dryRunOnlineUpdate').on('click', function () {
    var start = function () {
      runFlow(dryRunSteps, {checkOnly: false, dryRun: true, allowCurrent: true});
    };
    if (window.layer) {
      layer.confirm('\u6d41\u7a0b\u6f14\u7ec3\u4f1a\u6267\u884c\u9884\u68c0\u3001\u4e0b\u8f7d\u3001\u6821\u9a8c\u3001\u5907\u4efd\u548c\u89e3\u538b\uff0c\u4f46\u4e0d\u4f1a\u8986\u76d6\u7a0b\u5e8f\u6587\u4ef6\u6216\u6267\u884c\u6570\u636e\u5e93\u8fc1\u79fb\u3002', {
        icon: 3,
        title: '\u786e\u8ba4\u6d41\u7a0b\u6f14\u7ec3',
        btn: ['\u5f00\u59cb\u6f14\u7ec3', '\u53d6\u6d88']
      }, start);
    } else if (window.confirm('\u786e\u8ba4\u5f00\u59cb\u6d41\u7a0b\u6f14\u7ec3\uff1f')) {
      start();
    }
  });
})(jQuery);

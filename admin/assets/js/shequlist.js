(function(window, document, $) {
  'use strict';

  var MSG = {
    emptyUrl: '\u8bf7\u5148\u586b\u5199\u7f51\u7ad9\u57df\u540d\uff01',
    invalidUrl: '\u7f51\u7ad9\u57df\u540d\u4e0d\u80fd\u5e26 http \u6216 / \u7b26\u53f7\uff0c\u53ea\u586b\u5199\u57df\u540d',
    checkSuccess: '\u8fde\u901a\u6027\u826f\u597d',
    checkBlocked: '\u8be5\u7f51\u7ad9\u7531\u4e8e\u9632\u706b\u5899\u539f\u56e0\u56fd\u5916\u4e3b\u673a\u65e0\u6cd5\u8fde\u63a5\uff0c\u8bf7\u4f7f\u7528\u56fd\u5185\u4e3b\u673a',
    checkTimeout: '\u76ee\u6807\u7ad9\u70b9\u8fde\u63a5\u8d85\u65f6',
    serverError: '\u670d\u52a1\u5668\u9519\u8bef'
  };

  function showAlert(message) {
    if (window.layer && typeof window.layer.alert === 'function') {
      window.layer.alert(message);
      return;
    }

    window.alert(message);
  }

  function showMessage(message) {
    if (window.layer && typeof window.layer.msg === 'function') {
      window.layer.msg(message);
      return;
    }

    window.alert(message);
  }

  function getPluginMap() {
    return window.SHEQU_PLUGIN_MAP || {};
  }

  function getConfig() {
    return window.SHEQU_PAGE_CONFIG || {};
  }

  function setNote($form, html) {
    var $note = $form.find('[data-shequ-plugin-note]');

    if (!$note.length) {
      return;
    }

    if (!html) {
      $note.prop('hidden', true).empty();
      return;
    }

    $note.html(html).prop('hidden', false);
  }

  function updateFormLabels($form) {
    var pluginMap = getPluginMap();
    var type = String($form.find('[data-shequ-type]').val() || '');
    var plugin = pluginMap[type] || {};
    var input = plugin.input || {};
    var $paypwdGroup = $form.find('[data-shequ-group="paypwd"]');
    var $paytypeGroup = $form.find('[data-shequ-group="paytype"]');
    var config = getConfig();

    $form.find('[data-shequ-label="url"]').text((input.url || '\u7f51\u7ad9\u57df\u540d') + ':');
    $form.find('[data-shequ-label="username"]').text((input.username || '\u767b\u5f55\u8d26\u53f7') + ':');
    $form.find('[data-shequ-label="password"]').text((input.password || '\u767b\u5f55\u5bc6\u7801') + ':');

    if (input.paypwd) {
      $form.find('[data-shequ-label="paypwd"]').text(input.paypwd + ':');
      $paypwdGroup.prop('hidden', false);
    } else {
      $paypwdGroup.prop('hidden', true);
    }

    if (input.paytype) {
      $form.find('[data-shequ-label="paytype"]').text(input.paytype + ':');
      $paytypeGroup.prop('hidden', false);
    } else {
      $paytypeGroup.prop('hidden', true);
    }

    setNote($form, '');

    if (plugin.showip && config.serverIpEndpoint) {
      $.ajax({
        type: 'GET',
        url: config.serverIpEndpoint,
        dataType: 'json'
      }).done(function(data) {
        if (!data || !data.ip) {
          return;
        }

        setNote($form, '<i class="fa fa-exclamation-triangle"></i><span>\u8bf7\u628a\u5f53\u524d\u670d\u52a1\u5668 IP \u52a0\u5165\u767d\u540d\u5355\uff1a' + $('<div>').text(data.ip).html() + '</span>');
      });
    }
  }

  function validateDomain(raw) {
    if (!raw) {
      showAlert(MSG.emptyUrl);
      return false;
    }

    if (/^https?:\/\//i.test(raw) || raw.indexOf('/') !== -1) {
      showAlert(MSG.invalidUrl);
      return false;
    }

    return true;
  }

  function bindConnectivityCheck() {
    $(document).on('click', '[data-shequ-check-url]', function() {
      var $form = $(this).closest('form');
      var url = $.trim($form.find('input[name="url"]').val() || '');
      var config = getConfig();
      var loadingIndex = null;

      if (!validateDomain(url)) {
        return;
      }

      if (!config.checkUrlEndpoint) {
        showMessage(MSG.serverError);
        return;
      }

      if (window.layer && typeof window.layer.load === 'function') {
        loadingIndex = window.layer.load(2, { shade: [0.12, '#fff'] });
      }

      $.ajax({
        type: 'POST',
        url: config.checkUrlEndpoint,
        data: {
          url: url
        },
        dataType: 'json'
      }).done(function(data) {
        if (loadingIndex !== null && window.layer) {
          window.layer.close(loadingIndex);
        }

        if (data && parseInt(data.code, 10) === 1) {
          showMessage(MSG.checkSuccess);
          return;
        }

        showAlert(MSG.checkBlocked);
      }).fail(function() {
        if (loadingIndex !== null && window.layer) {
          window.layer.close(loadingIndex);
        }

        showMessage(MSG.checkTimeout);
      });
    });
  }

  function bindForms() {
    $('[data-shequ-form]').each(function() {
      var $form = $(this);
      var $type = $form.find('[data-shequ-type]');

      updateFormLabels($form);

      $type.on('change', function() {
        updateFormLabels($form);
      });
    });
  }

  function initPopovers() {
    if ($.fn && typeof $.fn.popover === 'function') {
      $('[data-toggle="popover"]').popover();
    }
  }

  $(function() {
    initPopovers();
    bindForms();
    bindConnectivityCheck();
  });
})(window, document, window.jQuery);

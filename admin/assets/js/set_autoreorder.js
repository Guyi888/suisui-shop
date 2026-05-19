(function (window, $) {
  'use strict';

  var fieldRules = {
    autoreorder_interval: { min: 1, max: 1440, label: '\u8865\u5355\u95f4\u9694' },
    autoreorder_limit: { min: 1, max: 1000, label: '\u6bcf\u6b21\u8865\u5355\u6570' },
    autoreorder_max_retries: { min: 1, max: 10, label: '\u6700\u5927\u91cd\u8bd5\u6b21\u6570' },
    autoreorder_after: { min: 0, max: 1440, label: '\u8865\u5355\u5ef6\u8fdf' },
    autoreorder_timeout: { min: 5, max: 1440, label: '\u8865\u5355\u8d85\u65f6' }
  };

  function escapeHtml(value) {
    return $('<div/>').text(value === null || value === undefined ? '' : String(value)).html();
  }

  function showMessage(type, title, text) {
    var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    var $message = $('#autoReorderMessage');

    $message
      .removeClass('is-hidden admin-auto-reorder-alert--success admin-auto-reorder-alert--danger')
      .addClass('admin-auto-reorder-alert--' + (type === 'success' ? 'success' : 'danger'))
      .html([
        '<i class="fa ', icon, '"></i>',
        '<div>',
        '<strong>', escapeHtml(title), '</strong>',
        '<p>', escapeHtml(text), '</p>',
        '</div>'
      ].join(''));
  }

  function validateNumberField($form, name, rule) {
    var $field = $form.find('[name="' + name + '"]');
    var value = $.trim($field.val());
    var numberValue;

    if (!/^\d+$/.test(value)) {
      showMessage('danger', '\u6821\u9a8c\u5931\u8d25', rule.label + '\u5fc5\u987b\u586b\u5199\u6574\u6570');
      $field.focus();
      return false;
    }

    numberValue = parseInt(value, 10);
    if (numberValue < rule.min || numberValue > rule.max) {
      showMessage('danger', '\u6821\u9a8c\u5931\u8d25', rule.label + '\u8303\u56f4\u5fc5\u987b\u5728 ' + rule.min + ' \u5230 ' + rule.max + ' \u4e4b\u95f4');
      $field.focus();
      return false;
    }

    return true;
  }

  function validateForm($form) {
    var key;

    for (key in fieldRules) {
      if (Object.prototype.hasOwnProperty.call(fieldRules, key) && !validateNumberField($form, key, fieldRules[key])) {
        return false;
      }
    }

    if (!$form.find('input[name="autoreorder_status[]"]:checked').length) {
      showMessage('danger', '\u6821\u9a8c\u5931\u8d25', '\u81f3\u5c11\u9009\u62e9\u4e00\u4e2a\u8865\u5355\u8ba2\u5355\u72b6\u6001');
      return false;
    }

    return true;
  }

  function submitConfig($form) {
    var loadingIndex = layer.load(2, { shade: [0.1, '#fff'] });
    var $submit = $form.find('[type="submit"]');

    $submit.prop('disabled', true);

    $.ajax({
      type: 'POST',
      url: $form.attr('action') || window.location.href,
      data: $form.serialize() + '&ajax=1',
      dataType: 'json',
      cache: false
    }).done(function (data) {
      if (data && parseInt(data.code, 10) === 1) {
        showMessage('success', '\u8bbe\u7f6e\u5df2\u4fdd\u5b58', data.msg || '\u4fdd\u5b58\u6210\u529f');
      } else {
        showMessage('danger', '\u4fdd\u5b58\u5931\u8d25', data && data.msg ? data.msg : '\u670d\u52a1\u5668\u8fd4\u56de\u5f02\u5e38');
      }
    }).fail(function () {
      showMessage('danger', '\u4fdd\u5b58\u5931\u8d25', '\u670d\u52a1\u5668\u8bf7\u6c42\u5931\u8d25\uff0c\u8bf7\u7a0d\u540e\u91cd\u8bd5');
    }).always(function () {
      layer.close(loadingIndex);
      $submit.prop('disabled', false);
    });
  }

  function renderRunConfirm() {
    return [
      '<div class="admin-auto-reorder-dialog">',
      '<p class="admin-auto-reorder-dialog__eyebrow">Run Once</p>',
      '<h3>\u786e\u5b9a\u7acb\u5373\u6267\u884c\u4e00\u6b21\u81ea\u52a8\u8865\u5355\uff1f</h3>',
      '<p>\u8fd9\u4f1a\u76f4\u63a5\u8c03\u7528\u73b0\u6709\u8865\u5355\u5165\u53e3\uff0c\u5e76\u6309\u5f53\u524d\u914d\u7f6e\u5904\u7406\u7b26\u5408\u6761\u4ef6\u7684\u8ba2\u5355\u3002</p>',
      '<div class="admin-auto-reorder-dialog__footer">',
      '<button type="button" class="btn btn-default js-auto-reorder-cancel"><i class="fa fa-times"></i> \u53d6\u6d88</button>',
      '<button type="button" class="btn btn-primary js-auto-reorder-confirm"><i class="fa fa-play-circle"></i> \u7acb\u5373\u6267\u884c</button>',
      '</div>',
      '</div>'
    ].join('');
  }

  function showRunResult(type, text) {
    var $result = $('#autoReorderRunResult');

    $result
      .prop('hidden', false)
      .html('<strong>' + (type === 'success' ? '\u6267\u884c\u5b8c\u6210' : '\u6267\u884c\u5931\u8d25') + '</strong><br>' + escapeHtml(text));
  }

  function runAutoReorder(url) {
    var loadingIndex = layer.load(2, { shade: [0.1, '#fff'] });

    $.ajax({
      type: 'GET',
      url: url,
      dataType: 'json',
      cache: false
    }).done(function (data) {
      if (data && parseInt(data.code, 10) === 1) {
        showRunResult('success', data.msg || '\u6267\u884c\u6210\u529f');
        layer.msg(data.msg || '\u6267\u884c\u6210\u529f', { icon: 1 });
      } else {
        showRunResult('danger', data && data.msg ? data.msg : '\u6267\u884c\u7ed3\u679c\u5f02\u5e38');
        layer.msg(data && data.msg ? data.msg : '\u6267\u884c\u5931\u8d25', { icon: 2 });
      }
    }).fail(function () {
      showRunResult('danger', '\u8bf7\u6c42\u5931\u8d25\uff0c\u8bf7\u68c0\u67e5\u7f51\u7edc\u6216 cron \u5165\u53e3');
      layer.msg('\u8bf7\u6c42\u5931\u8d25', { icon: 2 });
    }).always(function () {
      layer.close(loadingIndex);
    });
  }

  function openRunConfirm(url) {
    layer.open({
      type: 1,
      title: '\u624b\u52a8\u8865\u5355\u786e\u8ba4',
      skin: 'layui-layer-rim admin-auto-reorder-layer',
      area: $(window).width() <= 767 ? ['94%', 'auto'] : ['560px', 'auto'],
      shadeClose: true,
      content: renderRunConfirm(),
      success: function (layero, index) {
        $(layero).on('click', '.js-auto-reorder-cancel', function () {
          layer.close(index);
        });
        $(layero).on('click', '.js-auto-reorder-confirm', function () {
          layer.close(index);
          runAutoReorder(url);
        });
      }
    });
  }

  $(function () {
    $(document).on('submit', '#autoReorderForm', function (event) {
      var $form = $(this);

      event.preventDefault();
      if (validateForm($form)) {
        submitConfig($form);
      }
    });

    $(document).on('click', '#runAutoReorder', function () {
      var runUrl = $(this).attr('data-run-url');

      if (!runUrl) {
        showMessage('danger', '\u6267\u884c\u5931\u8d25', '\u672a\u627e\u5230\u624b\u52a8\u8865\u5355\u5165\u53e3');
        return;
      }
      openRunConfirm(runUrl);
    });
  });
})(window, jQuery);

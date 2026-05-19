(function (window, $) {
  'use strict';

  function getEmptyContent() {
    return [
      '<div class="admin-order-monitor-modal__empty">',
      '<i class="fa fa-play-circle"></i>',
      '<span>\u70b9\u51fb\u201c\u624b\u52a8\u6267\u884c\u4e00\u6b21\u201d\u540e\u4f1a\u5728\u8fd9\u91cc\u663e\u793a cron \u8fd4\u56de\u5185\u5bb9\u3002</span>',
      '</div>'
    ].join('');
  }

  function openOrderMonitorResult() {
    var $button = $('#runOrderMonitor');
    var $modal = $('#showresult');
    var $content = $('#result_content');
    var monitorUrl = $button.attr('data-monitor-url');
    var $loading;
    var $iframe;

    if (!monitorUrl) {
      layer.msg('\u672a\u627e\u5230\u76d1\u63a7\u6267\u884c\u5730\u5740');
      return;
    }

    $loading = $([
      '<div class="admin-order-monitor-modal__loading">',
      '<i class="fa fa-spinner fa-spin"></i>',
      '<strong>\u6b63\u5728\u6267\u884c\u8ba2\u5355\u72b6\u6001\u68c0\u6d4b...</strong>',
      '</div>'
    ].join(''));

    $iframe = $('<iframe/>', {
      class: 'admin-order-monitor-frame',
      src: monitorUrl,
      frameborder: 0,
      scrolling: 'auto',
      title: '\u624b\u52a8\u540c\u6b65\u8ba2\u5355\u72b6\u6001'
    }).on('load', function () {
      $loading.fadeOut(160);
    });

    $content.empty().append($loading).append($iframe);
    $modal.modal('show');
  }

  function validateMonitorForm() {
    var $input = $('input[name="updatestatus_interval"]');
    var value = $.trim($input.val());

    if (!/^\d+$/.test(value) || parseInt(value, 10) < 1) {
      layer.msg('\u8bf7\u586b\u5199\u5927\u4e8e\u7b49\u4e8e 1 \u7684\u6574\u6570\u95f4\u9694');
      $input.focus();
      return false;
    }

    return true;
  }

  $(function () {
    $(document).on('click', '#runOrderMonitor', function () {
      openOrderMonitorResult();
    });

    $(document).on('submit', '.admin-order-monitor-form', function () {
      return validateMonitorForm();
    });

    $('#showresult').on('hidden.bs.modal', function () {
      $('#result_content').html(getEmptyContent());
    });
  });

  window.showresult = openOrderMonitorResult;
})(window, jQuery);

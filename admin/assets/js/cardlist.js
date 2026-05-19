(function(window, document, $) {
  'use strict';

  var MSG = {
    selectTool: '\u8bf7\u5148\u9009\u62e9\u5546\u54c1',
    noTool: '\u8be5\u5206\u7c7b\u4e0b\u6ca1\u6709\u5546\u54c1',
    serverError: '\u670d\u52a1\u5668\u9519\u8bef',
    selectToolFirst: '\u8bf7\u5148\u9009\u62e9\u5546\u54c1',
    emptyValue: '\u8bf7\u586b\u5199\u5546\u54c1\u4efd\u6570',
    emptyNum: '\u8bf7\u586b\u5199\u751f\u6210\u6570\u91cf'
  };

  function showMessage(message) {
    if (window.layer && typeof window.layer.alert === 'function') {
      window.layer.alert(message);
      return;
    }

    window.alert(message);
  }

  function loadTools(cid) {
    var $tool = $('#tid');
    var loadingIndex = null;

    if (!$tool.length) {
      return;
    }

    if (window.layer && typeof window.layer.load === 'function') {
      loadingIndex = window.layer.load(2, { shade: [0.1, '#fff'] });
    }

    $tool.empty().append('<option value="0">' + MSG.selectTool + '</option>');

    $.ajax({
      type: 'GET',
      url: './ajax.php?act=gettool&cid=' + encodeURIComponent(cid),
      dataType: 'json'
    }).done(function(data) {
      var count = 0;

      if (loadingIndex !== null && window.layer) {
        window.layer.close(loadingIndex);
      }

      if (!data || data.code !== 0 || !$.isArray(data.data)) {
        showMessage(data && data.msg ? data.msg : MSG.serverError);
        return;
      }

      $.each(data.data, function(_, item) {
        $tool.append('<option value="' + item.tid + '">' + $('<div>').text(item.name).html() + '</option>');
        count += 1;
      });

      $tool.val('0');
      if (!count && String(cid) !== '0') {
        $tool.html('<option value="0">' + MSG.noTool + '</option>');
      }
    }).fail(function() {
      if (loadingIndex !== null && window.layer) {
        window.layer.close(loadingIndex);
      }
      showMessage(MSG.serverError);
    });
  }

  function validateCreateForm(event) {
    var form = event.currentTarget;
    var tid = String(form.tid.value || '0');
    var value = $.trim(form.value.value || '');
    var num = $.trim(form.num.value || '');

    if (tid === '0') {
      event.preventDefault();
      showMessage(MSG.selectToolFirst);
      return;
    }

    if (!value) {
      event.preventDefault();
      showMessage(MSG.emptyValue);
      return;
    }

    if (!num) {
      event.preventDefault();
      showMessage(MSG.emptyNum);
    }
  }

  $(function() {
    var $cid = $('#cid');
    var form = document.getElementById('cardListCreateForm');

    if ($cid.length) {
      $cid.on('change', function() {
        loadTools(this.value);
      });
      loadTools($cid.val() || '0');
    }

    if (form) {
      form.addEventListener('submit', validateCreateForm);
    }
  });
})(window, document, window.jQuery);

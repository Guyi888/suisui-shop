(function(window, document, $) {
  'use strict';

  var MSG = {
    chooseAction: '\u8bf7\u5148\u9009\u62e9\u6279\u91cf\u64cd\u4f5c',
    chooseItem: '\u8bf7\u81f3\u5c11\u9009\u62e9\u4e00\u4e2a\u5546\u54c1',
    toggleOnline: '\u786e\u8ba4\u5c06\u8be5\u5546\u54c1\u8bbe\u4e3a\u4e0a\u67b6\u72b6\u6001\u5417\uff1f',
    toggleOffline: '\u786e\u8ba4\u5c06\u8be5\u5546\u54c1\u8bbe\u4e3a\u4e0b\u67b6\u72b6\u6001\u5417\uff1f',
    deleteItem: '\u786e\u8ba4\u5220\u9664\u8be5\u5546\u54c1\u5417\uff1f',
    serverError: '\u670d\u52a1\u5668\u9519\u8bef',
    loading: '\u6b63\u5728\u5904\u7406\uff0c\u8bf7\u7a0d\u5019'
  };

  function showMessage(message) {
    if (window.layer && typeof window.layer.msg === 'function') {
      window.layer.msg(message);
      return;
    }

    window.alert(message);
  }

  function openConfirm(message, callback) {
    if (window.layer && typeof window.layer.confirm === 'function') {
      window.layer.confirm(message, {
        icon: 3,
        title: '\u64cd\u4f5c\u786e\u8ba4',
        btn: ['\u786e\u5b9a', '\u53d6\u6d88']
      }, function(index) {
        window.layer.close(index);
        callback();
      });
      return;
    }

    if (window.confirm(message)) {
      callback();
    }
  }

  function toggleAll(checked) {
    $('.fakaListItemCheckbox').prop('checked', checked);
    $('#fakaListSelectAll, #fakaListSelectAllFooter').prop('checked', checked);
  }

  function syncMasterCheckbox() {
    var total = $('.fakaListItemCheckbox').length;
    var checked = $('.fakaListItemCheckbox:checked').length;
    var allChecked = total > 0 && total === checked;

    $('#fakaListSelectAll, #fakaListSelectAllFooter').prop('checked', allChecked);
  }

  function sendAction(url, payload) {
    var loadingIndex = null;

    if (window.layer && typeof window.layer.load === 'function') {
      loadingIndex = window.layer.load(2, { shade: [0.12, '#fff'] });
    }

    $.ajax({
      type: 'POST',
      url: url,
      data: payload,
      dataType: 'json'
    }).done(function(data) {
      if (loadingIndex !== null && window.layer) {
        window.layer.close(loadingIndex);
      }

      if (!data || data.code !== 0) {
        showMessage(data && data.msg ? data.msg : MSG.serverError);
        return;
      }

      window.location.reload();
    }).fail(function() {
      if (loadingIndex !== null && window.layer) {
        window.layer.close(loadingIndex);
      }
      showMessage(MSG.serverError);
    });
  }

  function bindRowActions() {
    $(document).on('click', '[data-fakalist-action]', function() {
      var $button = $(this);
      var action = $button.data('fakalistAction');
      var tid = parseInt($button.data('tid'), 10) || 0;
      var active = parseInt($button.data('active'), 10);
      var config = window.FAKA_LIST_CONFIG || {};
      var message;

      if (!tid) {
        return;
      }

      if (action === 'toggle-active') {
        message = active === 1 ? MSG.toggleOnline : MSG.toggleOffline;
        openConfirm(message, function() {
          sendAction(config.statusUrl || './ajax_shop.php?act=setTools', {
            tid: tid,
            active: active,
            csrf_token: window.ADMIN_CSRF_TOKEN || ''
          });
        });
        return;
      }

      if (action === 'delete') {
        openConfirm(MSG.deleteItem, function() {
          sendAction(config.deleteUrl || './ajax_shop.php?act=delTool', {
            tid: tid,
            csrf_token: window.ADMIN_CSRF_TOKEN || ''
          });
        });
      }
    });
  }

  function bindBulkForm() {
    var form = document.getElementById('fakaListBulkForm');

    if (!form) {
      return;
    }

    form.addEventListener('submit', function(event) {
      var action = document.getElementById('fakaListBulkAction');
      var checked = document.querySelectorAll('.fakaListItemCheckbox:checked');

      if (!action || String(action.value || '0') === '0') {
        event.preventDefault();
        showMessage(MSG.chooseAction);
        return;
      }

      if (!checked.length) {
        event.preventDefault();
        showMessage(MSG.chooseItem);
      }
    });
  }

  function bindCategoryFilter() {
    var parent = document.getElementById('fakaListParentCategory');
    var child = document.getElementById('fakaListChildCategory');

    if (!parent || !child) {
      return;
    }

    function syncChildOptions() {
      var parentId = String(parent.value || '0');
      var currentChild = child.options[child.selectedIndex];
      var currentParent = currentChild ? String(currentChild.getAttribute('data-parent') || '0') : '0';

      Array.prototype.forEach.call(child.options, function(option) {
        var optionParent = String(option.getAttribute('data-parent') || '0');
        option.hidden = parentId !== '0' && optionParent !== '0' && optionParent !== parentId;
      });

      if (parentId !== '0' && currentParent !== '0' && currentParent !== parentId) {
        child.value = '0';
      }
    }

    parent.addEventListener('change', syncChildOptions);
    syncChildOptions();
  }

  $(function() {
    $('#fakaListSelectAll, #fakaListSelectAllFooter').on('change', function() {
      toggleAll(this.checked);
    });

    $(document).on('change', '.fakaListItemCheckbox', syncMasterCheckbox);

    bindRowActions();
    bindBulkForm();
    bindCategoryFilter();
  });
})(window, document, window.jQuery);

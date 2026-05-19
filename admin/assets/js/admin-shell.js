(function(window, document, $) {
  'use strict';

  var pageLoader;

  function getPageLoader() {
    if (!pageLoader) {
      pageLoader = document.getElementById('pageLoader');
    }
    return pageLoader;
  }

  function setLoaderVisible(visible) {
    var loader = getPageLoader();
    if (!loader) {
      return;
    }

    if (visible) {
      loader.classList.remove('is-hidden');
      return;
    }

    loader.classList.add('is-hidden');
  }

  function isInternalLink(link) {
    var rawHref;

    if (!link || !link.href) {
      return false;
    }

    rawHref = link.getAttribute('href') || '';

    if (!rawHref || rawHref.charAt(0) === '#') {
      return false;
    }

    if (rawHref.indexOf('javascript:') === 0) {
      return false;
    }

    if (link.target === '_blank' || link.hasAttribute('download')) {
      return false;
    }

    return link.origin === window.location.origin;
  }

  function bindNavigationLoader() {
    var links = document.querySelectorAll('a[href]');

    Array.prototype.forEach.call(links, function(link) {
      link.addEventListener('click', function(event) {
        if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
          return;
        }

        if (!isInternalLink(link)) {
          return;
        }

        setLoaderVisible(true);
      });
    });
  }

  function normalizeText(text) {
    return (text || '').replace(/\s+/g, ' ').trim();
  }

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function(character) {
      var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      };
      return map[character];
    });
  }

  function renderSearchResults(results) {
    var summary = document.getElementById('searchResultsSummary');
    var list = document.getElementById('searchResultsList');
    var empty = document.getElementById('searchResultsEmpty');
    var items;

    if (!summary || !list || !empty) {
      return;
    }

    if (!results.length) {
      summary.textContent = '\u6ca1\u6709\u627e\u5230\u4e0e\u5173\u952e\u8bcd\u5339\u914d\u7684\u83dc\u5355\u3002';
      list.innerHTML = '';
      empty.hidden = false;
      return;
    }

    summary.textContent = '\u627e\u5230 ' + results.length + ' \u4e2a\u5339\u914d\u83dc\u5355';
    empty.hidden = true;

    items = results.map(function(result) {
      return '' +
        '<li class="list-group-item">' +
          '<a class="admin-search-modal__link" href="' + escapeHtml(result.href) + '">' +
            '<span class="admin-search-modal__name">' + escapeHtml(result.text) + '</span>' +
            '<span class="admin-search-modal__hint">\u6253\u5f00</span>' +
          '</a>' +
        '</li>';
    });

    list.innerHTML = items.join('');
  }

  function performGlobalSearch() {
    var input = document.getElementById('global-search');
    var query;
    var menuItems;
    var results = [];

    if (!input) {
      return;
    }

    query = normalizeText(input.value).toLowerCase();

    if (!query) {
      input.focus();
      return;
    }

    menuItems = document.querySelectorAll('.sidebar-nav a[href]');
    Array.prototype.forEach.call(menuItems, function(item) {
      var text = normalizeText(item.textContent);
      var href = item.getAttribute('href') || '';

      if (!text || !href || href === 'javascript:void(0)') {
        return;
      }

      if (text.toLowerCase().indexOf(query) === -1) {
        return;
      }

      results.push({
        text: text,
        href: href
      });
    });

    renderSearchResults(results);

    if ($ && $('#searchResultsModal').length) {
      $('#searchResultsModal').modal('show');
    }
  }

  function bindGlobalSearch() {
    var input = document.getElementById('global-search');

    if (!input) {
      return;
    }

    input.addEventListener('keydown', function(event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        performGlobalSearch();
      }
    });
  }

  function openConfirmDialog(message, onConfirm) {
    var title = '\u64cd\u4f5c\u786e\u8ba4';

    if (window.layer && typeof window.layer.confirm === 'function') {
      window.layer.confirm(message, {
        icon: 3,
        title: title,
        btn: ['\u786e\u5b9a', '\u53d6\u6d88']
      }, function(index) {
        window.layer.close(index);
        if (typeof onConfirm === 'function') {
          onConfirm();
        }
      });
      return;
    }

    if (window.confirm(message) && typeof onConfirm === 'function') {
      onConfirm();
    }
  }

  function submitTriggerForm(trigger) {
    var form;

    if (!trigger) {
      return;
    }

    form = trigger.form || trigger.closest('form');
    if (!form) {
      return;
    }

    if (typeof form.requestSubmit === 'function') {
      form.requestSubmit(trigger);
      return;
    }

    form.submit();
  }

  function bindConfirmActions() {
    document.addEventListener('click', function(event) {
      var trigger = event.target.closest('[data-admin-confirm]');
      var message;

      if (!trigger || trigger.disabled || trigger.classList.contains('disabled')) {
        return;
      }

      message = trigger.getAttribute('data-admin-confirm');
      if (!message) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();

      openConfirmDialog(message, function() {
        if (trigger.tagName === 'A') {
          window.location.href = trigger.getAttribute('href');
          return;
        }

        submitTriggerForm(trigger);
      });
    });
  }

  function syncYearText() {
    var year = String(new Date().getFullYear());
    var targets = document.querySelectorAll('#year-copy');

    Array.prototype.forEach.call(targets, function(target) {
      target.textContent = year;
    });
  }

  window.performGlobalSearch = performGlobalSearch;

  window.addEventListener('beforeunload', function() {
    setLoaderVisible(true);
  });

  window.addEventListener('load', function() {
    setLoaderVisible(false);
  });

  document.addEventListener('DOMContentLoaded', function() {
    syncYearText();
    bindGlobalSearch();
    bindNavigationLoader();
    bindConfirmActions();
    setLoaderVisible(false);
  });
})(window, document, window.jQuery);

(function ($) {
  'use strict';

  var config = window.pricePageConfig || {};
  var endpoints = config.endpoints || {};
  var MSG = {
    addTitle: '\u65b0\u589e\u52a0\u4ef7\u6a21\u677f',
    editTitle: '\u4fee\u6539\u52a0\u4ef7\u6a21\u677f',
    categoryTitle: '\u6279\u91cf\u5e94\u7528\u6a21\u677f\u5230\u5206\u7c7b',
    currentRule: '\u5f53\u524d\u6a21\u677f\uff1a',
    emptyFields: '\u8bf7\u786e\u4fdd\u5404\u9879\u4e0d\u80fd\u4e3a\u7a7a\uff01',
    selectRule: '\u8bf7\u81f3\u5c11\u9009\u62e9\u4e00\u4e2a\u6a21\u677f',
    selectCategory: '\u8bf7\u81f3\u5c11\u9009\u62e9\u4e00\u4e2a\u5206\u7c7b\uff01',
    confirmDelete: '\u4f60\u786e\u5b9e\u8981\u5220\u9664\u6b64\u6a21\u677f\u5417\uff1f',
    confirmBatchPrefix: '\u786e\u5b9a\u8981\u5220\u9664\u9009\u4e2d\u7684 ',
    confirmBatchSuffix: ' \u4e2a\u52a0\u4ef7\u6a21\u677f\u5417\uff1f\u5220\u9664\u540e\u5c06\u65e0\u6cd5\u6062\u590d\uff01',
    confirmOk: '\u786e\u5b9a',
    confirmCancel: '\u53d6\u6d88',
    confirmApplyAll: '\u786e\u5b9a\u8981\u628a\u5f53\u524d\u52a0\u4ef7\u6a21\u677f\u5e94\u7528\u5230\u5168\u7ad9\u6240\u6709\u5546\u54c1\u5417\uff1f',
    serverError: '\u670d\u52a1\u5668\u9519\u8bef',
    summaryPrefix: '\u5f53\u524d\u663e\u793a ',
    summarySuffix: ' \u4e2a\u6a21\u677f',
    selectedPrefix: '\uff0c\u5df2\u9009 ',
    selectedSuffix: ' \u4e2a',
    loading: '\u6b63\u5728\u5904\u7406...',
    fixedPlaceholder: '\u8f93\u5165\u52a0\u4ef7\u91d1\u989d',
    ratioPlaceholder: '\u8f93\u5165\u52a0\u4ef7\u500d\u6570\uff08\u5927\u4e8e 1 \u7684\u5c0f\u6570\uff09'
  };

  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function formatNumber(value) {
    var number = Number(value);
    if (!isFinite(number)) {
      return '0.00';
    }
    return (Math.round(number * 100) / 100).toFixed(2);
  }

  function closeAllModals() {
    $('.admin-price-modal.is-active').removeClass('is-active').attr('aria-hidden', 'true');
    $('body').removeClass('admin-price-modal-open');
  }

  function openModal(id) {
    closeAllModals();
    var $modal = $('#' + id);
    if (!$modal.length) {
      return;
    }
    $modal.addClass('is-active').attr('aria-hidden', 'false');
    $('body').addClass('admin-price-modal-open');
    window.setTimeout(function () {
      var $focusTarget = $modal.find('input,select,button,textarea').filter(':visible').first();
      if ($focusTarget.length) {
        $focusTarget.trigger('focus');
      }
    }, 40);
  }

  function toast(message, icon) {
    layer.msg(message, { icon: icon || 0, time: 2200 });
  }

  function alertMessage(message, icon) {
    layer.alert(message, { icon: icon || 0 });
  }

  function buildSummary(visibleCount, selectedCount) {
    var text = MSG.summaryPrefix + visibleCount + MSG.summarySuffix;
    if (selectedCount > 0) {
      text += MSG.selectedPrefix + selectedCount + MSG.selectedSuffix;
    }
    return text;
  }

  function updateSummary() {
    var visibleCount = $('[data-rule-row]:visible').length;
    var selectedCount = $('[data-price-select]:checked').length;
    $('#priceRuleSummary').text(buildSummary(visibleCount, selectedCount));
  }

  function updateSelectAllState() {
    var $items = $('[data-price-select]');
    var checkedCount = $items.filter(':checked').length;
    $('#priceRuleSelectAll').prop('checked', $items.length > 0 && checkedCount === $items.length);
    updateSummary();
  }

  function filterRows() {
    var keyword = $.trim($('#priceRuleSearch').val() || '').toLowerCase();
    var kind = $('#priceRuleKindFilter').val();

    $('[data-rule-row]').each(function () {
      var $row = $(this);
      var matchesKeyword = !keyword ||
        String($row.attr('data-name') || '').toLowerCase().indexOf(keyword) !== -1 ||
        String($row.attr('data-formula') || '').toLowerCase().indexOf(keyword) !== -1;
      var matchesKind = !kind || String($row.attr('data-kind')) === kind;
      $row.toggle(matchesKeyword && matchesKind);
    });

    updateSummary();
  }

  function updatePlaceholders() {
    var isFixed = String($('#priceRuleKind').val()) === '1';
    var placeholder = isFixed ? MSG.fixedPlaceholder : MSG.ratioPlaceholder;
    $('#priceRuleP2, #priceRuleP1, #priceRuleP0').attr('placeholder', placeholder);
  }

  function calculatePreview(basePrice, delta, kind) {
    var base = Number(basePrice);
    var value = delta === '' ? 0 : Number(delta);
    if (!isFinite(base)) {
      base = 0;
    }
    if (!isFinite(value)) {
      value = 0;
    }
    return kind === 1 ? base + value : base * value;
  }

  function updatePreview() {
    var kind = parseInt($('#priceRuleKind').val() || '0', 10);
    var testPrice = $('#priceRuleTestPrice').val() || '0';
    $('#priceRulePreviewP2').text(formatNumber(calculatePreview(testPrice, $('#priceRuleP2').val(), kind)));
    $('#priceRulePreviewP1').text(formatNumber(calculatePreview(testPrice, $('#priceRuleP1').val(), kind)));
    $('#priceRulePreviewP0').text(formatNumber(calculatePreview(testPrice, $('#priceRuleP0').val(), kind)));
  }

  function resetRuleForm() {
    $('#priceRuleForm')[0].reset();
    $('#priceRuleAction').val('add');
    $('#priceRuleId').val('');
    $('#priceRuleTestPrice').val('100');
    $('#priceRuleModalTitle').text(MSG.addTitle);
    updatePlaceholders();
    updatePreview();
  }

  function fillRuleForm(data) {
    $('#priceRuleAction').val('edit');
    $('#priceRuleId').val(data.id || '');
    $('#priceRuleName').val(data.name || '');
    $('#priceRuleKind').val(String(data.kind || '0'));
    $('#priceRuleP2').val(data.p_2 || '');
    $('#priceRuleP1').val(data.p_1 || '');
    $('#priceRuleP0').val(data.p_0 || '');
    $('#priceRuleModalTitle').text(MSG.editTitle);
    updatePlaceholders();
    updatePreview();
  }

  function openAddRule() {
    resetRuleForm();
    openModal('priceRuleModal');
  }

  function openEditRule(id) {
    if (!id) {
      return;
    }
    var loadingIndex = layer.load(2, { shade: [0.08, '#fff'] });
    $.ajax({
      type: 'GET',
      url: String(endpoints.get || '') + encodeURIComponent(id),
      dataType: 'json'
    }).done(function (data) {
      if (data && Number(data.code) === 0) {
        fillRuleForm(data);
        openModal('priceRuleModal');
      } else {
        alertMessage((data && data.msg) || MSG.serverError, 2);
      }
    }).fail(function () {
      toast(MSG.serverError, 2);
    }).always(function () {
      layer.close(loadingIndex);
    });
  }

  function saveRule() {
    var action = $('#priceRuleAction').val() === 'edit' ? 'edit' : 'add';
    var url = action === 'edit' ? endpoints.edit : endpoints.add;
    var formData = {
      prid: $('#priceRuleId').val(),
      name: $.trim($('#priceRuleName').val() || ''),
      kind: $('#priceRuleKind').val(),
      p_2: $.trim($('#priceRuleP2').val() || ''),
      p_1: $.trim($('#priceRuleP1').val() || ''),
      p_0: $.trim($('#priceRuleP0').val() || '')
    };

    if (!formData.name || formData.p_2 === '' || formData.p_1 === '' || formData.p_0 === '') {
      alertMessage(MSG.emptyFields, 2);
      return;
    }

    var loadingIndex = layer.load(2, { shade: [0.08, '#fff'] });
    $.ajax({
      type: 'POST',
      url: url,
      data: formData,
      dataType: 'json'
    }).done(function (data) {
      if (data && Number(data.code) === 0) {
        closeAllModals();
        toast(data.msg, 1);
        window.setTimeout(function () {
          window.location.reload();
        }, 900);
      } else {
        alertMessage((data && data.msg) || MSG.serverError, 2);
      }
    }).fail(function () {
      toast(MSG.serverError, 2);
    }).always(function () {
      layer.close(loadingIndex);
    });
  }

  function deleteRule(id) {
    if (!id) {
      return;
    }
    layer.confirm(MSG.confirmDelete, {
      btn: [MSG.confirmOk, MSG.confirmCancel],
      icon: 3
    }, function (index) {
      layer.close(index);
      $.ajax({
        type: 'GET',
        url: String(endpoints.delete || '') + encodeURIComponent(id),
        dataType: 'json'
      }).done(function (data) {
        if (data && Number(data.code) === 0) {
          toast(data.msg, 1);
          window.setTimeout(function () {
            window.location.reload();
          }, 700);
        } else {
          alertMessage((data && data.msg) || MSG.serverError, 2);
        }
      }).fail(function () {
        toast(MSG.serverError, 2);
      });
    });
  }

  function getSelectedIds() {
    return $('[data-price-select]:checked').map(function () {
      return $(this).val();
    }).get();
  }

  function batchDeleteRules() {
    var ids = getSelectedIds();
    if (!ids.length) {
      alertMessage(MSG.selectRule, 2);
      return;
    }

    layer.confirm(MSG.confirmBatchPrefix + ids.length + MSG.confirmBatchSuffix, {
      btn: [MSG.confirmOk, MSG.confirmCancel],
      icon: 3
    }, function (index) {
      layer.close(index);
      var loadingIndex = layer.load(2, { shade: [0.08, '#fff'] });
      $.ajax({
        type: 'POST',
        url: endpoints.batch,
        traditional: true,
        data: {
          aid: 1,
          checkbox: ids
        },
        dataType: 'json'
      }).done(function (data) {
        if (data && Number(data.code) === 0) {
          toast(data.msg, 1);
          window.setTimeout(function () {
            window.location.reload();
          }, 800);
        } else {
          alertMessage((data && data.msg) || MSG.serverError, 2);
        }
      }).fail(function () {
        toast(MSG.serverError, 2);
      }).always(function () {
        layer.close(loadingIndex);
      });
    });
  }

  function openCategoryModal(id, name) {
    $('#priceCategoryRuleId').val(id || '');
    $('#priceCategoryRuleInfo').html('<strong>' + escapeHtml(MSG.currentRule + (name || '')) + '</strong><p>\u9009\u62e9\u4e00\u4e2a\u6216\u591a\u4e2a\u5206\u7c7b\u540e\uff0c\u7cfb\u7edf\u4f1a\u6309\u539f\u63a5\u53e3\u903b\u8f91\u628a\u8be5\u6a21\u677f\u5e94\u7528\u5230\u8fd9\u4e9b\u5206\u7c7b\u4e0b\u7684\u5546\u54c1\u3002</p>');
    $('#priceCategoryModalTitle').text(MSG.categoryTitle);
    $('#priceCategorySelect').val([]);
    openModal('priceCategoryModal');
  }

  function setAllCategoriesSelected(selected) {
    $('#priceCategorySelect option').prop('selected', !!selected);
  }

  function applyRuleToCategories() {
    var ruleId = $('#priceCategoryRuleId').val();
    var selectedCategories = $('#priceCategorySelect').val() || [];
    if (!ruleId) {
      return;
    }
    if (!selectedCategories.length) {
      alertMessage(MSG.selectCategory, 2);
      return;
    }

    var loadingIndex = layer.load(2, { shade: [0.08, '#fff'] });
    $.ajax({
      type: 'POST',
      url: endpoints.assign,
      data: {
        id: ruleId,
        'cids[]': selectedCategories
      },
      dataType: 'json'
    }).done(function (data) {
      if (data && Number(data.code) === 0) {
        closeAllModals();
        toast(data.msg, 1);
      } else {
        alertMessage((data && data.msg) || MSG.serverError, 2);
      }
    }).fail(function () {
      toast(MSG.serverError, 2);
    }).always(function () {
      layer.close(loadingIndex);
    });
  }

  function applyRuleToAllGoods() {
    var ruleId = $('#priceCategoryRuleId').val();
    if (!ruleId) {
      return;
    }

    layer.confirm(MSG.confirmApplyAll, {
      btn: [MSG.confirmOk, MSG.confirmCancel],
      icon: 3
    }, function (index) {
      layer.close(index);
      var loadingIndex = layer.load(2, { shade: [0.08, '#fff'] });
      $.ajax({
        type: 'POST',
        url: endpoints.assign,
        data: {
          id: ruleId,
          scope: 'all'
        },
        dataType: 'json'
      }).done(function (data) {
        if (data && Number(data.code) === 0) {
          closeAllModals();
          toast(data.msg, 1);
        } else {
          alertMessage((data && data.msg) || MSG.serverError, 2);
        }
      }).fail(function () {
        toast(MSG.serverError, 2);
      }).always(function () {
        layer.close(loadingIndex);
      });
    });
  }

  function bindEvents() {
    $(document).on('click', '[data-price-modal-close]', function () {
      closeAllModals();
    });

    $('.admin-price-modal').on('click', function (event) {
      if (event.target === this) {
        closeAllModals();
      }
    });

    $(document).on('keydown', function (event) {
      if (event.key === 'Escape') {
        closeAllModals();
      }
    });

    $('#priceRuleSearch').on('input', filterRows);
    $('#priceRuleKindFilter').on('change', filterRows);

    $('#priceRuleSelectAll').on('change', function () {
      $('[data-price-select]').prop('checked', $(this).is(':checked'));
      updateSelectAllState();
    });

    $(document).on('change', '[data-price-select]', updateSelectAllState);

    $('#priceRuleKind').on('change', function () {
      updatePlaceholders();
      updatePreview();
    });
    $('#priceRuleTestPrice, #priceRuleP2, #priceRuleP1, #priceRuleP0').on('input', updatePreview);

    $('#priceRuleSaveButton').on('click', saveRule);
    $('#priceCategoryApplyButton').on('click', applyRuleToCategories);
    $('#priceCategoryApplyAllButton').on('click', applyRuleToAllGoods);

    $(document).on('click', '[data-price-action]', function () {
      var $button = $(this);
      var action = $button.attr('data-price-action');
      var ruleId = $button.attr('data-rule-id');
      var ruleName = $button.attr('data-rule-name') || '';

      if (action === 'add') {
        openAddRule();
      } else if (action === 'reload') {
        window.location.reload();
      } else if (action === 'edit') {
        openEditRule(ruleId);
      } else if (action === 'delete') {
        deleteRule(ruleId);
      } else if (action === 'assign') {
        openCategoryModal(ruleId, ruleName);
      } else if (action === 'batch-delete') {
        batchDeleteRules();
      } else if (action === 'select-all-categories') {
        setAllCategoriesSelected(true);
      } else if (action === 'clear-categories') {
        setAllCategoriesSelected(false);
      }
    });
  }

  $(function () {
    bindEvents();
    updatePlaceholders();
    updatePreview();
    filterRows();

    window.addframe = openAddRule;
    window.editframe = openEditRule;
    window.save = saveRule;
    window.delItem = deleteRule;
    window.change = openCategoryModal;
    window.batchOperation = batchDeleteRules;
    window.changeKind = function () {
      updatePlaceholders();
      updatePreview();
    };
    window.changeTest = updatePreview;
    window.unselectall = updateSelectAllState;
    window.checkAll = function () {
      var nextState = !$('#priceRuleSelectAll').is(':checked');
      $('#priceRuleSelectAll').prop('checked', nextState).trigger('change');
    };
    window.check1 = function () {
      $('#priceRuleSelectAll').trigger('change');
      return $('#priceRuleSelectAll').is(':checked') ? 'false' : 'true';
    };
  });
})(jQuery);

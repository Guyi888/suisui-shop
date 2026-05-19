(function ($, window, document) {
  'use strict';

  var MSG = {
    loading: '\u6b63\u5728\u52a0\u8f7d\u5206\u7c7b\u5217\u8868',
    loadFail: '\u5206\u7c7b\u5217\u8868\u52a0\u8f7d\u5931\u8d25\uff0c\u8bf7\u5237\u65b0\u91cd\u8bd5',
    serverError: '\u670d\u52a1\u5668\u8bf7\u6c42\u5931\u8d25\uff0c\u8bf7\u7a0d\u540e\u91cd\u8bd5',
    needName: '\u8bf7\u5148\u586b\u5199\u5206\u7c7b\u540d\u79f0',
    addSuccess: '\u5206\u7c7b\u5df2\u6dfb\u52a0',
    saveSuccess: '\u4fdd\u5b58\u6210\u529f',
    actionSuccess: '\u64cd\u4f5c\u6210\u529f',
    selectOne: '\u8bf7\u81f3\u5c11\u9009\u62e9\u4e00\u4e2a\u5206\u7c7b',
    selectAction: '\u8bf7\u5148\u9009\u62e9\u6279\u91cf\u64cd\u4f5c',
    confirmTitle: '\u8bf7\u786e\u8ba4',
    confirm: '\u786e\u5b9a',
    cancel: '\u53d6\u6d88',
    deleteTitle: '\u5220\u9664\u5206\u7c7b',
    deleteText: '\u5220\u9664\u540e\u4f1a\u540c\u65f6\u5220\u9664\u8be5\u5206\u7c7b\u4e0b\u7684\u5546\u54c1\uff0c\u6b64\u64cd\u4f5c\u65e0\u6cd5\u64a4\u56de\u3002\u786e\u5b9a\u7ee7\u7eed\u5417\uff1f',
    batchDeleteText: '\u6279\u91cf\u5220\u9664\u4f1a\u540c\u65f6\u5220\u9664\u9009\u4e2d\u5206\u7c7b\u4e0b\u7684\u5546\u54c1\uff0c\u6b64\u64cd\u4f5c\u65e0\u6cd5\u64a4\u56de\u3002\u786e\u5b9a\u7ee7\u7eed\u5417\uff1f',
    addSubTitle: '\u65b0\u589e\u5b50\u5206\u7c7b',
    addSubPlaceholder: '\u8bf7\u8f93\u5165\u5b50\u5206\u7c7b\u540d\u79f0',
    payTitle: '\u7981\u7528\u652f\u4ed8\u65b9\u5f0f',
    payIntro: '\u9009\u4e2d\u7684\u652f\u4ed8\u65b9\u5f0f\u5c06\u5728\u8be5\u5206\u7c7b\u5546\u54c1\u4e0b\u5355\u65f6\u88ab\u7981\u7528\u3002',
    areaTitle: '\u4e0d\u53ef\u552e\u5730\u533a',
    areaIntro: '\u591a\u4e2a\u5730\u533a\u8bf7\u7528\u9017\u53f7\u5206\u9694\uff0c\u4f8b\u5982\uff1a\u5317\u4eac\u5e02,\u5e7f\u4e1c\u7701\u6df1\u5733\u5e02\u3002',
    areaPlaceholder: '\u7559\u7a7a\u8868\u793a\u4e0d\u9650\u5236\u5730\u533a',
    noticeTitle: '\u524d\u53f0\u5206\u7c7b\u63d0\u793a\u8bed',
    noticeIntro: '\u524d\u53f0\u9009\u4e2d\u8be5\u5206\u7c7b\u65f6\u53ef\u7528\u4e8e\u5f39\u7a97\u6216\u63d0\u793a\u3002\u7559\u7a7a\u5373\u4e0d\u663e\u793a\u3002',
    noticePlaceholder: '\u8bf7\u8f93\u5165\u63d0\u793a\u8bed',
    imageTitle: '\u5206\u7c7b\u56fe\u7247',
    imageIntro: '\u53ef\u624b\u52a8\u586b\u5199 URL\uff0c\u4e5f\u53ef\u4e0a\u4f20\u6216\u4ece\u8be5\u5206\u7c7b\u5546\u54c1\u4e2d\u63d0\u53d6\u4e00\u5f20\u56fe\u3002',
    imagePlaceholder: '\u586b\u5199\u56fe\u7247 URL',
    upload: '\u4e0a\u4f20',
    autoImage: '\u63d0\u53d6\u5546\u54c1\u56fe',
    preview: '\u9884\u89c8',
    save: '\u4fdd\u5b58',
    noImage: '\u8bf7\u5148\u586b\u5199\u6216\u4e0a\u4f20\u56fe\u7247',
    imageSaved: '\u5206\u7c7b\u56fe\u7247\u5df2\u4fdd\u5b58',
    imageLoaded: '\u5df2\u63d0\u53d6\u5546\u54c1\u56fe\u7247',
    imageMissing: '\u8be5\u5206\u7c7b\u4e0b\u6682\u65e0\u53ef\u63d0\u53d6\u7684\u5546\u54c1\u56fe\u7247',
    uploadSuccess: '\u56fe\u7247\u4e0a\u4f20\u6210\u529f',
    noFile: '\u8bf7\u5148\u9009\u62e9\u56fe\u7247',
    payAlipay: '\u652f\u4ed8\u5b9d',
    payQq: 'QQ\u94b1\u5305',
    payWx: '\u5fae\u4fe1\u652f\u4ed8',
    payRmb: '\u4f59\u989d',
    emptySearch: '\u6ca1\u6709\u5339\u914d\u7684\u5206\u7c7b',
    allImagesSaved: '\u5168\u90e8\u5206\u7c7b\u56fe\u7247\u5df2\u4fdd\u5b58'
  };

  var payTypes = [
    { value: 'alipay', label: MSG.payAlipay },
    { value: 'qqpay', label: MSG.payQq },
    { value: 'wxpay', label: MSG.payWx },
    { value: 'rmb', label: MSG.payRmb }
  ];

  function hasLayer() {
    return typeof window.layer !== 'undefined';
  }

  function notify(message, icon) {
    if (hasLayer()) {
      window.layer.msg(message, { icon: icon || 1, time: 1500 });
      return;
    }
    window.alert(message);
  }

  function loading() {
    if (hasLayer()) {
      return window.layer.load(2, { shade: [0.08, '#fff'] });
    }
    return null;
  }

  function closeLoading(index) {
    if (hasLayer() && index !== null && typeof index !== 'undefined') {
      window.layer.close(index);
    }
  }

  function request(options) {
    return $.ajax($.extend({
      dataType: 'json',
      timeout: 18000
    }, options));
  }

  function escapeHtml(value) {
    return $('<div>').text(value == null ? '' : String(value)).html();
  }

  function modalArea(width, height) {
    if ($(window).width() < 640) {
      return ['92vw', height || 'auto'];
    }
    return [width, height || 'auto'];
  }

  function getRow(cid) {
    return $('[data-class-row][data-cid="' + cid + '"]');
  }

  function getClassName(cid) {
    var row = getRow(cid);
    var value = row.find('input[name="name[' + cid + ']"]').val();
    return value || row.data('name') || '';
  }

  function getImageValue(cid) {
    var input = $('[data-class-image-url="' + cid + '"]');
    if (input.length) {
      return input.val();
    }
    var row = getRow(cid);
    return row.data('img') || '';
  }

  function setImageValue(cid, value) {
    $('[data-class-image-url="' + cid + '"]').val(value);
    getRow(cid).attr('data-img', value).data('img', value);
    $('[data-class-card][data-cid="' + cid + '"]').attr('data-img', value).data('img', value);
    $('#classImageModalUrl').val(value);
    updateImageCardPreview(cid, value);
    renderImagePreview(value);
  }

  function resolveImageUrl(value) {
    if (!value) {
      return '';
    }
    if (/^(https?:)?\/\//i.test(value) || /^data:/i.test(value)) {
      return value;
    }
    return '../' + value.replace(/^\.\.\//, '');
  }

  function updateImageCardPreview(cid, value) {
    var card = $('[data-class-card][data-cid="' + cid + '"]');
    if (!card.length) {
      return;
    }
    var holder = card.find('.admin-class-image-card__preview');
    var src = resolveImageUrl(value);
    if (src) {
      holder.html('<img src="' + escapeHtml(src) + '" alt="">');
    } else {
      holder.html('<span><i class="fa fa-picture-o"></i></span>');
    }
  }

  function renderImagePreview(value) {
    var target = $('#classImageModalPreview');
    if (!target.length) {
      return;
    }
    var src = resolveImageUrl(value);
    if (!src) {
      target.html('<span><i class="fa fa-picture-o"></i></span>');
      return;
    }
    target.html('<img src="' + escapeHtml(src) + '" alt="">');
  }

  function listTable(query) {
    var shell = $('#listTable');
    if (!shell.length) {
      return;
    }
    var source = shell.data('source') || './classlist-table.php';
    var url = source + (query ? ('?' + query) : '');
    var index = loading();
    shell.html('<div class="admin-class-loading"><i class="fa fa-spinner fa-spin"></i><span>' + MSG.loading + '</span></div>');
    $.ajax({
      type: 'GET',
      url: url,
      dataType: 'html',
      cache: false
    }).done(function (html) {
      shell.html(html || '<div class="admin-class-empty"><i class="fa fa-folder-open-o"></i><strong>' + MSG.emptySearch + '</strong></div>');
      applySearchFilter();
    }).fail(function () {
      shell.html('<div class="admin-class-empty"><i class="fa fa-exclamation-triangle"></i><strong>' + MSG.loadFail + '</strong></div>');
      notify(MSG.loadFail, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function addClass(pid, name) {
    var className = $.trim(name || '');
    if (!className) {
      notify(MSG.needName, 2);
      return;
    }
    var index = loading();
    request({
      type: 'POST',
      url: './ajax_class.php?act=addClass',
      data: { name: className, pid: pid || 0 }
    }).done(function (res) {
      if (res && res.code === 0) {
        notify(res.msg || MSG.addSuccess, 1);
        $('#newClassName').val('');
        listTable();
      } else {
        notify(res && res.msg ? res.msg : MSG.serverError, 2);
      }
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function saveOne(cid) {
    var name = $.trim(getRow(cid).find('input[name="name[' + cid + ']"]').val());
    if (!name) {
      notify(MSG.needName, 2);
      return;
    }
    var index = loading();
    request({
      type: 'POST',
      url: './ajax_class.php?act=editClass&cid=' + encodeURIComponent(cid),
      data: { name: name }
    }).done(function (res) {
      if (res && res.code === 0) {
        notify(res.msg || MSG.saveSuccess, 1);
        listTable();
      } else {
        notify(res && res.msg ? res.msg : MSG.serverError, 2);
      }
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function saveAll() {
    var form = $('#classlist');
    if (!form.length) {
      return;
    }
    var index = loading();
    request({
      type: 'POST',
      url: './ajax_class.php?act=editClassAll',
      data: form.serialize()
    }).done(function (res) {
      if (res && res.code === 0) {
        notify(res.msg || MSG.saveSuccess, 1);
        listTable();
      } else {
        notify(res && res.msg ? res.msg : MSG.serverError, 2);
      }
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function setActive(cid, active) {
    var index = loading();
    request({
      type: 'GET',
      url: './ajax_class.php?act=setClass',
      data: { cid: cid, active: active }
    }).done(function (res) {
      if (res && res.code === 0) {
        notify(MSG.actionSuccess, 1);
        listTable();
      } else {
        notify(res && res.msg ? res.msg : MSG.serverError, 2);
      }
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function sortClass(cid, sortType) {
    var index = loading();
    request({
      type: 'GET',
      url: './ajax_class.php?act=setClassSort',
      data: { cid: cid, sort: sortType }
    }).done(function (res) {
      if (res && res.code === 0) {
        notify(MSG.actionSuccess, 1);
        listTable();
      } else {
        notify(res && res.msg ? res.msg : MSG.serverError, 2);
      }
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function deleteClass(cid, name) {
    var message = (name ? escapeHtml(name) + '<br>' : '') + MSG.deleteText;
    confirmAction(message, function () {
      var index = loading();
      request({
        type: 'GET',
        url: './ajax_class.php?act=delClass',
        data: { cid: cid }
      }).done(function (res) {
        if (res && res.code === 0) {
          notify(res.msg || MSG.actionSuccess, 1);
          listTable();
        } else {
          notify(res && res.msg ? res.msg : MSG.serverError, 2);
        }
      }).fail(function () {
        notify(MSG.serverError, 2);
      }).always(function () {
        closeLoading(index);
      });
    });
  }

  function batchOperation() {
    var form = $('#classlist');
    var action = form.find('[data-bulk-action]').val();
    var checked = form.find('[data-class-checkbox]:checked');
    if (!checked.length) {
      notify(MSG.selectOne, 2);
      return;
    }
    if (!action) {
      notify(MSG.selectAction, 2);
      return;
    }
    var run = function () {
      var index = loading();
      request({
        type: 'POST',
        url: './ajax_class.php?act=batchOperation',
        data: form.serialize()
      }).done(function (res) {
        if (res && res.code === 0) {
          notify(res.msg || MSG.actionSuccess, 1);
          listTable();
        } else {
          notify(res && res.msg ? res.msg : MSG.serverError, 2);
        }
      }).fail(function () {
        notify(MSG.serverError, 2);
      }).always(function () {
        closeLoading(index);
      });
    };
    if (String(action) === '3') {
      confirmAction(MSG.batchDeleteText, run);
      return;
    }
    run();
  }

  function confirmAction(message, callback) {
    if (!hasLayer()) {
      if (window.confirm($('<div>').html(message).text())) {
        callback();
      }
      return;
    }
    window.layer.confirm(message, {
      title: MSG.confirmTitle,
      icon: 3,
      btn: [MSG.confirm, MSG.cancel]
    }, function (index) {
      window.layer.close(index);
      callback();
    });
  }

  function promptSubClass(cid, parentName) {
    if (!hasLayer()) {
      var fallback = window.prompt(MSG.addSubPlaceholder);
      if (fallback) {
        addClass(cid, fallback);
      }
      return;
    }
    window.layer.prompt({
      formType: 0,
      title: MSG.addSubTitle + ' - ' + parentName,
      value: '',
      area: modalArea('420px', '120px')
    }, function (value, index) {
      window.layer.close(index);
      addClass(cid, value);
    });
  }

  function openPayModal(cid) {
    var index = loading();
    request({
      type: 'POST',
      url: './ajax_class.php?act=getBlockPay',
      data: { cid: cid }
    }).done(function (res) {
      if (!res || res.code !== 0) {
        notify(res && res.msg ? res.msg : MSG.serverError, 2);
        return;
      }
      var active = $.isArray(res.data) ? res.data : [];
      var html = '<div class="admin-class-modal"><p>' + MSG.payIntro + '</p><div class="admin-class-pay-options">';
      $.each(payTypes, function (_, item) {
        var checked = $.inArray(item.value, active) > -1 ? ' checked' : '';
        html += '<label class="admin-class-pay-option"><input type="checkbox" name="class_paytype" value="' + item.value + '"' + checked + '> <span>' + item.label + '</span></label>';
      });
      html += '</div></div>';
      window.layer.open({
        type: 1,
        title: MSG.payTitle + ' #' + cid,
        area: modalArea('460px'),
        shadeClose: true,
        btn: [MSG.save, MSG.cancel],
        content: html,
        yes: function (modalIndex) {
          var selected = [];
          $('input[name="class_paytype"]:checked').each(function () {
            selected.push($(this).val());
          });
          savePayTypes(cid, selected, modalIndex);
        }
      });
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function savePayTypes(cid, selected, modalIndex) {
    var index = loading();
    request({
      type: 'POST',
      url: './ajax_class.php?act=setBlockPay',
      traditional: true,
      data: { cid: cid, paytype: selected }
    }).done(function (res) {
      if (res && res.code === 0) {
        notify(res.msg || MSG.saveSuccess, 1);
        window.layer.close(modalIndex);
      } else {
        notify(res && res.msg ? res.msg : MSG.serverError, 2);
      }
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function openAreaModal(cid) {
    var index = loading();
    request({
      type: 'POST',
      url: './ajax_class.php?act=getBlock',
      data: { cid: cid }
    }).done(function (res) {
      if (!res || res.code !== 0) {
        notify(res && res.msg ? res.msg : MSG.serverError, 2);
        return;
      }
      var html = '<div class="admin-class-modal"><p>' + MSG.areaIntro + '</p><textarea id="classAreaText" class="form-control" placeholder="' + MSG.areaPlaceholder + '"></textarea></div>';
      window.layer.open({
        type: 1,
        title: MSG.areaTitle + ' #' + cid,
        area: modalArea('520px'),
        shadeClose: true,
        btn: [MSG.save, MSG.cancel],
        content: html,
        success: function () {
          $('#classAreaText').val(res.data || '');
        },
        yes: function (modalIndex) {
          saveArea(cid, $('#classAreaText').val(), modalIndex);
        }
      });
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function saveArea(cid, value, modalIndex) {
    var index = loading();
    request({
      type: 'POST',
      url: './ajax_class.php?act=setBlock',
      data: { cid: cid, data: value }
    }).done(function (res) {
      if (res && res.code === 0) {
        notify(res.msg || MSG.saveSuccess, 1);
        window.layer.close(modalIndex);
      } else {
        notify(res && res.msg ? res.msg : MSG.serverError, 2);
      }
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function openNoticeModal(cid) {
    var index = loading();
    request({
      type: 'POST',
      url: './ajax_class.php?act=getNotice',
      data: { cid: cid }
    }).done(function (res) {
      if (!res || res.code !== 0) {
        notify(res && res.msg ? res.msg : MSG.serverError, 2);
        return;
      }
      var html = '<div class="admin-class-modal"><p>' + MSG.noticeIntro + '</p><textarea id="classNoticeText" class="form-control" placeholder="' + MSG.noticePlaceholder + '"></textarea></div>';
      window.layer.open({
        type: 1,
        title: MSG.noticeTitle + ' #' + cid,
        area: modalArea('540px'),
        shadeClose: true,
        btn: [MSG.save, MSG.cancel],
        content: html,
        success: function () {
          $('#classNoticeText').val(res.data || '');
        },
        yes: function (modalIndex) {
          saveNotice(cid, $('#classNoticeText').val(), modalIndex);
        }
      });
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function saveNotice(cid, value, modalIndex) {
    var index = loading();
    request({
      type: 'POST',
      url: './ajax_class.php?act=setNotice',
      data: { cid: cid, notice: value }
    }).done(function (res) {
      if (res && res.code === 0) {
        notify(res.msg || MSG.saveSuccess, 1);
        getRow(cid).attr('data-notice', value).data('notice', value);
        window.layer.close(modalIndex);
      } else {
        notify(res && res.msg ? res.msg : MSG.serverError, 2);
      }
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function openImageModal(cid) {
    var current = getImageValue(cid);
    var name = getClassName(cid);
    var html = [
      '<div class="admin-class-modal admin-class-image-modal">',
      '<p>', MSG.imageIntro, '</p>',
      '<input type="file" class="admin-class-file-input" data-modal-image-file data-cid="', cid, '" accept="image/*">',
      '<input type="text" id="classImageModalUrl" class="form-control" value="', escapeHtml(current), '" placeholder="', MSG.imagePlaceholder, '">',
      '<div class="admin-class-image-card__actions admin-class-image-modal__actions">',
      '<button type="button" class="btn btn-default" data-class-action="modal-image-upload" data-cid="', cid, '"><i class="fa fa-upload"></i> ', MSG.upload, '</button>',
      '<button type="button" class="btn btn-default" data-class-action="modal-image-auto" data-cid="', cid, '"><i class="fa fa-magic"></i> ', MSG.autoImage, '</button>',
      '<button type="button" class="btn btn-default" data-class-action="modal-image-preview"><i class="fa fa-eye"></i> ', MSG.preview, '</button>',
      '</div>',
      '<div id="classImageModalPreview" class="admin-class-image-modal__preview"></div>',
      '</div>'
    ].join('');
    window.layer.open({
      type: 1,
      title: MSG.imageTitle + ' - ' + name,
      area: modalArea('560px'),
      shadeClose: true,
      btn: [MSG.save, MSG.cancel],
      content: html,
      success: function () {
        renderImagePreview(current);
      },
      yes: function (modalIndex) {
        saveImage(cid, $('#classImageModalUrl').val(), modalIndex);
      }
    });
  }

  function saveImage(cid, value, modalIndex) {
    var data = {};
    data['img[' + cid + ']'] = $.trim(value || '');
    var index = loading();
    request({
      type: 'POST',
      url: './ajax_class.php?act=editClassImages',
      data: data
    }).done(function (res) {
      if (res && res.code === 0) {
        setImageValue(cid, data['img[' + cid + ']']);
        notify(MSG.imageSaved, 1);
        if (modalIndex) {
          window.layer.close(modalIndex);
        }
      } else {
        notify(res && res.msg ? res.msg : MSG.serverError, 2);
      }
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function autoImage(cid) {
    var index = loading();
    request({
      type: 'GET',
      url: './ajax_class.php?act=getClassImage',
      data: { cid: cid }
    }).done(function (res) {
      if (res && res.code === 0 && res.url) {
        setImageValue(cid, res.url);
        notify(MSG.imageLoaded, 1);
      } else {
        notify(res && res.msg ? res.msg : MSG.imageMissing, 2);
      }
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function uploadImage(input) {
    var file = input.files && input.files[0];
    var cid = $(input).data('cid');
    if (!file) {
      notify(MSG.noFile, 2);
      return;
    }
    var formData = new FormData();
    formData.append('do', 'upload');
    formData.append('type', 'class');
    formData.append('file', file);
    var index = loading();
    $.ajax({
      url: 'ajax.php?act=uploadimg',
      data: formData,
      type: 'POST',
      dataType: 'json',
      cache: false,
      processData: false,
      contentType: false
    }).done(function (res) {
      if (res && res.code === 0) {
        setImageValue(cid, res.url);
        notify(MSG.uploadSuccess, 1);
      } else {
        notify(res && res.msg ? res.msg : MSG.serverError, 2);
      }
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
      $(input).val('');
    });
  }

  function previewImage(cid) {
    var value = getImageValue(cid);
    if (!value) {
      notify(MSG.noImage, 2);
      return;
    }
    var src = resolveImageUrl(value);
    window.layer.open({
      type: 1,
      title: MSG.preview,
      area: modalArea('520px'),
      shadeClose: true,
      content: '<div class="admin-class-modal"><div class="admin-class-image-modal__preview"><img src="' + escapeHtml(src) + '" alt=""></div></div>'
    });
  }

  function saveAllImages() {
    var form = $('#classImageForm');
    if (!form.length) {
      return;
    }
    var index = loading();
    request({
      type: 'POST',
      url: './ajax_class.php?act=editClassImages',
      data: form.serialize()
    }).done(function (res) {
      if (res && res.code === 0) {
        notify(MSG.allImagesSaved, 1);
      } else {
        notify(res && res.msg ? res.msg : MSG.serverError, 2);
      }
    }).fail(function () {
      notify(MSG.serverError, 2);
    }).always(function () {
      closeLoading(index);
    });
  }

  function applySearchFilter() {
    var keyword = $.trim($('#classSearch').val() || '').toLowerCase();
    var rows = $('[data-class-row]');
    rows.removeClass('is-filtered');
    $('.admin-class-search-empty').remove();
    if (!keyword) {
      return;
    }
    rows.each(function () {
      var row = $(this);
      var haystack = String(row.data('search') || '').toLowerCase();
      if (haystack.indexOf(keyword) === -1) {
        row.addClass('is-filtered');
      }
    });
    if (rows.length && rows.not('.is-filtered').length === 0) {
      $('#classlisttbody').append('<tr class="admin-class-search-empty"><td colspan="6"><div class="admin-class-empty"><i class="fa fa-search"></i><strong>' + MSG.emptySearch + '</strong></div></td></tr>');
    }
  }

  function bindEvents() {
    $(document).on('click', '[data-class-action]', function (event) {
      var button = $(this);
      var action = button.data('class-action');
      var cid = button.data('cid');
      if (action !== 'modal-image-preview') {
        event.preventDefault();
      }
      switch (action) {
        case 'refresh':
          listTable();
          break;
        case 'add-parent':
          addClass(0, $('#newClassName').val());
          break;
        case 'add-sub':
          promptSubClass(cid, button.data('name') || '');
          break;
        case 'save-one':
          saveOne(cid);
          break;
        case 'save-all':
          saveAll();
          break;
        case 'toggle':
          setActive(cid, button.data('active'));
          break;
        case 'sort':
          sortClass(cid, button.data('sort'));
          break;
        case 'delete':
          deleteClass(cid, button.data('name'));
          break;
        case 'batch':
          batchOperation();
          break;
        case 'pay':
          openPayModal(cid);
          break;
        case 'area':
          openAreaModal(cid);
          break;
        case 'notice':
          openNoticeModal(cid);
          break;
        case 'image':
          openImageModal(cid);
          break;
        case 'image-upload':
          $('.admin-class-file-input[data-cid="' + cid + '"]').first().trigger('click');
          break;
        case 'modal-image-upload':
          $('[data-modal-image-file][data-cid="' + cid + '"]').trigger('click');
          break;
        case 'image-auto':
        case 'modal-image-auto':
          autoImage(cid);
          break;
        case 'image-preview':
          previewImage(cid);
          break;
        case 'modal-image-preview':
          renderImagePreview($('#classImageModalUrl').val());
          break;
        case 'save-all-images':
          saveAllImages();
          break;
      }
    });

    $(document).on('change', '[data-class-check-all]', function () {
      $('[data-class-checkbox]').prop('checked', $(this).prop('checked'));
    });

    $(document).on('change', '.admin-class-file-input', function () {
      uploadImage(this);
    });

    $(document).on('input', '#classImageModalUrl', function () {
      renderImagePreview($(this).val());
    });

    $(document).on('input', '#classSearch', applySearchFilter);

    $(document).on('keydown', '#newClassName', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        addClass(0, $(this).val());
      }
    });
  }

  $(function () {
    bindEvents();
    if ($('#listTable').length) {
      listTable();
    }
  });

  window.listTable = listTable;
})(jQuery, window, document);

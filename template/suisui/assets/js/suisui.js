(function () {
  function ready(fn) {
    if (document.readyState !== "loading") fn();
    else document.addEventListener("DOMContentLoaded", fn);
  }

  function escapeHtml(text) {
    return String(text || "").replace(/[&<>"']/g, function (ch) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" }[ch];
    });
  }

  function decodeAttr(text) {
    if (!text || text === "null") return "";
    try {
      return unescape(text);
    } catch (e) {
      return text;
    }
  }

  ready(function () {
    if (!window.jQuery) return;
    var $ = window.jQuery;
    var cfg = window.SUISUI_TEMPLATE || {};
    var classes = cfg.classes || [];
    var children = cfg.children || {};
    var classMap = {};
    var pendingTid = 0;
    var currentClassCid = 0;
    var defaultClassImg = cfg.defaultClassImg || "/assets/img/Product/default.png";

    $.each(classes, function (_, item) {
      classMap[parseInt(item.cid, 10)] = item;
    });

    function className(cid) {
      cid = parseInt(cid, 10);
      return classMap[cid] ? classMap[cid].name : "";
    }

    function primaryOf(cid) {
      cid = parseInt(cid, 10);
      if (!classMap[cid]) return 0;
      return parseInt(classMap[cid].pid, 10) > 0 ? parseInt(classMap[cid].pid, 10) : cid;
    }

    function normalizeImage(src) {
      src = $.trim(String(src || ""));
      if (!src || src === "null" || src === "undefined" || src === "#") return "";
      if (src.indexOf("http://") === 0 || src.indexOf("https://") === 0 || src.indexOf("//") === 0 || src.charAt(0) === "/") return src;
      return "/" + src.replace(/^\/+/, "");
    }

    function classImage(cid) {
      cid = parseInt(cid, 10) || 0;
      var row = classMap[cid] || {};
      var img = normalizeImage(row.shopimg);
      if (img) return img;
      var parent = primaryOf(cid);
      if (parent && parent !== cid && classMap[parent]) {
        img = normalizeImage(classMap[parent].shopimg);
        if (img) return img;
      }
      return defaultClassImg;
    }

    function setClassImage(src) {
      var target = normalizeImage(src) || defaultClassImg;
      var $img = $("#classImg");
      if (!$img.length) return;
      $img
        .off("error.suisuiClass")
        .on("error.suisuiClass", function () {
          if ($(this).attr("src") !== defaultClassImg) $(this).attr("src", defaultClassImg);
        })
        .attr("src", target);
    }

    function applyClassVisual(cid) {
      cid = parseInt(cid, 10) || 0;
      currentClassCid = cid;
      $("#className").text(cid > 0 ? (className(cid) || "\u5df2\u9009\u5206\u7c7b") : "\u7b49\u5f85\u9009\u62e9\u5206\u7c7b");
      setClassImage(classImage(cid));
    }

    function resetProduct() {
      $("#tid").html('<option value="0">\u8bf7\u9009\u62e9\u5546\u54c1</option>');
      $("#need").val("");
      $("#leftcount").val("");
      $("#display_price,#display_left,#display_num,#display_num_note,#alert_frame").hide();
      $("#inputsname").html("");
      $("#suisuiInputPlaceholder").show();
      $("#suisuiSelectedProduct").html("<strong>\u8bf7\u5148\u9009\u62e9\u5546\u54c1</strong><span>\u9009\u62e9\u540e\u4f1a\u663e\u793a\u4ef7\u683c\u3001\u5e93\u5b58\u3001\u5546\u54c1\u4ecb\u7ecd\u548c\u586b\u5199\u8981\u6c42\u3002</span>");
      setStep(0);
    }

    function setStep(index) {
      index = parseInt(index, 10) || 0;
      $(".suisui-steps span").each(function () {
        var step = parseInt($(this).data("step"), 10) || 0;
        $(this).toggleClass("is-active", step === index);
        $(this).toggleClass("is-done", step < index);
      });
    }

    function renderSubOptions(primaryCid, selectedCid) {
      var list = children[String(primaryCid)] || children[primaryCid] || [];
      var html = "";
      if (!list.length) {
        $("#display_selectsubclass").hide();
        $("#subcid").prop("disabled", true).html('<option value="0"></option>');
        return false;
      }
      $("#display_selectsubclass").show();
      html += '<option value="0">\u8bf7\u9009\u62e9\u4e8c\u7ea7\u5206\u7c7b</option>';
      $.each(list, function (_, item) {
        var cid = parseInt(item.cid, 10);
        html += '<option value="' + cid + '"' + (cid === selectedCid ? " selected" : "") + ">" + escapeHtml(item.name) + "</option>";
      });
      $("#subcid").prop("disabled", false).html(html);
      return true;
    }

    function loadCategory(cid) {
      cid = parseInt(cid, 10) || 0;
      $("#cid").val(cid);
      resetProduct();
      applyClassVisual(cid);
      if (cid > 0) {
        setStep(1);
        $("#cid").trigger("change");
      }
    }

    function selectCategory(cid, tid) {
      cid = parseInt(cid, 10) || 0;
      tid = parseInt(tid, 10) || 0;
      pendingTid = tid;
      var primary = primaryOf(cid) || cid;
      if (primary > 0) {
        $("#primary_cid").val(primary);
        var hasSub = renderSubOptions(primary, cid !== primary ? cid : 0);
        if (hasSub && cid === primary) {
          $("#cid").val(0);
          resetProduct();
          applyClassVisual(primary);
          return;
        }
      }
      loadCategory(cid);
    }

    function applyPendingTid() {
      if (!pendingTid) return;
      var target = String(pendingTid);
      if ($('#tid option[value="' + target + '"]').length) {
        $("#tid").val(target).trigger("change");
        pendingTid = 0;
      }
    }

    function syncProductPanel() {
      var selected = $("#tid option:selected");
      var tid = selected.val();
      if (!tid || tid === "0") {
        $("#suisuiInputPlaceholder").show();
        return;
      }
      var name = selected.text();
      var price = selected.attr("price") || "";
      var stock = selected.attr("stock");
      var close = selected.attr("close") === "1";
      var desc = decodeAttr(selected.attr("desc"));
      var status = close ? "\u7ef4\u62a4\u4e2d" : "\u53ef\u4e0b\u5355";
      $("#need").val(price ? "\u00a5" + price + "\u5143" : $("#need").val());
      $("#suisuiSelectedProduct").html(
        "<strong>" + escapeHtml(name) + "</strong>" +
        "<span>\u4ef7\u683c\uff1a" + escapeHtml(price ? "\u00a5" + price : "\u5f85\u786e\u8ba4") +
        " / \u72b6\u6001\uff1a" + status +
        (stock && stock !== "null" ? " / \u5e93\u5b58\uff1a" + escapeHtml(stock) : "") +
        "</span>"
      );
      if (desc && !$("#alert_frame").is(":visible")) {
        $("#alert_frame").html(desc).show();
      }
      setStep(2);
      window.setTimeout(function () {
        $("#suisuiInputPlaceholder").toggle($("#inputsname").children().length === 0);
      }, 80);
    }

    $("#primary_cid").on("change", function () {
      var primary = parseInt($(this).val(), 10) || 0;
      pendingTid = 0;
      if (!primary) {
        $("#subcid").prop("disabled", true).html('<option value="0">\u8bf7\u5148\u9009\u62e9\u4e00\u7ea7\u5206\u7c7b</option>');
        $("#display_selectsubclass").hide();
        $("#cid").val(0);
        applyClassVisual(0);
        resetProduct();
        return;
      }
      var hasSub = renderSubOptions(primary, 0);
      if (!hasSub) loadCategory(primary);
      else {
        $("#cid").val(0);
        applyClassVisual(primary);
        resetProduct();
      }
    });

    $("#subcid").on("change", function () {
      var sub = parseInt($(this).val(), 10) || 0;
      if (sub > 0) loadCategory(sub);
    });

    $(document).on("change", "#tid", function () {
      if (typeof window.getPoint === "function") window.getPoint();
      window.setTimeout(syncProductPanel, 60);
    });

    $(document).ajaxSuccess(function (_, __, settings) {
      if (settings && settings.url && settings.url.indexOf("act=gettool") >= 0) {
        window.setTimeout(function () {
          applyPendingTid();
          syncProductPanel();
          applyClassVisual(currentClassCid || parseInt($("#cid").val(), 10) || 0);
        }, 120);
      }
    });

    $(document).on("click", "[data-suisui-tool]", function () {
      var cid = parseInt($(this).data("cid"), 10) || 0;
      var tid = parseInt($(this).data("tid"), 10) || 0;
      $('.suisui-tabs a[href="#orderPane"]').tab("show");
      selectCategory(cid, tid);
      window.scrollTo({ top: 0, behavior: "smooth" });
    });

    $("#doSearch").on("click", function () {
      pendingTid = 0;
    });

    var modalReturnTarget = null;
    $(document).on("show.bs.modal", ".suisui-modal", function (event) {
      modalReturnTarget = event.relatedTarget || document.activeElement || $("#primary_cid").get(0);
      $(this).removeAttr("aria-hidden");
    });

    $(document).on("click", ".suisui-modal [data-dismiss='modal']", function () {
      this.blur();
    });

    $(document).on("hide.bs.modal", ".suisui-modal", function () {
      if ($.contains(this, document.activeElement)) document.activeElement.blur();
    });

    $(document).on("hidden.bs.modal", ".suisui-modal", function () {
      var fallback = $("#primary_cid").get(0) || $("#tab-query").get(0);
      var target = modalReturnTarget && document.documentElement.contains(modalReturnTarget) ? modalReturnTarget : fallback;
      if (target && target !== document.body && typeof target.focus === "function") target.focus();
    });

    var initialCid = parseInt(cfg.initialCid, 10) || 0;
    var initialTid = parseInt(cfg.initialTid, 10) || 0;
    if (initialCid > 0) selectCategory(initialCid, initialTid);
    else {
      applyClassVisual(0);
      resetProduct();
    }

    var popup = $("#suisuiPopupNotice");
    if (popup.length && cfg.popupEnabled) {
      var cookieKey = "suisui_popup_notice_" + (cfg.version || "v1");
      var everyTime = parseInt(cfg.modalShowType, 10) === 0;
      if (everyTime || !$.cookie(cookieKey)) {
        window.setTimeout(function () {
          popup.modal({ keyboard: true });
          if (!everyTime) $.cookie(cookieKey, "1", { expires: 365, path: "/" });
        }, 420);
      }
    }
  });
})();

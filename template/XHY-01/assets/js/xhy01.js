/* XHY-01 template interactions. Keep business actions in assets/js/main.js. */
(function (window, document, $) {
  "use strict";

function setXhyBgFallback(targetUrl){
    var bg = document.getElementById('anime-bg');
    if(!bg) return;
    var img = new Image();
    img.onload = function(){ bg.style.backgroundImage = "url('" + targetUrl + "')"; };
    img.onerror = function(){ bg.style.backgroundImage = "url('/template/XHY-01/bg-fallback.jpg')"; };
    img.src = targetUrl;
}
setTimeout(function(){ setXhyBgFallback('/template/XHY-01/bg-fallback.jpg'); }, 80);

// 显示弹窗
	function showModal() {
		document.getElementById('lxkf').style.display = 'block';
		document.getElementById('modalOverlay').style.display = 'block';
	}

	// 关闭弹窗
	function closeModal() {
		document.getElementById('lxkf').style.display = 'none';
		document.getElementById('modalOverlay').style.display = 'none';
	}

	// 点击遮罩层关闭弹窗
	var modalOverlay = document.getElementById('modalOverlay');
	if (modalOverlay) {
		modalOverlay.onclick = closeModal;
	}

	function openXhyOnlineChat(e) {
		if (e) e.preventDefault();
		closeModal();
		var tryOpenChat = function() {
			if (window.$chatwoot && typeof window.$chatwoot.toggle === 'function') {
				window.$chatwoot.toggle('open');
				return true;
			}
			var chatButton = document.querySelector('.woot-widget-bubble, .woot--bubble-holder, .woot-widget-holder');
			if (chatButton) {
				chatButton.click();
				return true;
			}
			return false;
		};
		if (!tryOpenChat()) {
			window.open(window.XHY01_CONTACT_URL || "./", "_blank");
		}
		return false;
	}

	// 修改客服链接的点击事件
	document.addEventListener('DOMContentLoaded', function() {
		var links = document.querySelectorAll('a[href="#lxkf"]');
		links.forEach(function(link) {
			link.removeAttribute('data-toggle');
			link.onclick = function(e) {
				e.preventDefault();
				showModal();
			};
		});
	});

function AddFavorite(title, url) {
  try {
      window.external.addFavorite(url, title);
  }
catch (e) {
     try {
       window.sidebar.addPanel(title, url, "");
    }
     catch (e) {
         alert("手机用户：点击底部菜单添加书签/收藏网址！\n\n电脑用户：请按 Ctrl+D 手动收藏本网址！");
     }
  }
}

window.showModal = showModal;
window.closeModal = closeModal;
window.openXhyOnlineChat = openXhyOnlineChat;
window.AddFavorite = AddFavorite;

// 确保文档加载完成后初始化tooltip
	$(document).ready(function() {
		// 初始化所有tooltip
		if (typeof $().tooltip !== 'undefined') {
			$('[data-toggle="tooltip"]').tooltip();
		} else {
		}
	});

// 弹出公告控制
		$(document).ready(function() {
			if (isModal) {
				// 检查是否需要显示公告
				if (modalShowType == 0) {
					// 每次进网站都弹
					$('#anounce').modal('show');
				} else if (modalShowType == 1) {
					// 只弹一次
					var modalShown = localStorage.getItem('modal_shown');
					if (!modalShown) {
						$('#anounce').modal('show');
						localStorage.setItem('modal_shown', '1');
					}
				}
			}
		});
		$(function() {
   			if (typeof $.fn.lazyload !== 'undefined') {
       			$("img.lazy").lazyload({
           		effect: "fadeIn"
        		});
    		} else {
    		}
		});
		// 移除无效的计时脚本依赖
		var stimeElement = document.createElement('div');
		stimeElement.id = 'stime';
		stimeElement.style.display = 'none';
		document.body.appendChild(stimeElement);
		var ss = 0,
		    mm = 0,
		    hh = 0;

		function TimeGo() {
		    ss++;
		    if (ss >= 60) {
		        mm += 1;
		        ss = 0
		    }
		    if (mm >= 60) {
		        hh += 1;
		        mm = 0
		    }
		    var ss_str = (ss < 10 ? "0" + ss : ss);
		    var mm_str = (mm < 10 ? "0" + mm : mm);
		    var tMsg = "" + hh + "小时" + mm_str + "分" + ss_str + "秒";
		    var stimeElement = document.getElementById("stime");
		    if (stimeElement) {
		        stimeElement.innerHTML = tMsg;
		    }
		    setTimeout(TimeGo, 1000)
		}
		TimeGo();
$("#submit_buy").addClass("btn btn-primary btn-block btn-rounded q8-submit-buy");

// 购买成功后自动显示订单详情弹窗
	$(document).ready(function(){
		// 使用纯JavaScript获取URL参数，避免混合PHP
		function getUrlParam(name) {
			var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
			var r = window.location.search.substr(1).match(reg);
			if (r != null) return decodeURIComponent(r[2]);
			return '';
		}

		if(getUrlParam('buyok') == '1'){
			// 直接从URL获取订单ID和skey参数
			var orderid = getUrlParam('orderid');
			var skey = getUrlParam('skey');

			// 如果有订单ID和skey参数，直接显示订单详情
			if(orderid && skey){
				showOrder(orderid, skey);
			}
			// 否则，为了保持兼容性，仍然通过查询获取最新订单
			else {
				var searchtype = getUrlParam('searchtype') || 1;
				var qq = getUrlParam('qq') || '';

				// 先检查 $_GET 变量的类型，确保正确处理
				if (typeof window.$_GET === 'function') {
					// 保存原始 $_GET 函数
					var original_GET = window.$_GET;
					// 创建一个临时的$_GET函数，移除buyok参数
					window.$_GET = function() {
						var url = window.document.location.href.toString();
						var u = url.split("?");
						if(typeof(u[1]) == "string"){
							u = u[1].split("&");
							var get = {};
							for(var i in u){
								var j = u[i].split("=");
								get[j[0]] = j[1];
							}
							// 关键修改：将 buyok 设置为 0，禁用 showOrder 调用
							get['buyok'] = 0;
							return get;
						} else {
							return {};
						}
					};
				}

				// 临时设置 querymode，禁用查询弹窗显示
				var tempQuerymode = window.querymode;
				window.querymode = 'noPopup';

				// 执行订单查询
				queryOrder(searchtype, qq, 1);

				// 恢复原始设置
				setTimeout(function() {
					window.querymode = tempQuerymode;
					if (typeof original_GET !== 'undefined') {
						window.$_GET = original_GET;
					}
				}, 100);
			}
		}
	});

$(function(){
  var $userjsModal = $('#userjs');
  if ($userjsModal.length && !$userjsModal.parent().is('body')) {
    $userjsModal.appendTo(document.body);
  }
});

(function() {
	function q8EscapeHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
	function q8BuildRecommendOption(res) {
		return '<option value="'+res.tid+'" cid="'+res.cid+'" price="'+res.price+'" desc="'+escape(res.desc || '')+'" alert="'+escape(res.alert || '')+'" inputname="'+(res.input || '')+'" inputsname="'+(res.inputs || '')+'" multi="'+res.multi+'" isfaka="'+res.isfaka+'" count="'+res.value+'" close="'+res.close+'" prices="'+(res.prices || '')+'" max="'+res.max+'" min="'+res.min+'" stock="'+res.stock+'">'+q8EscapeHtml(res.name)+'</option>';
	}
	function q8SelectRecommendTool(cid, tid, pcid) {
		cid = parseInt(cid, 10) || 0;
		tid = parseInt(tid, 10) || 0;
		pcid = parseInt(pcid, 10) || cid;
		if (!cid || !tid) return;
		if ($('#subcid').length && typeof getPoint === 'function') {
			history.replaceState({}, null, './?cid='+cid+'&tid='+tid);
			$_GET['cid'] = cid;
			$_GET['tid'] = tid;
			$('#cid').val(pcid);
			$('#goodType').hide('normal');
			$('#goodTypeContent,#goodTypeContents').show('normal');
			$.getJSON('./ajax.php?act=getsubclass', {cid: pcid}, function(data) {
				if (data && data.code == 0) {
					$('#subcid').html(data.html || '<option value="0">请选择二级分类</option>');
					$('#subcid').val(cid);
					if ($('#subcid option').length > 1) $('#display_selectsubclass').show(); else $('#display_selectsubclass').hide();
				}
			});
			$.getJSON('./ajax.php?act=gettool', {tid: tid}, function(data) {
				if (data && data.code == 0 && data.data && data.data.length) {
					$('#tid').html(q8BuildRecommendOption(data.data[0])).val(tid);
					getPoint();
				}
			});
			return;
		}
		if (typeof toTool === 'function') {
			toTool(cid, tid);
		}
	}
	function q8LoadTodayRecommend() {
		if (!$('#q8TodayRecommend').length) return;
		$.ajax({
			type: 'GET',
			url: './ajax.php?act=gettodayrecommend&limit=8',
			dataType: 'json',
			success: function(data) {
				if (!data || data.code != 0 || !data.data || !data.data.length) return;
				var html = '';
				$.each(data.data, function(i, res) {
					var cid = parseInt(res.cid, 10) || 0;
					var tid = parseInt(res.tid, 10) || 0;
					var pcid = parseInt(res.pcid, 10) || cid;
					html += '<button type="button" class="q8-today-recommend__item" data-cid="'+cid+'" data-tid="'+tid+'" data-pcid="'+pcid+'">' +
						'<div class="q8-today-recommend__name">'+q8EscapeHtml(res.name)+'</div>' +
						'<div class="q8-today-recommend__meta"><span class="q8-today-recommend__price">&yen;'+q8EscapeHtml(res.price)+'</span><span class="q8-today-recommend__action">去下单</span></div>' +
					'</button>';
				});
				$('#q8TodayRecommendList').html(html);
				var $anchor = $('.custom-btn:visible').first();
				if ($anchor.length) $('#q8TodayRecommend').insertAfter($anchor);
				$('#q8TodayRecommend').removeClass('is-open').show();
				$('#q8TodayRecommend .q8-today-recommend__toggle').attr('aria-expanded', 'false').find('span').text('\u5c55\u5f00\u63a8\u8350');
			}
		});
	}
	$(document).ready(function() {
		q8LoadTodayRecommend();
		$(document).on('click', '.q8-today-recommend__toggle', function() {
			var $box = $(this).closest('.q8-today-recommend');
			var isOpen = !$box.hasClass('is-open');
			$box.toggleClass('is-open', isOpen);
			$(this).attr('aria-expanded', isOpen ? 'true' : 'false').find('span').text(isOpen ? '\u6536\u8d77\u63a8\u8350' : '\u5c55\u5f00\u63a8\u8350');
		});
		$(document).on('click', '.q8-today-recommend__item', function() {
			q8SelectRecommendTool($(this).data('cid'), $(this).data('tid'), $(this).data('pcid'));
		});
	});
})();

(function(){
	function q8ToolJumpOption(res){
		return '<option value="'+res.tid+'" cid="'+res.cid+'" price="'+res.price+'" desc="'+escape(res.desc || '')+'" alert="'+escape(res.alert || '')+'" inputname="'+(res.input || '')+'" inputsname="'+(res.inputs || '')+'" multi="'+res.multi+'" isfaka="'+res.isfaka+'" count="'+res.value+'" close="'+res.close+'" prices="'+(res.prices || '')+'" max="'+res.max+'" min="'+res.min+'" stock="'+res.stock+'">'+String(res.name || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')+'</option>';
	}
	function q8EnforceUrlToolSelect(){
		var tid = parseInt((window.$_GET && window.$_GET.tid) || 0, 10);
		if (!tid || !$('#tid').length) return;
		$.getJSON('./ajax.php?act=gettool', {tid: tid}, function(data){
			if (!data || data.code != 0 || !data.data || !data.data.length) return;
			var res = data.data[0];
			if (res.cid) {
				$('#cid').val(res.cid);
				if (window.$_GET) window.$_GET.cid = String(res.cid);
			}
			if ($('#tid option[value="'+res.tid+'"]').length === 0) {
				$('#tid').append(q8ToolJumpOption(res));
			}
			$('#tid').val(String(res.tid));
			if (typeof getPoint === 'function') getPoint();
			$('#goodType').hide('normal');
			$('#goodTypeContent,#goodTypeContents').show('normal');
		});
	}
	$(window).on('load', function(){
		setTimeout(q8EnforceUrlToolSelect, 600);
		setTimeout(q8EnforceUrlToolSelect, 1800);
	});
})();

})(window, document, window.jQuery);

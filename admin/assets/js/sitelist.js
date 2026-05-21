function listTable(query){
	var url = window.document.location.href.toString();
	var queryString = url.split("?")[1];
	query = query || queryString;
	if(query == 'start' || query == undefined){
		query = '';
		history.replaceState({}, null, './sitelist.php');
	}else if(query != undefined){
		history.replaceState({}, null, './sitelist.php?'+query);
	}
	layer.closeAll();
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'GET',
		url : 'sitelist-table.php?'+query,
		dataType : 'html',
		cache : false,
		success : function(data) {
			layer.close(ii);
			$("#listTable").html(data)
		},
		error:function(data){
			layer.close(ii);
			// 添加更详细的错误信息
			var errorMsg = '服务器错误';
			if(data.status) errorMsg += ' - HTTP状态码: ' + data.status;
			if(data.statusText) errorMsg += ' - ' + data.statusText;
			layer.msg(errorMsg);
			return false;
		}
	});
}
var siteDialogIndex = null;
function openSiteDialog(selector, title){
	var dialogWidth = Math.min(520, $(window).width() - 24);
	siteDialogIndex = layer.open({
		type: 1,
		title: title,
		area: [dialogWidth + 'px', 'auto'],
		shade: 0,
		skin: 'admin-shell-layer',
		content: $(selector)
	});
}
function closeSiteDialog(){
	if(siteDialogIndex !== null){
		layer.close(siteDialogIndex);
		siteDialogIndex = null;
	}
}
function showRecharge(zid) {
	var $modal = $('#modal-rmb');
	$("input[name='zid']").val(zid);
	if ($modal.parent()[0] !== document.body) {
		$modal.appendTo(document.body);
	}
	$modal.modal('show');
}
function setSuper(zid) {
	$.ajax({
		type : 'GET',
		url : 'ajax_site.php?act=setSuper&zid='+zid,
		dataType : 'json',
		success : function(data) {
			layer.msg('切换成功');
			listTable();
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
}
function setActive(zid,active) {
	$.ajax({
		type : 'GET',
		url : 'ajax_site.php?act=setSite&zid='+zid+'&active='+active,
		dataType : 'json',
		success : function(data) {
			listTable();
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
}
function setEndtime(zid) {
	layer.prompt({title: '需要延时多少个月', value: '12', formType: 0}, function(text, index){
		$.ajax({
			type : 'POST',
			url : 'ajax_site.php?act=setEndtime',
			data : {zid:zid,month:text},
			dataType : 'json',
			success : function(data) {
				if(data.code == 0){
					layer.msg(data.msg);
					listTable();
				}else{
					layer.alert(data.msg);
				}
			},
			error:function(data){
				layer.msg('服务器错误');
				return false;
			}
		});
	});
}
function delSite(zid) {
	var confirmobj = layer.confirm('你确实要删除此站点吗？', {
	  btn: ['确定','取消']
	}, function(){
	  $.ajax({
		type : 'GET',
		url : 'ajax_site.php?act=delSite&zid='+zid,
		dataType : 'json',
		success : function(data) {
			if(data.code == 0){
				layer.msg('删除成功');
				listTable();
			}else{
				layer.alert(data.msg,{icon:0});
			}
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	  });
	}, function(){
	  layer.close(confirmobj);
	});
}
$(document).ready(function(){
	$("#recharge").click(function(){
		var zid=$("input[name='zid']").val();
		var actdo=$("select[name='do']").val();
		var rmb=$("input[name='rmb']").val();
		var remark=$("input[name='remark']").val();
		if(rmb==''){layer.alert('请输入金额');return false;}
		var ii = layer.load(2, {shade:[0.1,'#fff']});
		$.ajax({
			type : "POST",
			url : "ajax_site.php?act=siteRecharge",
			data : {zid:zid,actdo:actdo,rmb:rmb,remark:remark},
			dataType : 'json',
			success : function(data) {
				layer.close(ii);
				if(data.code == 0){
					layer.msg('修改余额成功');
					$('#modal-rmb').modal('hide');
					listTable();
				}else{
					layer.alert(data.msg);
				}
			},
			error:function(data){
				layer.msg('服务器错误');
				return false;
			}
		});
	});
	$("#search_submit").click(function(){
		var kw=$("input[name='kw']").val();
		var zid=$("input[name='zid']").val();
		closeSiteDialog();
		if(kw != ''){
			listTable('kw='+kw);
		}else if(zid != ''){
			listTable('zid='+zid);
		}else{
			listTable('start');
		}
	});
	$("#search2_submit").click(function(){
		var power=$("select[name='power']").val();
		closeSiteDialog();
		if(power == '0'){
			listTable('start');
		}else{
			listTable('power='+power);
		}
	});
	$("#tabSort").change(function(){
		if($(this).val() == '0'){
			listTable('sort=0');
		}else if($(this).val() == '1'){
			listTable('sort=1');
		}else{
			listTable('start');
		}
	});
	$("#openSiteSearch").click(function(){
		openSiteDialog('#siteSearchDialog', '\u641c\u7d22\u5206\u7ad9');
	});
	$("#openSitePowerFilter").click(function(){
		openSiteDialog('#sitePowerDialog', '\u5206\u7c7b\u67e5\u770b');
	});
	$("[data-site-dialog-close]").click(function(){
		closeSiteDialog();
	});
});
$(document).ready(function(){
	listTable();
})

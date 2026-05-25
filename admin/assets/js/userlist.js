function listTable(query){
	var url = window.document.location.href.toString();
	var queryString = url.split("?")[1];
	query = query || queryString;
	if(query == 'start' || query == undefined){
		query = '';
		history.replaceState({}, null, './userlist.php');
	}else if(query != undefined){
		history.replaceState({}, null, './userlist.php?'+query);
	}
	layer.closeAll();
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'GET',
		url : 'userlist-table.php?'+query,
		dataType : 'html',
		cache : false,
		success : function(data) {
			layer.close(ii);
			$("#listTable").html(data)
		},
		error:function(data){
			layer.close(ii);
			layer.msg('服务器错误');
			return false;
		}
	});
}
function getQueryParam(name){
	var params = new URLSearchParams(window.location.search);
	return params.get(name) || '';
}
function syncUserFiltersFromQuery(){
	$('#userPowerFilter').val(getQueryParam('power'));
	$('#userStatusFilter').val(getQueryParam('status'));
	$('#userSortFilter').val(getQueryParam('sort'));
}
function buildUserFilterQuery(){
	var params = [];
	var power = $('#userPowerFilter').val();
	var status = $('#userStatusFilter').val();
	var sort = $('#userSortFilter').val();
	if(power !== '') params.push('power=' + encodeURIComponent(power));
	if(status !== '') params.push('status=' + encodeURIComponent(status));
	if(sort !== '') params.push('sort=' + encodeURIComponent(sort));
	return params.length ? params.join('&') : 'start';
}
function showSiteRelation(zid){
	var dialogWidth = Math.min(860, $(window).width() - 24);
	var dialogHeight = Math.min(640, $(window).height() - 80);
	layer.open({
		type: 2,
		title: '<i class="fa fa-sitemap"></i> \u4ece\u5c5e\u5173\u7cfb',
		area: [dialogWidth + 'px', dialogHeight + 'px'],
		shade: 0.16,
		skin: 'admin-shell-layer admin-relation-layer',
		content: 'site_relation.php?zid=' + encodeURIComponent(zid)
	});
}
function showRecharge(zid) {
	var $modal = $('#modal-rmb');
	$("input[name='zid']").val(zid);
	$("input[name='rmb']").val('');
	$("input[name='remark']").val('');
	$("input[name='rebate_rate']").val('');
	if ($modal.parent()[0] !== document.body) {
		$modal.appendTo(document.body);
	}
	$modal.modal('show');
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
function delUser(zid) {
	var confirmobj = layer.confirm('你确实要删除此用户吗？', {
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
		var rebate_rate=$("input[name='rebate_rate']").val();
		if(rmb==''){layer.alert('请输入金额');return false;}
		var ii = layer.load(2, {shade:[0.1,'#fff']});
		$.ajax({
			type : "POST",
			url : "ajax_site.php?act=siteRecharge",
			data : {zid:zid,actdo:actdo,rmb:rmb,remark:remark,rebate_rate:rebate_rate},
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
		$("#userSearchModal").modal('hide');
		if(kw == ''){
			listTable('start');
		}else{
			listTable('kw='+encodeURIComponent(kw));
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
	$("#userFilterSubmit").click(function(){
		listTable(buildUserFilterQuery());
	});
	$("#userFilterReset").click(function(){
		$('#userPowerFilter,#userStatusFilter,#userSortFilter').val('');
		listTable('start');
	});
	$('#userPowerFilter,#userStatusFilter,#userSortFilter').change(function(){
		listTable(buildUserFilterQuery());
	});
});
$(document).ready(function(){
	syncUserFiltersFromQuery();
	listTable();
})

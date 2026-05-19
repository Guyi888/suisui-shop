<?php
$is_defend=true;
require '../includes/common.php';
if($islogin2==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

if($userrow['power']==2){
	$type = '0,2,4';
}elseif($userrow['power']==1){
	$type = '0,2,3';
}else{
	$type = '0,1';
}
$msgcount=$DB->getColumn("SELECT count(*) FROM pre_message WHERE type IN ($type) AND active=1");
$msgread = explode(',',$userrow['msgread']);
$limit=isset($_GET['limit'])?intval($_GET['limit']):10;
$rs=$DB->query("SELECT * FROM pre_message WHERE type IN ($type) AND active=1 ORDER BY id DESC LIMIT 0,$limit");
$msgrow=array();
while($res = $rs->fetch()){
	if(in_array($res['id'],$msgread))$res['read']=true;
	else $res['read']=false;
	$msgrow[]=$res;
}

$title = '消息列表';
include 'head.php';
?>
<link rel="stylesheet" href="./public/css/blue_theme.css?v=q8msg1">
<style>
	.q8-message-table td{vertical-align:middle!important}
	.q8-message-action{width:88px}
	.q8-message-action .btn{height:28px;min-width:62px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;font-weight:800;line-height:1}
	.q8-msg-card{min-width:360px;max-width:680px;background:#f7fbff}
	.q8-msg-head{padding:18px 20px;text-align:center;background:linear-gradient(135deg,#1677ff,#22c4c8);color:#fff}
	.q8-msg-head h4{margin:0;color:#fff;font-weight:900}
	.q8-msg-head small{display:block;margin-top:6px;color:rgba(255,255,255,.82)}
	.q8-msg-body{padding:18px 20px;color:#1f2937;line-height:1.8;background:#fff;word-break:break-word}
	@media(max-width:768px){.q8-msg-card{min-width:0;width:92vw}.q8-message-action{width:auto}}
	</style>
<div class="wrapper">
<div class="col-sm-12">
<div class="panel panel-default">
<div class="panel-heading font-bold">消息列表</div>
<div class="panel-body"><a href="javascript:msg_read_all();" class="btn btn-primary">一键已读</a></div>
<div class="well well-sm" style="margin: 0;">我共收到 <b><?php echo $msgcount?></b> 个消息</div>      <div class="table-responsive">
        <table class="table table-striped b-t b-light q8-message-table">
          <thead><th>操作</th><th>通知标题</th><th>接收时间</th><th>阅读状态</th></tr></thead>
<?php
foreach($msgrow as $row){
echo '
	<tr class="onclick '.($row['read']?'':'warning').'"  >
	<td class="q8-message-action"><a class="btn btn-info btn-xs" onclick="show('.$row['id'].')">&#26597;&#30475;</a></td>
	<td>'.$row['title'].'</td>
	<td>'.$row['addtime'].'</td>
	<td>'.($row['read']?'<span class="label label-success">已读</span>':'<span class="label label-warning">未读</span>').'</td>
</tr>';
}
if($msgcount==0){
	echo '<tr><td class="text-center"><font color="grey">消息列表空空如也</font></td></tr>';
}
?>
          <tbody>
          </tbody>
        </table>
		<?php if($msgcount>$limit){?>
		<div class="list-group-item"><center><a href="?limit=<?php echo $limit+10;?>" id="btnload">加载更多</a></center></div>
		<?php }?>
      </div>
</div>
</div>
</div>
<?php include './foot.php';?>
<script>
function msg_read_all()
{
	$.ajax({
		type : 'GET',
		url : 'ajax_user.php?act=msg_read_all',
		dataType : 'json',
		success : function(data) {
			if(data.code==0){
				window.location.reload();
			}else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
}
function show(id) {
	$.ajax({
		type : 'GET',
		url : 'ajax_user.php?act=msginfo&id='+id,
		dataType : 'json',
		success : function(data) {
			if(data.code==0){
				layer.open({
				  type: 1,
				  skin: 'layui-layer-rim q8-msg-layer',
				  anim: 0,
				  btn: ['关闭窗口'],
				  btnAlign:'c',
				  shadeClose: true,
				  title: '查看消息内容',
				  content: '<div class="msg-head"><h4><b>'+data.title+'</b></h4><small><font color="grey">管理员  '+data.date+'</font></small></div><div class="msg-body">'+data.content+'</div>',
				  end: function(){
					  window.location.reload()
				  }
				});
			}else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
}
</script>
</body>
</html>
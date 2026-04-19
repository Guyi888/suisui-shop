<?php
/**
 * 分类管理
**/
include("../includes/common.php");
$title='分类管理';
include './head.php';
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
$my=isset($_GET['my'])?$_GET['my']:null;
?>
<style>
#classlist tbody tr>td:nth-child(2){max-width:50px}
</style>
<script src="assets/js/jquery.dragsort-0.5.2.min.js"></script>
    <div class="col-sm-12 col-md-10 center-block" style="float: none;">
<?php
adminpermission('shop', 1);

$my=isset($_GET['my'])?$_GET['my']:null;

if($my=='qk2')
{
$sql="TRUNCATE TABLE `pre_class`";
if($DB->exec($sql)!==false)
	exit("<script language='javascript'>alert('清空成功！');window.location.href='classlist.php';</script>");
else
	exit("<script language='javascript'>alert('清空失败！".$DB->error()."');history.go(-1);</script>");
}
elseif($my=='classimg'){
$numrows=$DB->getColumn("SELECT count(*) from pre_class");
?>
<div class="block">
	<div class="block-title">
		<h2>修改分类图片&nbsp;[<a href="./classlist.php">返回</a>]</h2>
	</div>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>分类名称</th><th style="min-width:220px">图片URL</th></tr></thead>
          <tbody><form id="classlist">
<?php

$rs=$DB->query("SELECT * FROM pre_class WHERE 1 order by sort asc");
while($res = $rs->fetch())
{
	echo '<tr><td>'.$res['name'].'</td><td><div class="input-group"><input type="file" id="file'.$res['cid'].'" onchange="fileUpload('.$res['cid'].')" style="display:none;"/><input type="text" class="form-control input-sm" name="img['.$res['cid'].']" value="'.$res['shopimg'].'" placeholder="填写图片URL" required><span class="input-group-btn"><a href="javascript:fileSelect('.$res['cid'].')" class="btn btn-success btn-sm" title="上传图片"><i class="glyphicon glyphicon-upload"></i></a><a href="javascript:getImage('.$res['cid'].')" class="btn btn-info btn-sm" title="自动获取图片"><i class="glyphicon glyphicon-search"></i></a><a href="javascript:fileView('.$res['cid'].')" class="btn btn-warning btn-sm" title="查看图片"><i class="glyphicon glyphicon-picture"></i></a></span></div></td></tr>';
}
echo '</form><tr><td></td><td><span class="btn btn-primary btn-sm btn-block" onclick="saveAllImages()">保存全部</span></td></tr>';
?>
			</form>
          </tbody>
        </table>
      </div>
	  <div class="panel-footer">
	  <span class="glyphicon glyphicon-info-sign"></span>当前图片仅适用于部分首页模板
	  </div>
	</div>
<?php
}else{
?>
<div class="block">
	<div class="block-title">
	<div class="block-options pull-right"><a href="javascript:listTable()" class="btn btn-default" title="刷新分类列表"><i class="fa fa-refresh"></i></a></div>
		<h2>商品分类</h2>
	</div>
      <div id="listTable"></div>
	</div>
<?php }?>
  </div>
</div>
<script src="<?php echo $cdnpublic?>layer/3.1.1/layer.js"></script>
<script src="assets/js/classlist.js?ver=<?php echo VERSION?>"></script>
</body>
</html>
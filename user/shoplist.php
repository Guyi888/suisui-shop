<?php
/**
 * 商品管理
**/
include("../includes/common.php");
$title='商品管理';
include './head.php';
if($islogin2==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

unset($islogin2);
?>
<link rel="stylesheet" href="./public/css/blue_theme.css">
<div class="wrapper">
  <div class="col-sm-12">
<?php
if($userrow['power']==0){
	showmsg('&#20320;&#27809;&#26377;&#26435;&#38480;&#20351;&#29992;&#27492;&#21151;&#33021;&#65281;',3);
}

$my=isset($_GET['my'])?$_GET['my']:null;

$rs=$DB->query("SELECT * FROM pre_class WHERE active=1 ORDER BY sort ASC");
$select='<option value="0">&#35831;&#36873;&#25321;&#20998;&#31867;</option>';
$shua_class[0]='&#26410;&#20998;&#31867;';
while($res = $rs->fetch()){
	$shua_class[$res['cid']]=$res['name'];
	$select.='<option value="'.$res['cid'].'">'.$res['name'].'</option>';
}
?>
<div class="modal fade" align="left" id="search2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">&#21830;&#21697;&#26597;&#25214;</h4>
      </div>
      <div class="modal-body">
      <form action="shoplist.php" method="GET">
      <div class="form-group">
        <label for="cid">&#21830;&#21697;&#20998;&#31867;</label>
        <select name="cid" class="form-control">
		<?php echo $select?>
		</select>
      </div>
      <div class="form-group">
        <label for="kw">&#20851;&#38190;&#35789;</label>
        <input type="text" class="form-control" name="kw" placeholder="&#36755;&#20837;&#21830;&#21697;&#21517;&#31216;&#20851;&#38190;&#35789;" value="<?php echo isset($_GET['kw'])?htmlspecialchars($_GET['kw']):''?>">
      </div>
      <input type="submit" class="btn btn-primary btn-block" value="&#26597;&#25214;"></form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">&#20851;&#38381;</button>
      </div>
    </div>
  </div>
</div>
<?php
$price_obj = new \lib\Price($userrow['zid'],$userrow);

if($my=='edit')
{
$tid=intval($_GET['tid']);
$row=$DB->getRow("SELECT * FROM pre_tools WHERE tid='$tid' LIMIT 1");
$price_obj->setToolInfo($tid,$row);
// 鑾峰彇涔嬪墠鐨勫垎绫诲拰鍏抽敭璇嶅弬鏁?
$prev_cid = isset($_GET['cid']) ? intval($_GET['cid']) : 0;
$prev_kw = isset($_GET['kw']) ? urlencode($_GET['kw']) : '';

// 鏋勫缓杩斿洖閾炬帴
$back_url = './shoplist.php';
if($prev_cid > 0 || $prev_kw != '') {
    $back_url .= '?cid=' . $prev_cid;
    if($prev_kw != '') {
        $back_url .= '&kw=' . $prev_kw;
    }
}

echo '<div class="panel panel-default">
<div class="panel-heading font-bold">&#20462;&#25913;&#21830;&#21697;&#20215;&#26684; <a href="'.$back_url.'" style="color:#fff00f"> &#36820;&#22238; </a></div>';
echo '<div class="panel-body">';
echo '<form action="./shoplist.php?my=edit_submit&tid='.$tid.'&cid='.$prev_cid.'&kw='.$prev_kw.'" method="POST">
<div class="form-group">
<label>&#21830;&#21697;&#21517;&#31216;:</label><br>
<input type="text" class="form-control" name="name" value="'.$row['name'].'" disabled>
</div>';
if($userrow['power']==2)echo '
<div class="form-group">
<label>&#25104;&#26412;&#20215;&#26684;:</label><br>
<input type="text" class="form-control" name="self_cost2" value="'.$price_obj->getManageSelfCostPrice($tid).'" disabled>
</div>
<div class="form-group">
<label>&#19979;&#32423;&#19987;&#19994;&#29256;&#20215;&#26684;:</label><br>
<input type="text" class="form-control" name="cost2" value="'.$price_obj->getManageChildProfessionalPrice($tid).'">
</div>
<div class="form-group">
<label>&#19979;&#32423;&#26222;&#36890;&#29256;&#20215;&#26684;:</label><br>
<input type="text" class="form-control" name="cost" value="'.$price_obj->getManageChildNormalPrice($tid).'">
</div>';
else echo '
<div class="form-group">
<label>&#25104;&#26412;&#20215;&#26684;:</label><br>
<input type="text" class="form-control" name="cost" value="'.$price_obj->getManageSelfCostPrice($tid).'" disabled>
</div>';
echo '<div class="form-group">
<label>&#38144;&#21806;&#20215;&#26684;:</label><br>
<input type="text" class="form-control" name="price" value="'.$price_obj->getManageSalePrice($tid).'">
</div>
<div class="form-group">
<label>&#26159;&#21542;&#19978;&#26550;:</label><br>
<select class="form-control" name="del" default="'.$price_obj->getToolDel($tid).'"><option value="0">&#26159;</option><option value="1">&#21542;</option></select>
</div>
<input type="submit" class="btn btn-primary btn-block" value="&#30830;&#23450;&#20462;&#25913;"></form>
</div>
</div>';
echo '
<script>
var items = $("select[default]");
for (i = 0; i < items.length; i++) {
	$(items[i]).val($(items[i]).attr("default")||0);
}
</script>';
}
elseif($my=='edit_submit')
{
$tid=intval($_GET['tid']);
$rows=$DB->getRow("SELECT * FROM pre_tools WHERE tid='$tid' LIMIT 1");
if(!$rows)
	showmsg('&#24403;&#21069;&#35760;&#24405;&#19981;&#23384;&#22312;&#65281;',3);
$price_obj->setToolInfo($tid,$rows);
$price=round(daddslashes($_POST['price']),2);
$mainprice = $price_obj->getMainPrice();//涓荤珯鍟嗗搧鍞环
$maincost = $price_obj->getMainCost();//涓荤珯鍟嗗搧鎴愭湰浠?
$maincost2 = $price_obj->getMainCost2();//涓荤珯涓撲笟鐗堟垚鏈价
$del=intval($_POST['del']);
if(!is_numeric($price) || !preg_match('/^[0-9.]+$/', $price))showmsg('&#20215;&#26684;&#36755;&#20837;&#19981;&#35268;&#33539;',3);
if($userrow['power']==2){
	$cost2=round(daddslashes($_POST['cost2']),2);
	$cost=round(daddslashes($_POST['cost']),2);
	if(!is_numeric($cost2) || !preg_match('/^[0-9.]+$/', $cost2))showmsg('&#20215;&#26684;&#36755;&#20837;&#19981;&#35268;&#33539;',3);
	if(!is_numeric($cost) || !preg_match('/^[0-9.]+$/', $cost))showmsg('&#20215;&#26684;&#36755;&#20837;&#19981;&#35268;&#33539;',3);
	if($cost2<$price_obj->getToolCost2($tid)){
		showmsg('&#19979;&#32423;&#19987;&#19994;&#29256;&#20215;&#26684;&#19981;&#33021;&#20302;&#20110;&#25104;&#26412;&#20215;&#26684;&#65281;',3);
	}
	if($cost<$cost2){
		showmsg('&#19979;&#32423;&#26222;&#36890;&#29256;&#20215;&#26684;&#19981;&#33021;&#20302;&#20110;&#19979;&#32423;&#19987;&#19994;&#29256;&#20215;&#26684;&#65281;',3);
	}
	if($price<$cost){
		showmsg('&#38144;&#21806;&#20215;&#26684;&#19981;&#33021;&#20302;&#20110;&#19979;&#32423;&#26222;&#36890;&#29256;&#20215;&#26684;&#65281;',3);
	}
	if($conf['fenzhan_pricelimit']==1 && $maincost2>0 && ($maincost2>1 && $cost2>$maincost2*2 || $maincost2<=1 && $cost2>2))
		showmsg('&#19979;&#32423;&#19987;&#19994;&#29256;&#20215;&#26684;&#26368;&#39640;&#19981;&#33021;&#36229;&#36807;&#21407;&#19987;&#19994;&#29256;&#20215;&#26684;&#30340;2&#20493;&#65288;&#20302;&#20110;1&#20803;&#30340;&#21830;&#21697;&#65292;&#26368;&#39640;&#19981;&#33021;&#36229;&#36807;2&#20803;&#65289;',3);
	if($conf['fenzhan_pricelimit']==1 && $maincost>0 && ($maincost>1 && $cost>$maincost*2 || $maincost<=1 && $cost>2))
		showmsg('&#19979;&#32423;&#26222;&#36890;&#29256;&#20215;&#26684;&#26368;&#39640;&#19981;&#33021;&#36229;&#36807;&#21407;&#26222;&#36890;&#29256;&#20215;&#26684;&#30340;2&#20493;&#65288;&#20302;&#20110;1&#20803;&#30340;&#21830;&#21697;&#65292;&#26368;&#39640;&#19981;&#33021;&#36229;&#36807;2&#20803;&#65289;',3);
}else{
	if($price<$price_obj->getToolCost($tid)){
		showmsg('&#38144;&#21806;&#20215;&#26684;&#19981;&#33021;&#20302;&#20110;&#25104;&#26412;&#20215;&#26684;&#65281;',3);
	}
	$cost=0;
	$cost2=null;
}
if($conf['fenzhan_pricelimit']==1 && ($mainprice>1 && $price>$mainprice*2 || $mainprice<=1 && $price>2))
	showmsg('&#21830;&#21697;&#38144;&#21806;&#20215;&#26684;&#26368;&#39640;&#19981;&#33021;&#36229;&#36807;&#21407;&#38144;&#21806;&#20215;&#26684;&#30340;2&#20493;&#65288;&#20302;&#20110;1&#20803;&#30340;&#21830;&#21697;&#65292;&#26368;&#39640;&#19981;&#33021;&#36229;&#36807;2&#20803;&#65289;',3);
// 鑾峰彇涔嬪墠鐨勫垎绫诲拰鍏抽敭璇嶅弬鏁?
$prev_cid = isset($_GET['cid']) ? intval($_GET['cid']) : 0;
$prev_kw = isset($_GET['kw']) ? urlencode($_GET['kw']) : '';

// 鏋勫缓杩斿洖閾炬帴
$back_url = './shoplist.php';
if($prev_cid > 0 || $prev_kw != '') {
    $back_url .= '?cid=' . $prev_cid;
    if($prev_kw != '') {
        $back_url .= '&kw=' . $prev_kw;
    }
}

if($price_obj->setPriceInfo($tid,$del,$price,$cost,$cost2))
	showmsg('&#20462;&#25913;&#21830;&#21697;&#25104;&#21151;&#65281;<br/><br/><a href="'.$back_url.'">&#62;&#62;&#36820;&#22238;&#21830;&#21697;&#21015;&#34920;</a>',1);
else
	showmsg('&#20462;&#25913;&#21830;&#21697;&#22833;&#36133;&#65306;'.$DB->error(),4);
}
elseif($my=='reset')
{
if($DB->exec("UPDATE pre_site SET price=NULL WHERE zid='{$userrow['zid']}'"))
	showmsg('&#37325;&#32622;&#25104;&#21151;&#65281;<br/><br/><a href="./shoplist.php">&#62;&#62;&#36820;&#22238;&#21830;&#21697;&#21015;&#34920;</a>',1);
else
	showmsg('&#37325;&#32622;&#22833;&#36133;&#65306;'.$DB->error(),4);
}
else
{
// 澶勭悊鍒嗙被鍜屽叧閿瘝鎼滅储
$cid = intval($_GET['cid']);
$kw = isset($_GET['kw']) ? trim(daddslashes($_GET['kw'])) : '';

// 鏋勫缓鏌ヨ鏉′欢
$where = " active=1";
if($cid > 0) {
	$where .= " AND cid='$cid'";
}
if(!empty($kw)) {
	$where .= " AND name LIKE '%$kw%'";
}

// 璁＄畻鍟嗗搧鏁伴噺
$numrows=$DB->getColumn("SELECT count(*) FROM pre_tools WHERE{$where}");

// 鏋勫缓閾炬帴鍙傛暟
$link = '';
if($cid > 0) {
	$link .= '&cid='.$cid;
}
if(!empty($kw)) {
	$link .= '&kw='.urlencode($kw);
}

// 鏋勫缓椤甸潰鍐呭
if($cid > 0 || !empty($kw)) {
	$title_text = '';
	if($cid > 0) {
		$title_text .= $shua_class[$cid].'&#20998;&#31867;';
	}
	if(!empty($kw)) {
		$title_text .= (!empty($title_text) ? ' - ' : '') . '&#20851;&#38190;&#35789; "'.htmlspecialchars($kw).'"';
	}
	$con='
	<div class="panel panel-default"><div class="panel-heading font-bold">'.$title_text.' - [<a href="shoplist.php" style="color:#fff00f">&#26597;&#30475;&#20840;&#37096;</a>]</div>
	<div class="well well-sm" style="margin: 0;">&#20849;&#25214;&#21040; <b>'.$numrows.'</b> &#20010;&#21830;&#21697;</div>
	<div class="wrapper">
    <a href="#" data-toggle="modal" data-target="#search2" id="search2" class="btn btn-primary"><i class="fa fa-navicon"></i>&nbsp;&#21830;&#21697;&#26597;&#25214;</a>&nbsp;<a class="btn btn-info" href="javascript:void(0)" onclick="up_price('.$cid.')"><i class="fa fa-plus-circle"></i>&nbsp;&#25552;&#21319;&#38144;&#21806;&#20215;</a></div>';
} else {
	$con='
	<div class="panel panel-default"><div class="panel-heading font-bold">&#21830;&#21697;&#21015;&#34920;</div>
	<div class="well well-sm" style="margin: 0;">&#31995;&#32479;&#20849;&#26377; <b>'.$numrows.'</b> &#20010;&#21830;&#21697;&#65292;&#35831;&#26681;&#25454;&#33258;&#36523;&#32463;&#33829;&#38656;&#27714;&#21512;&#29702;&#35843;&#20215;&#12290;</div>
    <div class="wrapper">
    <a href="#" data-toggle="modal" data-target="#search2" id="search2" class="btn btn-primary"><i class="fa fa-navicon"></i>&nbsp;&#21830;&#21697;&#26597;&#25214;</a>&nbsp;<a class="btn btn-success" href="#" data-toggle="modal" data-target="#resetPriceModal"><i class="fa fa-refresh"></i>&nbsp;&#24674;&#22797;&#20215;&#26684;</a>&nbsp;<a class="btn btn-warning" href="#" data-toggle="modal" data-target="#batchPriceModal"><i class="fa fa-calculator"></i>&nbsp;&#25209;&#37327;&#20462;&#25913;</a>&nbsp;<a class="btn btn-info" href="javascript:void(0)" onclick="restoreLastPrice()"><i class="fa fa-undo"></i>&nbsp;&#24674;&#22797;&#21040;&#19978;&#19968;&#27425;&#20462;&#25913;</a>&nbsp;<a class="btn btn-danger" href="javascript:void(0)" onclick="showPriceHistory()"><i class="fa fa-history"></i>&nbsp;&#21382;&#21490;&#35760;&#24405;</a></div>';
}

// 璁剧疆鏈€缁堢殑SQL鏉′欢
$sql = $where;

echo $con;

?>
      <div class="table-responsive">
        <table class="table table-striped b-t b-light">
          <thead><tr><th>&#25805;&#20316;</th><th>&#21830;&#21697;&#21517;&#31216;</th><th>&#25104;&#26412;&#20215;&#26684;</th><?php if($userrow['power']==2){?><th style="font-size:14px">&#19979;&#32423;&#19987;&#19994;&#29256;&#20215;&#26684;</th><th style="font-size:14px">&#19979;&#32423;&#26222;&#36890;&#29256;&#20215;&#26684;</th><?php }?><th>&#38144;&#21806;&#20215;&#26684;</th><th>&#29366;&#24577;</th></tr></thead>
          <tbody>
<?php
$pagesize=30;
$pages=ceil($numrows/$pagesize);
$page=isset($_GET['page'])?intval($_GET['page']):1;
$offset=$pagesize*($page - 1);

$rs=$DB->query("SELECT * FROM pre_tools WHERE{$sql} ORDER BY sort ASC LIMIT $offset,$pagesize");
while($res = $rs->fetch())
{
	$price_obj->setToolInfo($res['tid'],$res);
echo '<tr>
			<td>
					<a href="./shoplist.php?my=edit&tid='.$res['tid'].'&cid='.$cid.'&kw='.urlencode($kw).'" class="btn btn-info btn-xs">&#32534;&#36753;</a>
			</td>
			<td><b><a title="&#28857;&#20987;&#19979;&#21333;" style="color:#000" href="./shop.php?cid='.$res['cid'].'&tid='.$res['tid'].'">'.$res['name'].'</a></b></td>
			<td><font color="#FF0000">'.$price_obj->getManageSelfCostPrice($res['tid']).'&#20803;</font></td>';
if($userrow['power']==2){
	echo '<td><font color="#8A2BE2">'.$price_obj->getManageChildProfessionalPrice($res['tid']).'&#20803;</font></td><td><font color="#9400D3">'.$price_obj->getManageChildNormalPrice($res['tid']).'&#20803;</font></td>';
}
echo '<td><font color="#FF0ff0">'.$price_obj->getManageSalePrice($res['tid']).'&#20803;</font></td>
			<td>'.($price_obj->getToolDel($res['tid'])==1 || $res['close']==1?'<font color=red>&#24050;&#19979;&#26550;</font>':'<font color=green>&#19978;&#26550;&#20013;</font>').'</td>
		</tr>';}
?>

          </tbody>
        </table>
<?php
echo'<ul class="pagination"  style="margin-left:1em">';
$first=1;
$prev=$page-1;
$next=$page+1;
$last=$pages;
if ($page>1)
{
echo '<li><a href="shoplist.php?page='.$first.$link.'">&#39318;&#39029;</a></li>';
echo '<li><a href="shoplist.php?page='.$prev.$link.'">&laquo;</a></li>';
} else {
echo '<li class="disabled"><a>&#39318;&#39029;</a></li>';
echo '<li class="disabled"><a>&laquo;</a></li>';
}
$start=$page-10>1?$page-10:1;
$end=$page+10<$pages?$page+10:$pages;
for ($i=$start;$i<$page;$i++)
echo '<li><a href="shoplist.php?page='.$i.$link.'">'.$i .'</a></li>';
echo '<li class="disabled"><a>'.$page.'</a></li>';
for ($i=$page+1;$i<=$end;$i++)
echo '<li><a href="shoplist.php?page='.$i.$link.'">'.$i .'</a></li>';
if ($page<$pages)
{
echo '<li><a href="shoplist.php?page='.$next.$link.'">&raquo;</a></li>';
echo '<li><a href="shoplist.php?page='.$last.$link.'">&#23614;&#39029;</a></li>';
} else {
echo '<li class="disabled"><a>&raquo;</a></li>';
echo '<li class="disabled"><a>&#23614;&#39029;</a></li>';
}
echo'</ul>';
#鍒嗛〉
}?></div>
<div class="panel-footer">
<span class="fa fa-info-circle"></span>
&#20462;&#25913;&#20215;&#26684;&#21518;&#39318;&#39029;&#20215;&#26684;&#27809;&#21464;&#21270;&#65311;&#36864;&#20986;&#24403;&#21069;&#30331;&#24405;&#36134;&#21495;&#21518;&#65292;&#39318;&#39029;&#25165;&#20250;&#26174;&#31034;&#20320;&#35774;&#23450;&#30340;&#38144;&#21806;&#20215;&#65292;&#21542;&#21017;&#30475;&#21040;&#30340;&#21487;&#33021;&#26159;&#25104;&#26412;&#20215;&#12290;</div>
</div>

</div>
<style>
.q8-shoplist-modal-footer{
	display:flex;
	align-items:center;
	justify-content:flex-end;
	gap:12px;
	padding:16px 20px 20px;
	border-top:1px solid #e6edf7;
}
.q8-shoplist-modal-btn{
	min-width:136px;
	height:42px;
	border-radius:999px;
	font-weight:600;
	transition:all .2s ease;
}
.q8-shoplist-modal-btn--ghost{
	background:#ffffff;
	border:1px solid #c7d5e8;
	color:#49617f;
}
.q8-shoplist-modal-btn--ghost:hover,
.q8-shoplist-modal-btn--ghost:focus{
	background:#f4f8ff;
	border-color:#95b2db;
	color:#264b7f;
}
.q8-shoplist-modal-btn--primary{
	background:linear-gradient(135deg,#2f80ff 0%,#19b9d7 100%);
	border:0;
	color:#ffffff;
	box-shadow:0 12px 24px rgba(45,128,255,.18);
}
.q8-shoplist-modal-btn--primary:hover,
.q8-shoplist-modal-btn--primary:focus{
	color:#ffffff;
	transform:translateY(-1px);
	box-shadow:0 14px 26px rgba(45,128,255,.22);
}
.q8-shoplist-batch-note{
	margin-top:8px;
	font-size:12px;
	line-height:1.7;
	color:#5f6f86;
}
@media (max-width: 767px){
	.q8-shoplist-modal-footer{
		flex-direction:column-reverse;
		align-items:stretch;
	}
	.q8-shoplist-modal-btn{
		width:100%;
		min-width:0;
	}
}
</style>
<?php include './foot.php';?>
<!-- 批量修改价格模态框 -->
<div class="modal fade" id="batchPriceModal" tabindex="-1" role="dialog" aria-labelledby="batchPriceModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="batchPriceModalLabel">&#25209;&#37327;&#35843;&#20215; / &#20998;&#23618;&#25913;&#20215;</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <strong>&#25552;&#31034;&#65306;</strong>&#21830;&#21697;&#20215;&#26684;&#20026; 0 &#25110;&#21830;&#21697;&#21517;&#31216;&#21253;&#21547;&#8220;&#20813;&#36153;&#8221;&#30340;&#21830;&#21697;&#19981;&#21442;&#19982;&#36816;&#31639;&#12290;
        </div>

        <div class="form-group">
          <label for="operationType">&#25805;&#20316;&#31867;&#22411;&#65306;</label>
          <select class="form-control" id="operationType">
            <option value="add">&#21152;&#20215;</option>
            <option value="subtract">&#38477;&#20215;</option>
          </select>
        </div>

        <div class="form-group">
          <label for="priceType">&#35843;&#25972;&#20215;&#26684;&#65306;</label>
          <select class="form-control" id="priceType">
            <option value="price">&#38144;&#21806;&#20215;&#26684;</option>
            <?php if($userrow['power']==2){?>
            <option value="cost2">&#19979;&#32423;&#19987;&#19994;&#29256;&#20215;&#26684;</option>
            <option value="cost">&#19979;&#32423;&#26222;&#36890;&#29256;&#20215;&#26684;</option>
            <?php }?>
          </select>
          <p class="q8-shoplist-batch-note"><?php echo $userrow['power']==2 ? '&#19987;&#19994;&#29256;&#31449;&#38271;&#21487;&#20197;&#25353;&#29031;&#38144;&#21806;&#20215;&#12289;&#19979;&#32423;&#19987;&#19994;&#29256;&#20215;&#12289;&#19979;&#32423;&#26222;&#36890;&#29256;&#20215;&#20998;&#21035;&#25209;&#37327;&#35843;&#25972;&#12290;' : '&#25209;&#37327;&#20462;&#25913;&#40664;&#35748;&#21482;&#35843;&#25972;&#26222;&#36890;&#29992;&#25143;&#30475;&#21040;&#30340;&#38144;&#21806;&#20215;&#26684;&#12290;'; ?></p>
        </div>

        <div class="form-group">
          <label for="batchType">&#25209;&#37327;&#31867;&#22411;&#65306;</label>
          <select class="form-control" id="batchType">
            <option value="fixed">&#22266;&#23450;&#20540;</option>
            <option value="percent">&#30334;&#20998;&#27604;</option>
          </select>
        </div>

        <div class="form-group">
          <label for="modifyValue">&#20462;&#25913;&#20540;&#65306;</label>
          <input type="text" class="form-control" id="modifyValue" placeholder="&#22635;&#20889;&#30334;&#20998;&#27604;&#25110;&#22266;&#23450;&#20540;">
        </div>

        <div class="form-group">
          <label for="goodsCategory">&#25152;&#23646;&#20998;&#31867;&#65306;</label>
          <select class="form-control" id="goodsCategory" multiple="multiple" size="5">
            <option value="0">&#20840;&#37096;&#20998;&#31867;</option>
            <?php echo $select; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="goodsName">&#21830;&#21697;&#21517;&#31216;&#65306;</label>
          <input type="text" class="form-control" id="goodsName" placeholder="&#22635;&#20889;&#21830;&#21697;&#21517;&#31216;&#20851;&#38190;&#35789;&#65292;&#30041;&#31354;&#21017;&#20026;&#20840;&#37096;&#21830;&#21697;">
        </div>

        <div class="form-group">
          <label for="goodsStatus">&#21830;&#21697;&#29366;&#24577;&#65306;</label>
          <select class="form-control" id="goodsStatus">
            <option value="0">&#20840;&#37096;&#21830;&#21697;</option>
            <option value="1">&#19978;&#26550;&#20013;</option>
            <option value="2">&#24050;&#19979;&#26550;</option>
          </select>
        </div>
      </div>
      <div class="modal-footer q8-shoplist-modal-footer">
        <button type="button" class="btn q8-shoplist-modal-btn q8-shoplist-modal-btn--ghost" data-dismiss="modal">&#20851;&#38381;&#31383;&#21475;</button>
        <button type="button" class="btn q8-shoplist-modal-btn q8-shoplist-modal-btn--primary" id="batchPriceSubmitBtn">&#30830;&#23450;&#20462;&#25913;</button>
      </div>
    </div>
  </div>
</div>

<!-- 恢复价格模态框 -->
<div class="modal fade" id="resetPriceModal" tabindex="-1" role="dialog" aria-labelledby="resetPriceModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="resetPriceModalLabel">&#24674;&#22797;&#21830;&#21697;&#20215;&#26684;</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <strong>&#25552;&#31034;&#65306;</strong>&#24674;&#22797;&#20215;&#26684;&#20250;&#28165;&#38500;&#25152;&#26377;&#33258;&#23450;&#20041;&#20215;&#26684;&#35774;&#23450;&#65292;&#24674;&#22797;&#21040;&#26368;&#21021;&#29366;&#24577;&#12290;
        </div>

        <div class="form-group">
          <label for="resetGoodsCategory">&#25152;&#23646;&#20998;&#31867;</label>
          <select class="form-control" id="resetGoodsCategory">
            <option value="0">&#20840;&#37096;&#20998;&#31867;</option>
            <?php echo $select; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="resetGoodsName">&#21830;&#21697;&#21517;&#31216;</label>
          <input type="text" class="form-control" id="resetGoodsName" placeholder="&#36755;&#20837;&#21830;&#21697;&#21517;&#31216;&#20851;&#38190;&#35789;&#65292;&#30041;&#31354;&#21017;&#20026;&#20840;&#37096;&#21830;&#21697;">
        </div>

        <div class="form-group">
          <label for="resetGoodsStatus">&#21830;&#21697;&#29366;&#24577;</label>
          <select class="form-control" id="resetGoodsStatus">
            <option value="0">&#20840;&#37096;&#21830;&#21697;</option>
            <option value="1">&#19978;&#26550;&#20013;</option>
            <option value="2">&#24050;&#19979;&#26550;</option>
          </select>
        </div>
      </div>
      <div class="modal-footer q8-shoplist-modal-footer">
        <button type="button" class="btn q8-shoplist-modal-btn q8-shoplist-modal-btn--ghost" data-dismiss="modal">&#20851;&#38381;&#31383;&#21475;</button>
        <button type="button" class="btn q8-shoplist-modal-btn q8-shoplist-modal-btn--primary" id="resetPriceSubmitBtn">&#30830;&#23450;&#24674;&#22797;</button>
      </div>
    </div>
  </div>
</div>

<!-- 价格历史记录模态框 -->
<div class="modal fade" id="priceHistoryModal" tabindex="-1" role="dialog" aria-labelledby="priceHistoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="priceHistoryModalLabel">&#20215;&#26684;&#20462;&#25913;&#21382;&#21490;&#35760;&#24405;</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <strong>&#25552;&#31034;&#65306;</strong>&#20197;&#19979;&#26159;&#24744;&#30340;&#20215;&#26684;&#20462;&#25913;&#21382;&#21490;&#35760;&#24405;&#65292;&#24744;&#21487;&#20197;&#36873;&#25321;&#29305;&#23450;&#29256;&#26412;&#36827;&#34892;&#24674;&#22797;&#12290;
        </div>
        <div id="historyList" class="table-responsive">
          <!-- 历史记录列表将通过 AJAX 动态加载 -->
        </div>
      </div>
      <div class="modal-footer q8-shoplist-modal-footer">
        <button type="button" class="btn q8-shoplist-modal-btn q8-shoplist-modal-btn--ghost" data-dismiss="modal">&#20851;&#38381;&#31383;&#21475;</button>
      </div>
    </div>
  </div>
</div>

<script>
function reset_price(cid){
	if(confirm('\u662f\u5426\u8981\u91cd\u7f6e\u6240\u6709\u5546\u54c1\u4ef7\u683c\u8bbe\u5b9a\uff0c\u6062\u590d\u5230\u6700\u521d\u72b6\u6001\uff1f')){
		$.ajax({
			type:"post",
			url:"ajax_user.php?act=reset_price",
			data:{
				cid:cid
			},
			dataType:"json",
			success:function(data){
				if(data.code==0){
					layer.alert('\u6062\u590d\u4ef7\u683c\u6210\u529f\uff01',{icon:1},function(){
				      window.location.reload();
				    });
				}else{
					layer.alert(data.msg);
				}
			}
		});
	}
}

// 新的恢复价格函数，支持按条件恢复
function resetPrice() {
    var goodsCategory = $('#resetGoodsCategory').val();
    var goodsName = $('#resetGoodsName').val();
    var goodsStatus = $('#resetGoodsStatus').val();
    var $submitButton = $('#resetPriceSubmitBtn');
    var submitButtonDefaultText = '\u786e\u5b9a\u6062\u590d';

    layer.confirm('\u786e\u5b9a\u8981\u6062\u590d\u7b26\u5408\u6761\u4ef6\u7684\u5546\u54c1\u4ef7\u683c\u5417\uff1f', {icon: 3, title: '\u786e\u8ba4'}, function(index) {
        layer.close(index);

        $submitButton.prop('disabled', true).text('\u5904\u7406\u4e2d...');
        var loadingIndex = layer.load(1, {shade: [0.1,'#fff']});

        $.ajax({
            type: "post",
            url: "ajax_user.php?act=reset_price",
            data: {
                cid: goodsCategory,
                name: goodsName,
                status: goodsStatus
            },
            dataType: "json",
            success: function(data) {
                layer.close(loadingIndex);

                if (data.code == 0) {
                    layer.alert(data.msg, {icon: 1}, function() {
                        $('#resetPriceModal').modal('hide');
                        window.location.reload();
                    });
                } else {
                    layer.alert(data.msg, {icon: 5});
                }
            },
            error: function() {
                layer.close(loadingIndex);
                layer.alert('\u64cd\u4f5c\u5931\u8d25\uff0c\u8bf7\u7a0d\u540e\u91cd\u8bd5', {icon: 5});
            },
            complete: function() {
                $submitButton.prop('disabled', false).text(submitButtonDefaultText);
            }
        });
    });
}
function up_price(cid){
    // 鑾峰彇褰撳墠绛涢€夋潯浠?    var kw = '';
    var urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('kw')){
        kw = urlParams.get('kw');
    }

    layer.prompt({title: '\u4ef7\u683c\u63d0\u5347\u767e\u5206\u6bd4\uff0c\u4f8b\u5982 5\uff0c\u6700\u597d\u4e0d\u8981\u8d85\u8fc7 30', formType: 0}, function(text, index){
	layer.close(index);
	$.ajax({
		type:"post",
		url:"ajax_user.php?act=up_price",
		data:{
			up:text,cid:cid,kw:kw
		},
		dataType:"json",
		success:function(data){
			if(data.code==0){
				layer.alert('\u4ef7\u683c\u63d0\u5347\u6210\u529f\uff01\u5237\u65b0\u540e\u5373\u53ef\u770b\u5230\u6548\u679c\u3002',{icon:1},function(){
		              window.location.reload();
		            });
			}else{
				layer.alert(data.msg);
			}
		}
	});
	});
}

function batchUpdatePrice() {
    var operationType = $('#operationType').val();
    var priceType = $('#priceType').val();
    var batchType = $('#batchType').val();
    var modifyValue = $('#modifyValue').val();
    var goodsCategory = $('#goodsCategory').val();
    var goodsName = $('#goodsName').val();
    var goodsStatus = $('#goodsStatus').val();
    var priceTypeText = $('#priceType option:selected').text();
    var $submitButton = $('#batchPriceSubmitBtn');
    var submitButtonDefaultText = '\u786e\u5b9a\u4fee\u6539';

    if (Array.isArray(goodsCategory)) {
        goodsCategory = goodsCategory.join(',');
    }

    if (!modifyValue) {
        layer.alert('\u8bf7\u586b\u5199\u4fee\u6539\u503c', {icon: 5});
        return;
    }

    if (batchType == 'percent' && (isNaN(modifyValue) || parseFloat(modifyValue) <= 0)) {
        layer.alert('\u767e\u5206\u6bd4\u5fc5\u987b\u4e3a\u6b63\u6570', {icon: 5});
        return;
    }

    if (batchType == 'fixed' && isNaN(modifyValue)) {
        layer.alert('\u56fa\u5b9a\u503c\u5fc5\u987b\u4e3a\u6570\u5b57', {icon: 5});
        return;
    }

    layer.confirm('\u786e\u5b9a\u8981\u5bf9\u300c' + priceTypeText + '\u300d\u6267\u884c\u6279\u91cf\u4fee\u6539\u5417\uff1f', {icon: 3, title: '\u786e\u8ba4'}, function(index) {
        layer.close(index);

        $submitButton.prop('disabled', true).text('\u63d0\u4ea4\u4e2d...');
        var loadingIndex = layer.load(1, {shade: [0.1,'#fff']});

        $.ajax({
            type: "post",
            url: "ajax_user.php?act=batch_update_price",
            data: {
                operation: operationType,
                price_type: priceType,
                batch_type: batchType,
                value: modifyValue,
                cid: goodsCategory,
                name: goodsName,
                status: goodsStatus
            },
            dataType: "json",
            success: function(data) {
                layer.close(loadingIndex);

                if (data.code == 0) {
                    var successHtml = '<div style="line-height:1.9;white-space:normal;word-break:keep-all;">' + data.msg + '</div>';
                    layer.alert(successHtml, {icon: 1, area: ['420px', 'auto']}, function() {
                        $('#batchPriceModal').modal('hide');
                        window.location.reload();
                    });
                } else {
                    layer.alert(data.msg, {icon: 5});
                }
            },
            error: function() {
                layer.close(loadingIndex);
                layer.alert('\u64cd\u4f5c\u5931\u8d25\uff0c\u8bf7\u7a0d\u540e\u91cd\u8bd5', {icon: 5});
            },
            complete: function() {
                $submitButton.prop('disabled', false).text(submitButtonDefaultText);
            }
        });
    });
}

// 恢复到上一次修改
function restoreLastPrice() {
    layer.confirm('\u786e\u5b9a\u8981\u6062\u590d\u5230\u4e0a\u4e00\u6b21\u4fee\u6539\u7684\u72b6\u6001\u5417\uff1f', {
        icon: 3,
        title: '\u786e\u8ba4\u6062\u590d',
        btn: ['\u786e\u5b9a', '\u53d6\u6d88']
    }, function(index) {
        layer.close(index);

        // 鏄剧ず鍔犺浇灞?        var loadingIndex = layer.load(1, {shade: [0.1,'#fff']});

        // 鍙戦€丄JAX璇锋眰
        $.ajax({
            type: "post",
            url: "ajax_user.php?act=restore_last_price",
            dataType: "json",
            success: function(data) {
                layer.close(loadingIndex);

                if (data.code == 0) {
                    layer.alert(data.msg, {icon: 1}, function() {
                        window.location.reload();
                    });
                } else {
                    layer.alert(data.msg, {icon: 5});
                }
            },
            error: function() {
                layer.close(loadingIndex);
                layer.alert('\u64cd\u4f5c\u5931\u8d25\uff0c\u8bf7\u7a0d\u540e\u91cd\u8bd5', {icon: 5});
            }
        });
    });
}

// 显示价格修改历史记录
function showPriceHistory() {
    // 显示历史记录模态框
    $('#priceHistoryModal').modal('show');

    // 加载历史记录
    loadHistoryList();
}

// 加载历史记录列表
function loadHistoryList(page) {
    page = page || 1;

    // 显示加载提示
    $('#historyList').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> &#21152;&#36733;&#20013;...</div>');

    // 鍙戦€丄JAX璇锋眰
    $.ajax({
        type: "post",
        url: "ajax_user.php?act=get_price_history",
        data: {
            page: page
        },
        dataType: "json",
        success: function(data) {
            if (data.code == 0) {
                renderHistoryList(data);
            } else {
                $('#historyList').html('<div class="alert alert-danger text-center">' + data.msg + '</div>');
            }
        },
        error: function() {
            $('#historyList').html('<div class="alert alert-danger text-center">&#21152;&#36733;&#22833;&#36133;&#65292;&#35831;&#31245;&#21518;&#37325;&#35797;</div>');
        }
    });
}

// 渲染历史记录列表
function renderHistoryList(data) {
    var html = '';

    if (data.data.length > 0) {
        html += '<table class="table table-striped b-t b-light">';
        html += '<thead><tr><th>&#25805;&#20316;&#25551;&#36848;</th><th>&#20462;&#25913;&#26102;&#38388;</th><th>&#28041;&#21450;&#21830;&#21697;&#25968;&#37327;</th><th>&#25805;&#20316;</th></tr></thead>';
        html += '<tbody>';

        $.each(data.data, function(index, item) {
            var countLabel = item.price_data_count_accurate == 1 ? item.price_data_count : '\u65e7\u8bb0\u5f55';
            html += '<tr>';
            html += '<td>' + item.description + '</td>';
            html += '<td>' + item.create_time + '</td>';
            html += '<td>' + countLabel + '</td>';
            html += '<td><button class="btn btn-primary btn-xs" onclick="restoreFromHistory(' + item.id + ')">&#24674;&#22797;&#27492;&#29256;&#26412;</button></td>';
            html += '</tr>';
        });

        html += '</tbody>';
        html += '</table>';

        // 分页
        var totalPages = Math.ceil(data.total / data.pagesize);
        if (totalPages > 1) {
            html += '<nav aria-label="Page navigation" style="margin-top: 15px;">';
            html += '<ul class="pagination pagination-sm">';

            // 上一页
            if (data.page > 1) {
                html += '<li><a href="javascript:void(0)" onclick="loadHistoryList(' + (data.page - 1) + ')">&laquo;</a></li>';
            } else {
                html += '<li class="disabled"><a href="javascript:void(0)">&laquo;</a></li>';
            }

            // 页码
            for (var i = 1; i <= totalPages; i++) {
                if (i == data.page) {
                    html += '<li class="active"><a href="javascript:void(0)">' + i + '</a></li>';
                } else {
                    html += '<li><a href="javascript:void(0)" onclick="loadHistoryList(' + i + ')">' + i + '</a></li>';
                }
            }

            // 下一页
            if (data.page < totalPages) {
                html += '<li><a href="javascript:void(0)" onclick="loadHistoryList(' + (data.page + 1) + ')">&raquo;</a></li>';
            } else {
                html += '<li class="disabled"><a href="javascript:void(0)">&raquo;</a></li>';
            }

            html += '</ul>';
            html += '</nav>';
        }
    } else {
        html += '<div class="alert alert-info text-center">&#26242;&#26080;&#20215;&#26684;&#20462;&#25913;&#21382;&#21490;&#35760;&#24405;</div>';
    }

    $('#historyList').html(html);
}

// 从指定历史记录恢复
function restoreFromHistory(id) {
    layer.confirm('\u786e\u5b9a\u8981\u6062\u590d\u5230\u8be5\u5386\u53f2\u7248\u672c\u5417\uff1f\u6b64\u64cd\u4f5c\u5c06\u8986\u76d6\u5f53\u524d\u7684\u4ef7\u683c\u8bbe\u7f6e\u3002', {
        icon: 3,
        title: '\u786e\u8ba4\u6062\u590d',
        btn: ['\u786e\u5b9a', '\u53d6\u6d88']
    }, function(index) {
        layer.close(index);

        // 鏄剧ず鍔犺浇灞?        var loadingIndex = layer.load(1, {shade: [0.1,'#fff']});

        // 鍙戦€丄JAX璇锋眰
        $.ajax({
            type: "post",
            url: "ajax_user.php?act=restore_from_history",
            data: {
                id: id
            },
            dataType: "json",
            success: function(data) {
                layer.close(loadingIndex);

                if (data.code == 0) {
                    layer.alert(data.msg, {icon: 1}, function() {
                        // 关闭历史记录模态框
                        $('#priceHistoryModal').modal('hide');
                        // 刷新页面
                        window.location.reload();
                    });
                } else {
                    layer.alert(data.msg, {icon: 5});
                }
            },
            error: function() {
                layer.close(loadingIndex);
                layer.alert('\u64cd\u4f5c\u5931\u8d25\uff0c\u8bf7\u7a0d\u540e\u91cd\u8bd5', {icon: 5});
            }
        });
    });
}

$('#batchPriceSubmitBtn').on('click', batchUpdatePrice);
$('#resetPriceSubmitBtn').on('click', resetPrice);
</script>
</body>
</html>

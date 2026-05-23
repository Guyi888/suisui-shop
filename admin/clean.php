<?php
/**
 * 系统数据清理
 * 岁岁 @qqfaka
 * TG：@qqfaka
**/
include("../includes/common.php");
$title='系统数据清理';
include './head.php';
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
<style>
.clean-section {
    margin-bottom: 20px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 4px;
}
.clean-section h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
    font-size: 14px;
    font-weight: bold;
}
.clean-btn {
    margin: 5px 0;
    padding: 8px 15px;
}
.clean-form {
    margin-top: 10px;
    padding: 10px;
    background: #fff;
    border-radius: 4px;
}
.clean-form input {
    display: inline-block;
    width: 80px;
    margin: 0 5px;
}
.clean-form .btn {
    margin-left: 10px;
}
</style>
    <div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
<?php
adminpermission('super', 1);
$mod=isset($_GET['mod'])?$_GET['mod']:null;

function clean_cutoff_time($days)
{
    return date("Y-m-d H:i:s", strtotime("-" . intval($days) . " days"));
}

function clean_format_money($money)
{
    $money = trim((string)$money);
    return is_numeric($money) ? number_format((float)$money, 2, '.', '') : false;
}

function clean_exec_delete($sql)
{
    global $DB;
    $result = $DB->exec($sql);
    return $result === false ? false : intval($result);
}

function clean_sum_counts($counts)
{
    $total = 0;
    foreach ($counts as $count) {
        if ($count === false) {
            return false;
        }
        $total += intval($count);
    }
    return $total;
}

function clean_optimize_tables($tables)
{
    global $DB;
    foreach ((array)$tables as $table) {
        $DB->exec("OPTIMIZE TABLE `" . $table . "`");
    }
}

function clean_show_result($message, $count)
{
    if ($count === false) {
        showmsg($message . '失败，请检查数据库表结构或错误日志。', 4);
    }
    showmsg($message . '成功，本次删除 ' . intval($count) . ' 条记录。', 1);
}

if($mod=='cleancache'){
$CACHE->clear();
if(function_exists("opcache_reset"))@opcache_reset();
showmsg('清理系统设置缓存成功！',1);
}elseif($mod=='cleanlog'){
$count = intval($DB->getColumn("SELECT COUNT(*) FROM `pre_logs`"));
$result = $DB->exec("TRUNCATE TABLE `pre_logs`");
clean_show_result('清空社区对接日志', $result === false ? false : $count);
}elseif($mod=='cleanpay'){
$count = clean_sum_counts(array(
    clean_exec_delete("DELETE FROM `pre_pay` WHERE addtime<'".clean_cutoff_time(30)."'"),
    clean_exec_delete("DELETE FROM `pre_pay` WHERE addtime<'".date("Y-m-d H:i:s",strtotime("-3 hours"))."' and status=0"),
    clean_exec_delete("DELETE FROM `pre_cart` WHERE addtime<'".clean_cutoff_time(30)."'"),
    clean_exec_delete("DELETE FROM `pre_cart` WHERE addtime<'".date("Y-m-d H:i:s",strtotime("-12 hours"))."' and status<2")
));
clean_optimize_tables(array('pre_pay', 'pre_cart'));
clean_show_result('删除支付记录', $count);
}elseif($mod=='cleanorders'){
$count = clean_exec_delete("DELETE FROM `pre_orders` WHERE addtime<'".clean_cutoff_time(30)."'");
clean_optimize_tables('pre_orders');
clean_show_result('删除30天前订单记录', $count);
}elseif($mod=='cleanqiandao'){
$count = clean_exec_delete("DELETE FROM `pre_qiandao` WHERE time<'".clean_cutoff_time(30)."'");
clean_optimize_tables('pre_qiandao');
clean_show_result('删除30天前签到记录', $count);
}elseif($mod=='cleanwork'){
$count = clean_exec_delete("DELETE FROM `pre_workorder` WHERE addtime<'".clean_cutoff_time(30)."'");
clean_optimize_tables('pre_workorder');
clean_show_result('删除30天前工单记录', $count);
}elseif($mod=='cleanpoints'){
$count = clean_exec_delete("DELETE FROM `pre_points` WHERE addtime<'".clean_cutoff_time(7)."'");
clean_optimize_tables('pre_points');
clean_show_result('删除7天前收支明细', $count);
}elseif($mod=='cleangift'){
$count = clean_exec_delete("DELETE FROM `pre_giftlog` WHERE addtime<'".clean_cutoff_time(1)."'");
clean_optimize_tables('pre_giftlog');
clean_show_result('删除1天前中奖记录', $count);
}elseif($mod=='cleaninvite'){
$count = clean_exec_delete("DELETE FROM `pre_invitelog` WHERE date<'".clean_cutoff_time(1)."'");
clean_optimize_tables('pre_invitelog');
clean_show_result('删除1天前推广记录', $count);
}elseif($mod=='cleanpayi' && $_POST['do']=='submit'){
if(!checkRefererHost())exit();
$days = intval($_POST['days']);
$money = clean_format_money($_POST['money']);
if($days<=0 || $money===false)showmsg('请确保每项不能为空，金额必须为数字',3);
$count = clean_exec_delete("DELETE FROM `pre_pay` WHERE CAST(money AS DECIMAL(10,2))<='{$money}' and addtime<'".clean_cutoff_time($days)."'");
clean_optimize_tables('pre_pay');
clean_show_result('删除支付记录', $count);
}elseif($mod=='cleanordersi' && $_POST['do']=='submit'){
if(!checkRefererHost())exit();
$days = intval($_POST['days']);
$money = clean_format_money($_POST['money']);
if($days<=0 || $money===false)showmsg('请确保每项不能为空，金额必须为数字',3);
$count = clean_exec_delete("DELETE FROM `pre_orders` WHERE money<='{$money}' and addtime<'".clean_cutoff_time($days)."'");
clean_optimize_tables('pre_orders');
clean_show_result('删除订单记录', $count);
}elseif($mod=='cleansite' && $_POST['do']=='submit'){
if(!checkRefererHost())exit();
$days = intval($_POST['days']);
$money = clean_format_money($_POST['money']);
if($days<=0 || $money===false)showmsg('请确保每项不能为空，金额必须为数字',3);
$cutoff = clean_cutoff_time($days);
$count = clean_exec_delete("DELETE FROM `pre_site` WHERE rmb<='{$money}' and addtime<'{$cutoff}' and (lasttime<'{$cutoff}' or lasttime is null)");
clean_optimize_tables('pre_site');
clean_show_result('删除分站记录', $count);
}else{
?>
<div class="block">
<div class="block-title clearfix">
<h2>系统数据清理</h2>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="clean-section">
            <h4><i class="fa fa-database"></i> 缓存清理</h4>
            <a href="./clean.php?mod=cleancache" class="btn btn-block btn-default clean-btn">
                <i class="fa fa-refresh"></i> 清理设置缓存
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="clean-section">
            <h4><i class="fa fa-eraser"></i> 日志清理</h4>
            <a href="./clean.php?mod=cleanlog" onclick="return confirm('你确实要清空所有社区对接日志吗？');" class="btn btn-block btn-default clean-btn">
                <i class="fa fa-trash"></i> 清空社区对接日志
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="clean-section">
            <h4><i class="fa fa-credit-card"></i> 支付记录清理</h4>
            <a href="./clean.php?mod=cleanpay" onclick="return confirm('你确实要删除30天前的支付记录吗？');" class="btn btn-block btn-default clean-btn">
                <i class="fa fa-trash"></i> 删除30天前支付记录
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="clean-section">
            <h4><i class="fa fa-shopping-cart"></i> 订单记录清理</h4>
            <a href="./clean.php?mod=cleanorders" onclick="return confirm('你确实要删除30天前的订单记录吗？');" class="btn btn-block btn-default clean-btn">
                <i class="fa fa-trash"></i> 删除30天前订单记录
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="clean-section">
            <h4><i class="fa fa-money"></i> 收支明细清理</h4>
            <a href="./clean.php?mod=cleanpoints" onclick="return confirm('你确实要删除7天前收支明细吗？');" class="btn btn-block btn-default clean-btn">
                <i class="fa fa-trash"></i> 删除7天前收支明细
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="clean-section">
            <h4><i class="fa fa-gift"></i> 中奖记录清理</h4>
            <a href="./clean.php?mod=cleangift" onclick="return confirm('你确实要删除1天前的中奖记录吗？');" class="btn btn-block btn-default clean-btn">
                <i class="fa fa-trash"></i> 删除1天前中奖记录
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="clean-section">
            <h4><i class="fa fa-share-alt"></i> 推广记录清理</h4>
            <a href="./clean.php?mod=cleaninvite" onclick="return confirm('你确实要删除1天前的推广记录吗？');" class="btn btn-block btn-default clean-btn">
                <i class="fa fa-trash"></i> 删除1天前推广记录
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="clean-section">
            <h4><i class="fa fa-calendar-check-o"></i> 签到记录清理</h4>
            <a href="./clean.php?mod=cleanqiandao" onclick="return confirm('你确实要删除30天前的签到记录吗？');" class="btn btn-block btn-default clean-btn">
                <i class="fa fa-trash"></i> 删除30天前签到记录
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="clean-section">
            <h4><i class="fa fa-tasks"></i> 工单记录清理</h4>
            <a href="./clean.php?mod=cleanwork" onclick="return confirm('你确实要删除30天前的工单记录吗？');" class="btn btn-block btn-default clean-btn">
                <i class="fa fa-trash"></i> 删除30天前工单记录
            </a>
        </div>
    </div>
</div>

<div class="clean-section" style="margin-top: 20px; background: #e8e8e8;">
    <h4><i class="fa fa-cogs"></i> 自定义清理</h4>

    <div class="row">
        <div class="col-md-4">
            <form action="./clean.php?mod=cleanpayi" method="post" role="form" class="clean-form">
                <input type="hidden" name="do" value="submit"/>
                <label>支付记录</label><br/>
                <input type="text" name="days" value="" placeholder="天数" class="form-control input-sm"/> 天前
                金额不超过 <input type="text" name="money" value="" placeholder="上限金额" class="form-control input-sm"/> 元（含）
                <input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
            </form>
        </div>

        <div class="col-md-4">
            <form action="./clean.php?mod=cleanordersi" method="post" role="form" class="clean-form">
                <input type="hidden" name="do" value="submit"/>
                <label>订单记录</label><br/>
                <input type="text" name="days" value="" placeholder="天数" class="form-control input-sm"/> 天前
                金额不超过 <input type="text" name="money" value="" placeholder="上限金额" class="form-control input-sm"/> 元（含）
                <input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
            </form>
        </div>

        <div class="col-md-4">
            <form action="./clean.php?mod=cleansite" method="post" role="form" class="clean-form">
                <input type="hidden" name="do" value="submit"/>
                <label>分站记录</label><br/>
                <input type="text" name="days" value="30" placeholder="天数" class="form-control input-sm"/> 天前
                金额不超过 <input type="text" name="money" value="0" placeholder="上限金额" class="form-control input-sm"/> 元（含）
                <input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
            </form>
        </div>
    </div>
</div>

<div class="panel-footer">
    <span class="fa fa-info-circle"></span>
    定期清理数据有助于提升网站访问速度
</div>
</div>
<?php }?>
 </div>
</div>
</html>

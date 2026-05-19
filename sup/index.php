<?php
/*
岁岁云商城
维护：岁岁 @qqfaka
*/
$is_defend=true;
require '../includes/common.php';
if($islogin3==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

if($_GET['mod']=='faka'){
	exit("<script language='javascript'>window.location.href='../?mod=faka&&id={$_GET['id']}&skey={$_GET['skey']}';</script>");
}
$title = '供货商管理中心';
include 'head.php';

$scriptpath = str_replace('\\','/',$_SERVER['SCRIPT_NAME']);
$scriptpath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
$scriptpath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
?>
<link rel="stylesheet" href="<?php echo $cdnpublic?>toastr.js/latest/css/toastr.min.css">
<style>
/* 苹果UI风格样式 */
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    background-color: #f2f2f7;
    color: #1c1c1e;
}

/* 卡片样式 */
.apple-card {
    background-color: #ffffff;
    border-radius: 13px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e5ea;
    transition: all 0.2s ease;
    overflow: hidden;
}

.apple-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* 欢迎区域 */
.welcome-section {
    background-color: #ffffff;
    border-radius: 13px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e5ea;
    padding: 24px;
    text-align: center;
    margin-bottom: 24px;
}

.welcome-section h2 {
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 600;
    color: #1c1c1e;
}

.welcome-section p {
    margin: 0;
    color: #8e8e93;
    font-size: 14px;
}

/* 安全提醒 */
.alert-modern {
    border-radius: 13px;
    border: 1px solid #e5e5ea;
    padding: 16px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    background-color: #ffffff;
}

/* 统计卡片 */
.stats-card {
    background-color: #ffffff;
    color: #1c1c1e;
    border-radius: 13px;
    padding: 16px;
    text-align: center;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e5ea;
}

.stats-card i {
    font-size: 24px;
    color: #007aff;
    margin-bottom: 12px;
}

.stats-card h3 {
    font-size: 24px;
    margin: 0 0 4px 0;
    font-weight: 600;
    color: #1c1c1e;
}

.stats-card p {
    margin: 0;
    color: #8e8e93;
    font-size: 14px;
}

/* 用户信息卡片 */
.user-info-card {
    background-color: #ffffff;
    border-radius: 13px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e5ea;
    overflow: hidden;
}

.user-info-card .card-header {
    background-color: #f2f2f7;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e5ea;
}

.user-info-card .avatar-large {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    object-fit: cover;
}

.user-info-card h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1c1c1e;
}

.user-info-card .card-subtitle {
    color: #8e8e93;
    font-size: 14px;
    margin: 2px 0 0 0;
}

/* 快捷操作 */
.action-card {
    background-color: #ffffff;
    border-radius: 13px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e5ea;
    overflow: hidden;
}

.action-card .card-header {
    background-color: #f2f2f7;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e5ea;
}

.action-card .card-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1c1c1e;
    display: flex;
    align-items: center;
}

.action-card .card-title i {
    margin-right: 8px;
    color: #007aff;
}

/* 操作按钮 */
.apple-action-btn {
    background-color: #f2f2f7;
    border-radius: 10px;
    border: none;
    color: #1c1c1e;
    font-weight: 500;
    font-size: 14px;
    padding: 12px 16px;
    margin: 4px;
    min-height: 70px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    z-index: 999999;
    position: relative;
}

.apple-action-btn:hover {
    background-color: #e5e5ea;
    color: #1c1c1e;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.apple-action-btn i {
    font-size: 20px;
    margin-bottom: 6px;
    color: #007aff;
}

/* 主按钮 */
.apple-btn-primary {
    background-color: #007aff;
    border-radius: 10px;
    border: none;
    color: white;
    font-weight: 500;
    font-size: 14px;
    padding: 8px 16px;
    transition: all 0.2s ease;
    z-index: 999999;
    position: relative;
}

.apple-btn-primary:hover {
    background-color: #0056b3;
    color: white;
}

/* 辅助按钮 */
.apple-btn-secondary {
    background-color: #f2f2f7;
    border-radius: 10px;
    border: none;
    color: #1c1c1e;
    font-weight: 500;
    font-size: 14px;
    padding: 8px 16px;
    transition: all 0.2s ease;
    z-index: 999999;
    position: relative;
}

.apple-btn-secondary:hover {
    background-color: #e5e5ea;
    color: #1c1c1e;
}

/* 白色按钮 */
.apple-btn-white {
    background-color: white;
    border-radius: 10px;
    border: 1px solid #007aff;
    color: #007aff;
    font-weight: 500;
    font-size: 14px;
    padding: 8px 16px;
    transition: all 0.2s ease;
    z-index: 999999;
    position: relative;
}

.apple-btn-white:hover {
    background-color: #f2f2f7;
    color: #007aff;
}

/* 公告卡片 */
.announcement-card {
    background-color: #ffffff;
    border-radius: 13px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e5ea;
    overflow: hidden;
}

.announcement-card .card-header {
    background-color: #f2f2f7;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e5ea;
}

.announcement-card .card-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1c1c1e;
    display: flex;
    align-items: center;
}

.announcement-card .card-title i {
    margin-right: 8px;
    color: #007aff;
}

.announcement-card .card-body {
    padding: 16px 20px;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .apple-action-btn {
        min-height: 60px;
        padding: 10px 12px;
    }

    .apple-action-btn i {
        font-size: 18px;
        margin-bottom: 4px;
    }

    .stats-card h3 {
        font-size: 20px;
    }

    .welcome-section h2 {
        font-size: 20px;
    }
}
</style>
</style>

<div class="wrapper">
    <!-- 欢迎区域 -->
    <div class="welcome-section">
        <h2>欢迎回来，<?php echo $nickname?>！</h2>
        <p>今天是 <?php echo date('Y年m月d日'); ?>，祝您生意兴隆！</p>
    </div>

    <!-- 安全提醒 -->
    <div class="col-sm-12">
    <?php
    if($suprow['rmb']>4){
    if(strlen($suprow['pwd'])<6 || is_numeric($suprow['pwd']) && strlen($suprow['pwd'])<=10 || $suprow['pwd']===$suprow['qq']){
        echo '<div class="alert alert-danger alert-modern"><i class="fa fa-exclamation-triangle"></i> <strong>安全提醒：</strong>你的密码过于简单，请不要使用较短的纯数字或自己的QQ号当做密码，以免造成资金损失！ <a href="uset.php?mod=user" class="apple-btn-primary">立即修改</a></div>';
    }elseif($suprow['user']===$suprow['pwd']){
        echo '<div class="alert alert-danger alert-modern"><i class="fa fa-exclamation-triangle"></i> <strong>安全提醒：</strong>你的用户名与密码相同，极易被黑客破解，请及时修改密码 <a href="uset.php?mod=user" class="apple-btn-primary">立即修改</a></div>';
    }
    }
    ?>
    </div>

    <!-- 统计卡片 -->
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="stats-card">
            <i class="fa fa-money"></i>
            <h3>¥<?php echo $suprow['rmb']?></h3>
            <p>账户余额</p>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="stats-card">
            <i class="fa fa-user-o"></i>
            <h3>ID<?php echo $suprow['sid']?></h3>
            <p>用户ID</p>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="stats-card">
            <i class="fa fa-line-chart"></i>
            <h3 id="income_today">¥0</h3>
            <p>今日收益</p>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="stats-card">
            <i class="fa fa-clock-o"></i>
            <h3><?php echo date('H:i'); ?></h3>
            <p>当前时间</p>
        </div>
    </div>

    <!-- 用户信息卡片 -->
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="user-info-card">
            <div class="card-header">
                <div class="row">
                    <div class="col-xs-4">
                        <img src="<?php echo $faceimg ?>" alt="Avatar" class="avatar-large">
                    </div>
                    <div class="col-xs-8">
                        <h4><?php echo $nickname?></h4>
                        <p class="card-subtitle">供货商</p>
                        <div style="margin-top: 15px;">
                            <a href="recharge.php" class="apple-btn-white" style="margin-right: 10px;"><i class="fa fa-plus"></i> 充值</a>
                            <a href="tixian.php" class="apple-btn-secondary"><i class="fa fa-download"></i> 提现</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 快捷操作 -->
    <div class="col-lg-8 col-md-6 col-sm-12">
        <div class="action-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-rocket"></i> 快捷操作</h3>
            </div>
            <div class="card-body" style="padding: 20px;">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                        <a href="shoplist.php" class="apple-action-btn btn-block">
                            <i class="fa fa-shopping-cart"></i>
                            <span>商品管理</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                        <a href="fakalist.php" class="apple-action-btn btn-block">
                            <i class="fa fa-credit-card"></i>
                            <span>卡密管理</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                        <a href="record.php" class="apple-action-btn btn-block">
                            <i class="fa fa-list-alt"></i>
                            <span>收支明细</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                        <a href="uset.php?mod=user" class="apple-action-btn btn-block">
                            <i class="fa fa-cog"></i>
                            <span>系统设置</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 公告区域 -->
    <div class="col-lg-12">
        <div class="announcement-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-bullhorn"></i> 系统公告</h3>
            </div>
            <div class="card-body">
                <?php if(!empty($conf['sup_notice'])){ ?>
                    <div style="line-height: 1.6; color: #8e8e93; font-size: 14px;">
                        <?php echo $conf['sup_notice']?>
                    </div>
                <?php }else{ ?>
                    <div style="text-align: center; color: #8e8e93; padding: 20px;">
                        <i class="fa fa-info-circle fa-3x" style="margin-bottom: 15px; color: #e5e5ea;"></i>
                        <p>暂无公告信息</p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php include './foot.php';?>
<script src="<?php echo $cdnpublic?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script>
// 获取今日收益
$.ajax({
    type : "GET",
    url : "ajax_user.php?act=msg",
    dataType : 'json',
    success : function(data) {
        if(data.code=='0'){
            $("#income_today").html(data.income_today+'元');
        }
    }
});

// 实时更新时间
setInterval(function() {
    var now = new Date();
    var timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
    $('.stats-card:last h3').text(timeStr);
}, 1000);

if(window.location.hash=='#chongzhi'){
    $("#userjs").modal('show');
}
</script>
</body>
</html>

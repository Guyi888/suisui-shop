<?php
include("../includes/common.php");
$title = '分站任务';
include './head.php';
if($islogin2==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
if($userrow['power'] < 1) exit("<script language='javascript'>window.location.href='./index.php';</script>");

$tasks = $DB->getAll("SELECT * FROM pre_sitetask WHERE active=1 ORDER BY sort ASC, id ASC");
$task_status = array();
if(function_exists('q8_sitetask_status')) {
    foreach($tasks as $task_index => $task_row) {
        $task_status[intval($task_row['id'])] = q8_sitetask_status($task_row, $userrow);
    }
}
?>
<link rel="stylesheet" href="./public/css/blue_theme.css">
<style>
.st-banner{
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    border-radius:12px;padding:28px 24px;margin-bottom:20px;
    color:#fff;position:relative;overflow:hidden;
}
.st-banner::before{
    content:'';position:absolute;right:-50px;top:-50px;
    width:200px;height:200px;border-radius:50%;
    background:rgba(255,255,255,.07);
}
.st-banner::after{
    content:'';position:absolute;right:40px;bottom:-30px;
    width:120px;height:120px;border-radius:50%;
    background:rgba(255,255,255,.05);
}
.st-banner-tag{
    display:inline-block;background:rgba(255,255,255,.22);
    border-radius:20px;padding:3px 14px;font-size:12px;margin-bottom:10px;
}
.st-banner h2{font-size:22px;font-weight:700;margin:0 0 6px;color:#fff;}
.st-banner p{font-size:13px;opacity:1;margin:0;color:#fff;font-weight:700;}
.st-grid{
    display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;
}
@media(max-width:600px){.st-grid{grid-template-columns:1fr;}}
.st-card{
    background:#fff;border-radius:12px;padding:18px 16px;
    display:flex;align-items:flex-start;gap:14px;
    box-shadow:0 2px 10px rgba(0,0,0,.07);
    transition:box-shadow .2s,transform .2s;
    border:1px solid #f0f0f0;
}
.st-card:hover{box-shadow:0 6px 20px rgba(0,0,0,.12);transform:translateY(-2px);}
.st-icon{
    width:48px;height:48px;border-radius:12px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;
    font-size:20px;color:#fff;
}
.st-card-body{flex:1;min-width:0;}
.st-badge{
    font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;
    display:inline-block;margin-bottom:6px;color:#fff;
}
.st-card-title{font-size:14px;font-weight:700;color:#1a1a2e;margin-bottom:4px;}
.st-card-desc{font-size:12px;color:#999;line-height:1.6;}
.st-reward{font-size:12px;color:#e67e22;font-weight:700;margin-top:6px;}
.st-progress{height:7px;border-radius:999px;background:#eef2f7;overflow:hidden;margin-top:10px;}
.st-progress span{display:block;height:100%;border-radius:999px;background:linear-gradient(90deg,#22c55e,#14b8a6);}
.st-status{font-size:12px;color:#667085;margin-top:7px;display:flex;justify-content:space-between;gap:8px;}
.st-status b{color:#16a34a;}
.st-status .is-pending{color:#f59e0b;}
.st-status .is-soldout{color:#ef4444;}
.st-empty{text-align:center;color:#ccc;padding:50px 0;font-size:14px;}
.st-footer-note{color:#bbb;font-size:12px;text-align:center;margin-top:4px;}
</style>
<div class="wrapper">
<div class="col-sm-12">
<div class="panel panel-default">
    <div class="panel-heading font-bold">
        <i class="fa fa-tasks"></i>&nbsp;&#20998;&#31449;&#20219;&#21153;
    </div>
    <div class="panel-body">

        <div class="st-banner">
            <div class="st-banner-tag">&#128226; &#24179;&#21488;&#20844;&#21578;</div>
            <h2>&#11088; &#24179;&#21488;&#20998;&#31449;&#20219;&#21153;&#25919;&#31574;</h2>
            <p>&#23436;&#25104;&#20219;&#21153;&#21363;&#21487;&#33719;&#24471;&#22870;&#21169;&#65281;&#31283;&#23450; &middot; &#38271;&#20037; &middot; &#35802;&#20449;&#65292;&#27426;&#36814;&#38271;&#26399;&#21512;&#20316;&#12290;</p>
        </div>

        <?php if(empty($tasks)){ ?>
        <div class="st-empty">
            <i class="fa fa-inbox fa-3x" style="display:block;margin-bottom:10px;"></i>
            &#26242;&#26080;&#20219;&#21153;&#65292;&#35831;&#31561;&#24453;&#24179;&#21488;&#21457;&#24067;
        </div>
        <?php }else{ ?>
        <div class="st-grid">
        <?php
        $icon_map = [
            0 => ['fa fa-bullhorn',        '#e67e22'],
            1 => ['fa fa-credit-card',     '#3498db'],
            2 => ['fa fa-shopping-cart',   '#27ae60'],
            3 => ['fa fa-bar-chart',       '#9b59b6'],
            4 => ['fa fa-key',             '#1abc9c'],
            5 => ['fa fa-calendar-check-o','#e74c3c'],
        ];
        $i = 1;
        foreach($tasks as $t){
            $type  = intval($t['task']);
            $icon  = isset($icon_map[$type]) ? $icon_map[$type][0] : 'fa fa-star';
            $color = isset($icon_map[$type]) ? $icon_map[$type][1] : '#95a5a6';
            $name  = htmlspecialchars((string)$t['name'],  ENT_QUOTES, 'UTF-8');
            $desc  = htmlspecialchars((string)($t['desc'] ?: ''), ENT_QUOTES, 'UTF-8');
            $money = htmlspecialchars((string)$t['money'], ENT_QUOTES, 'UTF-8');
            $status = isset($task_status[intval($t['id'])]) ? $task_status[intval($t['id'])] : array('progress'=>0,'target'=>floatval($t['value']),'done'=>false,'claimed'=>false,'claim_text'=>'');
            $target = max(0.01, floatval($status['target']));
            $progress = floatval($status['progress']);
            $percent = min(100, round($progress / $target * 100, 1));
            $stateText = $status['claimed'] ? '&#24050;&#33258;&#21160;&#21457;&#25918;' : ($status['claim_text'] === 'soldout' ? '&#22870;&#21169;&#24050;&#21457;&#23436;' : ($status['done'] ? '&#36798;&#26631;&#24453;&#21457;&#25918;' : '&#26410;&#36798;&#26631;'));
            $stateClass = $status['claimed'] ? '' : ($status['claim_text'] === 'soldout' ? 'is-soldout' : ($status['done'] ? 'is-pending' : ''));
        ?>
        <div class="st-card">
            <div class="st-icon" style="background:<?php echo $color?>">
                <i class="<?php echo $icon?>"></i>
            </div>
            <div class="st-card-body">
                <span class="st-badge" style="background:<?php echo $color?>">&#20219;&#21153;<?php echo $i?></span>
                <div class="st-card-title"><?php echo $name?></div>
                <?php if($desc){ ?><div class="st-card-desc"><?php echo $desc?></div><?php }?>
                <div class="st-reward">&#9733; &#22870;&#21161; &#165;<?php echo $money?></div>
                <div class="st-progress"><span style="width:<?php echo $percent;?>%"></span></div>
                <div class="st-status">
                    <span><?php echo round($progress, 2);?> / <?php echo htmlspecialchars((string)$status['target'], ENT_QUOTES, 'UTF-8');?></span>
                    <b class="<?php echo $stateClass;?>"><?php echo $stateText;?></b>
                </div>
            </div>
        </div>
        <?php $i++; } ?>
        </div>
        <?php }?>

        <p class="st-footer-note">
            &#20197;&#19978;&#20219;&#21153;&#26368;&#32456;&#35299;&#37322;&#26435;&#24402;&#26412;&#24179;&#21488;&#25152;&#26377;&#65292;&#20855;&#20307;&#22870;&#21161;&#20197;&#23454;&#38469;&#32467;&#31639;&#20026;&#20934;&#12290;
        </p>
    </div>
</div>
</div>
</div>
<?php include './foot.php'; ?>

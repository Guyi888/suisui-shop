<?php
include("../includes/common.php");
if($islogin2!=1) exit("<script language='javascript'>window.location.href='./login.php';</script>");
if($userrow['power'] < 1) exit("<script language='javascript'>window.location.href='./index.php';</script>");

// AJAX 解锁
if(isset($_POST['act']) && $_POST['act'] == 'unlock'){
    header('Content-Type: application/json');
    $pwd     = trim($_POST['pwd'] ?? '');
    $correct = (string)$DB->getColumn("SELECT v FROM pre_config WHERE k='agenttool_pwd'");
    if($correct !== '' && $pwd === $correct){
        $_SESSION['agenttool_unlocked'] = 1;
        echo json_encode(['code'=>1]);
    } else {
        echo json_encode(['code'=>0,'msg'=>'密码错误，请联系站长获取']);
    }
    exit;
}

$title = '代理工具';
include './head.php';

$tools      = $DB->getAll("SELECT * FROM pre_agenttool WHERE active=1 ORDER BY sort ASC, id ASC");
$unlocked   = !empty($_SESSION['agenttool_unlocked']);
$has_locked = false;
foreach($tools as $t){ if(!$t['is_free']){ $has_locked = true; break; } }
?>
<link rel="stylesheet" href="./public/css/blue_theme.css">
<style>
.at-banner{
    background:linear-gradient(135deg,#11998e 0%,#38ef7d 100%);
    border-radius:12px;padding:28px 24px;margin-bottom:20px;
    color:#fff;position:relative;overflow:hidden;
}
.at-banner::before{
    content:'';position:absolute;right:-50px;top:-50px;
    width:200px;height:200px;border-radius:50%;
    background:rgba(255,255,255,.07);
}
.at-banner-tag{
    display:inline-block;background:rgba(255,255,255,.22);
    border-radius:20px;padding:3px 14px;font-size:12px;margin-bottom:10px;
}
.at-banner h2{font-size:22px;font-weight:700;margin:0 0 6px;color:#fff;}
.at-banner p{font-size:13px;margin:0;color:#fff;font-weight:700;}
.at-unlock-bar{
    background:#fff7e6;border:1px solid #ffd591;border-radius:10px;
    padding:12px 16px;margin-bottom:16px;
    display:flex;align-items:center;gap:10px;flex-wrap:wrap;
}
.at-unlock-bar span{flex:1;font-size:13px;color:#d46b08;}
.at-grid{
    display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;
}
@media(max-width:600px){.at-grid{grid-template-columns:1fr;}}
.at-card{
    background:#fff;border-radius:12px;padding:18px 16px;
    box-shadow:0 2px 10px rgba(0,0,0,.07);
    border:1px solid #f0f0f0;
    transition:box-shadow .2s,transform .2s;
}
.at-card:hover{box-shadow:0 6px 20px rgba(0,0,0,.12);transform:translateY(-2px);}
.at-card-top{display:flex;align-items:flex-start;gap:14px;margin-bottom:12px;}
.at-icon{
    width:48px;height:48px;border-radius:12px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;
    font-size:20px;color:#fff;
}
.at-card-body{flex:1;min-width:0;}
.at-badge{
    font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;
    display:inline-block;margin-bottom:6px;color:#fff;
}
.at-card-title{font-size:15px;font-weight:700;color:#1a1a2e;margin-bottom:3px;}
.at-version{font-size:11px;color:#bbb;margin-bottom:4px;}
.at-card-desc{font-size:12px;color:#999;line-height:1.6;}
.at-card-footer{
    border-top:1px solid #f5f5f5;padding-top:10px;margin-top:4px;
    display:flex;align-items:center;justify-content:space-between;
}
.at-dl-btn{
    display:inline-flex;align-items:center;gap:6px;
    background:linear-gradient(135deg,#11998e,#38ef7d);
    color:#fff;border-radius:8px;padding:7px 16px;
    font-size:13px;font-weight:600;text-decoration:none;
    border:none;cursor:pointer;transition:opacity .2s;
}
.at-dl-btn:hover{opacity:.85;color:#fff;text-decoration:none;}
.at-lock-wrap{display:flex;align-items:center;gap:8px;}
.at-lock-icon{color:#ccc;font-size:18px;}
.at-lock-tip{font-size:12px;color:#bbb;}
.at-unlock-btn{
    display:inline-flex;align-items:center;gap:5px;
    background:#fa8c16;color:#fff;border-radius:8px;
    padding:6px 14px;font-size:12px;font-weight:600;
    border:none;cursor:pointer;transition:opacity .2s;
}
.at-unlock-btn:hover{opacity:.85;}
.at-empty{text-align:center;color:#ccc;padding:50px 0;font-size:14px;}
.at-footer-note{color:#bbb;font-size:12px;text-align:center;margin-top:4px;}
.at-modal-mask{
    display:none;position:fixed;top:0;left:0;width:100%;height:100%;
    background:rgba(0,0,0,.45);z-index:99999;
}
.at-modal-mask.show{display:block;}
.at-modal{
    background:#fff;border-radius:16px;padding:28px 24px;width:320px;
    box-shadow:0 8px 40px rgba(0,0,0,.18);
    position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
}
.at-modal h4{font-size:16px;font-weight:700;margin:0 0 6px;}
.at-modal p{font-size:13px;color:#999;margin:0 0 16px;}
.at-modal input{
    width:100%;border:1.5px solid #e0e0e0;border-radius:8px;
    padding:9px 12px;font-size:14px;outline:none;box-sizing:border-box;
    margin-bottom:12px;transition:border-color .2s;
}
.at-modal input:focus{border-color:#11998e;}
.at-modal-btns{display:flex;gap:10px;}
.at-modal-btns button{flex:1;padding:9px;border-radius:8px;font-size:14px;font-weight:600;border:none;cursor:pointer;}
.at-modal-cancel{background:#f5f5f5;color:#666;}
.at-modal-confirm{background:linear-gradient(135deg,#11998e,#38ef7d);color:#fff;}
.at-modal-err{font-size:12px;color:#e74c3c;margin-bottom:8px;display:none;}
</style>

<div class="wrapper">
<div class="col-sm-12">
<div class="panel panel-default">
    <div class="panel-heading font-bold">
        <i class="fa fa-wrench"></i>&nbsp;&#20195;&#29702;&#24037;&#20855;
    </div>
    <div class="panel-body">

        <div class="at-banner">
            <div class="at-banner-tag">&#128295; &#20195;&#29702;&#19987;&#23646;</div>
            <h2>&#127959; &#20195;&#29702;&#24037;&#20855;&#20013;&#24515;</h2>
            <p>&#25552;&#20379;&#19987;&#19994;&#36816;&#33829;&#24037;&#20855;&#65292;&#21161;&#20320;&#25320;&#23458;&#21464;&#24471;&#26356;&#31616;&#21333;&#12290;</p>
        </div>

        <?php if($has_locked && !$unlocked){ ?>
        <div class="at-unlock-bar">
            <span><i class="fa fa-lock"></i>&nbsp;&#37096;&#20998;&#24037;&#20855;&#38656;&#35201;&#23494;&#30721;&#35299;&#38145;&#65292;&#35831;&#32852;&#31995;&#31449;&#38271;&#33719;&#21462;</span>
            <button class="at-unlock-btn" onclick="showUnlock()">
                <i class="fa fa-key"></i> 输入密码
            </button>
        </div>
        <?php } ?>
        <?php if($unlocked){ ?>
        <div class="alert alert-success" style="border-radius:10px;font-size:13px;padding:10px 16px;margin-bottom:16px;">
            <i class="fa fa-check-circle"></i>&nbsp;&#24037;&#20855;&#24211;&#24050;&#35299;&#38145;&#65292;&#25152;&#26377;&#24037;&#20855;&#21487;&#27491;&#24120;&#19979;&#36733;
        </div>
        <?php } ?>

        <?php if(empty($tools)){ ?>
        <div class="at-empty">
            <i class="fa fa-inbox fa-3x" style="display:block;margin-bottom:10px;"></i>
            暂无工具，等待维护发布
        </div>
        <?php }else{ ?>
        <div class="at-grid">
        <?php foreach($tools as $t):
            $locked = !$t['is_free'] && !$unlocked;
            $name   = htmlspecialchars((string)$t['name'],     ENT_QUOTES, 'UTF-8');
            $desc   = htmlspecialchars((string)($t['desc']??''),  ENT_QUOTES, 'UTF-8');
            $ver    = htmlspecialchars((string)($t['version']??''),ENT_QUOTES, 'UTF-8');
            $url    = htmlspecialchars((string)($t['download_url']??''),ENT_QUOTES,'UTF-8');
            $icon   = htmlspecialchars((string)($t['icon']??'fa fa-download'),ENT_QUOTES,'UTF-8');
            $color  = htmlspecialchars((string)($t['color']??'#3498db'),ENT_QUOTES,'UTF-8');
        ?>
        <div class="at-card">
            <div class="at-card-top">
                <div class="at-icon" style="background:<?php echo $color?>">
                    <i class="<?php echo $icon?>"></i>
                </div>
                <div class="at-card-body">
                    <span class="at-badge" style="background:<?php echo $color?>"><?php echo $t['is_free']?'免费':($locked?'密码':'免费')?></span>
                    <div class="at-card-title"><?php echo $name?></div>
                    <?php if($ver){ ?><div class="at-version">v<?php echo $ver?></div><?php }?>
                    <?php if($desc){ ?><div class="at-card-desc"><?php echo $desc?></div><?php }?>
                </div>
            </div>
            <div class="at-card-footer">
                <?php if($locked){ ?>
                <div class="at-lock-wrap">
                    <i class="fa fa-lock at-lock-icon"></i>
                    <span class="at-lock-tip">&#32852;&#31995;&#31449;&#38271;&#33719;&#21462;&#23494;&#30721;</span>
                </div>
                <button class="at-unlock-btn" onclick="showUnlock()">
                    <i class="fa fa-key"></i> &#35299;&#38145;
                </button>
                <?php }else{ ?>
                <span style="font-size:12px;color:#bbb;"><i class="fa fa-clock-o"></i> <?php echo date('Y-m-d', strtotime($t['addtime']))?></span>
                <a href="<?php echo $url?>" class="at-dl-btn" target="_blank" rel="noopener">
                    <i class="fa fa-download"></i> &#31435;&#21363;&#19979;&#36733;
                </a>
                <?php }?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php }?>

        <p class="at-footer-note">工具持续更新，使用前请留意及时关注最新版本。</p>
    </div>
</div>
</div>
</div>

<!-- 解锁弹窗 -->
<div class="at-modal-mask" id="unlockMask">
    <div class="at-modal">
        <h4><i class="fa fa-key"></i> 输入解锁密码</h4>
        <p>请联系站长获取密码，输入后即可查看所有付费工具</p>
        <div class="at-modal-err" id="unlockErr"></div>
        <input type="password" id="unlockPwd" placeholder="&#35831;&#36755;&#20837;&#23494;&#30721;" onkeydown="if(event.key==='Enter')doUnlock()">
        <div class="at-modal-btns">
            <button class="at-modal-cancel" onclick="hideUnlock()">&#21462;&#28040;</button>
            <button class="at-modal-confirm" onclick="doUnlock()">&#30830;&#35748;&#35299;&#38145;</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){ document.body.appendChild(document.getElementById('unlockMask')); });
function showUnlock(){ document.getElementById('unlockMask').classList.add('show'); setTimeout(function(){ document.getElementById('unlockPwd').focus(); },100); }
function hideUnlock(){ document.getElementById('unlockMask').classList.remove('show'); }
function doUnlock(){
    var pwd = document.getElementById('unlockPwd').value.trim();
    if(!pwd){ showErr('请输入密码'); return; }
    $.post('', {act:'unlock', pwd:pwd}, function(r){
        if(r.code==1){ location.reload(); }
        else { showErr(r.msg||'密码错误'); }
    }, 'json').fail(function(){ showErr('网络错误，请重试'); });
}
function showErr(msg){ var e=document.getElementById('unlockErr'); e.textContent=msg; e.style.display='block'; }
document.getElementById('unlockMask').addEventListener('click', function(e){ if(e.target===this) hideUnlock(); });
</script>
<?php include './foot.php'; ?>

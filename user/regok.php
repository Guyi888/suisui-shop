<?php
include("../includes/common.php");
$title = 'Site Open Success';
include './head2.php';

if(isset($_GET['orderid'])){
    $orderid = daddslashes($_GET['orderid']);
    $row=$DB->getRow("SELECT * FROM pre_pay WHERE trade_no='{$orderid}' LIMIT 1");
    if(!$row || $row['status']==0 || $row['tid']!=-2)showmsg('&#35746;&#21333;&#19981;&#23384;&#22312;&#25110;&#26410;&#23436;&#25104;&#25903;&#20184;&#65281;',3);
    if(!$cookiesid || $row['userid']!=$cookiesid)showmsg('&#20165;&#38480;&#26597;&#30475;&#33258;&#24049;&#24320;&#36890;&#30340;&#20998;&#31449;&#20449;&#24687;',3);
    $input=explode('|',$row['input']);
    $type = $input[0];
    if($type == 'update'){
        $zid = intval($input[1]);
        $row=$DB->getRow("SELECT * FROM pre_site WHERE zid='{$zid}' LIMIT 1");
        $kind = intval($row['power']);
        $domain = $row['domain'];
        $user = $row['user'];
        $pwd = $row['pwd'];
        $name = $row['sitename'];
        $qq = $row['qq'];
        $endtime = $row['endtime'];
    }else{
        $kind = intval($input[1]);
        $domain = daddslashes($input[2]);
        $user = daddslashes($input[3]);
        $pwd = daddslashes($input[4]);
        $name = daddslashes($input[5]);
        $qq = daddslashes($input[6]);
        $endtime = daddslashes($input[7]);
    }
    $sitepath = str_replace('/user','',$sitepath);
    $url = 'http://'.$domain.$sitepath.'/';
}elseif(isset($_GET['zid'])){
    $zid = intval($_GET['zid']);
    $row=$DB->getRow("SELECT * FROM pre_site WHERE zid='{$zid}' LIMIT 1");
    if(!$row || !$_SESSION['newzid'] || $_SESSION['newzid']!=$zid)showmsg('&#20320;&#25152;&#24320;&#36890;&#30340;&#20998;&#31449;&#20449;&#24687;&#19981;&#23384;&#22312;&#65281;',3);
    $kind = intval($row['power']);
    $domain = $row['domain'];
    $user = $row['user'];
    $pwd = $row['pwd'];
    $name = $row['sitename'];
    $qq = $row['qq'];
    $endtime = $row['endtime'];
    $sitepath = str_replace('/user','',$sitepath);
    $url = 'http://'.$domain.$sitepath.'/';
}else{
    showmsg('&#32570;&#23569;&#21442;&#25968;',4);
}
$admin_url = $url.'user/';
$kind_name = $kind==2 ? '&#19987;&#19994;&#29256;' : '&#26222;&#21450;&#29256;';
function q8_h($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
?>
<style>
html,body{min-height:100%;}
body{background:#eef5ff url("../template/XHY-01/bg-fallback.jpg") center center / cover fixed no-repeat!important;}
body:before{content:"";position:fixed;inset:0;z-index:-1;background:rgba(255,255,255,.18);backdrop-filter:blur(4px);}
body>.full-bg{display:none!important;}
.q8-regok-page{width:min(860px,calc(100% - 28px));margin:38px auto;}
.q8-regok-card{overflow:hidden;border:1px solid rgba(73,132,230,.18);border-radius:14px;background:#fff;box-shadow:0 18px 45px rgba(31,78,142,.16);}
.q8-regok-hero{position:relative;padding:26px 28px;background:linear-gradient(135deg,#1d7cf2 0%,#36b6ff 52%,#24c7a8 100%);color:#fff;}
.q8-regok-hero:after{content:"";position:absolute;right:-76px;top:-90px;width:230px;height:230px;border:38px solid rgba(255,255,255,.16);border-radius:50%;}
.q8-regok-hero-inner{position:relative;z-index:1;display:flex;align-items:center;gap:16px;}
.q8-regok-icon{display:flex;align-items:center;justify-content:center;flex:0 0 62px;width:62px;height:62px;border-radius:50%;background:#fff;color:#1979e8;font-size:34px;font-weight:900;box-shadow:0 10px 24px rgba(0,0,0,.16);}
.q8-regok-title{margin:0 0 6px;color:#fff;font-size:25px;font-weight:900;line-height:1.2;}
.q8-regok-subtitle{margin:0;color:rgba(255,255,255,.93);line-height:1.7;}
.q8-regok-body{padding:24px 26px 26px;background:#fff;}
.q8-regok-tip{display:flex;align-items:flex-start;gap:10px;margin-bottom:18px;padding:13px 14px;border:1px solid #dbeafe;border-radius:10px;background:#eef6ff;color:#175cd3;line-height:1.7;}
.q8-regok-tip-mark{display:inline-flex;align-items:center;justify-content:center;flex:0 0 18px;width:18px;height:18px;margin-top:2px;border-radius:50%;background:#1677ff;color:#fff;font-size:12px;font-weight:900;}
.q8-regok-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;}
.q8-regok-item{min-width:0;padding:14px;border:1px solid #e3edf9;border-radius:10px;background:linear-gradient(180deg,#fff 0%,#fbfdff 100%);}
.q8-regok-label{margin-bottom:8px;color:#526780;font-size:13px;font-weight:800;}
.q8-regok-value{display:flex;align-items:center;justify-content:space-between;gap:10px;min-height:32px;color:#14233b;font-size:15px;font-weight:800;word-break:break-all;}
.q8-regok-value a{color:#1769c7;text-decoration:none;}
.q8-regok-copy{flex:0 0 auto;border:0;border-radius:999px;padding:6px 10px;background:#eef6ff;color:#1769c7;font-size:12px;font-weight:800;}
.q8-regok-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:20px;}
.q8-regok-action{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:10px 18px;border-radius:999px;font-weight:900;text-decoration:none!important;}
.q8-regok-primary{background:linear-gradient(135deg,#1677ff,#38a3ff);color:#fff!important;box-shadow:0 8px 18px rgba(22,119,255,.2);}
.q8-regok-secondary{background:#eef6ff;color:#1769c7!important;}
.q8-regok-footer{margin-top:16px;color:#667085;font-size:13px;line-height:1.7;}
@media(max-width:640px){.q8-regok-page{width:calc(100% - 20px);margin:14px auto}.q8-regok-hero{padding:22px 18px}.q8-regok-hero-inner{align-items:flex-start}.q8-regok-icon{width:52px;height:52px;flex-basis:52px;font-size:28px}.q8-regok-title{font-size:21px}.q8-regok-body{padding:18px}.q8-regok-grid{grid-template-columns:1fr}.q8-regok-actions{display:grid;grid-template-columns:1fr}}
</style>
<div class="q8-regok-page">
    <div class="q8-regok-card">
        <div class="q8-regok-hero">
            <div class="q8-regok-hero-inner">
                <div class="q8-regok-icon">&#10003;</div>
                <div>
                    <h2 class="q8-regok-title">&#20998;&#31449;&#24320;&#36890;&#25104;&#21151;</h2>
                    <p class="q8-regok-subtitle">&#24685;&#21916;&#20320;&#65292;&#20998;&#31449;&#24050;&#32463;&#20934;&#22791;&#22909;&#12290;&#35831;&#22949;&#21892;&#20445;&#23384;&#20197;&#19979;&#31649;&#29702;&#20449;&#24687;&#12290;</p>
                </div>
            </div>
        </div>
        <div class="q8-regok-body">
            <div class="q8-regok-tip"><span class="q8-regok-tip-mark">i</span><div>&#39318;&#27425;&#30331;&#24405;&#21518;&#24314;&#35758;&#31435;&#21363;&#20462;&#25913;&#23494;&#30721;&#65292;&#24182;&#23436;&#21892;&#31449;&#28857;&#21517;&#31216;&#12289;&#25910;&#27454;&#21644;&#32852;&#31995;&#26041;&#24335;&#31561;&#20449;&#24687;&#12290;</div></div>
            <div class="q8-regok-grid">
                <div class="q8-regok-item"><div class="q8-regok-label">&#20998;&#31449;&#32593;&#22336;</div><div class="q8-regok-value"><a href="<?php echo q8_h($url)?>" target="_blank"><?php echo q8_h($url)?></a><button class="q8-regok-copy" data-copy="<?php echo q8_h($url)?>">&#22797;&#21046;</button></div></div>
                <div class="q8-regok-item"><div class="q8-regok-label">&#31649;&#29702;&#21518;&#21488;</div><div class="q8-regok-value"><a href="<?php echo q8_h($admin_url)?>" target="_blank"><?php echo q8_h($admin_url)?></a><button class="q8-regok-copy" data-copy="<?php echo q8_h($admin_url)?>">&#22797;&#21046;</button></div></div>
                <div class="q8-regok-item"><div class="q8-regok-label">&#31649;&#29702;&#21592;&#29992;&#25143;&#21517;</div><div class="q8-regok-value"><span><?php echo q8_h($user)?></span><button class="q8-regok-copy" data-copy="<?php echo q8_h($user)?>">&#22797;&#21046;</button></div></div>
                <div class="q8-regok-item"><div class="q8-regok-label">&#31649;&#29702;&#21592;&#23494;&#30721;</div><div class="q8-regok-value"><span><?php echo q8_h($pwd)?></span><button class="q8-regok-copy" data-copy="<?php echo q8_h($pwd)?>">&#22797;&#21046;</button></div></div>
                <div class="q8-regok-item"><div class="q8-regok-label">&#31449;&#28857;&#21517;&#31216;</div><div class="q8-regok-value"><span><?php echo q8_h($name)?></span></div></div>
                <div class="q8-regok-item"><div class="q8-regok-label">&#20998;&#31449;&#29256;&#26412;</div><div class="q8-regok-value"><span><?php echo $kind_name?></span></div></div>
            </div>
            <div class="q8-regok-actions">
                <a class="q8-regok-action q8-regok-primary" href="<?php echo q8_h($admin_url)?>" target="_blank">&#36827;&#20837;&#31649;&#29702;&#21518;&#21488;</a>
                <a class="q8-regok-action q8-regok-secondary" href="<?php echo q8_h($url)?>" target="_blank">&#35775;&#38382;&#25105;&#30340;&#20998;&#31449;</a>
                <a class="q8-regok-action q8-regok-secondary" href="../">&#36820;&#22238;&#39318;&#39029;</a>
            </div>
            <div class="q8-regok-footer">&#25552;&#37266;&#65306;&#35831;&#19981;&#35201;&#25226;&#31649;&#29702;&#21592;&#23494;&#30721;&#21457;&#32473;&#38476;&#29983;&#20154;&#12290;&#22914;&#26524;&#38656;&#35201;&#24110;&#21161;&#65292;&#21487;&#22312;&#29992;&#25143;&#21518;&#21488;&#25552;&#20132;&#24037;&#21333;&#25110;&#32852;&#31995;&#31449;&#38271;&#12290;</div>
        </div>
    </div>
</div>
<script>
document.addEventListener('click', function(e){
    var btn = e.target.closest ? e.target.closest('.q8-regok-copy') : null;
    if(!btn) return;
    var text = btn.getAttribute('data-copy') || '';
    var done = function(){ var old = btn.innerHTML; btn.innerHTML = '&#24050;&#22797;&#21046;'; setTimeout(function(){ btn.innerHTML = old; }, 1200); };
    if(navigator.clipboard && navigator.clipboard.writeText){ navigator.clipboard.writeText(text).then(done); }
    else{ var input = document.createElement('input'); input.value = text; document.body.appendChild(input); input.select(); document.execCommand('copy'); document.body.removeChild(input); done(); }
});
</script>
</body>
</html>

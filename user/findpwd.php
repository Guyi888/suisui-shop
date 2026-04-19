<?php
/**
 * 找回密码 - 美化版
 * 博客地址: zhonguo.ren
 * QQ群: 915043052
**/
$is_defend=true;
include("../includes/common.php");
if(isset($_GET['act']) && $_GET['act']=='qrlogin'){
	if(isset($_SESSION['findpwd_qq']) && $qq=$_SESSION['findpwd_qq']){
		$row=$DB->getRow("SELECT zid,user,pwd,status FROM pre_site WHERE qq=:qq LIMIT 1", [':qq'=>$qq]);
		unset($_SESSION['findpwd_qq']);
		if($row['user']){
			if($row['status']==0){
				exit('{"code":-1,"msg":"当前账号已被封禁！"}');
			}
			$session=md5($row['user'].$row['pwd'].$password_hash);
			$token=authcode("{$row['zid']}\t{$session}", 'ENCODE', SYS_KEY);
			setcookie("user_token", $token, time() + 604800, '/');
			log_result('分站找回密码', 'User:'.$row['user'].' IP:'.$clientip, null, 1);
			$DB->exec("UPDATE pre_site SET lasttime='$date' WHERE zid='{$row['zid']}'");
			exit('{"code":1,"msg":"登录成功，请在用户资料设置里重置密码","url":"./"}');
		}else{
			@header('Content-Type: application/json; charset=UTF-8');
			exit('{"code":-1,"msg":"当前QQ不存在，请确认你已注册过账号或开通过分站"}');
		}
	}else{
		@header('Content-Type: application/json; charset=UTF-8');
			exit('{"code":-2,"msg":"验证失败，请重新扫码"}');
	}
}elseif($islogin2==1){
	@header('Content-Type: text/html; charset=UTF-8');
	exit("<script language='javascript'>alert('您已登陆！');window.location.href='./';</script>");
}
$title='找回密码';

// 设置CDN路径
if($conf['cdnpublic']==1){
	$cdnpublic = '//lib.baomitu.com/';
}elseif($conf['cdnpublic']==2){
	$cdnpublic = 'https://cdn.bootcdn.net/ajax/libs/';
}elseif($conf['cdnpublic']==4){
	$cdnpublic = '//s1.pstatp.com/cdn/expire-1-M/';
}else{
	$cdnpublic = '//lib.baomitu.com/';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title;?></title>
    <link rel="stylesheet" href="../assets/css/login-new.css">
</head>
<body class="login-page">
    <div class="page">
        <div class="left">
            <div class="characters-wrap">
                <div class="characters" id="characters">
                    <div class="char char-purple" id="purple">
                        <div class="eyes-wrap" id="purple-eyes">
                            <div class="eyeball" id="purple-eye-l" style="width: 18px; height: 18px">
                                <div class="pupil" style="width: 7px; height: 7px"></div>
                            </div>
                            <div class="eyeball" id="purple-eye-r" style="width: 18px; height: 18px">
                                <div class="pupil" style="width: 7px; height: 7px"></div>
                            </div>
                        </div>
                    </div>
                    <div class="char char-black" id="black">
                        <div class="eyes-wrap" id="black-eyes">
                            <div class="eyeball" id="black-eye-l" style="width: 16px; height: 16px">
                                <div class="pupil" style="width: 6px; height: 6px"></div>
                            </div>
                            <div class="eyeball" id="black-eye-r" style="width: 16px; height: 16px">
                                <div class="pupil" style="width: 6px; height: 6px"></div>
                            </div>
                        </div>
                    </div>
                    <div class="char char-orange" id="orange">
                        <div class="eyes-wrap" id="orange-eyes">
                            <div class="pupil-only" style="width: 12px; height: 12px"></div>
                            <div class="pupil-only" style="width: 12px; height: 12px"></div>
                        </div>
                    </div>
                    <div class="char char-yellow" id="yellow">
                        <div class="eyes-wrap" id="yellow-eyes">
                            <div class="pupil-only" style="width: 12px; height: 12px"></div>
                            <div class="pupil-only" style="width: 12px; height: 12px"></div>
                        </div>
                        <div class="mouth" id="yellow-mouth"></div>
                    </div>
                </div>
            </div>
            <div class="grid-overlay"></div>
            <div class="blob1"></div>
            <div class="blob2"></div>
        </div>
        
        <div class="right">
            <div class="form-box">
                <div class="header">
                    <h1>找回密码</h1>
                    <p>通过QQ扫码找回密码</p>
                </div>
                <div class="form">
                    <div style="text-align: center; margin: 20px 0;">
                        <div style="font-weight: 500; color: #6b7280; margin-bottom: 20px; font-size: 14px;">
                            <span id="loginmsg">请使用QQ手机版扫描二维码</span><span id="loginload" style="padding-left: 10px;color: #790909;">.</span>
                        </div>
                        <div id="qrimg" style="margin: 20px 0; display: flex; justify-content: center;">
                        </div>
                        <button type="button" onclick="qrlogin()" class="hover-btn" style="margin-top: 20px;">
                            <span class="label">我已完成登录</span>
                            <div class="overlay">
                                <span>我已完成登录</span>
                                <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"></path>
                                </svg>
                            </div>
                        </button>
                    </div>
                    
                    <?php if($conf['login_qq']==1){?>
                    <div style="margin: 20px 0; padding: 16px; background-color: #f0f9ff; border-radius: 8px; font-size: 13px; color: #6b7280; line-height: 1.6;">
                        提示：只能找回注册时填写了QQ号码的帐号密码，QQ快捷登录的暂不支持该方式找回密码。
                    </div>
                    <?php }?>
                    
                    <div class="divider">
                        <a href="login.php">返回登录</a>
                        <span style="margin:0 8px;">|</span>
                        <a href="reg.php">注册用户</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo $cdnpublic?>jquery/1.12.4/jquery.min.js"></script>
    <script src="../assets/js/qrlogin.js?ver=<?php echo VERSION ?>"></script>
    <script src="../assets/js/login-new.js"></script>
</body>
</html>

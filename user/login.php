<?php
/**
 * 登录页面 - 美化版
 * 博客地址: zhonguo.ren
 * QQ群: 915043052
**/
$is_defend=true;
include("../includes/common.php");
if(isset($_GET['logout'])){
	if(!checkRefererHost())exit();
	setcookie("user_token", "", time() - 604800, '/');
	@header('Content-Type: text/html; charset=UTF-8');
	exit("<script language='javascript'>alert('您已成功注销本次登录！');window.location.href='./login.php';</script>");
}elseif($islogin2==1){
	@header('Content-Type: text/html; charset=UTF-8');
	exit("<script language='javascript'>alert('您已登录！');window.location.href='./';</script>");
}
$title='用户登录';

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
                    <h1>欢迎回来！</h1>
                    <p>请输入您的登录信息</p>
                </div>
                <form id="loginForm">
                    <div class="field">
                        <label for="user">用户名</label>
                        <input type="text" id="user" name="user" placeholder="请输入用户名" required="required" autocomplete="off">
                    </div>
                    <div class="field">
                        <label for="pass">密码</label>
                        <div class="input-wrap">
                            <input type="password" id="pass" name="pass" placeholder="请输入密码" required="required">
                            <button type="button" class="toggle-pw" id="togglePw" aria-label="切换密码可见性">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                                </svg>
                                <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="display: none">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <?php if($conf['captcha_open_login']==1 && $conf['captcha_open']>=1){?>
                    <input type="hidden" name="captcha_type" value="<?php echo $conf['captcha_open']?>">
                    <?php if($conf['captcha_open']>=2){?><input type="hidden" name="appid" value="<?php echo $conf['captcha_id']?>"><?php }?>
                    <div id="captcha">
                        <div id="captcha_text">正在加载验证码</div>
                        <div id="captcha_wait">
                            <div class="loading">
                                <div class="loading-dot"></div>
                                <div class="loading-dot"></div>
                                <div class="loading-dot"></div>
                                <div class="loading-dot"></div>
                            </div>
                        </div>
                    </div>
                    <div id="captchaform"></div>
                    <?php }?>
                    
                    <div class="row">
                        <label class="remember">
                            <input type="checkbox" name="remember" value="1"> 记住我
                        </label>
                        <a href="findpwd.php">忘记密码？</a>
                    </div>
                    
                    <button type="button" id="submit_login" class="hover-btn">
                        <span class="label">立即登录</span>
                        <div class="overlay">
                            <span>立即登录</span>
                            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"></path>
                            </svg>
                        </div>
                    </button>
                </form>
                
                <?php if($conf['login_qq']>=1 || $conf['login_wx']>=1){?>
                <div class="social-account-container">
                    <div class="title">社交账号登录</div>
                    <div class="social-accounts">
                        <?php if($conf['login_qq']>=1){?>
                        <a href="javascript:connect('qq')" class="social-button" title="QQ登录">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.374 0 0 5.373 0 12s5.374 12 12 12 12-5.373 12-12S18.626 0 12 0zm5.67 10.91c0 1.568-1.27 2.84-2.83 2.84s-2.83-1.272-2.83-2.84c0-1.567 1.27-2.838 2.83-2.838s2.83 1.271 2.83 2.838z"></path>
                            </svg>
                        </a>
                        <?php }?>
                        <?php if($conf['login_wx']>=1){?>
                        <a href="javascript:connect('wx')" class="social-button" title="微信登录">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.374 0 0 5.373 0 12s5.374 12 12 12 12-5.373 12-12S18.626 0 12 0z"></path>
                            </svg>
                        </a>
                        <?php }?>
                    </div>
                </div>
                <?php }?>
                
                <div class="divider">
                    还没有账号？<a href="<?php echo $conf['user_open']==1 ? 'reg.php' : 'regsite.php';?>">立即注册</a>
                    <span style="margin:0 8px;">|</span>
                    <a href="../">返回首页</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo $cdnpublic?>jquery/1.12.4/jquery.min.js"></script>
    <script src="<?php echo $cdnpublic?>layer/2.3/layer.js"></script>
    <script src="../assets/js/login.js?ver=<?php echo VERSION ?>"></script>
    <script src="../assets/js/login-new.js"></script>
</body>
</html>

<?php
/**
 * 注册页面 - 美化版
 * 官网：t.me/qqfaka
 * TG：@qqfaka
**/
$is_defend=true;
include("../includes/common.php");
if($islogin2==1){
	@header('Content-Type: text/html; charset=UTF-8');
	exit("<script language='javascript'>alert('您已登录！');window.location.href='./';</script>");
}
if(!$conf['user_open'] && $conf['fenzhan_buy']==1){
	exit("<script language='javascript'>window.location.href='./regsite.php';</script>");
}elseif(!$conf['user_open']){
	@header('Content-Type: text/html; charset=UTF-8');
	exit("<script language='javascript'>alert('未开放新用户注册');window.location.href='./';</script>");
}
$title='用户注册';

$addsalt=md5(mt_rand(0,999).time());
$_SESSION['addsalt']=$addsalt;
$x = new \lib\hieroglyphy();
$addsalt_js = $x->hieroglyphyString($addsalt);

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
                    <h1>新用户注册</h1>
                    <p>创建您的账号</p>
                </div>
                <form id="regForm">
                    <input type="hidden" name="captcha_type" value="<?php echo $conf['captcha_open']?>">
                    <div class="field">
                        <label for="user">用户名</label>
                        <input type="text" id="user" name="user" placeholder="请输入登录用户名" required="required" autocomplete="off">
                    </div>
                    <div class="field">
                        <label for="pwd">密码</label>
                        <div class="input-wrap">
                            <input type="password" id="pwd" name="pwd" placeholder="请输入6位以上密码" required="required">
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
                    <div class="field">
                        <label for="qq">QQ号码</label>
                        <input type="text" id="qq" name="qq" placeholder="请输入QQ号，用于找回密码" required="required">
                    </div>

                    <?php if($conf['captcha_open']>=1){?>
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
                    <?php }else{?>
                    <div class="field">
                        <label for="code">验证码</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" id="code" name="code" placeholder="请输入验证码" required="required" style="flex: 1;">
                            <img id="codeimg" src="./code.php?r=<?php echo time();?>" height="48" onclick="this.src='./code.php?r='+Math.random();" title="点击更换验证码" style="border-radius: 8px; cursor: pointer;">
                        </div>
                    </div>
                    <?php }?>

                    <button type="button" id="submit_reg" class="hover-btn">
                        <span class="label">立即注册</span>
                        <div class="overlay">
                            <span>立即注册</span>
                            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"></path>
                            </svg>
                        </div>
                    </button>
                </form>

                <div class="divider">
                    已有账号？<a href="login.php">返回登录</a>
                    <span style="margin:0 8px;">|</span>
                    <a href="../">返回首页</a>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="hashsalt" value="<?php echo $addsalt?>">
    <script src="<?php echo $cdnpublic?>jquery/1.12.4/jquery.min.js"></script>
    <script src="<?php echo $cdnpublic?>layer/2.3/layer.js"></script>
    <script>var hashsalt=<?php echo $addsalt_js?>;</script>
    <script src="../assets/js/reguser.js?ver=<?php echo VERSION ?>"></script>
    <script src="../assets/js/login-new.js"></script>
</body>
</html>

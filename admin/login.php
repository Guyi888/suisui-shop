<?php
/**
 * 登录
**/
$verifycode = 1;//验证码开关

if(!function_exists("imagecreate") || !file_exists('code.php'))$verifycode=0;
include("../includes/common.php");
if(isset($_POST['user']) && isset($_POST['pass'])){
	if($conf['thirdlogin_closepwd']==1 && $conf['thirdlogin_open']==1){
		@header('Content-Type: text/html; charset=UTF-8');
		exit("<script language='javascript'>alert('已关闭密码登录，请使用快捷登录！');history.go(-1);</script>");
	}
	// CSRF令牌验证
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== md5(session_id() . SYS_KEY)) {
		@header('Content-Type: text/html; charset=UTF-8');
		exit("<script language='javascript'>alert('CSRF验证失败！');history.go(-1);</script>");
	}
	$user=addslashes($_POST['user']);
	$pass=addslashes($_POST['pass']);
	$code=$_POST['code'];
	if ($verifycode==1 && (!$code || strtolower($code) != $_SESSION['vc_code'])) {
		unset($_SESSION['vc_code']);
		@header('Content-Type: text/html; charset=UTF-8');
		exit("<script language='javascript'>alert('验证码错误！');history.go(-1);</script>");
	}elseif($user===$conf['admin_user'] && $pass===$conf['admin_pwd']) {
		unset($_SESSION['vc_code']);
		$session=md5($user.$pass.$password_hash);
		$time = time() + 259200; // 修改为3天有效期（与common.php保持一致）
		$token=authcode("0\t{$user}\t{$session}\t{$time}", 'ENCODE', SYS_KEY);
		$_SESSION['admin_token'] = $token;
		setcookie("admin_token_backup", $token, time() + 259200, '/', $_SERVER['HTTP_HOST'], true, true); // 设置cookie备份，添加HttpOnly和Secure属性
		saveSetting('adminlogin',$date);
		log_result('后台登录', 'IP:'.$clientip, null, 1);
		@header('Content-Type: text/html; charset=UTF-8');
		exit("<script language='javascript'>window.location.href='./';</script>");
	}else {
		$userrow=$DB->getRow("SELECT * FROM pre_account WHERE username=:username limit 1", array(':username' => $user));
		if($userrow && $user===$userrow['username'] && $pass===$userrow['password']) {
			if($userrow['active']==0){
				@header('Content-Type: text/html; charset=UTF-8');
				exit("<script language='javascript'>alert('您的账号未激活！');history.go(-1);</script>");
			}
			unset($_SESSION['vc_code']);
			$session=md5($user.$pass.$password_hash);
			$time = time() + 259200; // 修改为3天有效期（与common.php保持一致）
			$token=authcode("1\t{$userrow['id']}\t{$session}\t{$time}", 'ENCODE', SYS_KEY);
			$_SESSION['admin_token'] = $token;
			setcookie("admin_token_backup", $token, time() + 259200, '/', $_SERVER['HTTP_HOST'], true, true); // 设置cookie备份，添加HttpOnly和Secure属性
			$DB->exec("update pre_account set lasttime=:date where id=:id", array(':date' => $date, ':id' => $userrow['id']));
			log_result('后台登录', 'User:'.$user.' IP:'.$clientip, null, 1);
			@header('Content-Type: text/html; charset=UTF-8');
			exit("<script language='javascript'>window.location.href='./';</script>");
		}
		unset($_SESSION['vc_code']);
		@header('Content-Type: text/html; charset=UTF-8');
		exit("<script language='javascript'>alert('用户名或密码不正确！');history.go(-1);</script>");
	}
}elseif(isset($_GET['act']) && $_GET['act']=='qrlogin'){
	if(!checkRefererHost())exit();
	if(!$_SESSION['thirdlogin_type']||!$_SESSION['thirdlogin_uin'])exit('{"code":-4,"msg":"校验失败，请重新登录"}');
	$type = $_SESSION['thirdlogin_type'];
	$uin = $_SESSION['thirdlogin_uin'];
	if($islogin==1){
		adminpermission('set', 2);
		if($type == 'qq'){
			saveSetting('thirdlogin_qq', $uin);
			$typename = 'QQ';
		}else{
			saveSetting('thirdlogin_wx', $uin);
			$typename = '微信';
		}
		$CACHE->clear();
		unset($_SESSION['thirdlogin_type']);
		unset($_SESSION['thirdlogin_uin']);
		exit('{"code":1,"msg":"'.$typename.'绑定成功！","url":"reload"}');
	}else{
		if(!$conf['thirdlogin_open'])exit('{"code":-4,"msg":"未开启快捷登录"}');
		$typename = $type == 'qq' ? 'QQ' : '微信';
		if(isset($conf['thirdlogin_qq']) && $type == 'qq' && $uin == $conf['thirdlogin_qq'] || isset($conf['thirdlogin_wx']) && $type == 'wx' && $uin == $conf['thirdlogin_wx']){
			unset($_SESSION['thirdlogin_type']);
			unset($_SESSION['thirdlogin_uin']);
			$session=md5($conf['admin_user'].$conf['admin_pwd'].$password_hash);
			$time = time() + 259200; // 修改为3天有效期（与common.php保持一致）
			$token=authcode("0\t{$conf['admin_user']}\t{$session}\t{$time}", 'ENCODE', SYS_KEY);
			$_SESSION['admin_token'] = $token;
			setcookie("admin_token_backup", $token, time() + 259200, '/', $_SERVER['HTTP_HOST'], true, true); // 设置cookie备份，添加HttpOnly和Secure属性
			saveSetting('adminlogin',$date);
			log_result('后台登录', 'IP:'.$clientip, null, 1);
			exit('{"code":1,"msg":"登陆管理中心成功！","url":"./"}');
		}else{
			exit('{"code":-1,"msg":"登录失败，该'.$typename.'未绑定！"}');
		}
	}
}elseif(isset($_GET['logout'])){
	if(!checkRefererHost())exit();
	unset($_SESSION['admin_token']);
	setcookie("admin_token_backup", '', time() - 604800, '/', $_SERVER['HTTP_HOST'], true, true); // 同时清除cookie
	@header('Content-Type: text/html; charset=UTF-8');
	exit("<script language='javascript'>alert('您已成功注销本次登陆！');window.location.href='./login.php';</script>");
}elseif($islogin==1){
	@header('Content-Type: text/html; charset=UTF-8');
	exit("<script language='javascript'>alert('您已登陆！');window.location.href='./';</script>");
}
$title='用户登录';
include './head.php';
$adminLoginLogoHref = function_exists('q8_brand_logo_href') ? q8_brand_logo_href() : '/assets/img/logo.png?r=74129';
?>

<!-- 补充引入必要的库 -->
<script src="//lib.baomitu.com/easy-pie-chart/2.1.6/jquery.easypiechart.min.js"></script>
<style>
  /* 3D 主题切换效果相关样式 */
  @layer inversion {
    /* ::before === clouds, ::after === stars */
    @media (prefers-color-scheme: light) {
      [data-invert="true"]::after,
      [data-invert="false"]::before {
        opacity: 1;
      }
      [data-invert="true"]::before,
      [data-invert="false"]::after {
        opacity: 0.1;
      }
    }
    @media (prefers-color-scheme: dark) {
      [data-invert="true"]::before,
      [data-invert="false"]::after {
        opacity: 1;
        background-position: 0% 50%;
      }
      [data-invert="true"]::after,
      [data-invert="false"]::before {
        opacity: 0;
      }
    }

    [data-invert="true"][data-theme="light"]::before {
      opacity: 0.1;
      background-position: 50% 50%;
    }
    [data-invert="true"][data-theme="dark"]::before {
      opacity: 1;
      background-position: 0% 50%;
    }
    [data-invert="true"][data-theme="light"]::after,
    [data-invert="false"][data-theme="dark"]::after {
      opacity: 1;
    }
    [data-invert="false"][data-theme="light"]::after,
    [data-invert="true"][data-theme="dark"]::after {
      opacity: 0;
    }
    [data-invert="false"][data-theme="light"]::before {
      opacity: 1;
      background-position: 50% 50%;
    }
    [data-invert="false"][data-theme="dark"]::before {
      opacity: 0.1;
      background-position: 0% 50%;
    }
  }

  :root {
    --perspective: 400vmax;
    --distance: -0.75;
    --duration: 2.0s;
    --ease: cubic-bezier(0.34, 1.56, 0.64, 1);
    view-transition-name: none;
    --login-bg-light: rgba(255, 255, 255, 0.95);
    --login-bg-dark: rgba(20, 20, 30, 0.95);
  }

  /* 移除原有的背景图片和样式，使用3D效果的背景 */
  .admin-bg-img {
    display: none;
  }

  /* 修改body样式以支持3D效果 */
  body.login-page {
    background: #f5f7fa;
    margin: 0;
    padding: 0;
    -ms-scroll-chaining: none;
    overscroll-behavior: none;
    view-transition-name: body;
    position: relative;
    min-height: 100vh;
    overflow: hidden;
  }

  /* 3D跳转效果的关键样式 */
  ::view-transition-new(body),
  ::view-transition-old(body) {
    transform: perspective(var(--perspective)) translate3d(0, 0, 0);
    animation: travel var(--duration) var(--ease);
    backface-visibility: hidden;
    will-change: transform;
  }

  ::view-transition-new(body) {
    --rotation-start: 90deg;
    --rotation-end: 0deg;
    --depth-start: 1;
    --depth-end: 0;
  }
  ::view-transition-old(body) {
    --depth-end: 1;
    --depth-start: 0;
    --rotation-start: 0deg;
    --rotation-end: -90deg;
  }

  @keyframes travel {
    0% {
      animation-timing-function: ease-in;
      transform: perspective(var(--perspective))
        translate3d(
          0,
          0,
          calc(
            (var(--perspective) * var(--distance)) * var(--depth-start)
          )
        )
        rotateY(var(--rotation-start, 0deg))
        translate3d(0, 0, calc(50vw * var(--depth-start)));
    }
    25% {
      animation-timing-function: var(--ease);
      transform: perspective(var(--perspective))
        translate3d(0, 0, calc(var(--perspective) * var(--distance)))
        rotateY(var(--rotation-start, 0deg)) translate3d(0, 0, 50vw);
    }
    75% {
      animation-timing-function: var(--ease);
      transform: perspective(var(--perspective))
        translate3d(0, 0, calc(var(--perspective) * var(--distance)))
        rotateY(var(--rotation-end, -90deg)) translate3d(0, 0, 50vw);
    }
    100% {
      animation-timing-function: var(--ease);
      transform: perspective(var(--perspective))
        translate3d(
          0,
          0,
          calc((var(--perspective) * var(--distance)) * var(--depth-end))
        )
        rotateY(var(--rotation-end, 0deg))
        translate3d(0, 0, calc(50vw * var(--depth-end)));
    }
  }

  /* 3D背景效果 - 使用实际div替代伪元素 */
  .bg-layer {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-size: cover;
    background-position: 50% 50%;
    transition: opacity calc(var(--duration) * 0.25)
        calc(var(--duration) * 0.5) ease-out,
      background-position calc(var(--duration) * 1) ease-out;
    z-index: -1;
    animation: float 20s infinite ease-in-out;
  }

  .bg-layer.clouds {
    background:
      radial-gradient(circle at 18% 22%, rgba(56, 189, 248, .24), transparent 28%),
      radial-gradient(circle at 78% 14%, rgba(34, 197, 94, .18), transparent 26%),
      linear-gradient(135deg, rgba(248, 251, 255, .92), rgba(232, 242, 255, .86));
    filter: brightness(1.03) saturate(1.05);
  }

  .bg-layer.stars {
    background:
      radial-gradient(circle at 24% 28%, rgba(255, 255, 255, .78) 0 2px, transparent 3px),
      radial-gradient(circle at 72% 36%, rgba(37, 99, 235, .16) 0 3px, transparent 4px),
      radial-gradient(circle at 48% 76%, rgba(14, 165, 233, .14) 0 4px, transparent 5px);
    filter: brightness(1.08) saturate(1.08);
  }

  /* 背景浮动动画 */
  @keyframes float {
    0%, 100% {
      background-position: 0% 0%;
      transform: scale(1.0);
    }
    25% {
      background-position: 10% 5%;
      transform: scale(1.02);
    }
    50% {
      background-position: 5% 10%;
      transform: scale(1.04);
    }
    75% {
      background-position: 10% 15%;
      transform: scale(1.02);
    }
  }

  /* 确保登录表单区域在3D背景上方 */
  .container-fluid {
    position: relative;
    z-index: 1;
    background: transparent;
    padding: 2rem 0;
  }

  /* 登录卡片增强样式 */
  .login-container {
    transition: all 0.6s var(--ease);
    transform-style: preserve-3d;
    perspective: 1000px;
  }

  .login-form-section {
    backdrop-filter: blur(10px);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.4s ease;
    background: var(--login-bg-light);
  }

  @media (prefers-color-scheme: dark) {
    .login-form-section {
      background: var(--login-bg-dark);
    }
  }

  /* 输入框动画效果 */
  .form-control {
    position: relative;
    transition: all 0.3s ease;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(200, 200, 220, 0.5);
    padding: 12px 16px;
  }

  .form-control:focus {
    box-shadow: 0 0 0 3px rgba(100, 149, 237, 0.3);
    transform: translateY(-1px);
    border-color: #6495ed;
  }

  /* 移除了旧的#loginButton样式，现在使用通用的button样式 */

  @keyframes spin {
    to { transform: translate(-50%, -50%) rotate(360deg); }
  }

  /* 登录标题动画 */
  .login-title {
    position: relative;
    display: inline-block;
    margin-bottom: 1.5rem;
    font-weight: 700;
    font-size: 1.75rem;
    color: #2d3748;
  }

  .login-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 60px;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 2px;
    transform: scaleX(0);
    transform-origin: right;
    transition: transform 0.4s ease;
  }

  .login-form-section:hover .login-title::after {
    transform: scaleX(1);
    transform-origin: left;
  }

  /* 响应式优化 */
  @media (max-width: 768px) {
    .login-form-section {
      margin: 0 1rem;
    }

    :root {
      --perspective: 300vmax;
    }
  }
</style>
<?php
if($conf['thirdlogin_open'] == 1 && $conf['thirdlogin_closepwd'] == 1){
	$mode = 3;
}elseif($conf['thirdlogin_open'] == 1){
	$mode = 2;
}else{
	$mode = 1;
}
?>

<!-- 模态框保持不变 -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">找回管理员密码方法</h4>
      </div>
      <div class="modal-body">
        <p>进入数据库管理器（phpMyAdmin），点击进入当前网站所在数据库，然后查看shua_config表即可找回管理员密码。</p>
		<?php if($mode==3){?>如需开启密码登录，请执行以下SQL：UPDATE shua_config SET v='0' WHERE k='thirdlogin_closepwd';UPDATE shua_cache SET v='' WHERE k='config';<?php }?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
    </div>
  </div>
</div>

<!-- 移除不存在的JS文件引用 -->
<!-- 后续可以根据实际需要添加正确路径的JS文件 -->

<style>
/* 基础样式优化 */
.logo.text-center img{
    height: 65px;
    margin-bottom: 1.8rem;
    transition: transform 0.3s ease;
    border-radius: 8px;
}
.logo.text-center img:hover{
    transform: scale(1.05);
}
.list-inline-item .icon {
    width: 2.5rem;
    height: 2.5rem;
}
.social-list-item {
    border: none;
    transition: all 0.3s ease;
}
.social-list-item:hover {
    transform: translateY(-3px) scale(1.05);
}
.allow_login_code_captcha{display:none;}

/* 整体布局 - 左侧登录区(白底) 右侧图片区 */
.container-fluid {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background-color: #f9fafb;
}
.row.no-gutters {
    width: 100%;
    max-width: 1200px;
    border-radius: 24px !important;
    box-shadow: 0 10px 50px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    display: flex;
    flex-direction: row-reverse; /* 调换左右顺序 */
    animation: fadeIn 0.6s ease forwards;
    opacity: 0;
}

/* 右侧图片区域 */
.col-xl-7.bglogo {
    width: 45%; /* 图片区宽度 */
    position: relative;
    min-height: 520px;
}
.auth-full-bg {
    position: absolute;
    top: 0;
    right: 0; /* 靠右显示 */
    width: 100%;
    height: 100%;
    padding: 0;
}
.bg-overlay {
    background: none;
    background-size: cover;
    opacity: 0;
    height: 100%;
    width: 100%;
    transition: transform 7s ease, opacity 1s ease;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 0;
}
.bg-overlay.loaded {
    background:
        radial-gradient(circle at 24% 20%, rgba(32, 197, 200, .24), transparent 30%),
        radial-gradient(circle at 82% 18%, rgba(139, 92, 246, .20), transparent 34%),
        linear-gradient(135deg, #f8fbff 0%, #eef6ff 100%);
    opacity: 1;
}
.col-xl-7.bglogo:hover .bg-overlay {
    transform: scale(1.05);
}
.admin-login-visual {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 520px;
    padding: 42px;
}
.admin-login-visual img {
    display: block;
    width: min(88%, 640px);
    height: auto;
    border-radius: 22px;
    box-shadow: 0 24px 52px rgba(22, 119, 255, .14);
}
.auth-full-bg .d-flex {
    padding: 3rem;
}
.auth-full-bg h1.text-white {
    font-size: 1.8rem;
    margin-bottom: 0.8rem;
    animation: slideLeft 0.8s ease forwards;
    opacity: 0;
}
.auth-full-bg p.text-white-50 {
    font-size: 0.9rem;
    animation: slideLeft 0.8s ease 0.3s forwards;
    opacity: 0;
}

/* 左侧登录区域 - 白底突出 */
.col-xl-5 {
    width: 55%; /* 登录区宽度 */
    background-color: #ffffff; /* 白底 */
}
.auth-full-page-content {
    padding: 3.5rem;
    display: flex;
    align-items: center;
    min-height: 520px;
}
.login_right {
    width: 100%;
    max-width: 420px;
    margin: 0 auto;
}

/* 输入框样式 - 突出显示 */
.form-control, .input-group-append, .btn {
    height: 50px;
    border-radius: 14px !important;
    transition: all 0.3s ease;
}
.form-control {
    border: 1px solid #e5e7eb;
    padding: 0 18px;
    background-color: #f9fafb; /* 输入框浅灰底突出 */
}
.form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    transform: translateY(-2px);
    background-color: #ffffff;
}

/* 标签页样式 */
.nav-tabs-custom .nav-link {
    border-radius: 14px !important;
    margin: 0 5px;
    padding: 8px 18px;
    color: #666;
}
.nav-tabs-custom .nav-link.active {
    background-color: #eff6ff;
    color: #2563eb;
    font-weight: 500;
    transform: translateY(-2px);
}
.nav-tabs {
    animation: fadeInUp 0.6s ease 0.3s forwards;
    opacity: 0;
}

/* 按钮样式 */
#loginButton {
  background: #fff;
  border: none;
  padding: 10px 20px;
  display: inline-block;
  font-size: 15px;
  font-weight: 600;
  width: 100%;
  text-transform: uppercase;
  cursor: pointer;
  transform: skew(-21deg);
}

#loginButton span {
  display: inline-block;
  transform: skew(21deg);
}

#loginButton::before {
  content: '';
  position: absolute;
  top: 0;
  bottom: 0;
  right: 100%;
  left: 0;
  background: rgb(20, 20, 20);
  opacity: 0;
  z-index: -1;
  transition: all 0.5s;
}

#loginButton:hover {
  color: #fff;
}

#loginButton:hover::before {
  left: 0;
  right: 0;
  opacity: 1;
}

/* 表单间距优化 */
.form-group {
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
    margin-bottom: 1.3rem;
}
.form-group:nth-child(1) { animation-delay: 0.2s; }
.form-group:nth-child(2) { animation-delay: 0.4s; }
.form-group:nth-child(3) { animation-delay: 0.6s; }

/* 动画效果定义 */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes slideLeft {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

/* 整体入场动画 */
.logo.text-center {
    animation: fadeInUp 0.6s ease forwards;
    opacity: 0;
}

/* 响应式调整 */
@media (max-width: 992px) {
    .col-xl-7.bglogo {
        width: 100%;
        min-height: 280px;
    }
    .col-xl-5 {
        width: 100%;
    }
    .auth-full-page-content {
        padding: 2.5rem;
    }
}

/* 手机端隐藏右侧图片 */
@media (max-width: 768px) {
    .col-xl-7.bglogo {
        display: none;
    }
    .row.no-gutters {
        flex-direction: row;
    }
    .col-xl-5 {
        width: 100%;
        border-radius: 24px !important;
    }
}
</style>

<div class="container-fluid p-0">
    <div class="row no-gutters">

        <!-- 背景层 -->
        <div class="bg-layer clouds"></div>
        <div class="bg-layer stars"></div>

        <!-- 右侧图片区域 -->
        <div class="col-xl-7 bglogo">
            <div class="auth-full-bg pt-lg-5 p-4">
                <div class="w-100">
                    <div class="bg-overlay"></div>
                    <div class="admin-login-visual">
                        <img src="<?php echo htmlspecialchars($adminLoginLogoHref, ENT_QUOTES, 'UTF-8'); ?>" alt="后台登录展示图">
                    </div>
                </div>
            </div>
        </div>

        <!-- 左侧登录区域 (白底) -->
        <div class="col-xl-5">
            <div class="auth-full-page-content p-md-5 p-4">
                <div class="login_right mx-auto">
                    <div class="d-flex flex-column h-100">
                        <div class="my-auto">
                            <div  class="logo text-center" >
                                <a href="#"><img src="<?php echo htmlspecialchars($adminLoginLogoHref, ENT_QUOTES, 'UTF-8'); ?>" alt="系统管理平台" class="cursor"></a>
                            </div>
                            <ul class="affs-nav nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                                <!-- 账号密码登录 -->
                                <li class="nav-item">
                                    <a class="nav-link fs-14 bg-transparent active" data-toggle="tab" href="#email" role="tab" aria-selected="true"><span class="fa fa-lock mr-1"></span> 账号密码</a>
                                </li>
                                <!-- 扫码登录 -->
                                <?php if($mode>1){
                                ?>
                                <li class="nav-item">
                                    <a class="nav-link fs-14 bg-transparent" data-toggle="tab" href="#block-tabs-home" role="tab" aria-selected="false"><span class="fa fa-qrcode mr-1"></span> 扫码登录</a>
                                </li>
                                <?php }?>
                            </ul>

                            <div class="mt-4">
                                <div class="tab-content">
                                    <!-- 账号密码登录 -->
                                    <div id="email" class="tab-pane active" role="tabpanel">
                                        <form method="post" action="" id="loginForm" >
<input type="hidden" name="csrf_token" value="<?php echo md5(session_id() . SYS_KEY); ?>">
                                            <div class="form-group">
                                                <label for="username">用户名</label>
                                                <input type="text" class="form-control" id="emailInp" name="user" value="" placeholder="请输入用户名">
                                            </div>
                                            <div class="form-group">
                                                <div class="d-flex justify-content-between">
                                                    <label for="userpassword">密码</label>
                                                    <a href="#myModal" class="text-primary mr-0" data-toggle="modal">忘记密码?</a>
                                                </div>
                                                <input type="password" class="form-control" id="emailPwdInp" name="pass" placeholder="请输入密码" onpaste="return false;">
                                            </div>
                                            <?php if($verifycode==1){
                                            ?>
                                            <div class="form-group" style="overflow:visible;">
                                                <label for="code">验证码</label>
                                                <div style="display: flex; align-items: center; width: 100%;">
                                                    <input type="text" class="form-control" id="code" name="code" placeholder="请输入验证码" style="flex: 1; margin-right: 10px;">
                                                    <img src="code.php?<?php echo time(); ?>" onclick="this.src='code.php?'+Math.random()" alt="验证码" style="width:120px;height:46px;cursor:pointer; flex-shrink: 0;">
                                                </div>
                                                <small class="form-text text-muted mt-1">点击图片可刷新</small>
                                            </div>
                                            <?php }?>
                                            <div class="mt-3" style="text-align: center;">
                                                <button type="button" id="loginButton">
  <span>登录</span>
</button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- 扫码登录 -->
                                    <?php if($mode>1){
                                    ?>
                                    <div class="tab-pane fade" id="block-tabs-home">
                                        <div class="text-center mb-4">
                                            <div class="list-group text-center">
                                                <div class="list-group-item" style="font-weight: bold;" id="login">
                                                    <span id="loginmsg">请使用微信或QQ扫描二维码</span><span id="loginload" style="padding-left: 10px;color: #790909;">.</span>
                                                </div>
                                                <div class="list-group-item" id="qrimg" title="点击刷新二维码">
                                                </div>
                                                <div class="list-group-item" id="mobile" style="display:none;"><button type="button" id="mlogin" onclick="mloginurl()" class="btn btn-warning btn-block">跳转QQ快捷登录</button><br/><button type="button" onclick="qrlogin()" class="btn btn-success btn-block">我已完成登录</button><br/>
                                                    <span class="text-muted">提示：手机用户如需微信扫码，可截图保存二维码，在微信内扫一扫，从相册识别二维码。</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php }?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // 页面加载完成后执行
        if(document.getElementById('qrimg')){
            getqrpic();
        }
        // 自动更新年份
        document.getElementById('year-copy').textContent = new Date().getFullYear();

        // 添加动态背景效果
        const body = document.querySelector('body.login-page');
        let mouseX = 0, mouseY = 0;

        // 鼠标移动产生视差效果
        document.addEventListener('mousemove', function(e) {
            mouseX = e.clientX / window.innerWidth - 0.5;
            mouseY = e.clientY / window.innerHeight - 0.5;

            // 应用视差效果到背景
            body.style.setProperty('--mouse-x', mouseX);
            body.style.setProperty('--mouse-y', mouseY);

            // 动态调整背景位置 - 使用实际div元素
            const cloudsLayer = document.querySelector('.bg-layer.clouds');
            const starsLayer = document.querySelector('.bg-layer.stars');

            if (cloudsLayer) {
                cloudsLayer.style.backgroundPosition = `${50 + mouseX * 2}% ${50 + mouseY * 2}%`;
            }

            if (starsLayer) {
                starsLayer.style.backgroundPosition = `${50 - mouseX * 3}% ${50 - mouseY * 3}%`;
            }
        });

        // 3D登录跳转效果增强版
        document.getElementById('loginButton').addEventListener('click', function() {
            // 验证表单（基本验证）
            const username = document.querySelector('input[name="user"]').value;
            const password = document.querySelector('input[name="pass"]').value;

            if (!username || !password) {
                alert('\u8bf7\u8f93\u5165\u7528\u6237\u540d\u548c\u5bc6\u7801');
                return;
            }

            // 使用View Transitions API实现3D跳转效果
            if (document.startViewTransition) {
                // 创建预加载动画
                const createPreloader = () => {
                    const preloader = document.createElement('div');
                    preloader.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100vw;
                        height: 100vh;
                        background: rgba(102, 126, 234, 0.1);
                        z-index: 9999;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        pointer-events: none;
                        backdrop-filter: blur(5px);
                        opacity: 0;
                        transition: opacity 0.3s ease;
                    `;

                    const spinner = document.createElement('div');
                    spinner.style.cssText = `
                        width: 60px;
                        height: 60px;
                        border: 3px solid rgba(255, 255, 255, 0.3);
                        border-radius: 50%;
                        border-top-color: #667eea;
                        animation: spin 1s linear infinite;
                        box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
                    `;

                    preloader.appendChild(spinner);
                    document.body.appendChild(preloader);

                    // 触发重排后显示预加载器
                    setTimeout(() => {
                        preloader.style.opacity = '1';
                    }, 10);

                    return preloader;
                };

                const preloader = createPreloader();

                // 延迟启动View Transition，给用户更明显的反馈
                setTimeout(() => {
                    document.startViewTransition(function() {
                        // 等待动画进行到中间位置再提交表单
                        setTimeout(function() {
                            document.getElementById('loginForm').submit();
                        }, 800);
                    });
                }, 300);
            } else {
                // 降级方案：如果浏览器不支持View Transitions
                setTimeout(function() {
                    document.getElementById('loginForm').submit();
                }, 500);
            }
        });

        // 为输入框添加焦点动画效果
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateZ(20px)';
            });

            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateZ(0)';
            });
        });

        // 添加页面进入动画 - 使用正确的选择器
        const loginFormSection = document.querySelector('.auth-full-page-content');
        if (loginFormSection) {
            loginFormSection.style.opacity = '0';
            loginFormSection.style.transform = 'translateY(30px) scale(0.95)';
            loginFormSection.style.transition = 'opacity 0.8s ease, transform 0.8s ease';

            // 页面加载后显示登录表单
            setTimeout(() => {
                loginFormSection.style.opacity = '1';
                loginFormSection.style.transform = 'translateY(0) scale(1)';
            }, 300);
        }

        // 延迟加载bg-overlay背景图片（最后加载）
        setTimeout(() => {
            const bgOverlay = document.querySelector('.bg-overlay');
            if (bgOverlay) {
                bgOverlay.classList.add('loaded');
            }
        }, 1000);
    });
</script>
                    <footer class="text-muted text-center mt-5">
                        <small><span id="year-copy"></span> &copy; <a href="#" class="text-primary"><?php echo $conf['sitename']?></a> 版权所有</small>
                    </footer>

<!-- 从模板中提取的样式 -->
<?php if($mode>1){?>
<script>var isbind = false;</script>
<script src="//lib.baomitu.com/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
<script src="./assets/js/qrlogin.js"></script>
<?php }?>
</body>
</html>

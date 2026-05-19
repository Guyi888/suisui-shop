<?php
/**
 * 注册
**/
$is_defend=true;
include("../includes/common.php");
if($islogin3==1) {
	@header('Content-Type: text/html; charset=UTF-8');
	exit("<script language='javascript'>alert('您已登录！');window.location.href='./';</script>");
}
if($conf['sup_reg']==0) {
	@header('Content-Type: text/html; charset=UTF-8');
	exit("<script language='javascript'>alert('暂不开放供货商注册！');window.location.href='./login.php';</script>");
}
$title='供货商注册';

// 生成hashsalt
if($conf['verify_open']==1){
	$_SESSION['addsalt'] = $addsalt = random(32);
}

@header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
  <title><?php echo $title ?></title>
  <link href="//lib.baomitu.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <style>
    /* 全局样式 */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      background-color: #f2f2f7;
      color: #333;
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      position: relative;
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* 背景图片 */
    .bg-image {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      <?php if($conf['background_image_enable'] == 1 && $conf['background_type'] == 'image'){ echo 'background-image: url(\'../assets/img/bj.png\');'; } ?>
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      filter: brightness(0.9);
    }

    /* 容器样式 */
    .container {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      justify-content: center;
      align-items: center;
      padding: 20px;
      position: relative;
    }

    /* 注册卡片 */
    .reg-card {
      width: 100%;
      max-width: 420px;
      background-color: rgba(255, 255, 255, 0.92);
      border-radius: 20px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      overflow: hidden;
      padding: 45px 35px;
      margin-bottom: 20px;
      border: 1px solid rgba(255, 255, 255, 0.5);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    /* 电脑端横向布局 */
    @media (min-width: 1024px) {
      .container {
        flex-direction: row;
        justify-content: center;
        align-items: flex-start;
        gap: 40px;
        padding: 40px;
      }

      .reg-card {
        width: 50%;
        max-width: 500px;
        margin-bottom: 0;
        margin-top: 10vh;
      }

      .home-button {
        position: absolute;
        top: 40px;
        left: 40px;
        margin-bottom: 0;
      }
    }



    /* 标题样式 */
    .logo-container {
      text-align: center;
      margin-bottom: 35px;
      position: relative;
    }

    .logo {
      width: 100px;
      height: 100px;
      border-radius: 24px;
      margin-bottom: 20px;
      border: 4px solid rgba(255, 255, 255, 0.8);
      transition: transform 0.3s ease;
    }

    .logo:hover {
      transform: rotate(-5deg) scale(1.05);
    }

    h1 {
      font-size: 26px;
      font-weight: 700;
      color: #1c1c1e;
      margin-bottom: 10px;
      background: linear-gradient(90deg, #00bcd4, #03a9f4, #2196f3);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      display: inline-block;
    }

    .subtitle {
      font-size: 16px;
      color: #8e8e93;
      margin-bottom: 5px;
    }

    /* 表单样式 */
    .form-group {
      margin-bottom: 22px;
      position: relative;
    }

    .input-field {
      width: 100%;
      padding: 16px 16px;
      border: 2px solid #e0e0e5;
      border-radius: 12px;
      font-size: 17px;
      background-color: rgba(255, 255, 255, 0.85);
      transition: all 0.3s ease;
      font-family: inherit;
      color: #1c1c1e;
    }

    .input-field:focus {
      outline: none;
      border-color: #007aff;
      background-color: white;
      box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.1);
    }

    .input-field::placeholder {
      color: #8e8e93;
    }

    .input-icon {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #8e8e93;
      font-size: 18px;
    }

    /* 按钮样式 */
    .btn {
      width: 100%;
      padding: 16px;
      font-size: 17px;
      font-weight: 600;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-family: inherit;
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .btn-primary {
      background: linear-gradient(135deg, #007aff, #5ac8fa);
      color: white;
      box-shadow: 0 4px 15px rgba(0, 122, 255, 0.3);
    }

    .btn-primary:hover {
      box-shadow: 0 6px 20px rgba(0, 122, 255, 0.4);
      transform: translateY(-2px);
    }

    .btn-primary:active {
      transform: translateY(0);
    }

    .btn-secondary {
      background-color: #f2f2f7;
      color: #1c1c1e;
      border: 2px solid #e0e0e5;
    }

    .btn-secondary:hover {
      background-color: #e5e5ea;
      border-color: #d1d1d6;
    }

    /* 操作链接 */
    .action-links {
      display: flex;
      justify-content: space-between;
      margin-top: 25px;
    }

    .action-link {
      color: #007aff;
      text-decoration: none;
      font-size: 16px;
      font-weight: 500;
      transition: all 0.2s ease;
      position: relative;
      padding: 5px 0;
    }

    .action-link:hover {
      color: #0056b3;
    }

    .action-link::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 0;
      left: 0;
      background-color: #007aff;
      transition: width 0.3s ease;
    }

    .action-link:hover::after {
      width: 100%;
    }

    /* 返回首页按钮 */
    .home-button {
      background-color: rgba(255, 255, 255, 0.85);
      color: #007aff;
      border: 2px solid #e0e0e5;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      padding: 12px 24px;
      border-radius: 12px;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
      backdrop-filter: blur(5px);
      -webkit-backdrop-filter: blur(5px);
    }

    .home-button:hover {
      background-color: rgba(255, 255, 255, 0.95);
      border-color: #007aff;
      transform: translateY(-2px);
    }

    /* 加载状态 */
    .loading {
      text-align: center;
      color: #8e8e93;
      padding: 20px;
    }

    .loading-dots {
      display: inline-block;
    }

    .loading-dots span {
      display: inline-block;
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background-color: #007aff;
      margin: 0 3px;
      animation: loading 1.4s infinite ease-in-out both;
    }

    .loading-dots span:nth-child(1) { animation-delay: -0.32s; }
    .loading-dots span:nth-child(2) { animation-delay: -0.16s; }

    @keyframes loading {
      0%, 80%, 100% { transform: scale(0); }
      40% { transform: scale(1); background-color: #5ac8fa; }
    }

    /* 验证码样式 */
    #captchaform {
      margin-top: 15px;
    }

    /* 响应式设计 - 移动端 */
    @media (max-width: 480px) {
      .reg-card {
        padding: 35px 25px;
      }

      h1 {
        font-size: 24px;
      }

      .input-field, .btn {
        font-size: 16px;
      }

      .logo {
        width: 80px;
        height: 80px;
      }
    }

    /* 响应式调整 - 平板端 */
    @media (min-width: 768px) and (max-width: 1023px) {
      .reg-card {
        max-width: 500px;
      }
    }


  </style>
</head>
<body>
  <!-- 背景图片 -->
  <div class="bg-image"></div>

  <div class="container">
    <div class="reg-card">
      <div class="logo-container">
        <img src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'];?>&spec=100" alt="供货商" class="logo">
        <h1>供货商注册</h1>
        <p class="subtitle">欢迎加入我们，成为供应商</p>
      </div>

      <form>
        <div class="form-group">
          <input type="text" name="user" class="input-field" required="required" placeholder="用户名"/>
          <span class="input-icon"><i class="fa fa-user"></i></span>
        </div>

        <div class="form-group">
          <input type="password" name="pass" class="input-field" required="required" placeholder="密码"/>
          <span class="input-icon"><i class="fa fa-lock"></i></span>
        </div>

        <div class="form-group">
          <input type="text" name="qq" class="input-field" required="required" placeholder="QQ号"/>
          <span class="input-icon"><i class="fa fa-qq"></i></span>
        </div>

        <?php if($conf['verify_open']==1){?><input type="hidden" name="hashsalt" value="<?php echo $addsalt?>"/><?php }?>

        <!-- 简单文本验证码 -->
        <?php if(!$conf['captcha_open'] || $conf['captcha_open']>3){?>
        <div class="form-group">
          <input type="text" name="code" class="input-field" required="required" placeholder="验证码" style="width:60%;float:left;"/>
          <img src="./code.php" onclick="this.src='./code.php?'+Math.random()" style="width:35%;float:right;height:42px;cursor:pointer;border-radius:8px;"/>
          <div style="clear:both;"></div>
        </div>
        <?php }?>

        <?php if($conf['captcha_open']>=1){?>
          <input type="hidden" name="captcha_type" value="<?php echo $conf['captcha_open']?>"/>
          <?php if($conf['captcha_open']>=2){?><input type="hidden" name="appid" value="<?php echo $conf['captcha_id']?>"/><?php }?>
          <div id="captcha" style="text-align: center;">
            <div id="captcha_text" class="loading">
              正在加载验证码<span class="loading-dots"><span></span><span></span><span></span></span>
            </div>
            <div id="captchaform"></div>
          </div>
        <?php }?>

        <div class="form-group">
          <button type="button" id="submit_reg" class="btn btn-primary">立即注册</button>
        </div>

        <div class="action-links">
          <a href="findpwd.php" class="action-link">找回密码</a>
          <a href="login.php" class="action-link">已有账号，去登录</a>
        </div>

        <?php if($conf['forceloginhome']==1){ echo '<p style="text-align:center; color:#8e8e93; font-size:14px; margin-top:20px;">'.$conf['footer'].'</p>';}?>
      </form>
    </div>

    <a href="../" class="home-button">返回首页</a>
  </div>

  <script src="//lib.baomitu.com/jquery/1.12.4/jquery.min.js"></script>
  <script src="//lib.baomitu.com/layer/2.3/layer.js"></script>
  <script src="../assets/js/reguser.js?ver=<?php echo VERSION ?>"></script>
</body>
</html>
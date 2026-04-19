<?php
if(!defined('IN_CRONLITE'))exit();

// 定义password_hash变量，防止undefined variable错误
if(!isset($password_hash)) {
    $password_hash = '!@#%!s!0';
}

// 修复Undefined index: ip_type错误
$clientip=real_ip(isset($conf['ip_type']) ? $conf['ip_type'] : 0);

// 首先尝试从SESSION读取token，如果不存在则尝试从Cookie读取备份token
$admin_token = isset($_SESSION['admin_token']) ? $_SESSION['admin_token'] : (isset($_COOKIE['admin_token_backup']) ? $_COOKIE['admin_token_backup'] : null);

if($admin_token)
{
	$token=authcode(daddslashes($admin_token), 'DECODE', SYS_KEY);
	if(!empty($token)){ // 确保token解码成功
		list($admintypeid, $user, $sid, $time) = explode("\t", $token);
		if($admintypeid == '1'){
			if($adminuserrow = $DB->getRow("SELECT * FROM pre_account WHERE id='".intval($user)."' LIMIT 1")){
				$session=md5($adminuserrow['username'].$adminuserrow['password'].$password_hash);
				if($session===$sid && $adminuserrow['active']==1 && $time > time()) {
					$islogin=1;
					// 如果是从Cookie恢复的，同时恢复SESSION
					if(!isset($_SESSION['admin_token']) && isset($_COOKIE['admin_token_backup'])) {
						$_SESSION['admin_token'] = $_COOKIE['admin_token_backup'];
					}
					// 延长Cookie有效期
					if(isset($_COOKIE['admin_token_backup'])) {
						setcookie("admin_token_backup", $_COOKIE['admin_token_backup'], time() + 259200, '/', '', false, false);
					}
				}
			}
		}else{
			$session=md5($conf['admin_user'].$conf['admin_pwd'].$password_hash);
			if($session===$sid && $time > time()) {
				$islogin=1;
				// 如果是从Cookie恢复的，同时恢复SESSION
				if(!isset($_SESSION['admin_token']) && isset($_COOKIE['admin_token_backup'])) {
					$_SESSION['admin_token'] = $_COOKIE['admin_token_backup'];
				}
				// 延长Cookie有效期
				if(isset($_COOKIE['admin_token_backup'])) {
					setcookie("admin_token_backup", $_COOKIE['admin_token_backup'], time() + 259200, '/', '', false, false);
				}
			}
		}
	}
}
if(isset($_COOKIE["user_token"]))
{
	$token=authcode(daddslashes($_COOKIE['user_token']), 'DECODE', SYS_KEY);
	list($zid, $sid) = explode("\t", $token);
	if($userrow = $DB->getRow("SELECT * FROM pre_site WHERE zid='".intval($zid)."' LIMIT 1")){
		$session=md5($userrow['user'].$userrow['pwd'].$password_hash);
		if($session===$sid && $userrow['status']==1) {
			$islogin2=1;
		}
	}
}
if(isset($_COOKIE["sup_token"]))
{
    $token=authcode(daddslashes($_COOKIE['sup_token']), 'DECODE', SYS_KEY);
    list($sid, $ssid) = explode("\t", $token);
    if($suprow = $DB->getRow("SELECT * FROM pre_supplier WHERE sid='".intval($sid)."' LIMIT 1")){
        $session=md5($suprow['user'].$suprow['pwd'].$password_hash);
        if($session===$ssid && $suprow['status']==1) {
            $islogin3=1;
        }
    }
}
?>
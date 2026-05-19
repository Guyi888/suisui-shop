<?php

error_reporting(0);
if (defined('IN_CRONLITE')) {
	return;
}
define('CACHE_FILE', 0);
define('VERSION', '20260519');
define('IN_CRONLITE', true);
define('tingdong', '3530793519');
define('SYSTEM_ROOT', dirname(__FILE__) . '/');
define('ROOT', dirname(SYSTEM_ROOT) . '/');
define('TEMPLATE_ROOT', ROOT . 'template/');
define('PLUGIN_ROOT', ROOT . 'includes/plugins/');

date_default_timezone_set('Asia/Shanghai');
$date = date("Y-m-d H:i:s");
include_once SYSTEM_ROOT . 'base.php';
@header('Cache-Control: no-store, no-cache, must-revalidate');
@header('Pragma: no-cache');
// 优化Session配置
ini_set('session.gc_maxlifetime', 259200); // 3天
ini_set('session.cookie_lifetime', 259200); // 3天
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
session_start();
include_once SYSTEM_ROOT . "autoloader.php";
Autoloader::register();
if ($is_defend == true || CC_Defender == 3) {
	if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
		include_once SYSTEM_ROOT . 'txprotect.php';
	}
	if (CC_Defender == 1 && check_spider() == false) {
	}
	if (CC_Defender == 1 && check_spider() == false || CC_Defender == 3) {
		cc_defender();
	}
}
$scriptpath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$sitepath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
$siteurl = ($_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $sitepath . '/';
if (is_file(SYSTEM_ROOT . '360safe/360webscan.php')) {
	require_once SYSTEM_ROOT . '360safe/360webscan.php';
}
require_once SYSTEM_ROOT . '360safe/xss.php';
require ROOT . 'config.php';
define('DBQZ', $dbconfig['dbqz']);

function checkDatabaseError($dbconfig, $exception = null)
{
	$error_info = array();

	if (!defined('SQLITE') && (!$dbconfig['user'] || !$dbconfig['pwd'] || !$dbconfig['dbname'])) {
		$error_info[] = '数据库配置信息不完整，请检查config.php文件中的数据库配置';
		if (!$dbconfig['user']) {
			$error_info[] = '缺少数据库用户名';
		}
		if (!$dbconfig['pwd']) {
			$error_info[] = '缺少数据库密码';
		}
		if (!$dbconfig['dbname']) {
			$error_info[] = '缺少数据库名称';
		}
		return $error_info;
	}

	if ($exception) {
		$error_msg = $exception->getMessage();

		if (strpos($error_msg, 'Access denied') !== false || strpos($error_msg, '1045') !== false) {
			$error_info[] = '数据库账号或密码错误';
			$error_info[] = '请检查config.php文件中的数据库用户名和密码是否正确';
		} elseif (strpos($error_msg, 'Unknown database') !== false || strpos($error_msg, '1049') !== false) {
			$error_info[] = '数据库不存在';
			$error_info[] = '请检查config.php文件中的数据库名称是否正确';
		} elseif (strpos($error_msg, 'Connection refused') !== false || strpos($error_msg, '2002') !== false || strpos($error_msg, "Can't connect to MySQL server") !== false || strpos($error_msg, '2003') !== false) {
			$mysql_service_status = checkMySQLService($dbconfig);
			if ($mysql_service_status === 'running') {
				$error_info[] = 'MySQL服务已启动，但无法连接';
				$error_info[] = '请检查config.php文件中的数据库主机地址和端口是否正确';
			} elseif ($mysql_service_status === 'not_running') {
				$error_info[] = 'MySQL服务未启动';
				$error_info[] = '请前往宝塔面板重启或开启MySQL数据库服务';
				$error_info[] = '宝塔面板 -> 软件商店 -> MySQL -> 设置 -> 重启/启动';
			} elseif ($mysql_service_status === 'not_installed') {
				$error_info[] = 'MySQL服务未安装或数据库没开启';
				$error_info[] = '请前往宝塔面板安装或开启MySQL数据库';
				$error_info[] = '宝塔面板 -> 软件商店 -> 搜索MySQL -> 安装/开启';
			} else {
				$error_info[] = '无法连接到MySQL服务器';
				$error_info[] = '请检查MySQL服务是否已启动';
				$error_info[] = '请检查config.php文件中的数据库主机地址和端口是否正确';
			}
		} elseif (strpos($error_msg, 'Table') !== false && strpos($error_msg, "doesn't exist") !== false) {
			$error_info[] = '数据库表不存在';
			$error_info[] = '请检查数据库是否已正确安装';
			$error_info[] = '如果直接覆盖新版本，请修改config.php配置后访问，不要重新安装';
		} else {
			$error_info[] = '数据库连接失败：' . $error_msg;
			$error_info[] = '请检查MySQL服务是否正常运行';
			$error_info[] = '请检查config.php文件中的数据库配置是否正确';
		}
	} else {
		$error_info[] = '数据库连接失败';
		$error_info[] = '请检查MySQL服务是否已启动';
		$error_info[] = '请检查config.php文件中的数据库配置是否正确';
	}

	return $error_info;
}

function checkMySQLService($dbconfig)
{
	$host = $dbconfig['host'];
	$port = isset($dbconfig['port']) ? $dbconfig['port'] : 3306;

	if ($host == 'localhost' || $host == '127.0.0.1') {
		$os = strtoupper(substr(PHP_OS, 0, 3));

		if ($os === 'WIN') {
			$port_check = @fsockopen($host, $port, $errno, $errstr, 2);
			if ($port_check) {
				fclose($port_check);
				return 'running';
			}

			$service_names = array('MySQL', 'MySQL80', 'MySQL57', 'MySQL56', 'mariadb');
			foreach ($service_names as $service_name) {
				@exec('sc query "' . $service_name . '" 2>&1', $output, $return_var);
				if (is_array($output) && count($output) > 0) {
					$output_str = implode(' ', $output);
					if (strpos($output_str, 'RUNNING') !== false) {
						return 'running';
					} elseif (strpos($output_str, 'STOPPED') !== false || strpos($output_str, '1060') !== false) {
						return 'not_running';
					}
				}
			}
			return 'not_installed';
		} else {
			$port_check = @fsockopen($host, $port, $errno, $errstr, 2);
			if ($port_check) {
				fclose($port_check);
				return 'running';
			}

			@exec('systemctl is-active mysql 2>&1', $output, $return_var);
			if (isset($output[0]) && $output[0] == 'active') {
				return 'running';
			}

			@exec('systemctl is-active mariadb 2>&1', $output, $return_var);
			if (isset($output[0]) && $output[0] == 'active') {
				return 'running';
			}

			@exec('service mysql status 2>&1', $output, $return_var);
			if (is_array($output) && count($output) > 0) {
				$output_str = implode(' ', $output);
				if (strpos($output_str, 'running') !== false || strpos($output_str, 'active') !== false) {
					return 'running';
				}
			}

			return 'not_running';
		}
	} else {
		$port_check = @fsockopen($host, $port, $errno, $errstr, 2);
		if ($port_check) {
			fclose($port_check);
			return 'running';
		}
		return 'not_running';
	}
}

function showDatabaseError($error_info)
{
	$tips_html = '';
	foreach ($error_info as $tip) {
		$tips_html .= '<div>' . htmlspecialchars($tip) . '</div>';
	}

	$install_lock_exists = file_exists(ROOT . 'install/install.lock');

	if ($install_lock_exists) {
		$tips_html .= '<div style="color:red;margin-top:15px;padding-top:15px;border-top:1px solid #eee;"> 检测到install.lock文件存在，说明系统已安装过。<br> 诺第一次安装请删掉install\install.lock</div>';
		$tips_html .= '<div style="color:red;">请直接修改网站根目录config.php文件内的数据库配置</div>';
		$tips_html .= '<div style="color:red;">不要点击"立即安装"按钮，否则会清空现有数据！</div>';
		$btn_html = '<a href="/" class="btn">返回首页</a>';
	} else {
		$tips_html .= '<div style="margin-top:15px;padding-top:15px;border-top:1px solid #eee;">如果数据库配置正确但仍无法连接，请检查MySQL服务是否正常运行</div>';
		$btn_html = '<a href="/install/" class="btn">立即安装</a>';
	}

	header('Content-type:text/html;charset=utf-8');
	echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>数据库连接失败</title><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;background:#f5f5f5;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}.container{background:#fff;border-radius:8px;padding:40px;max-width:600px;width:100%;box-shadow:0 2px 10px rgba(0,0,0,0.1)}h1{color:#333;font-size:24px;margin-bottom:20px;text-align:center}.tips{color:#666;font-size:14px;line-height:1.8;margin-bottom:30px}.tips div{margin-bottom:10px}.btn{display:block;width:100%;padding:12px;background:#12b7f5;color:#fff;text-decoration:none;text-align:center;border-radius:4px;font-size:16px;transition:background 0.3s}.btn:hover{background:#0a96d8}</style></head><body><div class="container"><h1>数据库连接失败</h1><div class="tips">' . $tips_html . '</div>' . $btn_html . '</div></body></html>';
	exit;
}

if (!defined('SQLITE') && !$dbconfig['user'] || !$dbconfig['pwd'] || !$dbconfig['dbname']) {
	$error_info = checkDatabaseError($dbconfig);
	showDatabaseError($error_info);
}
try {
	$DB = new \lib\PdoHelper($dbconfig);
	if ($DB->query("select * from pre_config where 1") == FALSE) {
		$error_info = array(
			'数据库表不存在或查询失败',
			'请检查数据库是否已正确安装',
			'如果直接覆盖新版本，请修改config.php配置后访问，不要重新安装'
		);
		showDatabaseError($error_info);
	}
} catch (\Exception $e) {
	$error_info = checkDatabaseError($dbconfig, $e);
	showDatabaseError($error_info);
}
$CACHE = new \lib\Cache();
$conf = $CACHE->pre_fetch();
define('SYS_KEY', $conf['syskey']);

if ($conf['qqjump'] == 1 && (!strpos($_SERVER['HTTP_USER_AGENT'], 'QQ/') === false || !strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false)) {
	if ($_GET['open'] == 1 && !strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false) {
		header('Content-Disposition: attachment; filename="load.doc"');
		header('Content-Type: application/vnd.ms-word;charset=utf-8');
	} else {
		header('Content-type:text/html;charset=utf-8');
	}
	include ROOT . 'template/default/jump.php';
	exit(0);
}
$password_hash = $conf['syskey'];
include_once SYSTEM_ROOT . "function.php";
include_once SYSTEM_ROOT . "core.func.php";
include_once SYSTEM_ROOT . "ajax.func.php";
include_once SYSTEM_ROOT . "member.php";

if (!file_exists(ROOT . 'install/install.lock') && file_exists(ROOT . 'install/index.php')) {
	sysmsg('<h2>检测到无 install.lock 文件</h2><ul><li><font size="4">如果您尚未安装本程序，请<a href="/install/">前往安装</a></font></li><li><font size="4">如果您已经安装本程序，请手动放置一个空的 install.lock 文件到 /install 文件夹下，<b>为了您站点安全，在您完成它之前我们不会工作。</b></font></li></ul><br/><h4>为什么必须建立 install.lock 文件？</h4>它是安装保护文件，如果检测不到它，就会认为站点还没安装，此时任何人都可以安装/重装你的网站。<br/><br/>');
	exit;
}

$cookiesid = $_COOKIE['mysid'];
if (!$cookiesid || !preg_match('/^[0-9a-z]{32}$/i', $cookiesid)) {
	$cookiesid = md5(uniqid(mt_rand(), 1) . time());
	setcookie('mysid', $cookiesid, time() + 604800, '/');
}
if (isset($_COOKIE['invite'])) {
	$invite_id = intval($_COOKIE['invite']);
}
$domain = addslashes($_SERVER['HTTP_HOST']);
$siterow = $DB->getRow("SELECT * FROM pre_site WHERE domain=:domain OR domain2=:domain OR domain3=:domain OR domain4=:domain OR domain5=:domain OR domain6=:domain LIMIT 1", array(':domain' => $domain));
if ($siterow && $siterow['status'] == 1) {
	$is_fenzhan = true;
	if ($siterow['template'] == NULL || $conf['fenzhan_template'] == 0) {
		$siterow['template'] = $conf['template'];
	}
	$conf = array_merge($conf, $siterow);
	$conf['kfqq'] = $conf['qq'];
} else {
	$is_fenzhan = false;
}

function x_real_ip()
{
	$ip = $_SERVER['REMOTE_ADDR'];
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all("#\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}#s", $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
		foreach ($matches[0] as $xip) {
			if (!preg_match("#^(10|172\\.16|192\\.168)\\.#", $xip)) {
				$ip = $xip;
			}
		}
	} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
		$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
	} elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match("/^([0-9]{1,3}\\.){3}[0-9]{1,3}\$/", $_SERVER['HTTP_X_REAL_IP'])) {
		$ip = $_SERVER['HTTP_X_REAL_IP'];
	}
	return $ip;
}

function check_spider()
{
	$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if (strpos($useragent, 'baiduspider') !== false) {
		return 'baiduspider';
	}
	if (strpos($useragent, 'googlebot') !== false) {
		return 'googlebot';
	}
	if (strpos($useragent, '360spider') !== false) {
		return '360spider';
	}
	if (strpos($useragent, 'soso') !== false) {
		return 'soso';
	}
	if (strpos($useragent, 'bing') !== false) {
		return 'bing';
	}
	if (strpos($useragent, 'yahoo') !== false) {
		return 'yahoo';
	}
	if (strpos($useragent, 'sohu-search') !== false) {
		return 'Sohubot';
	}
	if (strpos($useragent, 'sogou') !== false) {
		return 'sogou';
	}
	if (strpos($useragent, 'youdaobot') !== false) {
		return 'YoudaoBot';
	}
	if (strpos($useragent, 'robozilla') !== false) {
		return 'Robozilla';
	}
	if (strpos($useragent, 'msnbot') !== false) {
		return 'msnbot';
	}
	if (strpos($useragent, 'lycos') !== false) {
		return 'Lycos';
	}
	if (!strpos($useragent, 'ia_archiver') === false) {
	} elseif (!strpos($useragent, 'iaarchiver') === false) {
		return 'alexa';
	}
	if (strpos($useragent, 'archive.org_bot') !== false) {
		return 'Archive';
	}
	if (strpos($useragent, 'sitebot') !== false) {
		return 'SiteBot';
	}
	if (strpos($useragent, 'gosospider') !== false) {
		return 'gosospider';
	}
	if (strpos($useragent, 'gigabot') !== false) {
		return 'Gigabot';
	}
	if (strpos($useragent, 'yrspider') !== false) {
		return 'YRSpider';
	}
	if (strpos($useragent, 'gigabot') !== false) {
		return 'Gigabot';
	}
	if (strpos($useragent, 'wangidspider') !== false) {
		return 'WangIDSpider';
	}
	if (strpos($useragent, 'foxspider') !== false) {
		return 'FoxSpider';
	}
	if (strpos($useragent, 'docomo') !== false) {
		return 'DoCoMo';
	}
	if (strpos($useragent, 'yandexbot') !== false) {
		return 'YandexBot';
	}
	if (strpos($useragent, 'sinaweibobot') !== false) {
		return 'SinaWeiboBot';
	}
	if (strpos($useragent, 'catchbot') !== false) {
		return 'CatchBot';
	}
	if (strpos($useragent, 'surveybot') !== false) {
		return 'SurveyBot';
	}
	if (strpos($useragent, 'dotbot') !== false) {
		return 'DotBot';
	}
	if (strpos($useragent, 'purebot') !== false) {
		return 'Purebot';
	}
	if (strpos($useragent, 'ccbot') !== false) {
		return 'CCBot';
	}
	if (strpos($useragent, 'mlbot') !== false) {
		return 'MLBot';
	}
	if (strpos($useragent, 'adsbot-google') !== false) {
		return 'AdsBot-Google';
	}
	if (strpos($useragent, 'ahrefsbot') !== false) {
		return 'AhrefsBot';
	}
	if (strpos($useragent, 'spbot') !== false) {
		return 'spbot';
	}
	if (strpos($useragent, 'augustbot') !== false) {
		return 'AugustBot';
	}
	return false;
}

function cc_defender()
{
	$iptoken = md5(x_real_ip() . date('Ymd')) . md5(time() . rand(11111, 99999));
	if (!isset($_COOKIE['sec_defend']) || substr($_COOKIE['sec_defend'], 0, 32) !== substr($iptoken, 0, 32)) {
		if (!$_COOKIE['sec_defend_time']) {
			$_COOKIE['sec_defend_time'] = 0;
		}
		$x = new \lib\hieroglyphy();
		$setCookie = $x->hieroglyphyString($iptoken);
		$sec_defend_time = $_COOKIE['sec_defend_time'] + 1;
		header('Content-type:text/html;charset=utf-8');
		if ($sec_defend_time >= 10) {
			exit('浏览器不支持COOKIE或者不正常访问！');
		}
		echo '<html><head><meta http-equiv="pragma" content="no-cache"><meta http-equiv="cache-control" content="no-cache"><meta http-equiv="content-type" content="text/html;charset=utf-8"><title>正在加载中</title><script>function setCookie(name,value){var exp = new Date();exp.setTime(exp.getTime() + 60*60*1000);document.cookie = name + "="+ escape (value).replace(/\\+/g, \'%2B\') + ";expires=" + exp.toGMTString() + ";path=/";}function getCookie(name){var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");if(arr=document.cookie.match(reg))return unescape(arr[2]);else return null;}var sec_defend_time=getCookie(\'sec_defend_time\')||0;sec_defend_time++;setCookie(\'sec_defend\',' . $setCookie . ');setCookie(\'sec_defend_time\',sec_defend_time);if(sec_defend_time>1)window.location.href="./index.php";else window.location.reload();</script></head><body></body></html>';
		exit(0);
	} else {
		if (isset($_COOKIE['sec_defend_time'])) {
			setcookie('sec_defend_time', '', time() - 604800, '/');
		}
	}
}

function copydirs($source, $dest)
{
	if (!is_dir($dest)) {
		mkdir($dest, 0755, true);
	}
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
	foreach ($iterator as $item) {
		if ($item->isDir()) {
			$sent_dir = $dest . "/" . $iterator->getSubPathName();
			if (!is_dir($sent_dir)) {
				mkdir($sent_dir, 0755, true);
			}
		} else {
			copy($item, $dest . "/" . $iterator->getSubPathName());
		}
	}
}

function rmdirs($dir, $rmself = true)
{
	if (!is_dir($dir)) {
		return false;
	}
	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
	foreach ($files as $file) {
		$todo = $file->isDir() ? 'rmdir' : 'unlink';
		$todo($file->getRealPath());
	}
	if ($rmself) {
		@rmdir($dir);
	}
	return true;
}


// 防墙引导页功能检测
if($conf['wall_guide_open'] == 1) {
    $is_system_file = strpos($_SERVER['PHP_SELF'], 'admin/') !== false || strpos($_SERVER['PHP_SELF'], 'install/') !== false || strpos($_SERVER['PHP_SELF'], 'api/') !== false || strpos($_SERVER['PHP_SELF'], 'api.php') !== false || strpos($_SERVER['PHP_SELF'], 'cron.php') !== false;
    if(!$is_system_file) {
        // 只检查 cookie 是否存在，浏览器会自动处理过期
        if(!isset($_COOKIE['wall_guide_skip'])) {
            include TEMPLATE_ROOT . 'wall_guide.php';
            exit;
        }
    }
}

// 授权验证文件已删除，系统不再需要授权验证
// 引入授权验证文件
// if(!strpos($_SERVER['PHP_SELF'], 'install/') && !strpos($_SERVER['PHP_SELF'], 'api/') && !strpos($_SERVER['PHP_SELF'], 'cron.php')) {
//     include_once SYSTEM_ROOT . 'auth.php';
// }



/**
 * 域名落地页跳转功能
 * 岁岁 @qqfaka
 * Q群：qqfaka
 * 作者：岁岁 @qqfaka
 * 功能说明：根据配置的域名跳转规则，自动将旧域名跳转到新域名
 * 支持通配符匹配，如 *.old.com -> *.new.com
 */
if(!$is_fenzhan && !strpos($_SERVER['PHP_SELF'], 'admin/') && !strpos($_SERVER['PHP_SELF'], 'install/') && !strpos($_SERVER['PHP_SELF'], 'api/') && !strpos($_SERVER['PHP_SELF'], 'cron.php')) {
    $current_host = strtolower($_SERVER['HTTP_HOST']);
    $current_host = preg_replace('/^www\./', '', $current_host);

    $landing_rules = $DB->getAll("SELECT * FROM pre_domain_landing ORDER BY id DESC");

    foreach($landing_rules as $rule) {
        $old_domain = $rule['old_domain'];
        $new_domain = $rule['new_domain'];

        $is_match = false;
        $wildcard_value = '';

        if(strpos($old_domain, '*') !== false) {
            $pattern = str_replace('\*', '([^\.]+)', preg_quote($old_domain, '/'));
            if(preg_match('/^' . $pattern . '$/i', $current_host, $matches)) {
                $is_match = true;
                $wildcard_value = isset($matches[1]) ? $matches[1] : '';
            }
        } else {
            if($current_host === $old_domain) {
                $is_match = true;
            }
        }

        if($is_match) {
            $redirect_domain = $new_domain;
            if($wildcard_value && strpos($new_domain, '*') !== false) {
                $redirect_domain = str_replace('*', $wildcard_value, $new_domain);
            }

            $redirect_domain = preg_replace('/^www\./', '', $redirect_domain);

            if($redirect_domain !== $current_host) {
                $request_uri = $_SERVER['REQUEST_URI'];
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
                $redirect_url = $protocol . '://' . $redirect_domain . $request_uri;

                header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . $redirect_url);
                exit;
            }

            break;
        }
    }
}

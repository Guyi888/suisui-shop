<?php
require '../includes/common.php';
if ($islogin2 != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

q8_site_admin_notice_ensure_fields();
if (function_exists('q8_site_markup_template_ensure_fields')) {
    q8_site_markup_template_ensure_fields();
}

$mod = isset($_GET['mod']) ? trim($_GET['mod']) : 'site';
$allowedMods = array('user', 'user_n', 'site', 'site_n', 'site2', 'logo', 'skimg', 'upwxqrcode');
if (!in_array($mod, $allowedMods, true)) {
    $mod = $userrow['power'] > 0 ? 'site' : 'user';
}

$pageTitles = array(
    'user' => '资料设置',
    'user_n' => '资料设置',
    'site' => '站点信息设置',
    'site_n' => '站点信息设置',
    'site2' => '前台模板设置',
    'logo' => '首页 Logo 设置',
    'skimg' => '收款图设置',
    'upwxqrcode' => '客服微信二维码',
);
$title = isset($pageTitles[$mod]) ? $pageTitles[$mod] : '站点设置';

include 'head.php';

if ($conf['fenzhan_cost2'] <= 0) {
    $conf['fenzhan_cost2'] = $conf['fenzhan_price2'];
}

$mblist = \lib\Template::getList();
$q8_price_rule_rows = $userrow['power'] > 0 ? q8_price_rule_fetch_rows($userrow['zid']) : array();
$q8_current_site_price_rule_row = null;
$q8_current_site_price_rule_legacy = false;
if ($userrow['power'] > 0 && !empty($userrow['site_prid'])) {
    $q8_current_site_price_rule_row = q8_price_rule_fetch_row(intval($userrow['site_prid']));
    if ($q8_current_site_price_rule_row) {
        $q8_current_site_price_rule_legacy = intval($q8_current_site_price_rule_row['zid']) !== intval($userrow['zid']);
    }
}
$q8_default_template_name = q8_template_name_resolve(isset($conf['template']) ? $conf['template'] : '', 'XHY-01');
$q8_default_template_label = \lib\Template::getDisplayName($q8_default_template_name);
$q8_user_template_label = \lib\Template::getDisplayName(isset($userrow['template']) ? $userrow['template'] : '');
$q8_template_uses_default = !q8_template_name_is_valid(isset($userrow['template']) ? $userrow['template'] : '');
$q8_admin_notice_modes = function_exists('q8_site_admin_notice_mode_labels')
    ? q8_site_admin_notice_mode_labels()
    : array(
        0 => '仅显示主站公告',
        1 => '主站公告 + 分站自定义',
        2 => '仅显示分站自定义',
    );

function q8_user_setting_hidden($name, $value)
{
    return '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '">';
}

function q8_user_setting_alert($message, $goBack = true)
{
    $message = str_replace(array('\\', "'"), array('\\\\', "\\'"), $message);
    if ($goBack) {
        exit("<script language='javascript'>alert('{$message}');history.go(-1);</script>");
    }
    exit("<script language='javascript'>alert('{$message}');window.location.href='./usetmoban.php';</script>");
}

if ($mod === 'user_n') {
    if (!checkRefererHost()) exit();

    $qq = daddslashes(htmlspecialchars(strip_tags(isset($_POST['qq']) ? $_POST['qq'] : '')));
    $pay_type = daddslashes(intval(isset($_POST['pay_type']) ? $_POST['pay_type'] : 0));
    $pay_account = daddslashes(htmlspecialchars(strip_tags(isset($_POST['pay_account']) ? $_POST['pay_account'] : '')));
    $pay_name = daddslashes(htmlspecialchars(strip_tags(isset($_POST['pay_name']) ? $_POST['pay_name'] : '')));
    $pwd = daddslashes(htmlspecialchars(strip_tags(isset($_POST['pwd']) ? $_POST['pwd'] : '')));

    if (!empty($pwd) && !preg_match('/^[a-zA-Z0-9\_\!\@\#\$~\%\^\&\*.,]+$/', $pwd)) {
        q8_user_setting_alert('密码只能包含英文、数字和常见安全符号');
    }
    if (!preg_match('/^[0-9]{5,11}+$/', $qq)) {
        q8_user_setting_alert('QQ 格式不正确');
    }

    $DB->exec(
        "UPDATE pre_site SET qq=:qq,pay_type=:pay_type,pay_account=:pay_account,pay_name=:pay_name WHERE zid=:zid",
        array(':qq' => $qq, ':pay_type' => $pay_type, ':pay_account' => $pay_account, ':pay_name' => $pay_name, ':zid' => $userrow['zid'])
    );
    if (!empty($pwd)) {
        $DB->exec("UPDATE pre_site SET pwd=:pwd WHERE zid=:zid", array(':pwd' => $pwd, ':zid' => $userrow['zid']));
    }
    q8_user_setting_alert('资料保存成功');
}

if ($mod === 'site_n') {
    if ($userrow['power'] <= 0) {
        showmsg('你没有权限使用此功能！', 3);
    }
    if (!checkRefererHost()) exit();

    $sitename = trim(htmlspecialchars(strip_tags(isset($_POST['sitename']) ? $_POST['sitename'] : '')));
    $siteTitle = trim(htmlspecialchars(strip_tags(isset($_POST['title']) ? $_POST['title'] : '')));
    $keywords = trim(htmlspecialchars(strip_tags(isset($_POST['keywords']) ? $_POST['keywords'] : '')));
    $description = trim(htmlspecialchars(strip_tags(isset($_POST['description']) ? $_POST['description'] : '')));
    $kfqq = trim(htmlspecialchars(strip_tags(isset($_POST['kfqq']) ? $_POST['kfqq'] : '')));
    $kfwx = trim(htmlspecialchars(strip_tags(isset($_POST['kfwx']) ? $_POST['kfwx'] : '')));
    $anounce = isset($_POST['anounce']) ? $_POST['anounce'] : '';
    $modal = isset($_POST['modal']) ? $_POST['modal'] : '';
    $bottom = isset($_POST['bottom']) ? $_POST['bottom'] : '';
    $alert = isset($_POST['alert']) ? $_POST['alert'] : '';
    $admin_notice_panel = isset($_POST['admin_notice_panel']) ? $_POST['admin_notice_panel'] : '';
    $admin_notice_mode = function_exists('q8_site_admin_notice_normalize_mode')
        ? q8_site_admin_notice_normalize_mode(isset($_POST['admin_notice_mode']) ? $_POST['admin_notice_mode'] : 1)
        : 1;
    $ktfz_price = trim(isset($_POST['ktfz_price']) ? $_POST['ktfz_price'] : '');
    $ktfz_price2 = trim(isset($_POST['ktfz_price2']) ? $_POST['ktfz_price2'] : '');
    $site_prid = isset($_POST['site_prid']) ? intval($_POST['site_prid']) : 0;
    $template = q8_template_name_for_save(isset($_POST['template']) ? $_POST['template'] : '');
    $appurl = trim(isset($_POST['appurl']) ? $_POST['appurl'] : '');
    $siteHasModalShowType = function_exists('q8_site_has_column') ? q8_site_has_column('modal_show_type') : false;
    $siteHasSitePrid = function_exists('q8_site_has_column') ? q8_site_has_column('site_prid') : false;

    if ($sitename === '') {
        q8_user_setting_alert('请填写站点名称');
    }
    if ($template === false) {
        q8_user_setting_alert('所选模板不存在或没有首页文件');
    }
    if ($site_prid < 0) {
        $site_prid = 0;
    }
    if ($site_prid > 0) {
        $siteRuleExists = q8_price_rule_exists_for_owner($site_prid, $userrow['zid']);
        $siteRuleLegacySelected = intval($userrow['site_prid']) === $site_prid && q8_price_rule_fetch_row($site_prid) !== false;
        if (!$siteRuleExists && !$siteRuleLegacySelected) {
            q8_user_setting_alert('所选的分站加价模板不存在');
        }
    }
    if ($userrow['power'] == 2) {
        if (!is_numeric($ktfz_price) || !preg_match('/^[0-9.]+$/', $ktfz_price) || $ktfz_price < 0) {
            q8_user_setting_alert('普及版分站价格输入不规范');
        }
        if (!is_numeric($ktfz_price2) || !preg_match('/^[0-9.]+$/', $ktfz_price2) || $ktfz_price2 < 0) {
            q8_user_setting_alert('专业版分站价格输入不规范');
        }
        if ($ktfz_price2 < $conf['fenzhan_cost2']) {
            q8_user_setting_alert('专业版分站价格不能低于成本价');
        }
        if ($ktfz_price2 < $ktfz_price) {
            q8_user_setting_alert('专业版分站价格不能低于普及版分站价格');
        }
    }

    $data = array(
        ':sitename' => $sitename,
        ':title' => $siteTitle,
        ':keywords' => $keywords,
        ':description' => $description,
        ':kfqq' => $kfqq,
        ':kfwx' => $kfwx,
        ':admin_notice_panel' => $admin_notice_panel,
        ':admin_notice_mode' => $admin_notice_mode,
        ':template' => $template,
        ':appurl' => $appurl,
        ':zid' => $userrow['zid'],
    );

    $fields = array(
        '`sitename`=:sitename',
        '`title`=:title',
        '`keywords`=:keywords',
        '`description`=:description',
        '`kfqq`=:kfqq',
        '`kfwx`=:kfwx',
        '`admin_notice_panel`=:admin_notice_panel',
        '`admin_notice_mode`=:admin_notice_mode',
        '`template`=:template',
        '`appurl`=:appurl',
    );
    if ($siteHasSitePrid) {
        $fields[] = '`site_prid`=:site_prid';
        $data[':site_prid'] = $site_prid;
    }

    if ($conf['fenzhan_edithtml'] == 1) {
        $modal_show_type = isset($_POST['modal_show_type']) ? intval($_POST['modal_show_type']) : 0;
        $fields[] = '`anounce`=:anounce';
        $fields[] = '`modal`=:modal';
        $fields[] = '`bottom`=:bottom';
        $fields[] = '`alert`=:alert';
        $data[':anounce'] = $anounce;
        $data[':modal'] = $modal;
        $data[':bottom'] = $bottom;
        $data[':alert'] = $alert;
        if ($siteHasModalShowType) {
            $fields[] = '`modal_show_type`=:modal_show_type';
            $data[':modal_show_type'] = $modal_show_type;
        }
    }

    if ($userrow['power'] == 2) {
        $fields[] = '`ktfz_price`=:ktfz_price';
        $fields[] = '`ktfz_price2`=:ktfz_price2';
        $data[':ktfz_price'] = $ktfz_price;
        $data[':ktfz_price2'] = $ktfz_price2;
    }

    $sql = "UPDATE `pre_site` SET " . implode(',', $fields) . " WHERE `zid`=:zid";
    $result = $DB->exec($sql, $data);
    if ($result !== false) {
        q8_user_setting_alert('站点设置保存成功');
    }
    q8_user_setting_alert('站点设置保存失败：' . $DB->error());
}
?>
<link rel="stylesheet" href="./public/css/blue_theme.css">
<div class="wrapper">
    <div class="col-sm-12">
        <?php if ($mod === 'user') { ?>
        <?php
            $url = 'https://api.fcypay.com/';
            $mark = md5(rand(1000000, 9999999) . date('YmdHis') . uniqid());
            $code_url = $url . 'get_openid_qrcode?mark=' . $mark;
            $cron_url = $url . 'get_openid_status?mark=' . $mark;
        ?>
        <div class="panel panel-default">
            <div class="panel-heading font-bold">用户资料设置</div>
            <div class="panel-body">
                <form action="./usetmoban.php?mod=user_n" method="post" role="form">
                    <?php if ($conf['login_qq'] == 1) { ?>
                    <div class="form-group">
                        <label><img src="https://qzonestyle.gtimg.cn/qzone/vas/opensns/res/img/bt_blue_24X24.png" alt="QQ">&nbsp;QQ 快捷登录</label>
                        <?php if ($userrow['qq_openid']) { ?>
                        <span class="text-success">已绑定</span>
                        <button type="button" class="btn btn-xs btn-default" data-bind-action="unbind" data-bind-type="qq">解除绑定</button>
                        <?php } else { ?>
                        <span class="text-danger">未绑定</span>
                        <button type="button" class="btn btn-xs btn-success" data-bind-action="connect" data-bind-type="qq">立即绑定</button>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <?php if ($conf['login_wx'] == 1) { ?>
                    <div class="form-group">
                        <label><img src="https://res.wx.qq.com/a/wx_fed/assets/res/OTE0YTAw.png" width="24" alt="WeChat">&nbsp;微信快捷登录</label>
                        <?php if ($userrow['wx_openid']) { ?>
                        <span class="text-success">已绑定</span>
                        <button type="button" class="btn btn-xs btn-default" data-bind-action="unbind" data-bind-type="wx">解除绑定</button>
                        <?php } else { ?>
                        <span class="text-danger">未绑定</span>
                        <button type="button" class="btn btn-xs btn-success" data-bind-action="connect" data-bind-type="wx">立即绑定</button>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <div class="form-group">
                        <label>登录账号</label>
                        <input type="text" value="<?php echo htmlspecialchars($userrow['user'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                        <label>联系 QQ</label>
                        <input type="text" name="qq" value="<?php echo htmlspecialchars($userrow['qq'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="用于联系与找回密码" required>
                    </div>

                    <?php if ($userrow['power'] > 0) { ?>
                    <div class="form-group">
                        <label>提现方式</label>
                        <select class="form-control" name="pay_type" data-default="<?php echo intval($userrow['pay_type']); ?>">
                            <?php if ($conf['fenzhan_tixian_alipay'] == 1) { ?><option value="0">支付宝</option><?php } ?>
                            <?php if ($conf['fenzhan_tixian_wx'] == 1) { ?><option value="1">微信</option><?php } ?>
                            <?php if ($conf['fenzhan_tixian_qq'] == 1) { ?><option value="2">QQ 钱包</option><?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>提现账号</label>
                        <input type="text" name="pay_account" value="<?php echo htmlspecialchars($userrow['pay_account'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
                        <a href="javascript:;" class="btn btn-info" style="display:none" id="getopenid">自动获取</a>
                    </div>
                    <div class="form-group">
                        <label>提现姓名</label>
                        <input type="text" name="pay_name" value="<?php echo htmlspecialchars($userrow['pay_name'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
                    </div>
                    <?php } ?>

                    <?php if (substr($userrow['user'], 0, 3) != 'qq_') { ?>
                    <div class="form-group">
                        <label>重置密码</label>
                        <input type="text" name="pwd" value="" class="form-control" placeholder="不修改请留空">
                    </div>
                    <?php } ?>

                    <div class="form-group">
                        <input type="submit" name="submit" value="保存资料" class="btn btn-primary form-control">
                    </div>
                </form>
            </div>
        </div>

        <?php if (substr($userrow['user'], 0, 3) == 'qq_') { ?>
        <div class="panel panel-default">
            <div class="panel-heading font-bold">设置独立登录账号和密码</div>
            <div class="panel-body">
                <div class="alert alert-info">设置完成后，可以直接用账号密码登录，也不影响 QQ 快捷登录。</div>
                <form id="setpwdForm" method="post" role="form">
                    <div class="form-group">
                        <label>登录账号</label>
                        <input type="text" name="user" placeholder="输入新的登录账号" class="form-control" required>
                        <div class="help-block text-success">登录账号设置后将不支持再次修改。</div>
                    </div>
                    <div class="form-group">
                        <label>登录密码</label>
                        <input type="text" name="pwd" placeholder="输入 6 位以上新密码" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <input type="submit" name="submit" value="保存账号信息" class="btn btn-primary form-control">
                    </div>
                </form>
            </div>
        </div>
        <?php } ?>

        <?php } elseif ($mod === 'site' && $userrow['power'] > 0) { ?>
        <div class="panel panel-default">
            <div class="panel-heading font-bold">站点信息设置</div>
            <div class="panel-body">
                <div class="alert alert-info">
                    当前前台模板：
                    <strong><?php echo $q8_template_uses_default ? '默认模板（' . htmlspecialchars($q8_default_template_label, ENT_QUOTES, 'UTF-8') . '）' : htmlspecialchars($q8_user_template_label, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <?php if ($conf['fenzhan_template'] == 1) { ?>
                    <a href="./usetmoban.php?mod=site2" class="btn btn-xs btn-primary pull-right">前台模板设置</a>
                    <?php } ?>
                </div>

                <form action="./usetmoban.php?mod=site_n" method="post" role="form">
                    <?php echo q8_user_setting_hidden('template', isset($userrow['template']) ? $userrow['template'] : ''); ?>
                    <div class="form-group">
                        <label>网站名称</label>
                        <input type="text" name="sitename" value="<?php echo htmlspecialchars($userrow['sitename'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>标题栏后缀</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($userrow['title'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>关键词</label>
                        <input type="text" name="keywords" value="<?php echo htmlspecialchars($userrow['keywords'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>网站描述</label>
                        <input type="text" name="description" value="<?php echo htmlspecialchars($userrow['description'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
                    </div>

                    <?php if ($conf['fenzhan_kfqq'] == 1) { ?>
                    <div class="form-group">
                        <label>客服 QQ</label>
                        <input type="text" name="kfqq" value="<?php echo htmlspecialchars($userrow['kfqq'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="留空自动同步主站客服 QQ">
                    </div>
                    <?php if ($newuserhead) { ?>
                    <div class="form-group">
                        <label>客服微信</label>
                        <div class="input-group">
                            <input type="text" name="kfwx" value="<?php echo htmlspecialchars($userrow['kfwx'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="留空自动同步主站客服微信">
                            <span class="input-group-btn">
                                <a href="./usetmoban.php?mod=upwxqrcode" class="btn btn-default">上传二维码</a>
                            </span>
                        </div>
                    </div>
                    <?php } else { ?>
                    <?php echo q8_user_setting_hidden('kfwx', isset($userrow['kfwx']) ? $userrow['kfwx'] : ''); ?>
                    <?php } ?>
                    <?php } else { ?>
                    <?php echo q8_user_setting_hidden('kfqq', isset($userrow['kfqq']) ? $userrow['kfqq'] : ''); ?>
                    <?php echo q8_user_setting_hidden('kfwx', isset($userrow['kfwx']) ? $userrow['kfwx'] : ''); ?>
                    <?php } ?>

                    <?php if ($conf['fenzhan_edithtml'] == 1) { ?>
                    <div class="form-group">
                        <label>首页公告</label>
                        <textarea class="form-control" name="anounce" rows="6" placeholder="留空则同步主站首页公告"><?php echo htmlspecialchars($userrow['anounce'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>首页弹窗公告</label>
                        <select class="form-control" name="modal_show_type" style="width:auto;display:inline-block;margin-left:10px;" data-default="<?php echo isset($userrow['modal_show_type']) ? intval($userrow['modal_show_type']) : 0; ?>">
                            <option value="0">每次进入网站都弹出</option>
                            <option value="1">只弹一次</option>
                        </select>
                        <textarea class="form-control" name="modal" rows="5" placeholder="留空则同步主站首页弹窗"><?php echo htmlspecialchars($userrow['modal'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>首页底部排版</label>
                        <textarea class="form-control" name="bottom" rows="5" placeholder="留空则同步主站底部排版"><?php echo htmlspecialchars($userrow['bottom'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>在线下单提示</label>
                        <textarea class="form-control" name="alert" rows="5" placeholder="留空则同步主站下单提示"><?php echo htmlspecialchars($userrow['alert'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    <?php } else { ?>
                    <?php echo q8_user_setting_hidden('anounce', isset($userrow['anounce']) ? $userrow['anounce'] : ''); ?>
                    <?php echo q8_user_setting_hidden('modal', isset($userrow['modal']) ? $userrow['modal'] : ''); ?>
                    <?php echo q8_user_setting_hidden('modal_show_type', isset($userrow['modal_show_type']) ? intval($userrow['modal_show_type']) : 0); ?>
                    <?php echo q8_user_setting_hidden('bottom', isset($userrow['bottom']) ? $userrow['bottom'] : ''); ?>
                    <?php echo q8_user_setting_hidden('alert', isset($userrow['alert']) ? $userrow['alert'] : ''); ?>
                    <?php } ?>

                    <div class="form-group">
                        <label>后台公告显示方式</label>
                        <select class="form-control" name="admin_notice_mode" data-default="<?php echo isset($userrow['admin_notice_mode']) ? intval($userrow['admin_notice_mode']) : 1; ?>">
                            <?php foreach ($q8_admin_notice_modes as $noticeModeValue => $noticeModeLabel) { ?>
                            <option value="<?php echo intval($noticeModeValue); ?>"><?php echo htmlspecialchars($noticeModeLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>分站自定义后台公告</label>
                        <textarea class="form-control" name="admin_notice_panel" rows="6" placeholder="留空则只显示主站后台公告；选择“主站公告 + 分站自定义”时，会先显示主站，再显示你的补充公告。"><?php echo htmlspecialchars(isset($userrow['admin_notice_panel']) ? $userrow['admin_notice_panel'] : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>分站加价模板</label>
                        <select class="form-control" name="site_prid" data-default="<?php echo isset($userrow['site_prid']) ? intval($userrow['site_prid']) : 0; ?>">
                            <option value="0">不使用，默认继承上级价格</option>
                            <?php foreach ((array)$q8_price_rule_rows as $priceRuleRow) { ?>
                            <option value="<?php echo intval($priceRuleRow['id']); ?>"><?php echo htmlspecialchars($priceRuleRow['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php } ?>
                        </select>
                        <pre>未单独改价的商品将按分站自己的进货价叠加此模板，上级调价和新商品也会继续使用这套规则。</pre>
                    </div>
                    <?php if ($userrow['power'] == 2) { ?>
                    <div class="form-group">
                        <label>普及版分站价格</label>
                        <input type="text" name="ktfz_price" value="<?php echo $userrow['ktfz_price'] > 0 ? htmlspecialchars($userrow['ktfz_price'], ENT_QUOTES, 'UTF-8') : htmlspecialchars($conf['fenzhan_price'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
                        <pre>前台自助开通普及版分站的售价。</pre>
                    </div>
                    <div class="form-group">
                        <label>专业版分站价格</label>
                        <input type="text" name="ktfz_price2" value="<?php echo $userrow['ktfz_price2'] > $conf['fenzhan_cost2'] ? htmlspecialchars($userrow['ktfz_price2'], ENT_QUOTES, 'UTF-8') : htmlspecialchars($conf['fenzhan_price2'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
                        <pre>不能低于成本价 <?php echo htmlspecialchars($conf['fenzhan_cost2'], ENT_QUOTES, 'UTF-8'); ?> 元。</pre>
                    </div>
                    <?php } else { ?>
                    <?php echo q8_user_setting_hidden('ktfz_price', isset($userrow['ktfz_price']) ? $userrow['ktfz_price'] : ''); ?>
                    <?php echo q8_user_setting_hidden('ktfz_price2', isset($userrow['ktfz_price2']) ? $userrow['ktfz_price2'] : ''); ?>
                    <?php echo q8_user_setting_hidden('ktfz_domain', isset($userrow['ktfz_domain']) ? $userrow['ktfz_domain'] : ''); ?>
                    <?php } ?>

                    <?php if ($conf['fenzhan_editd'] > 0) { ?>
                    <div class="form-group">
                        <label>本站域名</label>
                        <div class="input-group">
                            <input type="text" name="domain" value="<?php echo htmlspecialchars($userrow['domain'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" disabled>
                            <div class="input-group-addon"><a href="cdomain.php">自助更换域名</a></div>
                        </div>
                    </div>
                    <?php } ?>

                    <div class="form-group">
                        <label>APP 下载地址</label>
                        <input type="text" name="appurl" value="<?php echo htmlspecialchars($userrow['appurl'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="没有请留空">
                    </div>
                    <div class="form-group">
                        <input type="submit" name="submit" value="保存站点设置" class="btn btn-primary form-control">
                    </div>
                </form>
            </div>
            <div class="panel-footer">
                <span class="fa fa-info-circle"></span>
                实用工具：
                <a href="http://www.w3school.com.cn/tiy/t.asp?f=html_basic" target="_blank" rel="noreferrer">HTML 在线测试</a> /
                <a href="http://pic.xiaojianjian.net/" target="_blank" rel="noreferrer">图床</a> /
                <a href="http://music.cccyun.cc/" target="_blank" rel="noreferrer">音乐外链</a>
            </div>
        </div>

        <?php } elseif ($mod === 'site2' && $userrow['power'] > 0) { ?>
        <div class="panel panel-default">
            <div class="panel-heading font-bold">前台模板设置</div>
            <div class="panel-body">
                <?php if ($conf['fenzhan_template'] != 1) { ?>
                <div class="alert alert-warning">主站当前未开启分站自定义模板功能，分站将继续继承主站模板：<?php echo htmlspecialchars($q8_default_template_label, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php } ?>
                <form action="./usetmoban.php?mod=site_n" method="post" role="form">
                    <?php
                    echo q8_user_setting_hidden('sitename', isset($userrow['sitename']) ? $userrow['sitename'] : '');
                    echo q8_user_setting_hidden('title', isset($userrow['title']) ? $userrow['title'] : '');
                    echo q8_user_setting_hidden('keywords', isset($userrow['keywords']) ? $userrow['keywords'] : '');
                    echo q8_user_setting_hidden('description', isset($userrow['description']) ? $userrow['description'] : '');
                    echo q8_user_setting_hidden('kfqq', isset($userrow['kfqq']) ? $userrow['kfqq'] : '');
                    echo q8_user_setting_hidden('kfwx', isset($userrow['kfwx']) ? $userrow['kfwx'] : '');
                    echo q8_user_setting_hidden('anounce', isset($userrow['anounce']) ? $userrow['anounce'] : '');
                    echo q8_user_setting_hidden('modal', isset($userrow['modal']) ? $userrow['modal'] : '');
                    echo q8_user_setting_hidden('modal_show_type', isset($userrow['modal_show_type']) ? intval($userrow['modal_show_type']) : 0);
                    echo q8_user_setting_hidden('bottom', isset($userrow['bottom']) ? $userrow['bottom'] : '');
                    echo q8_user_setting_hidden('alert', isset($userrow['alert']) ? $userrow['alert'] : '');
                    echo q8_user_setting_hidden('admin_notice_mode', isset($userrow['admin_notice_mode']) ? intval($userrow['admin_notice_mode']) : 1);
                    echo q8_user_setting_hidden('admin_notice_panel', isset($userrow['admin_notice_panel']) ? $userrow['admin_notice_panel'] : '');
                    echo q8_user_setting_hidden('ktfz_price', isset($userrow['ktfz_price']) ? $userrow['ktfz_price'] : '');
                    echo q8_user_setting_hidden('ktfz_price2', isset($userrow['ktfz_price2']) ? $userrow['ktfz_price2'] : '');
                    echo q8_user_setting_hidden('ktfz_domain', isset($userrow['ktfz_domain']) ? $userrow['ktfz_domain'] : '');
                    echo q8_user_setting_hidden('site_prid', isset($userrow['site_prid']) ? intval($userrow['site_prid']) : 0);
                    echo q8_user_setting_hidden('appurl', isset($userrow['appurl']) ? $userrow['appurl'] : '');
                    ?>
                    <div class="alert alert-info">
                        分站默认会继承主站模板：<strong><?php echo htmlspecialchars($q8_default_template_label, ENT_QUOTES, 'UTF-8'); ?></strong>
                        <?php if (!$q8_template_uses_default && !empty($userrow['template'])) { ?>
                        <br>你当前正在使用：<strong><?php echo htmlspecialchars($q8_user_template_label, ENT_QUOTES, 'UTF-8'); ?></strong>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label>首页模板</label>
                        <select class="form-control" name="template" <?php echo $conf['fenzhan_template'] == 1 ? '' : 'disabled'; ?>>
                            <option value=""<?php echo $q8_template_uses_default ? ' selected' : ''; ?>>默认模板（<?php echo htmlspecialchars($q8_default_template_label, ENT_QUOTES, 'UTF-8'); ?>）</option>
                            <?php foreach ($mblist as $row) { ?>
                            <option value="<?php echo htmlspecialchars($row, ENT_QUOTES, 'UTF-8'); ?>"<?php echo isset($userrow['template']) && $userrow['template'] === $row ? ' selected' : ''; ?>><?php echo htmlspecialchars(\lib\Template::getDisplayName($row), ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="submit" name="submit" value="保存模板设置" class="btn btn-primary form-control" <?php echo $conf['fenzhan_template'] == 1 ? '' : 'disabled'; ?>>
                    </div>
                </form>
            </div>
        </div>

        <?php } elseif ($mod === 'logo' && $userrow['power'] > 0 && $conf['fenzhan_edithtml'] == 1) { ?>
        <div class="panel panel-default">
            <div class="panel-heading font-bold">首页 Logo 设置</div>
            <div class="panel-body">
                <p>提示：部分模板不会显示 Logo 图片，属于正常现象。</p>
                <?php
                if (isset($_POST['s']) && $_POST['s'] == 1) {
                    if (!checkRefererHost()) exit();
                    $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
                    $file_type = $_FILES['file']['type'];
                    $file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                    $allowed_exts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                    $max_size = 2 * 1024 * 1024;
                    if (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_exts)) {
                        exit('只允许上传 JPG / JPEG / PNG / GIF / WEBP 图片文件');
                    }
                    if ($_FILES['file']['size'] > $max_size) {
                        exit('上传文件不能超过 2MB');
                    }
                    $image_info = getimagesize($_FILES['file']['tmp_name']);
                    if (!$image_info) {
                        exit('无法识别图片内容');
                    }
                    copy($_FILES['file']['tmp_name'], ROOT . 'assets/img/logo_' . $userrow['zid'] . '.png');
                    echo '<div class="alert alert-success">Logo 上传成功，若未立即生效请按 Ctrl+F5 强制刷新。</div>';
                }
                $logo = file_exists(ROOT . 'assets/img/logo_' . $userrow['zid'] . '.png') ? '../assets/img/logo_' . $userrow['zid'] . '.png' : '../assets/img/logo.png';
                ?>
                <form action="./usetmoban.php?mod=logo" method="post" enctype="multipart/form-data">
                    <input type="file" name="file" id="file">
                    <input type="hidden" name="s" value="1">
                    <br>
                    <input type="submit" class="btn btn-primary form-control" value="上传 Logo">
                </form>
                <br>
                <div>当前图片：</div>
                <img src="<?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo" style="max-width:30%">
            </div>
        </div>

        <?php } elseif ($mod === 'skimg' && $userrow['power'] > 0) { ?>
        <div class="panel panel-default">
            <div class="panel-heading font-bold">收款图设置</div>
            <div class="panel-body">
                <?php
                if (isset($_POST['s']) && $_POST['s'] == 1) {
                    if (!checkRefererHost()) exit();
                    $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
                    $file_type = $_FILES['shoukuan']['type'];
                    $file_ext = strtolower(pathinfo($_FILES['shoukuan']['name'], PATHINFO_EXTENSION));
                    $allowed_exts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                    $max_size = 5 * 1024 * 1024;
                    if (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_exts)) {
                        exit('只允许上传 JPG / JPEG / PNG / GIF / WEBP 图片文件');
                    }
                    if ($_FILES['shoukuan']['size'] > $max_size) {
                        exit('上传文件不能超过 5MB');
                    }
                    $image_info = getimagesize($_FILES['shoukuan']['tmp_name']);
                    if (!$image_info) {
                        exit('无法识别图片内容');
                    }
                    $skimg_dir = ROOT . 'assets/img/skimg/';
                    if (!is_dir($skimg_dir) && !mkdir($skimg_dir, 0755, true)) {
                        exit('收款图目录创建失败，请联系管理员检查目录权限');
                    }
                    $skimg_save = $skimg_dir . 'sk_' . $userrow['zid'] . '.png';
                    if (!move_uploaded_file($_FILES['shoukuan']['tmp_name'], $skimg_save)) {
                        exit('收款图保存失败，请联系管理员检查目录权限');
                    }
                    echo '<div class="alert alert-success">收款图上传成功，若未立即生效请按 Ctrl+F5 强制刷新。</div>';
                }
                $skimg_path = ROOT . 'assets/img/skimg/sk_' . $userrow['zid'] . '.png';
                $skimg = file_exists($skimg_path) ? '../assets/img/skimg/sk_' . $userrow['zid'] . '.png?t=' . filemtime($skimg_path) : '';
                ?>
                <form action="./usetmoban.php?mod=skimg" method="post" enctype="multipart/form-data">
                    <input type="file" name="shoukuan" id="shoukuan">
                    <input type="hidden" name="s" value="1">
                    <br>
                    <input type="submit" class="btn btn-primary form-control" value="上传收款图">
                </form>
                <br>
                <div>当前图片：</div>
                <?php if ($skimg !== '') { ?>
                <img src="<?php echo htmlspecialchars($skimg, ENT_QUOTES, 'UTF-8'); ?>" alt="收款图" class="q8-user-preview-img">
                <?php } else { ?>
                <strong>当前未上传收款图</strong>
                <?php } ?>
            </div>
        </div>

        <?php } elseif ($mod === 'upwxqrcode' && $userrow['power'] > 0 && $conf['fenzhan_kfqq'] == 1) { ?>
        <div class="panel panel-default">
            <div class="panel-heading font-bold">客服微信二维码</div>
            <div class="panel-body">
                <?php
                if (isset($_GET['del']) && intval($_GET['del']) === 1) {
                    if (file_exists(ROOT . 'assets/img/qrcode/wxqrcode_' . $userrow['zid'] . '.png')) {
                        unlink(ROOT . 'assets/img/qrcode/wxqrcode_' . $userrow['zid'] . '.png');
                    }
                    exit("<script language='javascript'>alert('删除成功');window.location.href='./usetmoban.php?mod=upwxqrcode';</script>");
                }
                if (isset($_POST['s']) && $_POST['s'] == 1) {
                    if (!checkRefererHost()) exit();
                    $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
                    $file_type = $_FILES['wxqrcode']['type'];
                    $file_ext = strtolower(pathinfo($_FILES['wxqrcode']['name'], PATHINFO_EXTENSION));
                    $allowed_exts = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                    $max_size = 2 * 1024 * 1024;
                    if (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_exts)) {
                        exit('只允许上传 JPG / JPEG / PNG / GIF / WEBP 图片文件');
                    }
                    if ($_FILES['wxqrcode']['size'] > $max_size) {
                        exit('上传文件不能超过 2MB');
                    }
                    $image_info = getimagesize($_FILES['wxqrcode']['tmp_name']);
                    if (!$image_info) {
                        exit('无法识别图片内容');
                    }
                    $qrcode_dir = ROOT . 'assets/img/qrcode/';
                    if (!is_dir($qrcode_dir) && !mkdir($qrcode_dir, 0755, true)) {
                        exit('二维码目录创建失败，请联系管理员检查目录权限');
                    }
                    $qrcode_save = $qrcode_dir . 'wxqrcode_' . $userrow['zid'] . '.png';
                    if (!move_uploaded_file($_FILES['wxqrcode']['tmp_name'], $qrcode_save)) {
                        exit('二维码保存失败，请联系管理员检查目录权限');
                    }
                    echo '<div class="alert alert-success">二维码上传成功，若未立即生效请按 Ctrl+F5 强制刷新。</div>';
                }
                if (file_exists(ROOT . 'assets/img/qrcode/wxqrcode_' . $userrow['zid'] . '.png')) {
                    $wxqrcode = '<br><img src="../assets/img/qrcode/wxqrcode_' . $userrow['zid'] . '.png" alt="客服微信二维码" class="q8-user-preview-img">';
                } elseif (!empty($userrow['kfqq'])) {
                    $wxqrcode = '<strong>当前使用根据客服 QQ 自动生成的二维码</strong>';
                } else {
                    $wxqrcode = '<strong>当前继承主站客服二维码</strong>';
                }
                ?>
                <form action="./usetmoban.php?mod=upwxqrcode" method="post" enctype="multipart/form-data">
                    <input type="file" name="wxqrcode" id="wxqrcode">
                    <input type="hidden" name="s" value="1">
                    <br>
                    <input type="submit" class="btn btn-primary form-control" value="上传微信收款码">
                    <br><br>
                    <a href="./usetmoban.php?mod=upwxqrcode&amp;del=1" class="btn btn-danger btn-block btn-sm">删除当前二维码</a>
                </form>
                <br>
                当前图片：<?php echo $wxqrcode; ?>
            </div>
        </div>

        <?php } else { ?>
        <?php showmsg('你没有权限使用此功能！', 3); ?>
        <?php } ?>
    </div>
</div>
<?php include './foot.php'; ?>
<script src="<?php echo $cdnpublic ?>jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
<script>
(function($){
    'use strict';

    $('select[data-default]').each(function(){
        $(this).val($(this).attr('data-default') || '0');
    });

    $(document).on('click', '[data-bind-action]', function(){
        var $button = $(this);
        var action = $button.attr('data-bind-action');
        var type = $button.attr('data-bind-type') || '';
        if (action === 'connect') {
            connectAccount(type);
        } else if (action === 'unbind') {
            unbindAccount(type);
        }
    });

    $('#setpwdForm').on('submit', function(){
        return setIndependentLogin();
    });

    <?php if ($mod === 'user' && $conf['fenzhan_daifu'] == 1) { ?>
    function getopenid() {
        var openIndex = layer.open({
            type: 1,
            title: '',
            content: '<div class="layui-card-body"><h3 style="text-align:center">\u8bf7\u4f7f\u7528\u5fae\u4fe1\u626b\u4e00\u626b</h3><div><div id="qrcode" style="padding:15px;"></div></div></div>',
            cancel: function() {
                layer.close(openIndex);
                window.clearInterval(cronTimer);
            },
            success: function() {
                $('#qrcode').qrcode({
                    text: '<?php echo $code_url; ?>',
                    width: 230,
                    height: 230,
                    foreground: '#000000',
                    background: '#ffffff',
                    typeNumber: -1
                });
            }
        });
        var cronTimer = setInterval(function() {
            $.ajax({
                type: 'GET',
                url: '<?php echo $cron_url; ?>' + '&r=' + Math.random(),
                dataType: 'json',
                success: function(data) {
                    if (data.code) {
                        $("input[name=pay_account]").val(data.data);
                        layer.close(openIndex);
                        window.clearInterval(cronTimer);
                    }
                }
            });
        }, 3000);
    }

    $('#getopenid').on('click', function(){
        getopenid();
    });

    $("select[name='pay_type']").on('change', function() {
        if ($(this).val() === '1') {
            $('#getopenid').show();
            $("input[name=pay_account]").attr('readonly', 'readonly');
        } else {
            $('#getopenid').hide();
            $("input[name=pay_account]").removeAttr('readonly');
        }
    }).trigger('change');
    <?php } ?>

    function setIndependentLogin() {
        var user = $.trim($("input[name='user']").val() || '');
        var pwd = $.trim($("#setpwdForm input[name='pwd']").val() || '');
        if (user === '' || pwd === '') {
            layer.alert('\u8bf7\u786e\u4fdd\u6bcf\u4e00\u9879\u90fd\u5df2\u586b\u5199');
            return false;
        }
        if (user.length < 3) {
            layer.alert('\u767b\u5f55\u8d26\u53f7\u81f3\u5c11\u9700\u8981 3 \u4f4d');
            return false;
        }
        if (user.length > 20) {
            layer.alert('\u767b\u5f55\u8d26\u53f7\u4e0d\u80fd\u8d85\u8fc7 20 \u4f4d');
            return false;
        }
        if (pwd.length < 6) {
            layer.alert('\u767b\u5f55\u5bc6\u7801\u4e0d\u80fd\u4f4e\u4e8e 6 \u4f4d');
            return false;
        }
        if (pwd.length > 30) {
            layer.alert('\u767b\u5f55\u5bc6\u7801\u4e0d\u80fd\u8d85\u8fc7 30 \u4f4d');
            return false;
        }

        var loadingIndex = layer.load(2, {shade: [0.1, '#fff']});
        $.ajax({
            type: 'POST',
            url: 'ajax_user.php?act=setpwd',
            data: {user: user, pwd: pwd},
            dataType: 'json',
            success: function(data) {
                layer.close(loadingIndex);
                if (data.code == 0) {
                    layer.alert(data.msg, {closeBtn: 0}, function() {
                        window.location.reload();
                    });
                } else {
                    layer.alert(data.msg, {icon: 0});
                }
            },
            error: function() {
                layer.close(loadingIndex);
                layer.alert('\u670d\u52a1\u5668\u9519\u8bef');
            }
        });
        return false;
    }

    function connectAccount(type) {
        var loadingIndex = layer.load(2, {shade: [0.1, '#fff']});
        $.ajax({
            type: 'POST',
            url: 'ajax.php?act=connect',
            data: {type: type},
            dataType: 'json',
            success: function(data) {
                layer.close(loadingIndex);
                if (data.code == 0) {
                    window.location.href = data.url;
                } else {
                    layer.alert(data.msg, {icon: 7});
                }
            },
            error: function() {
                layer.close(loadingIndex);
                layer.alert('\u670d\u52a1\u5668\u9519\u8bef');
            }
        });
    }

    function unbindAccount(type) {
        var confirmIndex = layer.confirm('\u89e3\u7ed1\u540e\u5c06\u65e0\u6cd5\u518d\u901a\u8fc7\u5feb\u6377\u767b\u5f55\u8fdb\u5165\uff0c\u786e\u8ba4\u89e3\u7ed1\u5417\uff1f', function() {
            var loadingIndex = layer.load(2, {shade: [0.1, '#fff']});
            $.ajax({
                type: 'POST',
                url: 'ajax.php?act=unbind',
                data: {type: type},
                dataType: 'json',
                success: function(data) {
                    layer.close(loadingIndex);
                    if (data.code == 0) {
                        layer.alert(data.msg, {icon: 1}, function() {
                            window.location.reload();
                        });
                    } else {
                        layer.alert(data.msg, {icon: 0});
                    }
                },
                error: function() {
                    layer.close(loadingIndex);
                    layer.alert('\u670d\u52a1\u5668\u9519\u8bef');
                }
            });
        }, function() {
            layer.close(confirmIndex);
        });
    }

    /*
    function normalizeLegacySettingLabels() {
        var adminNoticeModeGroup = $('select[name="admin_notice_mode"]').closest('.form-group');
        if (adminNoticeModeGroup.length) {
            adminNoticeModeGroup.find('label').first().text('后台公告显示方式');
        }

        var adminNoticePanelGroup = $('textarea[name="admin_notice_panel"]').closest('.form-group');
        if (adminNoticePanelGroup.length) {
            adminNoticePanelGroup.find('label').first().text('分站自定义后台公告');
            adminNoticePanelGroup.find('textarea[name="admin_notice_panel"]').attr('placeholder', '留空则只显示主站后台公告；选择“主站公告 + 分站自定义”时，会先显示主站，再显示你的补充公告。');
        }

        var sitePridGroup = $('select[name="site_prid"]').closest('.form-group');
        var sitePridSelect = sitePridGroup.find('select[name="site_prid"]');
        var legacyTemplate = <?php echo ($q8_current_site_price_rule_legacy && $q8_current_site_price_rule_row) ? json_encode(array('id' => intval($q8_current_site_price_rule_row['id']), 'name' => $q8_current_site_price_rule_row['name']), JSON_UNESCAPED_UNICODE) : 'null'; ?>;
        if (sitePridGroup.length) {
            sitePridGroup.find('label').first().text('分站加价模板');
            sitePridGroup.find('select[name="site_prid"] option').first().text('不使用，默认继承上级价格');
            sitePridGroup.find('pre').first().text('未单独改价的商品，将按分站自己的进货价套用这套模板；上级调价和新商品上架后，也会继续沿着这套规则计算。');
        }
    }

    normalizeLegacySettingLabels();

    function normalizeSitePriceTemplateCopy() {
        var sitePridGroup = $('select[name="site_prid"]').closest('.form-group');
        if (!sitePridGroup.length) {
            return;
        }
        if (legacyTemplate && !sitePridSelect.find('option[value="' + legacyTemplate.id + '"]').length) {
            $('<option></option>').val(String(legacyTemplate.id)).text('历史主站模板：' + legacyTemplate.name).appendTo(sitePridSelect);
        }
        sitePridGroup.find('label').first().text('分站加价模板');
        sitePridGroup.find('select[name="site_prid"] option').first().text('暂不使用模板，由站长自行选择');
        sitePridGroup.find('pre').first().text('新开分站默认不继承上级的分站加价模板；未单独改价的商品会先按上级价格链计算，站长选择自己的模板后，再按分站自己的进货价继续计算。');
    }

    normalizeSitePriceTemplateCopy();

    function finalizeSitePriceTemplateCopy() {
        var sitePridGroup = $('select[name="site_prid"]').closest('.form-group');
        var sitePridSelect = sitePridGroup.find('select[name="site_prid"]');
        var legacyTemplate = <?php echo ($q8_current_site_price_rule_legacy && $q8_current_site_price_rule_row) ? json_encode(array('id' => intval($q8_current_site_price_rule_row['id']), 'name' => $q8_current_site_price_rule_row['name']), JSON_UNESCAPED_UNICODE) : 'null'; ?>;
        if (!sitePridGroup.length || !sitePridSelect.length) {
            return;
        }
        if (legacyTemplate && !sitePridSelect.find('option[value="' + legacyTemplate.id + '"]').length) {
            $('<option></option>').val(String(legacyTemplate.id)).text('历史主站模板：' + legacyTemplate.name).appendTo(sitePridSelect);
        }
        sitePridGroup.find('label').first().text('分站加价模板');
        sitePridSelect.find('option').first().text('暂不使用模板，由站长自行选择');
        sitePridGroup.find('pre').first().text('这里只显示当前分站自己创建的加价模板。新开分站默认不继承主站模板；未单独改价的商品会按当前分站自己的模板和进货价继续计算。');
    }

    finalizeSitePriceTemplateCopy();

    var q8LegacySiteTemplateSelect = $('select[name="site_prid"]');
    var q8LegacySiteTemplateValue = <?php echo ($q8_current_site_price_rule_legacy && $q8_current_site_price_rule_row) ? intval($q8_current_site_price_rule_row['id']) : 0; ?>;
    if (q8LegacySiteTemplateSelect.length && q8LegacySiteTemplateValue > 0 && String(q8LegacySiteTemplateSelect.attr('data-default') || '0') === String(q8LegacySiteTemplateValue)) {
        q8LegacySiteTemplateSelect.val(String(q8LegacySiteTemplateValue));
    }
    */

    var q8LegacyTemplate = <?php echo ($q8_current_site_price_rule_legacy && $q8_current_site_price_rule_row) ? json_encode(array('id' => intval($q8_current_site_price_rule_row['id']), 'name' => $q8_current_site_price_rule_row['name']), JSON_UNESCAPED_UNICODE) : 'null'; ?>;

    function normalizeBranchSiteSettings() {
        var adminNoticeModeGroup = $('select[name="admin_notice_mode"]').closest('.form-group');
        var adminNoticePanelGroup = $('textarea[name="admin_notice_panel"]').closest('.form-group');
        var sitePridGroup = $('select[name="site_prid"]').closest('.form-group');
        var sitePridSelect = sitePridGroup.find('select[name="site_prid"]');
        var sitePridHelp = sitePridGroup.find('pre').first();
        var sitePridDefault = String(sitePridSelect.attr('data-default') || '0');

        if (adminNoticeModeGroup.length) {
            adminNoticeModeGroup.find('label').first().text('\u540e\u53f0\u516c\u544a\u663e\u793a\u65b9\u5f0f');
        }

        if (adminNoticePanelGroup.length) {
            adminNoticePanelGroup.find('label').first().text('\u5206\u7ad9\u81ea\u5b9a\u4e49\u540e\u53f0\u516c\u544a');
            adminNoticePanelGroup.find('textarea[name="admin_notice_panel"]').attr(
                'placeholder',
                '\u7559\u7a7a\u5219\u53ea\u663e\u793a\u4e3b\u7ad9\u540e\u53f0\u516c\u544a\uff1b\u9009\u62e9\u201c\u4e3b\u7ad9\u516c\u544a + \u5206\u7ad9\u81ea\u5b9a\u4e49\u201d\u65f6\uff0c\u4f1a\u5148\u663e\u793a\u4e3b\u7ad9\uff0c\u518d\u663e\u793a\u4f60\u7684\u8865\u5145\u516c\u544a\u3002'
            );
        }

        if (!sitePridGroup.length || !sitePridSelect.length) {
            return;
        }

        sitePridGroup.find('label').first().text('\u5206\u7ad9\u52a0\u4ef7\u6a21\u677f');
        sitePridSelect.find('option').first().text('\u6682\u4e0d\u4f7f\u7528\u6a21\u677f\uff0c\u7531\u7ad9\u957f\u81ea\u884c\u9009\u62e9');

        if (q8LegacyTemplate && !sitePridSelect.find('option[value="' + q8LegacyTemplate.id + '"]').length) {
            $('<option></option>')
                .val(String(q8LegacyTemplate.id))
                .text('\u5386\u53f2\u4e3b\u7ad9\u6a21\u677f\uff1a' + q8LegacyTemplate.name)
                .appendTo(sitePridSelect);
        }

        if (sitePridHelp.length) {
            sitePridHelp.text(
                '\u8fd9\u91cc\u53ea\u663e\u793a\u5f53\u524d\u5206\u7ad9\u81ea\u5df1\u521b\u5efa\u7684\u52a0\u4ef7\u6a21\u677f\u3002' +
                '\u65b0\u5f00\u5206\u7ad9\u9ed8\u8ba4\u4e0d\u7ee7\u627f\u4e3b\u7ad9\u6a21\u677f\uff1b' +
                '\u672a\u5355\u72ec\u6539\u4ef7\u7684\u5546\u54c1\u4f1a\u5148\u6309\u4e0a\u7ea7\u4ef7\u683c\u94fe\u8ba1\u7b97\uff0c' +
                '\u7ad9\u957f\u9009\u5b9a\u81ea\u5df1\u7684\u6a21\u677f\u540e\uff0c\u518d\u6309\u5f53\u524d\u5206\u7ad9\u81ea\u5df1\u7684\u8fdb\u8d27\u4ef7\u7ee7\u7eed\u8ba1\u7b97\u3002'
            );
        }

        if (q8LegacyTemplate && sitePridDefault === String(q8LegacyTemplate.id)) {
            sitePridSelect.val(String(q8LegacyTemplate.id));
        }
    }

    function normalizeSiteTemplatePanel() {
        var site2Heading = $('.panel-heading.font-bold').filter(function () {
            return $(this).closest('.panel').find('select[name="template"]').length > 0;
        });
        var templateSelect = $('select[name="template"]');
        var templateAlert = templateSelect.closest('form').find('.alert-info').first();

        if (site2Heading.length) {
            site2Heading.first().text('\u524d\u53f0\u6a21\u677f\u8bbe\u7f6e');
        }
        if (templateAlert.length) {
            var html = templateAlert.html() || '';
            html = html
                .replace(/[\s\S]*?<strong>/, '\u5206\u7ad9\u9ed8\u8ba4\u4f1a\u7ee7\u627f\u4e3b\u7ad9\u6a21\u677f\uff1a<strong>')
                .replace(/<br>[\s\S]*?<strong>/, '<br>\u4f60\u5f53\u524d\u6b63\u5728\u4f7f\u7528\uff1a<strong>');
            templateAlert.html(html);
        }
        if (templateSelect.length) {
            var firstOption = templateSelect.find('option').first();
            var currentText = firstOption.text();
            var match = currentText.match(/([A-Za-z0-9_-]+)$/);
            var templateName = match ? match[1] : '<?php echo addslashes($q8_default_template_label); ?>';
            firstOption.text('\u9ed8\u8ba4\u6a21\u677f\uff08' + templateName + '\uff09');
        }
    }

    normalizeBranchSiteSettings();
    normalizeSiteTemplatePanel();
})(jQuery);
</script>
</body>
</html>

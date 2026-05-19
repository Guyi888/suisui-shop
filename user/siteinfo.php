<?php
include '../includes/common.php';
if ($islogin2 != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

q8_site_admin_notice_ensure_fields();
if (function_exists('q8_site_markup_template_ensure_fields')) {
    q8_site_markup_template_ensure_fields();
}

$q8_admin_notice_context = $userrow['power'] > 0 ? q8_site_admin_notice_context($userrow, $conf) : array('html' => '');
$q8_notice_html = $userrow['power'] > 0
    ? (isset($q8_admin_notice_context['html']) ? $q8_admin_notice_context['html'] : '')
    : (isset($conf['gg_panel']) ? $conf['gg_panel'] : '');
$q8_unread_count = q8_count_unread_messages($userrow);
$q8_template_name = q8_template_name_resolve(
    isset($userrow['template']) ? $userrow['template'] : '',
    isset($conf['template']) ? $conf['template'] : 'default'
);
$q8_site_price_template_name = '默认继承上级价格';
if ($userrow['power'] > 0 && !empty($userrow['site_prid'])) {
    $q8_site_price_template_row = q8_price_rule_fetch_row(intval($userrow['site_prid']));
    if ($q8_site_price_template_row && !empty($q8_site_price_template_row['name'])) {
        $q8_site_price_template_name = intval($q8_site_price_template_row['zid']) === intval($userrow['zid']) ? $q8_site_price_template_row['name'] : ('历史主站模板：' . $q8_site_price_template_row['name']);
    }
}
if (empty($userrow['site_prid'])) {
    $q8_site_price_template_name = '默认按上级价格链计算';
}
$q8_domain_url = !empty($userrow['domain']) ? ('http://' . $userrow['domain'] . '/') : '';
$q8_site_type_label = $userrow['power'] == 2 ? '专业版' : '普及版';

$title = '站点信息';
include './head.php';
?>
<div class="wrapper">
<?php if ($userrow['power'] == 0) { ?>
<?php showmsg('你没有权限使用此功能！', 3); ?>
<?php } ?>

<div class="col-md-6 col-sm-12">
    <div class="panel panel-default">
        <div class="panel-heading font-bold text-center" style="background: linear-gradient(to right,#14b7ff,#b221ff);">
            <h3 class="panel-title"><font color="#fff"><i class="fa fa-globe"></i>&nbsp;&nbsp;<b>我的站点信息</b></font></h3>
        </div>
        <ul class="list-group no-radius">
            <li class="list-group-item">
                <b>通知提醒：</b>你当前有
                <font color="orange"><b id="tiaosu"><?php echo intval($q8_unread_count); ?></b></font>
                条消息未读
                <a href="./message.php" class="btn btn-primary btn-xs pull-right">立即查看</a>
            </li>
            <li class="list-group-item" style="font-weight:bold">
                我的域名：
                <?php if ($q8_domain_url) { ?>
                <a href="<?php echo htmlspecialchars($q8_domain_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noreferrer"><?php echo htmlspecialchars($userrow['domain'], ENT_QUOTES, 'UTF-8'); ?></a>
                <?php } else { ?>
                <font color="grey">暂未绑定</font>
                <?php } ?>
                <a href="./usetmoban.php?mod=site" class="btn btn-info btn-xs pull-right">编辑信息</a>
            </li>
            <?php if ($conf['fanghong_api']) { ?>
            <li class="list-group-item" style="font-weight:bold;overflow:hidden;">
                防红链接：
                <a href="javascript:;" id="copy-btn" data-clipboard-text="">Loading...</a>
                <span class="pull-right">
                    <button class="btn btn-default btn-xs" id="recreate_url" type="button">重新生成</button>
                    &nbsp;&nbsp;
                    <button class="btn btn-info btn-xs" id="fanghongHelpButton" type="button">说明</button>
                </span>
            </li>
            <?php } ?>
            <li class="list-group-item" style="font-weight:bold">网站名称：<font color="blue"><?php echo htmlspecialchars($userrow['sitename'], ENT_QUOTES, 'UTF-8'); ?></font></li>
            <li class="list-group-item" style="font-weight:bold">
                当前模板：<font color="green"><?php echo htmlspecialchars($q8_template_name, ENT_QUOTES, 'UTF-8'); ?></font>
                <a href="./usetmoban.php?mod=site2" class="btn btn-success btn-xs pull-right">前台模板设置</a>
            </li>
            <li class="list-group-item" style="font-weight:bold">
                分站加价模板：<font color="purple"><?php echo htmlspecialchars($q8_site_price_template_name, ENT_QUOTES, 'UTF-8'); ?></font>
                <a href="./usetmoban.php?mod=site" class="btn btn-default btn-xs pull-right">价格与站点设置</a>
            </li>
            <li class="list-group-item" style="font-weight:bold">
                站点类型：<font color="red"><?php echo htmlspecialchars($q8_site_type_label, ENT_QUOTES, 'UTF-8'); ?></font>
                <?php
                if ($conf['fenzhan_upgrade'] > 0 && $userrow['power'] == 1) {
                    echo '<a href="upsite.php" class="btn btn-danger btn-xs pull-right">升级站点</a>';
                } else {
                    echo '<a href="./sitelist.php" class="btn btn-danger btn-xs pull-right">下级管理</a>';
                }
                ?>
            </li>
            <?php if ($conf['fenzhan_expiry'] > 0) { ?>
            <li class="list-group-item" style="font-weight:bold">
                到期时间：<font color="orange"><?php echo htmlspecialchars($userrow['endtime'], ENT_QUOTES, 'UTF-8'); ?></font>
                <a href="renew.php" class="btn btn-primary btn-xs pull-right">立即续期</a>
            </li>
            <?php } ?>
            <?php if ($conf['appcreate_open'] == 1) { ?>
            <li class="list-group-item" style="font-weight:bold">
                客户端 APP：
                <?php echo $userrow['appurl'] ? '<a href="' . htmlspecialchars($userrow['appurl'], ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noreferrer" style="color:#337ab7;">点击下载</a>' : '<font color="grey">未生成</font>'; ?>
                <a href="appCreate.php" class="btn btn-warning btn-xs pull-right">在线生成</a>
            </li>
            <?php } ?>
        </ul>
    </div>
</div>

<?php if ($userrow['power'] > 0 || $conf['user_level'] == 1) { ?>
<div class="col-md-6 col-sm-12">
    <div class="panel panel-default">
        <div class="panel-heading font-bold text-center" style="background: linear-gradient(to right,#14b7ff,#b221ff);">
            <h3 class="panel-title"><font color="#fff"><i class="fa fa-volume-up"></i>&nbsp;&nbsp;<b>站点公告</b></font></h3>
        </div>
        <?php echo $q8_notice_html; ?>
    </div>
</div>
<?php } ?>

<?php include './foot.php'; ?>
<script src="<?php echo $cdnpublic ?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script>
(function($){
    'use strict';

    if ($('#copy-btn').length) {
        var clipboard = new Clipboard('#copy-btn');

        clipboard.on('success', function () {
            layer.msg('\u590d\u5236\u6210\u529f\uff01', {icon: 1});
        });

        clipboard.on('error', function () {
            layer.msg('\u590d\u5236\u5931\u8d25\uff0c\u8bf7\u957f\u6309\u94fe\u63a5\u540e\u624b\u52a8\u590d\u5236', {icon: 2});
        });
    }

    $('#fanghongHelpButton').on('click', function(){
        layer.alert('\u9632\u7ea2\u94fe\u63a5\u53ef\u4ee5\u5728 QQ \u5185\u76f4\u63a5\u6253\u5f00\u4f60\u7684\u7ad9\u70b9\uff0c\u4fbf\u4e8e\u5206\u4eab\u4e0e\u63a8\u5e7f\u3002<br>Tips\uff1a\u70b9\u51fb\u77ed\u94fe\u63a5\u5373\u53ef\u590d\u5236\u3002', {
            icon: 3,
            title: '\u5c0f\u63d0\u793a',
            skin: 'layui-layer-molv layui-layer-wxd'
        });
    });

    $('#recreate_url').on('click', function(){
        var self = $(this);
        if (self.attr('data-lock') === 'true') {
            return;
        }
        self.attr('data-lock', 'true');
        var loadingIndex = layer.load(1, {shade: [0.1, '#fff']});
        $.get('ajax_user.php?act=create_url&force=1', function(data) {
            layer.close(loadingIndex);
            if (data.code == 0) {
                layer.msg('\u751f\u6210\u94fe\u63a5\u6210\u529f');
                $('#copy-btn').html(data.url);
                $('#copy-btn').attr('data-clipboard-text', data.url);
            } else {
                layer.alert(data.msg);
            }
            self.attr('data-lock', 'false');
        }, 'json');
    });

    $.ajax({
        type: 'GET',
        url: 'ajax_user.php?act=create_url',
        dataType: 'json',
        async: true,
        success: function(data) {
            if (data.code == 0) {
                $('#copy-btn').html(data.url);
                $('#copy-btn').attr('data-clipboard-text', data.url);
            } else {
                $('#copy-btn').html(data.msg);
            }
        }
    });
})(jQuery);
</script>
</body>
</html>

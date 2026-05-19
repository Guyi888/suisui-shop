<?php
include "../includes/common.php";

$title = "联系与赞助";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

$supportAds = array(
    array(
        'type' => 'image',
        'title' => '图片广告位 A',
        'desc' => '适合展示品牌海报、活动入口或新服务横幅。',
        'image' => '../assets/beautify/img/gg-new.jpg',
    ),
    array(
        'type' => 'image',
        'title' => '图片广告位 B',
        'desc' => '适合展示赞助商、渠道合作或教程入口。',
        'image' => '../assets/beautify/img/gg-app.jpg',
    ),
    array(
        'type' => 'text',
        'title' => '文字广告位',
        'desc' => '可放置短公告、招商说明、联系方式或限时活动文案。',
        'tag' => 'Text Slot',
    ),
);

include "./head.php";
?>
<div class="col-xs-12 admin-ops-page admin-support-page">
    <section class="admin-ops-hero">
        <div>
            <p class="admin-ops-hero__eyebrow">维护信息</p>
            <h2>联系与赞助</h2>
            <p>本页展示后台维护、版本支持与赞助信息，不影响前台站长自定义客服配置。</p>
        </div>
        <div class="admin-ops-hero__actions">
            <a href="<?php echo htmlspecialchars(OWNER_SITE_URL, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="admin-ops-chip"><i class="fa fa-paper-plane"></i> 官方入口</a>
            <a href="./changelog.php" class="admin-ops-chip"><i class="fa fa-list-alt"></i> 更新日志</a>
        </div>
    </section>

    <section class="admin-ops-stats">
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--primary"><i class="fa fa-user-circle"></i></span>
            <div><span>维护方</span><strong><?php echo htmlspecialchars(OWNER_NAME, ENT_QUOTES, 'UTF-8'); ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--success"><i class="fa fa-at"></i></span>
            <div><span>账号</span><strong><?php echo htmlspecialchars(OWNER_HANDLE, ENT_QUOTES, 'UTF-8'); ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--warning"><i class="fa fa-comments"></i></span>
            <div><span>客服/群组</span><strong><?php echo htmlspecialchars(OWNER_CONTACT, ENT_QUOTES, 'UTF-8'); ?></strong></div>
        </article>
        <article class="admin-ops-stat admin-ui-stat">
            <span class="admin-ops-stat__icon admin-ui-stat__icon admin-ops-stat__icon--accent"><i class="fa fa-shield"></i></span>
            <div><span>页面用途</span><strong>维护信息</strong></div>
        </article>
    </section>

    <div class="block admin-ops-panel">
        <div class="block-title">
            <div>
                <h3>官方维护信息</h3>
                <p>用于后台维护、问题反馈和版本支持。</p>
            </div>
        </div>
        <div class="block-content">
            <div class="table-responsive">
                <table class="table table-striped admin-ops-table">
                    <tbody>
                    <tr>
                        <th>官方维护</th>
                        <td><?php echo htmlspecialchars(OWNER_NAME . ' ' . OWNER_HANDLE, ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <th>官网</th>
                        <td><a href="<?php echo htmlspecialchars(OWNER_SITE_URL, ENT_QUOTES, 'UTF-8'); ?>" target="_blank"><?php echo htmlspecialchars(OWNER_SITE_URL, ENT_QUOTES, 'UTF-8'); ?></a></td>
                    </tr>
                    <tr>
                        <th>客服/群组</th>
                        <td><?php echo htmlspecialchars(OWNER_CONTACT, ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <tr>
                        <th>USDT TRC20 赞助地址</th>
                        <td><input class="form-control" readonly value="<?php echo htmlspecialchars(OWNER_USDT_TRC20, ENT_QUOTES, 'UTF-8'); ?>"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="block admin-ops-panel">
        <div class="block-title">
            <div>
                <h3>广告展示位</h3>
                <p>后续远程更新时，直接修改本页 <code>$supportAds</code> 数组或替换对应本地图片，即可新增、下架或调整广告。</p>
            </div>
        </div>
        <div class="admin-support-ads">
            <?php foreach ($supportAds as $ad) { ?>
                <?php if ($ad['type'] === 'image') { ?>
                <article class="admin-support-ad admin-support-ad--image">
                    <img src="<?php echo htmlspecialchars($ad['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="admin-support-ad__body">
                        <h4><?php echo htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                        <p><?php echo htmlspecialchars($ad['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </article>
                <?php } else { ?>
                <article class="admin-support-ad admin-support-ad--text">
                    <span class="admin-support-ad__tag"><?php echo htmlspecialchars($ad['tag'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <div class="admin-support-ad__body">
                        <h4><?php echo htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                        <p><?php echo htmlspecialchars($ad['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </article>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>
<?php include "./foot.php"; ?>

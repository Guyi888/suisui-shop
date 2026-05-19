<?php
include "../includes/common.php";

$title = "联系与赞助";
if ($islogin != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

include "./head.php";
?>
<div class="col-xs-12 admin-ops-page admin-support-page">
    <section class="admin-ops-hero">
        <div>
            <p class="admin-ops-hero__eyebrow">维护信息</p>
            <h2>联系与赞助</h2>
            <p>本页仅展示程序维护方信息，不影响前台站长自行配置的客服联系方式。</p>
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
</div>
</body>
</html>

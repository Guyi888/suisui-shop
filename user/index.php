<?php
require '../includes/common.php';
if($islogin2==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

if($_GET['mod']=='faka'){
exit("<script language='javascript'>window.location.href='../?mod=faka&&id={$_GET['id']}&skey={$_GET['skey']}';</script>");
}
$title = '平台首页';
include 'head.php';
?>
<link rel="stylesheet" href="<?php echo $cdnpublic?>toastr.js/latest/css/toastr.min.css">
<style>
/* 首页特定样式 */
.dashboard-container {
    padding: 0;
}

/* 警告提示 */
.alert-warning {
    background: #fff3cd;
    border: 1px solid #ffc107;
    color: #856404;
    border-radius: var(--radius-md);
    padding: 16px;
    margin-bottom: 24px;
}

/* 主面板 */
.main-panel {
    background: var(--bg-secondary);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
}

.panel-header {
    background: var(--bg-tertiary);
    padding: 12px 20px;
    border-bottom: 1px solid var(--border-color);
    border-radius: var(--radius-md) var(--radius-md) 0 0;
}

.panel-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
}

.panel-title i {
    margin-right: 10px;
    color: var(--primary-color);
}

.panel-body {
    padding: 24px;
}

/* 用户信息区 */
.user-profile-section {
    display: flex;
    align-items: flex-start;
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--border-color);
}

.user-avatar {
    margin-right: 20px;
}

.user-avatar img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid var(--border-color);
}

.user-balance-info {
    flex: 1;
}

.balance-title {
    font-size: 16px;
    color: var(--text-secondary);
    margin-bottom: 8px;
}

.balance-amount {
    font-size: 28px;
    font-weight: 700;
    color: var(--success-color);
    margin-bottom: 16px;
}

.action-buttons a {
    margin-right: 12px;
}

/* 信息卡片网格 */
.info-cards-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 32px;
}

.info-card {
    background: var(--bg-tertiary);
    padding: 20px;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    text-align: center;
    transition: all var(--transition-fast);
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.info-card-label {
    font-size: 14px;
    color: var(--text-secondary);
    margin-bottom: 8px;
}

.info-card-value {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
}

/* 功能网格 */
.function-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.function-item {
    position: relative;
}

.function-link {
    display: block;
    background: var(--bg-secondary);
    padding: 20px;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    text-align: center;
    color: var(--text-primary);
    text-decoration: none;
    transition: all var(--transition-fast);
}

.function-link:hover {
    background: var(--bg-tertiary);
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.function-link.danger:hover {
    border-color: var(--danger-color);
    background: #fff5f5;
}

.function-icon {
    font-size: 28px;
    margin-bottom: 12px;
    color: var(--primary-color);
}

.function-link.danger .function-icon {
    color: var(--danger-color);
}

.function-label {
    font-size: 14px;
    font-weight: 500;
}

.notification-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: var(--danger-color);
    color: white;
    font-size: 12px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
    min-width: 20px;
    text-align: center;
}

/* 站点信息区 */
.site-info-section {
    margin-top: 24px;
}

.site-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 20px;
    text-align: center;
}

.site-info-list {
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
    padding: 20px;
    border: 1px solid var(--border-color);
}

.site-info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
}

.site-info-item:last-child {
    border-bottom: none;
}

.site-info-item.warning {
    background: #fff3cd;
    margin: -12px -20px;
    padding: 16px 20px;
    border-radius: var(--radius-md);
}

.site-info-label {
    font-weight: 500;
    color: var(--text-secondary);
}

.site-info-value {
    display: flex;
    align-items: center;
}

.domain-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.domain-link:hover {
    text-decoration: underline;
}

.fanghong-link {
    color: var(--primary-color);
    cursor: pointer;
    font-weight: 500;
}

.fanghong-link:hover {
    text-decoration: underline;
}

.site-status.normal {
    color: var(--success-color);
    font-weight: 600;
}

.site-status.expired {
    color: var(--danger-color);
    font-weight: 600;
}

/* 用户信息整合区域 */
.user-info-combined {
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
    border-radius: var(--radius-lg);
    padding: 16px;
    margin-bottom: 20px;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
}

.user-info-right {
    display: block;
}

@media (min-width: 992px) {
    .user-info-combined {
        display: flex;
        align-items: flex-start;
        gap: 24px;
    }

    .user-info-right {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
}

/* 减少dashboard容器顶部空白 */
.dashboard-container {
    padding-top: 0 !important;
}

/* 减少主面板顶部空白 */
.main-panel {
    margin-top: 0 !important;
}

.panel-body {
    padding-top: 16px !important;
}

.user-info-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color);
}

.user-info-header .user-avatar img {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 3px solid var(--primary-color);
    box-shadow: var(--shadow-sm);
}

.user-info-main {
    flex: 1;
}

.user-name {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 6px;
}

.user-info-header .balance-title {
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 4px;
}

.user-info-header .balance-amount {
    font-size: 24px;
    font-weight: 700;
    color: var(--success-color);
}

.user-info-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 12px;
}

.user-stat-item {
    background: var(--bg-secondary);
    padding: 12px 16px;
    border-radius: var(--radius-md);
    text-align: center;
    border: 1px solid var(--border-color);
    transition: all var(--transition-fast);
}

.user-stat-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.stat-label {
    font-size: 12px;
    color: var(--text-secondary);
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-primary);
}

.stat-value.income-amount {
    color: var(--success-color);
}

.user-info-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.user-info-actions .btn {
    flex: 1;
    padding: 10px 16px;
    font-weight: 500;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

/* 响应式 - 手机端 */
@media (max-width: 767.98px) {
    .user-info-combined {
        padding: 16px;
    }

    .user-info-header {
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }

    .user-info-header .user-avatar img {
        width: 60px;
        height: 60px;
    }

    .user-name {
        font-size: 18px;
    }

    .user-info-header .balance-amount {
        font-size: 22px;
    }

    .user-info-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .user-stat-item {
        padding: 10px 12px;
    }

    .stat-value {
        font-size: 16px;
    }

    .user-info-actions {
        flex-direction: column;
    }
}

.no-site-message {
    text-align: center;
    padding: 20px;
    color: var(--text-secondary);
}

/* 公告面板 */
.announcement-panel {
    margin-top: 24px;
}

.announcement-content {
    padding: 16px 20px;
    color: var(--text-secondary);
    line-height: 1.6;
}

/* 客服按钮 */
.service-button {
    margin-top: 24px;
    display: block;
    width: 100%;
}

/* 响应式 */
@media (max-width: 991px) {
    .user-profile-section {
        flex-direction: column;
        text-align: center;
    }

    .user-avatar {
        margin-right: 0;
        margin-bottom: 16px;
    }

    .action-buttons {
        margin-top: 16px;
    }

    .action-buttons a {
        margin: 0 6px;
    }

    .site-info-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .site-info-value {
        margin-top: 8px;
    }
}

/* 768px宽时显示3列 */
@media (min-width: 768px) and (max-width: 991px) {
    .info-cards-grid,
    .function-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* 767px以下显示1列 */
@media (max-width: 767px) {
    .info-cards-grid,
    .function-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="dashboard-container">
    <!-- 安全提示 -->
    <?php if($userrow['rmb']>4): ?>
        <?php if(strlen($userrow['pwd'])<6 || is_numeric($userrow['pwd']) && strlen($userrow['pwd'])<=10 || $userrow['pwd']==$userrow['qq']): ?>
            <div class="alert-warning">
                <span class="btn btn-danger btn-xs" style="background-color: #eb6060;">重要</span>
                你的密码过于简单，请不要使用较短的纯数字或自己的QQ号当做密码，以免造成资金损失！
                <a href="uset.php?mod=user" class="btn btn-primary btn-xs ml-2" style="background-color: #9aeafe;">点此修改密码</a>
            </div>
        <?php elseif($userrow['user']==$userrow['pwd']): ?>
            <div class="alert-warning">
                <span class="btn btn-danger btn-xs" style="background-color: #eb6060;">重要</span>
                你的用户名与密码相同，极易被黑客破解，请及时修改密码
                <a href="uset.php?mod=user" class="btn btn-primary btn-xs ml-2" style="background-color: #9aeafe;">点此修改密码</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- 主面板 -->
    <div class="main-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class="fa fa-user-circle"></i> 我的信息与站点管理</h3>
        </div>
        <div class="panel-body">
            <!-- 用户信息整合区域 -->
            <div class="user-info-combined">
                <div class="user-info-header">
                        <div class="user-avatar">
                            <img src="<?php echo $faceimg ?>" alt="用户头像">
                        </div>
                        <div class="user-info-main">
                            <div class="user-name"><?php echo $nickname ?></div>
                            <div class="user-balance-info">
                                <div class="balance-title">账户余额</div>
                                <div class="balance-amount"><?php echo $userrow['rmb'] ?> 元</div>
                            </div>
                        </div>
                    </div>

                    <div class="user-info-right">
                        <div class="user-info-stats">
                            <div class="user-stat-item">
                                <div class="stat-label">UID</div>
                                <div class="stat-value"><?php echo $userrow['zid'] ?></div>
                            </div>
                            <div class="user-stat-item">
                                <div class="stat-label">今日收益</div>
                                <div class="stat-value income-amount" id="income_today">0 元</div>
                            </div>
                        </div>

                        <div class="user-info-actions">
                            <a href="recharge.php" class="btn btn-success btn-sm" style="background-color: #53d0a6;"><i class="fa fa-plus"></i> 充值余额</a>
                            <a href="tixian.php" class="btn btn-info btn-sm" style="background-color: #5ac9dd;"><i class="fa fa-arrow-up"></i> 申请提现</a>
                        </div>
                    </div>
            </div>

            <!-- 功能网格 - 第一行 -->
            <div class="function-grid">
                <div class="function-item">
                    <a href="/user/shop.php" class="function-link">
                        <i class="fa fa-shopping-cart function-icon"></i>
                        <div class="function-label"><?php echo $userrow['power']>0?'低价下单':'自助下单' ?></div>
                    </a>
                </div>
                <div class="function-item">
                    <?php if($conf['qiandao_reward']): ?>
                        <a href="./qiandao.php" class="function-link">
                            <i class="fa fa-check-square function-icon"></i>
                            <div class="function-label">每日签到</div>
                        </a>
                    <?php else: ?>
                        <a href="recharge.php" class="function-link">
                            <i class="fa fa-money function-icon"></i>
                            <div class="function-label">充值余额</div>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="function-item">
                    <a href="message.php" class="function-link">
                        <i class="fa fa-bullhorn function-icon"></i>
                        <div class="function-label">站内消息</div>
                        <span id="message_count" class="notification-badge"></span>
                    </a>
                </div>
            </div>

            <!-- 功能网格 - 第二行 -->
            <div class="function-grid">
                <div class="function-item">
                    <a href="<?php echo $userrow['power']>0?'./shop.php?chadan=1':'../?chadan=1' ?>" class="function-link">
                        <i class="fa fa-search function-icon"></i>
                        <div class="function-label">自助查单</div>
                    </a>
                </div>
                <div class="function-item">
                    <a href="./workorder.php" class="function-link">
                        <i class="fa fa-check-square-o function-icon"></i>
                        <div class="function-label">我的工单</div>
                        <span id="work_count" class="notification-badge"></span>
                    </a>
                </div>
                <div class="function-item">
                    <a href="record.php" class="function-link">
                        <i class="fa fa-hashtag function-icon"></i>
                        <div class="function-label">收支明细</div>
                    </a>
                </div>
            </div>

            <?php if($userrow['power']>0): ?>
            <!-- 功能网格 - 第三行（站长） -->
            <div class="function-grid">
                <div class="function-item">
                    <a href="shoplist.php" class="function-link">
                        <i class="fa fa-list-alt function-icon"></i>
                        <div class="function-label">商品管理</div>
                    </a>
                </div>
                <div class="function-item">
                    <a href="list.php" class="function-link">
                        <i class="fa fa-list function-icon"></i>
                        <div class="function-label">订单记录</div>
                    </a>
                </div>
                <div class="function-item">
                    <?php if($userrow['power']==2): ?>
                        <a href="sitelist.php" class="function-link">
                            <i class="fa fa-sitemap function-icon"></i>
                            <div class="function-label">分站管理</div>
                        </a>
                    <?php else: ?>
                        <a href="login.php?logout" class="function-link danger">
                            <i class="fa fa-sign-out function-icon"></i>
                            <div class="function-label">安全退出</div>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 功能网格 - 第四行 -->
            <div class="function-grid">
                <div class="function-item">
                    <a href="cdomain.php" class="function-link">
                        <i class="fa fa-exchange function-icon"></i>
                        <div class="function-label">域名更换</div>
                    </a>
                </div>
                <div class="function-item">
                    <a href="ndomain.php" class="function-link">
                        <i class="fa fa-plus-circle function-icon"></i>
                        <div class="function-label">域名增加</div>
                    </a>
                </div>
                <div class="function-item">
                    <a href="usetmoban.php?mod=site2" class="function-link">
                        <i class="fa fa-home function-icon"></i>
                        <div class="function-label">模板设置</div>
                    </a>
                </div>
            </div>

            <!-- 功能网格 - 第五行 -->
            <div class="function-grid">
                <div class="function-item">
                    <a href="../sup" class="function-link">
                        <i class="fa fa-check-square function-icon"></i>
                        <div class="function-label">供货管理</div>
                    </a>
                </div>
                <div class="function-item">
                    <a href="../toollogs.php" class="function-link">
                        <i class="fa fa-list function-icon"></i>
                        <div class="function-label">上架日志</div>
                    </a>
                </div>
                <div class="function-item">
                    <a href="uset.php?mod=skimg" class="function-link">
                        <i class="fa fa-check-square function-icon"></i>
                        <div class="function-label">提现设置</div>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- 客服按钮 -->
            <a href="https://qqwxfh.github.io/?jOTdN" class="btn btn-default btn-block service-button">
                <i class="fa fa-comments"></i> 【QQ微信失联点这里】站长专属客服
            </a>

            <!-- 站点信息 -->
            <div class="site-info-section">
                <h4 class="site-title">我的站点信息</h4>
                <div class="site-info-list">
                    <?php if($userrow['power']>0): ?>
                        <div class="site-info-item">
                            <div class="site-info-label">通知提醒</div>
                            <div class="site-info-value">
                                <span>你当前有 0 条信息未阅读</span>
                                <a href="./message.php" class="btn btn-primary btn-xs ml-4" style="margin-left: 20px; background-color: #659dec;">立即查看</a>
                            </div>
                        </div>
                        <div class="site-info-item">
                            <div class="site-info-label">我的域名①</div>
                            <div class="site-info-value">
                                <a href="http://<?php echo $userrow['domain'] ?>" target="_blank" rel="noreferrer" class="domain-link"><?php echo $userrow['domain'] ?></a>
                            </div>
                        </div>
                        <?php if($userrow['domain2']): ?>
                            <div class="site-info-item">
                                <div class="site-info-label">我的域名②</div>
                                <div class="site-info-value">
                                    <a href="http://<?php echo $userrow['domain2'] ?>" target="_blank" rel="noreferrer" class="domain-link"><?php echo $userrow['domain2'] ?></a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if($userrow['domain3']): ?>
                            <div class="site-info-item">
                                <div class="site-info-label">我的域名③</div>
                                <div class="site-info-value">
                                    <a href="http://<?php echo $userrow['domain3'] ?>" target="_blank" rel="noreferrer" class="domain-link"><?php echo $userrow['domain3'] ?></a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if($userrow['domain4']): ?>
                            <div class="site-info-item">
                                <div class="site-info-label">我的域名④</div>
                                <div class="site-info-value">
                                    <a href="http://<?php echo $userrow['domain4'] ?>" target="_blank" rel="noreferrer" class="domain-link"><?php echo $userrow['domain4'] ?></a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if($userrow['domain5']): ?>
                            <div class="site-info-item">
                                <div class="site-info-label">我的域名⑤</div>
                                <div class="site-info-value">
                                    <a href="http://<?php echo $userrow['domain5'] ?>" target="_blank" rel="noreferrer" class="domain-link"><?php echo $userrow['domain5'] ?></a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if($userrow['domain6']): ?>
                            <div class="site-info-item">
                                <div class="site-info-label">我的域名⑥</div>
                                <div class="site-info-value">
                                    <a href="http://<?php echo $userrow['domain6'] ?>" target="_blank" rel="noreferrer" class="domain-link"><?php echo $userrow['domain6'] ?></a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if($conf['fanghong_api']): ?>
                            <div class="site-info-item">
                                <div class="site-info-label">防红链接①</div>
                                <div class="site-info-value">
                                    <a href="javascript:;" id="copy-btn" data-clipboard-text="" class="fanghong-link">加载中...</a>
                                    <div class="mt-2">
                                        <button class="btn btn-default btn-xs" id="recreate_url">重新生成</button>
                                        <a href="javascript:void(0)" onclick="layer.alert('防红链接：该链接可以在QQ直接打开的您的网站，方便推广！<br/>Tips：点击短网址即可复制哦~<br/>推荐建议使用防红链接！如果更换防红链接，之前的也是能打开的',{icon:3,title:'小提示',skin:'layui-layer-molv layui-layer-wxd'});" class="btn btn-info btn-xs ml-2">说明</a>
                                    </div>
                                </div>
                            </div>
                            <?php if($userrow['domain2']): ?>
                                <div class="site-info-item">
                                    <div class="site-info-label">防红链接②</div>
                                    <div class="site-info-value">
                                        <a href="javascript:;" id="copy-btn2" data-clipboard-text="" class="fanghong-link">加载中...</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="site-info-item warning">
                                <div class="site-info-label">注意事项</div>
                                <div class="site-info-value">
                                    <span>为了保护你站点域名不被QQ/微信拦截，推荐使用防红链接！点击防红域名自动复制</span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="site-info-item">
                            <div class="site-info-label">网站名称</div>
                            <div class="site-info-value">
                                <span style="margin-right: 10px;"><?php echo $userrow['sitename'] ?></span>
                                <a href="uset.php?mod=site" class="btn btn-info btn-xs ml-3" style="background-color: #4fc8de;">立即更换</a>
                            </div>
                        </div>
                        <div class="site-info-item">
                            <div class="site-info-label">代理类型</div>
                            <div class="site-info-value">
                                <span style="color: <?php echo $userrow['power']==2?'#ef4444':'#f59e0b' ?>; font-weight:600; margin-right: 10px;"><?php echo $userrow['power']==2?'专业版':'普及版' ?></span>
                                <?php if($conf['fenzhan_upgrade']>0 && $userrow['power']==1): ?>
                                    <a href="upsite.php" class="btn btn-danger btn-xs ml-3">升级站点</a>
                                <?php else: ?>
                                    <a href="./sitelist.php" class="btn btn-danger btn-xs ml-3" style="background-color: #ef6b6b;">下级管理</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if($conf['fenzhan_expiry']>0): ?>
                            <div class="site-info-item">
                                <div class="site-info-label">到期时间</div>
                                <div class="site-info-value">
                                    <span style="margin-right: 10px;"><?php echo $userrow['endtime'] ?></span>
                                    <a href="renew.php" class="btn btn-primary btn-xs ml-3" style="background-color: #5c99f0;">立即续期</a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="site-info-item">
                            <div class="site-info-label">当前状态</div>
                            <div class="site-info-value">
                                <span class="site-status <?php echo ($conf['fenzhan_expiry']>0 && $userrow['endtime']<$date)?'expired':'normal' ?>">
                                    <?php echo ($conf['fenzhan_expiry']>0 && $userrow['endtime']<$date)?'已到期':'正常运行' ?>
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-site-message">
                            <span>你还未开通分站</span>
                            <a href="regsite.php" class="btn btn-primary btn-sm ml-3" style="background-color: #f6f9fe; color: #000000; border-color: #e0e7ff;">点此开通分站</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 公告面板 -->
    <?php if($islogin2==1): ?>
        <div class="main-panel announcement-panel">
            <div class="panel-header">
                <h3 class="panel-title"><i class="fa fa-volume-up"></i> 站点公告</h3>
            </div>
            <div class="panel-body">
                <div class="announcement-content">
                    <marquee>
                        <strong>最亲爱的站长祝愿：祝各位站长幸福安康，快乐美满，好事成双，生意兴隆，如果是卡密问题或者软件跑路购买的卡密都会退款或者重新给你换一款，低价提卡，诚信邀代理，欢迎新老站长回归加盟</strong>
                    </marquee>
                </div>
            </div>
        </div>
        <?php echo $conf['gg_panel'] ?>
    <?php endif; ?>
</div>

<script src="<?php echo $cdnpublic?>layer/3.1.1/layer.js"></script>
<script src="<?php echo $cdnpublic?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script src="<?php echo $cdnpublic?>toastr.js/latest/toastr.min.js"></script>
<script>
$(document).ready(function(){
    // 剪贴板功能
    var clipboard = new Clipboard('#copy-btn');
    clipboard.on('success', function(e) {
        layer.msg('复制成功！', {icon:1});
    });
    clipboard.on('error', function(e) {
        layer.msg('复制失败，请长按链接后手动复制', {icon:2});
    });

    var clipboard2 = new Clipboard('#copy-btn2');
    clipboard2.on('success', function(e) {
        layer.msg('复制成功！', {icon:1});
    });
    clipboard2.on('error', function(e) {
        layer.msg('复制失败，请长按链接后手动复制', {icon:2});
    });

    // 重新生成防红链接
    $("#recreate_url").click(function(){
        var self = $(this);
        if (self.attr("data-lock") === "true") return;
        else self.attr("data-lock", "true");
        var ii = layer.load(1, {shade: [0.1, '#fff']});
        $.get("ajax.php?act=create_url&force=1", function(data) {
            layer.close(ii);
            if(data.code == 0){
                layer.msg('生成链接成功');
                $("#copy-btn").html(data.url);
                $("#copy-btn").attr('data-clipboard-text', data.url);
                if($("#copy-btn2").length > 0){
                    $("#copy-btn2").html(data.url2);
                    $("#copy-btn2").attr('data-clipboard-text', data.url2);
                }
            } else {
                layer.alert(data.msg);
            }
            self.attr("data-lock", "false");
        }, 'json');
    });

    // 充值弹窗
    if(window.location.hash == '#chongzhi'){
        $("#userjs").modal('show');
    }

    // 获取消息和收益信息
    $.ajax({
        type: "GET",
        url: "ajax.php?act=msg",
        dataType: 'json',
        async: true,
        success: function(data) {
            if(data.code == 0){
                if(data.count > 0){
                    $("#message_count").text(data.count).show();
                    toastr.info('<a href="message.php">您有<b>'+data.count+'</b>条新消息，请注意查收！</a>', '消息提醒');
                }
                if(data.count2 > 0){
                    $("#work_count").text(data.count2).show();
                    toastr.warning('<a href="workorder.php">您有<b>'+data.count2+'</b>个工单已被管理员回复！</a>', '工单提醒');
                }
                $("#income_today").html(data.income_today+' 元');
            }
        }
    });

    // 加载防红链接
    $.ajax({
        type: "GET",
        url: "ajax.php?act=create_url",
        dataType: 'json',
        async: true,
        success: function(data) {
            if(data.code == 0){
                $("#copy-btn").html(data.url);
                $("#copy-btn").attr('data-clipboard-text', data.url);
                if($("#copy-btn2").length > 0){
                    $("#copy-btn2").html(data.url2);
                    $("#copy-btn2").attr('data-clipboard-text', data.url2);
                }
            } else {
                $("#copy-btn").html(data.msg);
            }
        }
    });
});

// 子菜单切换
function toggleSubmenu(el) {
    var submenu = $(el).next('.nav-sub');
    var icon = $(el).find('.pull-right i');

    if (submenu.hasClass('open')) {
        submenu.removeClass('open');
        icon.removeClass('fa-angle-down').addClass('fa-angle-right');
    } else {
        // 先关闭其他子菜单
        $('.nav-sub.open').removeClass('open');
        $('.nav-sub').prev('.auto').find('.pull-right i').removeClass('fa-angle-down').addClass('fa-angle-right');
        // 打开当前
        submenu.addClass('open');
        icon.removeClass('fa-angle-right').addClass('fa-angle-down');
    }
}
</script>

<?php include_once SYSTEM_ROOT.'sakura.php'; loadSakuraEffect(); ?>
<?php include 'foot.php'; ?>
<?php
include '../includes/common.php';
if ($islogin2 != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

function q8_sitelist_text($encoded)
{
    return html_entity_decode($encoded, ENT_QUOTES, 'UTF-8');
}

function q8_sitelist_escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function q8_sitelist_url($params = array())
{
    $query = http_build_query($params);
    return './sitelist.php' . ($query !== '' ? '?' . $query : '');
}

function q8_sitelist_base_params()
{
    $params = array();
    if (isset($_GET['page']) && intval($_GET['page']) > 1) {
        $params['page'] = intval($_GET['page']);
    }
    if (isset($_GET['kw']) && trim((string)$_GET['kw']) !== '') {
        $params['kw'] = trim((string)$_GET['kw']);
    }
    if (isset($_GET['zid']) && intval($_GET['zid']) > 0) {
        $params['zid'] = intval($_GET['zid']);
    }
    return $params;
}

function q8_sitelist_back_link()
{
    return q8_sitelist_url(q8_sitelist_base_params());
}

function q8_sitelist_alert($message, $redirect = '')
{
    $messageJson = json_encode((string)$message);
    $redirectJson = json_encode($redirect !== '' ? $redirect : q8_sitelist_back_link());
    exit("<script>window.alert({$messageJson});window.location.href={$redirectJson};</script>");
}

function q8_sitelist_find_child_site($DB, $parentZid, $zid)
{
    return $DB->getRow(
        "SELECT * FROM pre_site WHERE zid=:zid AND upzid=:upzid AND power>0 LIMIT 1",
        array(':zid' => $zid, ':upzid' => $parentZid)
    );
}

function q8_sitelist_power_label($power)
{
    $power = intval($power);
    if ($power === 2) {
        return q8_sitelist_text('&#19987;&#19994;&#29256;');
    }
    return q8_sitelist_text('&#26222;&#21450;&#29256;');
}

function q8_sitelist_power_badge($power)
{
    $power = intval($power);
    $className = $power === 2 ? 'danger' : 'warning';
    return '<span class="label label-' . $className . '">' . q8_sitelist_escape(q8_sitelist_power_label($power)) . '</span>';
}

function q8_sitelist_allowed_domains($conf)
{
    $domains = array();
    foreach (explode(',', (string)$conf['fenzhan_domain']) as $domain) {
        $domain = trim(strtolower($domain));
        if ($domain !== '') {
            $domains[] = $domain;
        }
    }
    return array_values(array_unique($domains));
}

if ($userrow['power'] < 2 || (function_exists('q8_site_can_create_child_site') && !q8_site_can_create_child_site($userrow))) {
    showmsg(q8_sitelist_text('&#20320;&#27809;&#26377;&#26435;&#38480;&#20351;&#29992;&#27492;&#21151;&#33021;'), 3);
}

$title = q8_sitelist_text('&#20998;&#31449;&#31649;&#29702;');
include './head.php';

$my = isset($_GET['my']) ? trim((string)$_GET['my']) : '';
$allowedDomains = q8_sitelist_allowed_domains($conf);
$domainOptionsHtml = '';
foreach ($allowedDomains as $allowedDomain) {
    $domainOptionsHtml .= '<option value="' . q8_sitelist_escape($allowedDomain) . '">' . q8_sitelist_escape($allowedDomain) . '</option>';
}

if ($my === 'add_submit') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !checkRefererHost()) exit();
    if (!$conf['fenzhan_adds']) {
        showmsg(q8_sitelist_text('&#35831;&#22312;&#21069;&#21488;&#24320;&#36890;&#20998;&#31449;'), 3);
    }

    $user = trim((string)(isset($_POST['user']) ? $_POST['user'] : ''));
    $pwd = trim((string)(isset($_POST['pwd']) ? $_POST['pwd'] : ''));
    $kind = intval(isset($_POST['kind']) ? $_POST['kind'] : 1);
    $prefix = trim(strtolower((string)(isset($_POST['qz']) ? $_POST['qz'] : '')));
    $domainSuffix = trim(strtolower((string)(isset($_POST['domain']) ? $_POST['domain'] : '')));
    $qq = trim((string)(isset($_POST['qq']) ? $_POST['qq'] : ''));
    $endtime = trim((string)(isset($_POST['endtime']) ? $_POST['endtime'] : ''));
    $sitename = trim((string)(isset($_POST['sitename']) ? $_POST['sitename'] : ''));
    $domain = $prefix !== '' && $domainSuffix !== '' ? ($prefix . '.' . $domainSuffix) : '';
    $todayStart = date('Y-m-d') . ' 00:00:00';

    if ($user === '' || $pwd === '' || $prefix === '' || $domainSuffix === '' || $endtime === '' || $sitename === '') {
        showmsg(q8_sitelist_text('&#20445;&#23384;&#38169;&#35823;&#65292;&#35831;&#30830;&#20445;&#24517;&#22635;&#39033;&#37117;&#24050;&#23436;&#25104;'), 3);
    } elseif ($kind !== 1 && $kind !== 2) {
        showmsg(q8_sitelist_text('&#20998;&#31449;&#31867;&#22411;&#38169;&#35823;'), 3);
    } elseif (!in_array($domainSuffix, $allowedDomains, true)) {
        showmsg(q8_sitelist_text('&#22495;&#21517;&#21518;&#32512;&#19981;&#23384;&#22312;'));
    } elseif (strlen($prefix) < 2 || strlen($prefix) > 10 || !preg_match('/^[a-z0-9\-]+$/', $prefix)) {
        showmsg(q8_sitelist_text('&#22495;&#21517;&#21069;&#32512;&#19981;&#21512;&#26684;'));
    } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $user)) {
        showmsg(q8_sitelist_text('&#29992;&#25143;&#21517;&#21482;&#33021;&#20026;&#33521;&#25991;&#25110;&#25968;&#23383;'));
    } elseif (!preg_match('/^[a-zA-Z0-9\_\-\.]+$/', $domain)) {
        showmsg(q8_sitelist_text('&#22495;&#21517;&#26684;&#24335;&#19981;&#27491;&#30830;'));
    } elseif ($DB->getRow("SELECT zid FROM pre_site WHERE user=:user LIMIT 1", array(':user' => $user))) {
        showmsg(q8_sitelist_text('&#29992;&#25143;&#21517;&#24050;&#23384;&#22312;'));
    } elseif (strlen($pwd) < 6) {
        showmsg(q8_sitelist_text('&#23494;&#30721;&#19981;&#33021;&#20302;&#20110; 6 &#20301;'));
    } elseif (strlen($sitename) < 2) {
        showmsg(q8_sitelist_text('&#32593;&#31449;&#21517;&#31216;&#22826;&#30701;'));
    } elseif (strlen($qq) < 5 || !preg_match('/^[0-9]+$/', $qq)) {
        showmsg(q8_sitelist_text('QQ &#26684;&#24335;&#19981;&#27491;&#30830;'));
    } elseif ($DB->getRow("SELECT zid FROM pre_site WHERE domain=:domain OR domain2=:domain LIMIT 1", array(':domain' => $domain)) || $prefix === 'www' || $domain === $_SERVER['HTTP_HOST'] || in_array($domain, explode(',', (string)$conf['fenzhan_remain']), true)) {
        showmsg(q8_sitelist_text('&#35813;&#22495;&#21517;&#24050;&#34987;&#20351;&#29992;'));
    } elseif (intval($DB->getColumn("SELECT count(*) FROM pre_site WHERE upzid=:upzid AND addtime>:today_start", array(':upzid' => $userrow['zid'], ':today_start' => $todayStart))) > 20) {
        showmsg(q8_sitelist_text('&#20170;&#26085;&#26032;&#22686;&#20998;&#31449;&#36807;&#22810;&#65292;&#35831;&#20351;&#29992;&#21069;&#21488;&#33258;&#21161;&#24320;&#36890;'), 3);
    } else {
        $payload = array(
            'upzid' => intval($userrow['zid']),
            'power' => $kind,
            'domain' => $domain,
            'domain2' => null,
            'user' => $user,
            'pwd' => $pwd,
            'rmb' => '0.00',
            'qq' => $qq,
            'sitename' => $sitename,
            'title' => $conf['title'],
            'keywords' => $conf['keywords'],
            'description' => $conf['description'],
            'anounce' => !empty($conf['fenzhan_html']) ? $conf['anounce'] : '',
            'alert' => !empty($conf['fenzhan_html']) ? $conf['alert'] : '',
            'addtime' => $date,
            'endtime' => $endtime,
            'status' => 1
        );
        if (q8_insert_site_account($payload, $conf, $date) !== false) {
            q8_sitelist_alert(q8_sitelist_text('&#28155;&#21152;&#20998;&#31449;&#25104;&#21151;'), q8_sitelist_back_link());
        }
        q8_sitelist_alert(q8_sitelist_text('&#28155;&#21152;&#20998;&#31449;&#22833;&#36133;&#65306;') . $DB->error());
    }
}

if ($my === 'edit_submit') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !checkRefererHost()) exit();
    $zid = intval(isset($_GET['zid']) ? $_GET['zid'] : 0);
    $row = q8_sitelist_find_child_site($DB, $userrow['zid'], $zid);
    if (!$row) {
        showmsg(q8_sitelist_text('&#24403;&#21069;&#20998;&#31449;&#19981;&#23384;&#22312;&#25110;&#19981;&#23646;&#20110;&#20320;'), 3);
    }

    $domain2 = trim(strtolower((string)(isset($_POST['domain2']) ? $_POST['domain2'] : '')));
    $sitename = trim((string)(isset($_POST['sitename']) ? $_POST['sitename'] : ''));
    $endtime = trim((string)(isset($_POST['endtime']) ? $_POST['endtime'] : ''));

    if ($sitename === '' || $endtime === '') {
        showmsg(q8_sitelist_text('&#20445;&#23384;&#38169;&#35823;&#65292;&#35831;&#30830;&#20445;&#24517;&#22635;&#39033;&#19981;&#20026;&#31354;'), 3);
    } elseif ($domain2 !== '' && !preg_match('/^[a-zA-Z0-9\_\-\.]+$/', $domain2)) {
        showmsg(q8_sitelist_text('&#22495;&#21517;&#26684;&#24335;&#19981;&#27491;&#30830;'));
    } elseif ((!empty($domain2) && $DB->getRow("SELECT zid FROM pre_site WHERE (domain=:domain OR domain2=:domain) AND zid!=:zid LIMIT 1", array(':domain' => $domain2, ':zid' => $zid))) || $domain2 === $_SERVER['HTTP_HOST'] || (!empty($domain2) && (in_array($domain2, explode(',', (string)$conf['fenzhan_remain']), true) || in_array($domain2, $allowedDomains, true)))) {
        showmsg(q8_sitelist_text('&#27492;&#22495;&#21517;&#24050;&#34987;&#20351;&#29992;'));
    } else {
        if (strpos($domain2, 'www.') === 0) {
            $plainDomain = substr($domain2, 4);
            if (in_array($plainDomain, explode(',', (string)$conf['fenzhan_remain']), true) || in_array($plainDomain, $allowedDomains, true)) {
                showmsg(q8_sitelist_text('&#27492;&#22495;&#21517;&#24050;&#34987;&#20351;&#29992;'));
            }
        }

        if ($DB->exec(
            "UPDATE pre_site SET domain2=:domain2,sitename=:sitename,endtime=:endtime WHERE zid=:zid AND upzid=:upzid AND power>0",
            array(':domain2' => $domain2, ':sitename' => $sitename, ':endtime' => $endtime, ':zid' => $zid, ':upzid' => $userrow['zid'])
        ) !== false) {
            q8_sitelist_alert(q8_sitelist_text('&#20462;&#25913;&#20998;&#31449;&#25104;&#21151;'), q8_sitelist_back_link());
        }
        q8_sitelist_alert(q8_sitelist_text('&#20462;&#25913;&#20998;&#31449;&#22833;&#36133;&#65306;') . $DB->error());
    }
}

if ($my === 'delete_submit') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !checkRefererHost()) exit();
    $zid = intval(isset($_GET['zid']) ? $_GET['zid'] : 0);
    $row = q8_sitelist_find_child_site($DB, $userrow['zid'], $zid);
    if (!$row) {
        showmsg(q8_sitelist_text('&#24403;&#21069;&#20998;&#31449;&#19981;&#23384;&#22312;&#25110;&#19981;&#23646;&#20110;&#20320;'), 3);
    }
    if (floatval($row['rmb']) >= 1) {
        showmsg(q8_sitelist_text('&#24403;&#21069;&#31449;&#28857;&#20313;&#39069;&#36739;&#22810;&#65292;&#26080;&#27861;&#21024;&#38500;'), 3);
    }
    if ($DB->exec("DELETE FROM pre_site WHERE zid=:zid AND upzid=:upzid AND power>0 LIMIT 1", array(':zid' => $zid, ':upzid' => $userrow['zid'])) !== false) {
        q8_sitelist_alert(q8_sitelist_text('&#21024;&#38500;&#20998;&#31449;&#25104;&#21151;'), q8_sitelist_back_link());
    }
    q8_sitelist_alert(q8_sitelist_text('&#21024;&#38500;&#20998;&#31449;&#22833;&#36133;&#65306;') . $DB->error());
}
?>
<link rel="stylesheet" href="./public/css/blue_theme.css">
<style>
.q8-sitelist-actions{
    display:grid;
    gap:10px;
    min-width:150px;
}
.q8-sitelist-actions .btn{
    width:100%;
    position:relative;
    z-index:2;
}
.q8-sitelist-meta{
    margin-top:6px;
    color:#64748b;
    font-size:12px;
    line-height:1.7;
}
</style>
<div class="wrapper">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="modal fade" align="left" id="search" tabindex="-1" role="dialog" aria-labelledby="siteSearchTitle" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h4 class="modal-title" id="siteSearchTitle">&#25628;&#32034;&#20998;&#31449;</h4>
                        </div>
                        <div class="modal-body">
                            <form action="./sitelist.php" method="GET">
                                <input type="text" class="form-control" name="kw" placeholder="&#35831;&#36755;&#20837;&#29992;&#25143;&#21517;&#12289;QQ&#12289;UID&#12289;&#22495;&#21517;&#25110;&#31449;&#28857;&#21517;&#31216;"><br/>
                                <input type="submit" class="btn btn-primary btn-block" value="&#25628;&#32034;">
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">&#20851;&#38381;</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            if ($my === 'add') {
            ?>
            <div class="panel-heading font-bold">&#28155;&#21152;&#19979;&#32423;&#20998;&#31449;</div>
            <div class="panel-body">
                <form action="./sitelist.php?my=add_submit" method="POST" role="form">
                    <div class="form-group">
                        <label>&#20998;&#31449;&#31867;&#22411;</label>
                        <select name="kind" class="form-control">
                            <option value="1">&#26222;&#21450;&#29256;&#20998;&#31449;</option>
                            <option value="2">&#19987;&#19994;&#29256;&#20998;&#31449;</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&#31649;&#29702;&#21592;&#29992;&#25143;&#21517;</label>
                        <input type="text" class="form-control" name="user" value="" required>
                    </div>
                    <div class="form-group">
                        <label>&#31649;&#29702;&#21592;&#23494;&#30721;</label>
                        <input type="text" class="form-control" name="pwd" value="123456" required>
                    </div>
                    <div class="form-group">
                        <label>&#32465;&#23450;&#22495;&#21517;</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="qz" value="" placeholder="&#36755;&#20837;&#20108;&#32423;&#21069;&#32512;" required>
                            <div class="input-group-addon" style="padding:0;border:0;background:transparent;">
                                <select name="domain" class="form-control" style="border-radius:0 4px 4px 0;"><?php echo $domainOptionsHtml; ?></select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>&#32593;&#31449;&#21517;&#31216;</label>
                        <input type="text" class="form-control" name="sitename" value="<?php echo q8_sitelist_escape($conf['sitename']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>&#31449;&#38271; QQ</label>
                        <input type="text" class="form-control" name="qq" value="" required>
                    </div>
                    <div class="form-group">
                        <label>&#21040;&#26399;&#26102;&#38388;</label>
                        <input type="date" class="form-control" name="endtime" value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" required>
                    </div>
                    <input type="submit" class="btn btn-primary btn-block" value="&#30830;&#35748;&#28155;&#21152;">
                </form>
                <br/>
                <a href="<?php echo q8_sitelist_escape(q8_sitelist_back_link()); ?>" class="external">&gt;&gt; <?php echo q8_sitelist_text('&#36820;&#22238;&#20998;&#31449;&#21015;&#34920;'); ?></a>
            </div>
            <?php
            } elseif ($my === 'edit') {
                $zid = intval(isset($_GET['zid']) ? $_GET['zid'] : 0);
                $row = q8_sitelist_find_child_site($DB, $userrow['zid'], $zid);
                if (!$row) {
                    showmsg(q8_sitelist_text('&#24403;&#21069;&#20998;&#31449;&#19981;&#23384;&#22312;&#25110;&#19981;&#23646;&#20110;&#20320;'), 3);
                }
            ?>
            <div class="panel-heading font-bold">&#20462;&#25913;&#20998;&#31449;&#20449;&#24687;</div>
            <div class="panel-body">
                <div class="alert alert-info">
                    <?php echo q8_sitelist_text('&#24403;&#21069;&#27491;&#22312;&#31649;&#29702;&#20998;&#31449;&#65306;'); ?><b><?php echo q8_sitelist_escape($row['user']); ?></b>
                    <span class="pull-right">ZID&#65306;<?php echo intval($row['zid']); ?></span>
                </div>
                <form action="./sitelist.php?my=edit_submit&amp;zid=<?php echo intval($row['zid']); ?>" method="POST" role="form">
                    <div class="form-group">
                        <label>&#20998;&#31449;&#31867;&#22411;</label>
                        <input type="text" class="form-control" value="<?php echo q8_sitelist_escape(q8_sitelist_power_label($row['power'])); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>&#32465;&#23450;&#22495;&#21517;</label>
                        <input type="text" class="form-control" value="<?php echo q8_sitelist_escape($row['domain']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>&#39069;&#22806;&#22495;&#21517;</label>
                        <input type="text" class="form-control" name="domain2" value="<?php echo q8_sitelist_escape($row['domain2']); ?>" placeholder="&#27809;&#26377;&#35831;&#30041;&#31354;">
                    </div>
                    <div class="form-group">
                        <label>&#31449;&#28857;&#21517;&#31216;</label>
                        <input type="text" class="form-control" name="sitename" value="<?php echo q8_sitelist_escape($row['sitename']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>&#21040;&#26399;&#26102;&#38388;</label>
                        <input type="date" class="form-control" name="endtime" value="<?php echo date('Y-m-d', strtotime($row['endtime'])); ?>" required>
                    </div>
                    <input type="submit" class="btn btn-primary btn-block" value="&#20445;&#23384;&#20998;&#31449;&#20449;&#24687;">
                </form>
                <br/>
                <a href="<?php echo q8_sitelist_escape(q8_sitelist_back_link()); ?>" class="external">&gt;&gt; <?php echo q8_sitelist_text('&#36820;&#22238;&#20998;&#31449;&#21015;&#34920;'); ?></a>
            </div>
            <?php
            } elseif ($my === 'delete') {
                $zid = intval(isset($_GET['zid']) ? $_GET['zid'] : 0);
                $row = q8_sitelist_find_child_site($DB, $userrow['zid'], $zid);
                if (!$row) {
                    showmsg(q8_sitelist_text('&#24403;&#21069;&#20998;&#31449;&#19981;&#23384;&#22312;&#25110;&#19981;&#23646;&#20110;&#20320;'), 3);
                }
            ?>
            <div class="panel-heading font-bold">&#21024;&#38500;&#20998;&#31449;</div>
            <div class="panel-body">
                <div class="alert alert-danger">
                    <?php echo q8_sitelist_text('&#20320;&#21363;&#23558;&#21024;&#38500;&#20998;&#31449;&#65306;'); ?><b><?php echo q8_sitelist_escape($row['user']); ?></b>
                    <span class="pull-right">ZID&#65306;<?php echo intval($row['zid']); ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 160px;">&#20998;&#31449;&#31867;&#22411;</th>
                                <td><?php echo q8_sitelist_power_badge($row['power']); ?></td>
                            </tr>
                            <tr>
                                <th>&#31449;&#28857;&#21517;&#31216;</th>
                                <td><?php echo q8_sitelist_escape($row['sitename']); ?></td>
                            </tr>
                            <tr>
                                <th>&#20313;&#39069;</th>
                                <td><?php echo q8_sitelist_escape($row['rmb']); ?></td>
                            </tr>
                            <tr>
                                <th>&#32465;&#23450;&#22495;&#21517;</th>
                                <td><?php echo q8_sitelist_escape($row['domain']); ?><?php if (!empty($row['domain2'])) echo '<br>' . q8_sitelist_escape($row['domain2']); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-warning">&#21024;&#38500;&#21069;&#35831;&#30830;&#35748;&#35813;&#20998;&#31449;&#24050;&#22791;&#20221;&#65292;&#19988;&#36134;&#21495;&#20313;&#39069;&#19981;&#36275; 1 &#20803;&#12290;</div>
                <form action="./sitelist.php?my=delete_submit&amp;zid=<?php echo intval($row['zid']); ?>" method="POST" role="form">
                    <button type="submit" class="btn btn-danger btn-block">&#30830;&#35748;&#21024;&#38500;&#35813;&#20998;&#31449;</button>
                </form>
                <br/>
                <a href="<?php echo q8_sitelist_escape(q8_sitelist_back_link()); ?>" class="external">&gt;&gt; <?php echo q8_sitelist_text('&#36820;&#22238;&#20998;&#31449;&#21015;&#34920;'); ?></a>
            </div>
            <?php
            } else {
                $params = array(':upzid' => $userrow['zid']);
                $where = "upzid=:upzid AND power>0";
                $linkParams = array();

                if (isset($_GET['zid']) && intval($_GET['zid']) > 0) {
                    $filterZid = intval($_GET['zid']);
                    $where .= " AND zid=:filter_zid";
                    $params[':filter_zid'] = $filterZid;
                    $linkParams['zid'] = $filterZid;
                } elseif (isset($_GET['kw']) && trim((string)$_GET['kw']) !== '') {
                    $kw = trim((string)$_GET['kw']);
                    $where .= " AND (user=:kw_user OR domain=:kw_domain OR domain2=:kw_domain2 OR qq=:kw_qq OR zid=:kw_zid OR sitename=:kw_sitename)";
                    $params[':kw_user'] = $kw;
                    $params[':kw_domain'] = $kw;
                    $params[':kw_domain2'] = $kw;
                    $params[':kw_qq'] = $kw;
                    $params[':kw_zid'] = intval($kw);
                    $params[':kw_sitename'] = $kw;
                    $linkParams['kw'] = $kw;
                }

                $numrows = intval($DB->getColumn("SELECT count(*) FROM pre_site WHERE {$where}", $params));
                $normalSiteCount = intval($DB->getColumn("SELECT count(*) FROM pre_site WHERE upzid=:upzid AND power=1", array(':upzid' => $userrow['zid'])));
                $proSiteCount = intval($DB->getColumn("SELECT count(*) FROM pre_site WHERE upzid=:upzid AND power=2", array(':upzid' => $userrow['zid'])));
                $pagesize = 30;
                $pages = max(1, ceil($numrows / $pagesize));
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                if ($page > $pages) {
                    $page = $pages;
                }
                $offset = $pagesize * ($page - 1);

                $rows = $DB->getAll(
                    "SELECT zid,user,sitename,qq,rmb,addtime,endtime,domain,domain2,power FROM pre_site WHERE {$where} ORDER BY zid DESC LIMIT {$offset},{$pagesize}",
                    $params
                );
                echo '<div class="alert alert-info">' . q8_sitelist_text('&#20320;&#20849;&#26377;') . ' <b>' . $numrows . '</b> ' . q8_sitelist_text('&#20010;&#19979;&#32423;&#20998;&#31449;') . ' &nbsp;|&nbsp; ' . q8_sitelist_text('&#26222;&#21450;&#29256;') . ' <b>' . $normalSiteCount . '</b> &nbsp;|&nbsp; ' . q8_sitelist_text('&#19987;&#19994;&#29256;') . ' <b>' . $proSiteCount . '</b><br/>' . (!empty($conf['fenzhan_adds']) ? '<a href="./sitelist.php?my=add" class="btn btn-primary external">' . q8_sitelist_text('&#28155;&#21152;&#20998;&#31449;') . '</a>&nbsp;' : '') . '<a href="#" data-toggle="modal" data-target="#search" class="btn btn-success">' . q8_sitelist_text('&#25628;&#32034;') . '</a></div>';
            ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>ZID</th>
                        <th><?php echo q8_sitelist_text('&#29992;&#25143;&#21517;'); ?></th>
                        <th><?php echo q8_sitelist_text('&#20998;&#31449;&#20449;&#24687;'); ?></th>
                        <th><?php echo q8_sitelist_text('&#20313;&#39069;'); ?></th>
                        <th><?php echo q8_sitelist_text('&#24320;&#36890; / &#21040;&#26399;&#26102;&#38388;'); ?></th>
                        <th><?php echo q8_sitelist_text('&#32465;&#23450;&#22495;&#21517;'); ?></th>
                        <th><?php echo q8_sitelist_text('&#25805;&#20316;'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($rows) {
                        foreach ($rows as $res) {
                            echo '<tr>';
                            echo '<td><b>' . intval($res['zid']) . '</b></td>';
                            echo '<td>' . q8_sitelist_escape($res['user']) . '</td>';
                            echo '<td><div><strong>' . q8_sitelist_escape($res['sitename']) . '</strong> ' . q8_sitelist_power_badge($res['power']) . '</div><div class="q8-sitelist-meta">QQ&#65306;' . q8_sitelist_escape($res['qq']) . '</div></td>';
                            echo '<td>' . q8_sitelist_escape($res['rmb']) . '</td>';
                            echo '<td>' . q8_sitelist_escape($res['addtime']) . '<br>' . q8_sitelist_escape($res['endtime']) . '</td>';
                            echo '<td>' . q8_sitelist_escape($res['domain']) . (!empty($res['domain2']) ? '<br>' . q8_sitelist_escape($res['domain2']) : '') . '</td>';
                            echo '<td><div class="q8-sitelist-actions">';
                            echo '<button type="button" class="btn btn-info" data-q8-sitelist-href="./sitelist.php?my=edit&amp;zid=' . intval($res['zid']) . '">' . q8_sitelist_text('&#32534;&#36753;') . '</button>';
                            echo '<button type="button" class="btn btn-danger" data-q8-sitelist-href="./sitelist.php?my=delete&amp;zid=' . intval($res['zid']) . '">' . q8_sitelist_text('&#21024;&#38500;') . '</button>';
                            echo '</div></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="7" class="text-center text-muted">' . q8_sitelist_text('&#26242;&#26080;&#31526;&#21512;&#26465;&#20214;&#30340;&#19979;&#32423;&#20998;&#31449;') . '</td></tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <?php
                echo '<ul class="pagination" style="margin-left:1em">';
                $firstParams = $linkParams;
                $firstParams['page'] = 1;
                $prevParams = $linkParams;
                $prevParams['page'] = max(1, $page - 1);
                $nextParams = $linkParams;
                $nextParams['page'] = min($pages, $page + 1);
                $lastParams = $linkParams;
                $lastParams['page'] = $pages;

                if ($page > 1) {
                    echo '<li><a href="' . q8_sitelist_escape(q8_sitelist_url($firstParams)) . '" class="external">' . q8_sitelist_text('&#39318;&#39029;') . '</a></li>';
                    echo '<li><a href="' . q8_sitelist_escape(q8_sitelist_url($prevParams)) . '" class="external">&laquo;</a></li>';
                } else {
                    echo '<li class="disabled"><a>' . q8_sitelist_text('&#39318;&#39029;') . '</a></li>';
                    echo '<li class="disabled"><a>&laquo;</a></li>';
                }

                $start = $page - 10 > 1 ? $page - 10 : 1;
                $end = $page + 10 < $pages ? $page + 10 : $pages;
                for ($i = $start; $i < $page; $i++) {
                    $pageParams = $linkParams;
                    $pageParams['page'] = $i;
                    echo '<li><a href="' . q8_sitelist_escape(q8_sitelist_url($pageParams)) . '" class="external">' . $i . '</a></li>';
                }
                echo '<li class="disabled"><a>' . $page . '</a></li>';
                for ($i = $page + 1; $i <= $end; $i++) {
                    $pageParams = $linkParams;
                    $pageParams['page'] = $i;
                    echo '<li><a href="' . q8_sitelist_escape(q8_sitelist_url($pageParams)) . '" class="external">' . $i . '</a></li>';
                }

                if ($page < $pages) {
                    echo '<li><a href="' . q8_sitelist_escape(q8_sitelist_url($nextParams)) . '" class="external">&raquo;</a></li>';
                    echo '<li><a href="' . q8_sitelist_escape(q8_sitelist_url($lastParams)) . '" class="external">' . q8_sitelist_text('&#23614;&#39029;') . '</a></li>';
                } else {
                    echo '<li class="disabled"><a>&raquo;</a></li>';
                    echo '<li class="disabled"><a>' . q8_sitelist_text('&#23614;&#39029;') . '</a></li>';
                }
                echo '</ul>';
            }
            ?>
        </div>
    </div>
</div>
<?php include './foot.php'; ?>
<script>
$(document).on('click', '[data-q8-sitelist-href]', function (event) {
    event.preventDefault();
    var href = $(this).attr('data-q8-sitelist-href');
    if (href) {
        window.location.href = href.replace(/&amp;/g, '&');
    }
});
</script>
</body>
</html>

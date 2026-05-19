<?php
include '../includes/common.php';
if ($islogin2 != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

function q8_userlist_text($encoded)
{
    return html_entity_decode($encoded, ENT_QUOTES, 'UTF-8');
}

function q8_userlist_escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function q8_userlist_url($params = array())
{
    $query = http_build_query($params);
    return './userlist.php' . ($query !== '' ? '?' . $query : '');
}

function q8_userlist_base_params()
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

function q8_userlist_back_link()
{
    return q8_userlist_url(q8_userlist_base_params());
}

function q8_userlist_alert($message, $redirect = '')
{
    $messageJson = json_encode((string)$message);
    $redirectJson = json_encode($redirect !== '' ? $redirect : q8_userlist_back_link());
    exit("<script>window.alert({$messageJson});window.location.href={$redirectJson};</script>");
}

function q8_userlist_find_child_account($DB, $parentZid, $zid)
{
    return $DB->getRow(
        "SELECT zid,user,qq,rmb,addtime,power,domain,domain2,sitename FROM pre_site WHERE zid=:zid AND upzid=:upzid LIMIT 1",
        array(':zid' => $zid, ':upzid' => $parentZid)
    );
}

function q8_userlist_find_child_user($DB, $parentZid, $zid)
{
    return $DB->getRow(
        "SELECT zid,user,qq,rmb,addtime,power,domain,domain2,sitename FROM pre_site WHERE zid=:zid AND upzid=:upzid AND power=0 LIMIT 1",
        array(':zid' => $zid, ':upzid' => $parentZid)
    );
}

function q8_userlist_power_label($power)
{
    $power = intval($power);
    if ($power === 2) {
        return q8_userlist_text('&#19987;&#19994;&#29256;&#20998;&#31449;');
    }
    if ($power === 1) {
        return q8_userlist_text('&#26222;&#21450;&#29256;&#20998;&#31449;');
    }
    return q8_userlist_text('&#26222;&#36890;&#29992;&#25143;');
}

function q8_userlist_power_badge($power)
{
    $power = intval($power);
    $className = 'default';
    if ($power === 2) {
        $className = 'danger';
    } elseif ($power === 1) {
        $className = 'warning';
    } elseif ($power === 0) {
        $className = 'success';
    }
    return '<span class="label label-' . $className . '">' . q8_userlist_escape(q8_userlist_power_label($power)) . '</span>';
}

if ($userrow['power'] < 1) {
    showmsg(q8_userlist_text('&#20320;&#27809;&#26377;&#26435;&#38480;&#20351;&#29992;&#27492;&#21151;&#33021;'), 3);
}

$title = q8_userlist_text('&#29992;&#25143;&#31649;&#29702;');
include './head.php';

$my = isset($_GET['my']) ? trim((string)$_GET['my']) : '';

if ($my === 'edit_submit') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !checkRefererHost()) exit();

    $zid = intval(isset($_GET['zid']) ? $_GET['zid'] : 0);
    $row = q8_userlist_find_child_user($DB, $userrow['zid'], $zid);
    if (!$row) {
        showmsg(q8_userlist_text('&#24403;&#21069;&#36134;&#21495;&#19981;&#23384;&#22312;&#65292;&#25110;&#24050;&#24320;&#36890;&#20998;&#31449;&#65292;&#35831;&#21040;&#23545;&#24212;&#31649;&#29702;&#39029;&#22788;&#29702;'), 3);
    }

    $qq = trim((string)(isset($_POST['qq']) ? $_POST['qq'] : ''));
    $pwd = trim((string)(isset($_POST['pwd']) ? $_POST['pwd'] : ''));

    if ($qq !== '' && !preg_match('/^[0-9]{5,11}$/', $qq)) {
        q8_userlist_alert(q8_userlist_text('QQ &#26684;&#24335;&#19981;&#27491;&#30830;'));
    }
    if ($pwd !== '' && strlen($pwd) < 6) {
        q8_userlist_alert(q8_userlist_text('&#23494;&#30721;&#19981;&#33021;&#20302;&#20110; 6 &#20301;'));
    }
    if ($pwd !== '' && !preg_match('/^[a-zA-Z0-9_!@#$~%^&*.,-]+$/', $pwd)) {
        q8_userlist_alert(q8_userlist_text('&#23494;&#30721;&#21482;&#33021;&#21253;&#21547;&#33521;&#25991;&#12289;&#25968;&#23383;&#21644;&#24120;&#35265;&#23433;&#20840;&#31526;&#21495;'));
    }

    $sql = "UPDATE pre_site SET qq=:qq";
    $data = array(
        ':qq' => $qq,
        ':zid' => $zid,
        ':upzid' => $userrow['zid'],
    );
    if ($pwd !== '') {
        $sql .= ",pwd=:pwd";
        $data[':pwd'] = $pwd;
    }
    $sql .= " WHERE zid=:zid AND upzid=:upzid AND power=0";

    if ($DB->exec($sql, $data) !== false) {
        q8_userlist_alert(q8_userlist_text('&#29992;&#25143;&#36164;&#26009;&#20445;&#23384;&#25104;&#21151;'), q8_userlist_back_link());
    }
    q8_userlist_alert(q8_userlist_text('&#29992;&#25143;&#36164;&#26009;&#20445;&#23384;&#22833;&#36133;&#65306;') . $DB->error());
}

if ($my === 'delete_submit') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !checkRefererHost()) exit();

    $zid = intval(isset($_GET['zid']) ? $_GET['zid'] : 0);
    $row = q8_userlist_find_child_user($DB, $userrow['zid'], $zid);
    if (!$row) {
        showmsg(q8_userlist_text('&#24403;&#21069;&#36134;&#21495;&#19981;&#23384;&#22312;&#65292;&#25110;&#24050;&#24320;&#36890;&#20998;&#31449;&#65292;&#35831;&#21040;&#20998;&#31449;&#21015;&#34920;&#20013;&#22788;&#29702;'), 3);
    }

    if ($DB->exec(
        "DELETE FROM pre_site WHERE zid=:zid AND upzid=:upzid AND power=0 LIMIT 1",
        array(':zid' => $zid, ':upzid' => $userrow['zid'])
    ) !== false) {
        q8_userlist_alert(q8_userlist_text('&#19979;&#32423;&#29992;&#25143;&#21024;&#38500;&#25104;&#21151;'), q8_userlist_back_link());
    }
    q8_userlist_alert(q8_userlist_text('&#19979;&#32423;&#29992;&#25143;&#21024;&#38500;&#22833;&#36133;&#65306;') . $DB->error());
}
?>
<link rel="stylesheet" href="./public/css/blue_theme.css">
<style>
.q8-userlist-actions{
    display:grid;
    gap:10px;
    min-width:150px;
}
.q8-userlist-actions .btn{
    width:100%;
    position:relative;
    z-index:2;
}
.q8-userlist-meta{
    margin-top:6px;
    color:#64748b;
    font-size:12px;
    line-height:1.7;
}
</style>
<div class="wrapper">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="modal fade" align="left" id="search" tabindex="-1" role="dialog" aria-labelledby="userSearchTitle" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h4 class="modal-title" id="userSearchTitle">&#25628;&#32034;&#19979;&#32423;&#36134;&#21495;</h4>
                        </div>
                        <div class="modal-body">
                            <form action="./userlist.php" method="GET">
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
            if ($my === 'edit') {
                $zid = intval(isset($_GET['zid']) ? $_GET['zid'] : 0);
                $row = q8_userlist_find_child_user($DB, $userrow['zid'], $zid);
                if (!$row) {
                    showmsg(q8_userlist_text('&#35813;&#36134;&#21495;&#19981;&#26159;&#26222;&#36890;&#29992;&#25143;&#65292;&#35831;&#21040;&#20998;&#31449;&#21015;&#34920;&#36827;&#34892;&#31649;&#29702;'), 3);
                }
            ?>
            <div class="panel-heading font-bold">&#32534;&#36753;&#19979;&#32423;&#29992;&#25143;</div>
            <div class="panel-body">
                <div class="alert alert-info">
                    <?php echo q8_userlist_text('&#24403;&#21069;&#27491;&#22312;&#32534;&#36753;&#36134;&#21495;&#65306;'); ?><b><?php echo q8_userlist_escape($row['user']); ?></b>
                    <span class="pull-right">UID&#65306;<?php echo intval($row['zid']); ?></span>
                </div>
                <form action="./userlist.php?my=edit_submit&amp;zid=<?php echo intval($row['zid']); ?>" method="POST" role="form">
                    <div class="form-group">
                        <label>&#29992;&#25143;&#21517;</label>
                        <input type="text" class="form-control" value="<?php echo q8_userlist_escape($row['user']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>&#24403;&#21069;&#20313;&#39069;</label>
                        <input type="text" class="form-control" value="<?php echo q8_userlist_escape($row['rmb']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>&#32852;&#31995; QQ</label>
                        <input type="text" class="form-control" name="qq" value="<?php echo q8_userlist_escape($row['qq']); ?>" placeholder="&#27809;&#26377;&#35831;&#30041;&#31354;">
                    </div>
                    <div class="form-group">
                        <label>&#37325;&#32622;&#23494;&#30721;</label>
                        <input type="text" class="form-control" name="pwd" value="" placeholder="&#19981;&#20462;&#25913;&#35831;&#30041;&#31354;">
                    </div>
                    <div class="form-group">
                        <label>&#27880;&#20876;&#26102;&#38388;</label>
                        <input type="text" class="form-control" value="<?php echo q8_userlist_escape($row['addtime']); ?>" disabled>
                    </div>
                    <input type="submit" class="btn btn-primary btn-block" value="&#20445;&#23384;&#29992;&#25143;&#36164;&#26009;">
                </form>
                <br/>
                <a href="<?php echo q8_userlist_escape(q8_userlist_back_link()); ?>" class="external">&gt;&gt; <?php echo q8_userlist_text('&#36820;&#22238;&#29992;&#25143;&#21015;&#34920;'); ?></a>
            </div>
            <?php
            } elseif ($my === 'delete') {
                $zid = intval(isset($_GET['zid']) ? $_GET['zid'] : 0);
                $row = q8_userlist_find_child_user($DB, $userrow['zid'], $zid);
                if (!$row) {
                    showmsg(q8_userlist_text('&#35813;&#36134;&#21495;&#19981;&#26159;&#26222;&#36890;&#29992;&#25143;&#65292;&#35831;&#21040;&#20998;&#31449;&#21015;&#34920;&#36827;&#34892;&#31649;&#29702;'), 3);
                }
            ?>
            <div class="panel-heading font-bold">&#21024;&#38500;&#19979;&#32423;&#29992;&#25143;</div>
            <div class="panel-body">
                <div class="alert alert-danger">
                    <?php echo q8_userlist_text('&#20320;&#21363;&#23558;&#21024;&#38500;&#19979;&#32423;&#29992;&#25143;&#65306;'); ?><b><?php echo q8_userlist_escape($row['user']); ?></b>
                    <span class="pull-right">UID&#65306;<?php echo intval($row['zid']); ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 160px;">&#36134;&#21495;&#31867;&#22411;</th>
                                <td><?php echo q8_userlist_power_badge($row['power']); ?></td>
                            </tr>
                            <tr>
                                <th>&#32852;&#31995; QQ</th>
                                <td><?php echo q8_userlist_escape($row['qq']); ?></td>
                            </tr>
                            <tr>
                                <th>&#24403;&#21069;&#20313;&#39069;</th>
                                <td><?php echo q8_userlist_escape($row['rmb']); ?></td>
                            </tr>
                            <tr>
                                <th>&#27880;&#20876;&#26102;&#38388;</th>
                                <td><?php echo q8_userlist_escape($row['addtime']); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-warning">&#21024;&#38500;&#21518;&#23558;&#26080;&#27861;&#24674;&#22797;&#65292;&#35831;&#20877;&#27425;&#30830;&#35748;&#24403;&#21069;&#36134;&#21495;&#26159;&#21542;&#38656;&#35201;&#21024;&#38500;&#12290;</div>
                <form action="./userlist.php?my=delete_submit&amp;zid=<?php echo intval($row['zid']); ?>" method="POST" role="form">
                    <button type="submit" class="btn btn-danger btn-block">&#30830;&#35748;&#21024;&#38500;&#35813;&#29992;&#25143;</button>
                </form>
                <br/>
                <a href="<?php echo q8_userlist_escape(q8_userlist_back_link()); ?>" class="external">&gt;&gt; <?php echo q8_userlist_text('&#36820;&#22238;&#29992;&#25143;&#21015;&#34920;'); ?></a>
            </div>
            <?php
            } else {
                $params = array(':upzid' => $userrow['zid']);
                $where = "upzid=:upzid";
                $linkParams = array();

                if (isset($_GET['zid']) && intval($_GET['zid']) > 0) {
                    $filterZid = intval($_GET['zid']);
                    $where .= " AND zid=:filter_zid";
                    $params[':filter_zid'] = $filterZid;
                    $linkParams['zid'] = $filterZid;
                } elseif (isset($_GET['kw']) && trim((string)$_GET['kw']) !== '') {
                    $kw = trim((string)$_GET['kw']);
                    $where .= " AND (user=:kw_user OR qq=:kw_qq OR zid=:kw_zid OR domain=:kw_domain OR domain2=:kw_domain2 OR sitename=:kw_sitename)";
                    $params[':kw_user'] = $kw;
                    $params[':kw_qq'] = $kw;
                    $params[':kw_zid'] = intval($kw);
                    $params[':kw_domain'] = $kw;
                    $params[':kw_domain2'] = $kw;
                    $params[':kw_sitename'] = $kw;
                    $linkParams['kw'] = $kw;
                }

                $numrows = intval($DB->getColumn("SELECT count(*) FROM pre_site WHERE {$where}", $params));
                $normalCount = intval($DB->getColumn("SELECT count(*) FROM pre_site WHERE upzid=:upzid AND power=0", array(':upzid' => $userrow['zid'])));
                $siteCount = intval($DB->getColumn("SELECT count(*) FROM pre_site WHERE upzid=:upzid AND power>0", array(':upzid' => $userrow['zid'])));
                $pagesize = 30;
                $pages = max(1, ceil($numrows / $pagesize));
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                if ($page > $pages) {
                    $page = $pages;
                }
                $offset = $pagesize * ($page - 1);

                $rows = $DB->getAll(
                    "SELECT zid,user,qq,rmb,addtime,power,domain,domain2,sitename FROM pre_site WHERE {$where} ORDER BY zid DESC LIMIT {$offset},{$pagesize}",
                    $params
                );
                echo '<div class="alert alert-info">' . q8_userlist_text('&#20320;&#20849;&#26377;') . ' <b>' . $numrows . '</b> ' . q8_userlist_text('&#20010;&#19979;&#32423;&#36134;&#21495;') . ' &nbsp;|&nbsp; ' . q8_userlist_text('&#26222;&#36890;&#29992;&#25143;') . ' <b>' . $normalCount . '</b> &nbsp;|&nbsp; ' . q8_userlist_text('&#24050;&#24320;&#36890;&#20998;&#31449;') . ' <b>' . $siteCount . '</b><br/><a href="#" data-toggle="modal" data-target="#search" class="btn btn-success">' . q8_userlist_text('&#25628;&#32034;') . '</a></div>';
            ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>UID</th>
                            <th><?php echo q8_userlist_text('&#36134;&#21495;&#20449;&#24687;'); ?></th>
                            <th>QQ</th>
                            <th><?php echo q8_userlist_text('&#20313;&#39069;'); ?></th>
                            <th><?php echo q8_userlist_text('&#27880;&#20876;&#26102;&#38388;'); ?></th>
                            <th><?php echo q8_userlist_text('&#25805;&#20316;'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($rows) {
                        foreach ($rows as $res) {
                            echo '<tr>';
                            echo '<td><b>' . intval($res['zid']) . '</b></td>';
                            echo '<td><div><strong>' . q8_userlist_escape($res['user']) . '</strong> ' . q8_userlist_power_badge($res['power']) . '</div>';
                            echo '<div class="q8-userlist-meta">';
                            if (intval($res['power']) > 0) {
                                echo q8_userlist_escape($res['sitename']) . '<br>' . q8_userlist_escape($res['domain']);
                                if (!empty($res['domain2'])) {
                                    echo '<br>' . q8_userlist_escape($res['domain2']);
                                }
                            } else {
                                echo q8_userlist_text('&#26222;&#36890;&#19979;&#32423;&#29992;&#25143;&#36134;&#21495;');
                            }
                            echo '</div></td>';
                            echo '<td>' . q8_userlist_escape($res['qq']) . '</td>';
                            echo '<td>' . q8_userlist_escape($res['rmb']) . '</td>';
                            echo '<td>' . q8_userlist_escape($res['addtime']) . '</td>';
                            echo '<td><div class="q8-userlist-actions">';
                            if (intval($res['power']) > 0) {
                                echo '<button type="button" class="btn btn-info" data-q8-userlist-href="./sitelist.php?my=edit&amp;zid=' . intval($res['zid']) . '">' . q8_userlist_text('&#20998;&#31449;&#35814;&#24773;') . '</button>';
                                echo '<button type="button" class="btn btn-danger" data-q8-userlist-href="./sitelist.php?my=delete&amp;zid=' . intval($res['zid']) . '">' . q8_userlist_text('&#21024;&#38500;&#20998;&#31449;') . '</button>';
                            } else {
                                echo '<button type="button" class="btn btn-info" data-q8-userlist-href="./userlist.php?my=edit&amp;zid=' . intval($res['zid']) . '">' . q8_userlist_text('&#32534;&#36753;') . '</button>';
                                echo '<button type="button" class="btn btn-danger" data-q8-userlist-href="./userlist.php?my=delete&amp;zid=' . intval($res['zid']) . '">' . q8_userlist_text('&#21024;&#38500;') . '</button>';
                            }
                            echo '</div></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="6" class="text-center text-muted">' . q8_userlist_text('&#26242;&#26080;&#31526;&#21512;&#26465;&#20214;&#30340;&#19979;&#32423;&#36134;&#21495;') . '</td></tr>';
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
                    echo '<li><a href="' . q8_userlist_escape(q8_userlist_url($firstParams)) . '" class="external">' . q8_userlist_text('&#39318;&#39029;') . '</a></li>';
                    echo '<li><a href="' . q8_userlist_escape(q8_userlist_url($prevParams)) . '" class="external">&laquo;</a></li>';
                } else {
                    echo '<li class="disabled"><a>' . q8_userlist_text('&#39318;&#39029;') . '</a></li>';
                    echo '<li class="disabled"><a>&laquo;</a></li>';
                }

                $start = $page - 10 > 1 ? $page - 10 : 1;
                $end = $page + 10 < $pages ? $page + 10 : $pages;
                for ($i = $start; $i < $page; $i++) {
                    $pageParams = $linkParams;
                    $pageParams['page'] = $i;
                    echo '<li><a href="' . q8_userlist_escape(q8_userlist_url($pageParams)) . '" class="external">' . $i . '</a></li>';
                }
                echo '<li class="disabled"><a>' . $page . '</a></li>';
                for ($i = $page + 1; $i <= $end; $i++) {
                    $pageParams = $linkParams;
                    $pageParams['page'] = $i;
                    echo '<li><a href="' . q8_userlist_escape(q8_userlist_url($pageParams)) . '" class="external">' . $i . '</a></li>';
                }

                if ($page < $pages) {
                    echo '<li><a href="' . q8_userlist_escape(q8_userlist_url($nextParams)) . '" class="external">&raquo;</a></li>';
                    echo '<li><a href="' . q8_userlist_escape(q8_userlist_url($lastParams)) . '" class="external">' . q8_userlist_text('&#23614;&#39029;') . '</a></li>';
                } else {
                    echo '<li class="disabled"><a>&raquo;</a></li>';
                    echo '<li class="disabled"><a>' . q8_userlist_text('&#23614;&#39029;') . '</a></li>';
                }
                echo '</ul>';
            }
            ?>
        </div>
    </div>
</div>
<?php include './foot.php'; ?>
<script>
$(document).on('click', '[data-q8-userlist-href]', function (event) {
    event.preventDefault();
    var href = $(this).attr('data-q8-userlist-href');
    if (href) {
        window.location.href = href.replace(/&amp;/g, '&');
    }
});
</script>
</body>
</html>

<?php
include '../includes/common.php';
if ($islogin2 != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

function q8_visitlogs_text($encoded)
{
    return html_entity_decode($encoded, ENT_QUOTES, 'UTF-8');
}

function q8_visitlogs_escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function q8_visitlogs_url($params = array())
{
    $query = http_build_query($params);
    return './visitlogs.php' . ($query !== '' ? '?' . $query : '');
}

function q8_visitlogs_base_params()
{
    $params = array();
    if (isset($_GET['page']) && intval($_GET['page']) > 1) {
        $params['page'] = intval($_GET['page']);
    }
    if (isset($_GET['kw']) && trim((string)$_GET['kw']) !== '') {
        $params['kw'] = trim((string)$_GET['kw']);
    }
    if (isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$_GET['date'])) {
        $params['date'] = trim((string)$_GET['date']);
    }
    return $params;
}

if (intval($userrow['power']) < 1) {
    showmsg(q8_visitlogs_text('&#20320;&#27809;&#26377;&#26435;&#38480;&#20351;&#29992;&#27492;&#21151;&#33021;'), 3);
}

$siteZid = intval($userrow['zid']);
$title = q8_visitlogs_text('&#35775;&#38382;&#26126;&#32454;');
include './head.php';

$dashboard = q8_get_site_visit_dashboard($siteZid);
$todayVisits = intval($dashboard['today_visits']);
$todayIps = intval($dashboard['today_ips']);
$weekVisits = intval($dashboard['week_visits']);
$weekIps = intval($dashboard['week_ips']);

$tableExists = $DB->getColumn("SHOW TABLES LIKE 'shua_visit_ips'");
$rows = array();
$numrows = 0;
$kw = isset($_GET['kw']) ? trim((string)$_GET['kw']) : '';
$filterDate = isset($_GET['date']) ? trim((string)$_GET['date']) : '';
$linkParams = array();

if ($tableExists) {
    q8_visit_stats_ensure_site_columns();
    $where = "site_zid=:site_zid";
    $params = array(':site_zid' => $siteZid);

    if ($kw !== '') {
        $where .= " AND (ip LIKE :kw OR url LIKE :kw OR username LIKE :kw OR host LIKE :kw)";
        $params[':kw'] = '%' . $kw . '%';
        $linkParams['kw'] = $kw;
    }
    if ($filterDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterDate)) {
        $where .= " AND date=:filter_date";
        $params[':filter_date'] = $filterDate;
        $linkParams['date'] = $filterDate;
    } else {
        $filterDate = '';
    }

    $numrows = intval($DB->getColumn("SELECT COUNT(*) FROM shua_visit_ips WHERE {$where}", $params));
    $pagesize = 30;
    $pages = max(1, intval(ceil($numrows / $pagesize)));
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    if ($page > $pages) {
        $page = $pages;
    }
    $offset = $pagesize * ($page - 1);

    $rows = $DB->getAll(
        "SELECT id,date,ip,username,host,url,visits,created_at,updated_at FROM shua_visit_ips WHERE {$where} ORDER BY updated_at DESC,id DESC LIMIT {$offset},{$pagesize}",
        $params
    );
} else {
    $pages = 1;
    $page = 1;
}
?>
<link rel="stylesheet" href="./public/css/blue_theme.css">
<style>
.q8-visitlogs-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.q8-visitlogs-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    margin: 15px 0;
}
.q8-visitlogs-stat {
    padding: 12px 10px;
    border-radius: 10px;
    border: 1px solid #dbeafe;
    background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    box-shadow: 0 5px 16px rgba(22,119,255,.08);
    text-align: center;
}
.q8-visitlogs-stat span {
    display: block;
    color: #667085;
    font-size: 12px;
    margin-bottom: 6px;
}
.q8-visitlogs-stat strong {
    color: #1677ff;
    font-size: 24px;
    line-height: 1;
}
.q8-visitlogs-meta {
    color: #64748b;
    font-size: 12px;
    line-height: 1.7;
}
.q8-visitlogs-host {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 999px;
    background: #eef6ff;
    color: #175cd3;
    font-size: 12px;
    font-weight: 700;
}
.q8-visitlogs-path {
    max-width: 440px;
    word-break: break-all;
    color: #0f172a;
    font-family: Consolas, Monaco, monospace;
    font-size: 12px;
    line-height: 1.7;
}
.q8-visitlogs-empty {
    padding: 32px 18px;
    color: #64748b;
    text-align: center;
}
@media (max-width: 991px) {
    .q8-visitlogs-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 640px) {
    .q8-visitlogs-stats {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    .q8-visitlogs-toolbar {
        align-items: stretch;
    }
    .q8-visitlogs-toolbar .btn {
        width: 100%;
    }
}
</style>
<div class="wrapper">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="modal fade" align="left" id="visitSearch" tabindex="-1" role="dialog" aria-labelledby="visitSearchTitle" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h4 class="modal-title" id="visitSearchTitle"><?php echo q8_visitlogs_text('&#31579;&#36873;&#35775;&#38382;&#26126;&#32454;'); ?></h4>
                        </div>
                        <div class="modal-body">
                            <form action="./visitlogs.php" method="GET">
                                <div class="form-group">
                                    <label><?php echo q8_visitlogs_text('&#20851;&#38190;&#23383;'); ?></label>
                                    <input type="text" class="form-control" name="kw" value="<?php echo q8_visitlogs_escape($kw); ?>" placeholder="<?php echo q8_visitlogs_text('IP&#12289;&#35775;&#38382;&#36335;&#24452;&#12289;&#22495;&#21517;&#25110;&#35775;&#38382;&#29992;&#25143;'); ?>">
                                </div>
                                <div class="form-group">
                                    <label><?php echo q8_visitlogs_text('&#26085;&#26399;'); ?></label>
                                    <input type="date" class="form-control" name="date" value="<?php echo q8_visitlogs_escape($filterDate); ?>">
                                </div>
                                <input type="submit" class="btn btn-primary btn-block" value="<?php echo q8_visitlogs_text('&#24320;&#22987;&#31579;&#36873;'); ?>">
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo q8_visitlogs_text('&#20851;&#38381;'); ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-heading font-bold"><?php echo q8_visitlogs_text('&#20998;&#31449;&#35775;&#38382;&#26126;&#32454;'); ?></div>
            <div class="panel-body">
                <div class="q8-visitlogs-toolbar">
                    <div class="q8-visitlogs-meta">
                        <?php echo q8_visitlogs_text('&#36825;&#37324;&#26174;&#31034;&#20320;&#24403;&#21069;&#20998;&#31449;&#30340;&#26368;&#36817;&#35775;&#38382; IP&#12289;&#22495;&#21517;&#12289;&#35775;&#38382;&#36335;&#24452;&#21644;&#26368;&#21518;&#35775;&#38382;&#26102;&#38388;&#12290;'); ?><br>
                        <?php echo q8_visitlogs_text('&#25968;&#25454;&#25353;&#20998;&#31449;&#29420;&#31435;&#32479;&#35745;&#65292;&#26356;&#36866;&#21512;&#25490;&#26597;&#30495;&#23454;&#35775;&#38382;&#24773;&#20917;&#12290;'); ?>
                    </div>
                    <div class="btn-group">
                        <a href="./index.php" class="btn btn-default external"><i class="fa fa-home"></i> <?php echo q8_visitlogs_text('&#36820;&#22238;&#39318;&#39029;'); ?></a>
                        <a href="#" data-toggle="modal" data-target="#visitSearch" class="btn btn-primary"><i class="fa fa-search"></i> <?php echo q8_visitlogs_text('&#31579;&#36873;&#26126;&#32454;'); ?></a>
                    </div>
                </div>

                <div class="q8-visitlogs-stats">
                    <div class="q8-visitlogs-stat">
                        <span><?php echo q8_visitlogs_text('&#20170;&#26085;&#35775;&#38382;&#37327;'); ?></span>
                        <strong><?php echo $todayVisits; ?></strong>
                    </div>
                    <div class="q8-visitlogs-stat">
                        <span><?php echo q8_visitlogs_text('&#20170;&#26085;&#29420;&#31435; IP'); ?></span>
                        <strong><?php echo $todayIps; ?></strong>
                    </div>
                    <div class="q8-visitlogs-stat">
                        <span><?php echo q8_visitlogs_text('7 &#22825;&#35775;&#38382;&#37327;'); ?></span>
                        <strong><?php echo $weekVisits; ?></strong>
                    </div>
                    <div class="q8-visitlogs-stat">
                        <span><?php echo q8_visitlogs_text('7 &#22825;&#29420;&#31435; IP'); ?></span>
                        <strong><?php echo $weekIps; ?></strong>
                    </div>
                </div>

                <?php
                if (!$tableExists) {
                    echo '<div class="alert alert-warning">' . q8_visitlogs_text('&#24403;&#21069;&#23578;&#26410;&#29983;&#25104;&#35775;&#38382;&#26126;&#32454;&#34920;&#65292;&#26377;&#26032;&#35775;&#38382;&#21518;&#23558;&#33258;&#21160;&#24320;&#22987;&#32047;&#35745;&#12290;') . '</div>';
                } else {
                    echo '<div class="alert alert-info">';
                    echo q8_visitlogs_text('&#24403;&#21069;&#31526;&#21512;&#26465;&#20214;&#30340;&#35775;&#38382;&#35760;&#24405;&#20849;') . ' <b>' . $numrows . '</b> ' . q8_visitlogs_text('&#26465;');
                    if ($kw !== '' || $filterDate !== '') {
                        echo '<br><a href="' . q8_visitlogs_escape(q8_visitlogs_url()) . '" class="btn btn-success external" style="margin-top:8px;">' . q8_visitlogs_text('&#28165;&#31354;&#31579;&#36873;') . '</a>';
                    }
                    echo '</div>';
                }
                ?>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th style="width: 90px;">ID</th>
                            <th style="width: 150px;"><?php echo q8_visitlogs_text('IP &#22320;&#22336;'); ?></th>
                            <th style="width: 150px;"><?php echo q8_visitlogs_text('&#35775;&#38382;&#22495;&#21517;'); ?></th>
                            <th><?php echo q8_visitlogs_text('&#35775;&#38382;&#36335;&#24452;'); ?></th>
                            <th style="width: 90px;"><?php echo q8_visitlogs_text('&#27425;&#25968;'); ?></th>
                            <th style="width: 140px;"><?php echo q8_visitlogs_text('&#35775;&#38382;&#29992;&#25143;'); ?></th>
                            <th style="width: 180px;"><?php echo q8_visitlogs_text('&#26368;&#21518;&#35775;&#38382;&#26102;&#38388;'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($rows) {
                            foreach ($rows as $row) {
                                $host = trim((string)$row['host']) !== '' ? $row['host'] : '--';
                                $path = trim((string)$row['url']) !== '' ? $row['url'] : '/';
                                $username = trim((string)$row['username']) !== '' ? $row['username'] : q8_visitlogs_text('&#28216;&#23458;');
                                echo '<tr>';
                                echo '<td><b>#' . intval($row['id']) . '</b><br><span class="text-muted">' . q8_visitlogs_escape($row['date']) . '</span></td>';
                                echo '<td><span class="text-primary">' . q8_visitlogs_escape($row['ip']) . '</span></td>';
                                echo '<td><span class="q8-visitlogs-host">' . q8_visitlogs_escape($host) . '</span></td>';
                                echo '<td><div class="q8-visitlogs-path">' . q8_visitlogs_escape($path) . '</div></td>';
                                echo '<td><b>' . intval($row['visits']) . '</b></td>';
                                echo '<td>' . q8_visitlogs_escape($username) . '</td>';
                                echo '<td>' . q8_visitlogs_escape($row['updated_at']) . '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7" class="q8-visitlogs-empty">' . q8_visitlogs_text('&#26242;&#26080;&#31526;&#21512;&#26465;&#20214;&#30340;&#35775;&#38382;&#26126;&#32454;&#35760;&#24405;') . '</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>

                <?php
                if ($tableExists && $pages > 1) {
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
                        echo '<li><a href="' . q8_visitlogs_escape(q8_visitlogs_url($firstParams)) . '" class="external">' . q8_visitlogs_text('&#39318;&#39029;') . '</a></li>';
                        echo '<li><a href="' . q8_visitlogs_escape(q8_visitlogs_url($prevParams)) . '" class="external">&laquo;</a></li>';
                    } else {
                        echo '<li class="disabled"><a>' . q8_visitlogs_text('&#39318;&#39029;') . '</a></li>';
                        echo '<li class="disabled"><a>&laquo;</a></li>';
                    }

                    $start = $page - 10 > 1 ? $page - 10 : 1;
                    $end = $page + 10 < $pages ? $page + 10 : $pages;
                    for ($i = $start; $i < $page; $i++) {
                        $pageParams = $linkParams;
                        $pageParams['page'] = $i;
                        echo '<li><a href="' . q8_visitlogs_escape(q8_visitlogs_url($pageParams)) . '" class="external">' . $i . '</a></li>';
                    }
                    echo '<li class="disabled"><a>' . $page . '</a></li>';
                    for ($i = $page + 1; $i <= $end; $i++) {
                        $pageParams = $linkParams;
                        $pageParams['page'] = $i;
                        echo '<li><a href="' . q8_visitlogs_escape(q8_visitlogs_url($pageParams)) . '" class="external">' . $i . '</a></li>';
                    }

                    if ($page < $pages) {
                        echo '<li><a href="' . q8_visitlogs_escape(q8_visitlogs_url($nextParams)) . '" class="external">&raquo;</a></li>';
                        echo '<li><a href="' . q8_visitlogs_escape(q8_visitlogs_url($lastParams)) . '" class="external">' . q8_visitlogs_text('&#23614;&#39029;') . '</a></li>';
                    } else {
                        echo '<li class="disabled"><a>&raquo;</a></li>';
                        echo '<li class="disabled"><a>' . q8_visitlogs_text('&#23614;&#39029;') . '</a></li>';
                    }
                    echo '</ul>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php include './foot.php'; ?>
</body>
</html>

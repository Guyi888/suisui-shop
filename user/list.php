<?php
/**
 * 订单管理
 **/
include("../includes/common.php");
$title = '订单管理';
include './head.php';
if ($islogin2 == 1) {
} else {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
?>
<link rel="stylesheet" href="./public/css/blue_theme.css?v=q8userclean11">
<style>
    td.wbreak {
        max-width: 420px;
        word-break: break-all;
    }

    #orderItem .orderTitle {
        word-break: keep-all;
    }

    #orderItem .orderContent {
        word-break: break-all;
    }

    .form-inline .form-control {
        display: inline-block;
        width: auto;
        vertical-align: middle;
    }

    .form-inline .form-group {
        display: inline-block;
        margin-bottom: 0;
        vertical-align: middle;
    }






    /* Q8 clean user list layout */
    .q8-order-search {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin: 0;
        padding: 12px 0;
    }
    .q8-order-search label {
        margin: 0;
        white-space: nowrap;
    }
    .q8-order-search .form-control {
        width: 260px;
        max-width: 100%;
        height: 36px;
    }
    .q8-order-search .btn {
        width: auto !important;
        min-width: 96px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        margin: 0 !important;
        padding: 0 14px;
        border-radius: 8px;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
    }
    .q8-order-search .btn-success {
        min-width: 132px;
    }
    .table > tbody > tr > td {
        vertical-align: middle !important;
    }
    .q8-order-action-cell {
        vertical-align: middle !important;
    }
    .q8-order-action-btn {
        min-width: 62px;
        height: 30px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        border-radius: 999px !important;
        font-weight: 800;
        margin: 0 !important;
        line-height: 1 !important;
        padding: 0 12px !important;
    }
    .modal .modal-header {
        position: relative;
    }
    .modal .modal-header .close {
        position: absolute;
        right: 14px;
        top: 10px;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        border-radius: 50%;
        background: rgba(15,23,42,.08);
        opacity: 1;
        color: transparent;
        text-shadow: none;
        font-size: 0;
        line-height: 1;
    }
    .modal .modal-header .close:before,
    .modal .modal-header .close:after {
        content: "";
        position: absolute;
        width: 13px;
        height: 2px;
        border-radius: 2px;
        background: #334155;
    }
    .modal .modal-header .close:before {
        transform: rotate(45deg);
    }
    .modal .modal-header .close:after {
        transform: rotate(-45deg);
    }
    .modal .modal-header .close:hover {
        background: #ef4444;
    }
    .modal .modal-header .close:hover:before,
    .modal .modal-header .close:hover:after {
        background: #fff;
    }
    .modal .modal-footer {
        border-top: 1px solid #eaf1fb;
        background: #f7fbff;
        text-align: center;
    }
    .modal .modal-footer .btn {
        min-width: 118px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 999px;
        background: linear-gradient(135deg,#1677ff,#22c4c8);
        color: #fff;
        font-weight: 900;
        line-height: 1;
        box-shadow: 0 10px 22px rgba(22,119,255,.18);
    }
    .modal .modal-footer .btn:hover,
    .modal .modal-footer .btn:focus {
        color: #fff;
        filter: brightness(1.03);
    }
    #orderItem {
        margin: 0;
        border-collapse: separate;
        border-spacing: 0 8px;
        background: #f7fbff;
        padding: 10px;
    }
    #orderItem td {
        border: 0 !important;
        background: #fff !important;
        vertical-align: middle !important;
    }
    #orderItem .orderTitle {
        width: 118px;
        color: #1d4ed8;
        font-weight: 900;
        white-space: nowrap;
        background: #eef6ff !important;
        border-radius: 10px 0 0 10px;
    }
    #orderItem .orderContent {
        color: #111827;
        word-break: break-word;
        border-radius: 0 10px 10px 0;
    }
    #orderItem .orderContent img {
        max-width: 100%;
    }
    .q8-order-modal-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
        width: 100%;
    }
    .q8-order-modal-actions .btn {
        min-width: 116px;
        height: 34px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        border-radius: 999px !important;
        font-weight: 800;
        margin: 0 !important;
        white-space: nowrap;
    }
    .q8-kminfo-box {
        padding: 12px;
        border: 1px dashed #93c5fd;
        border-radius: 10px;
        background: #f8fbff;
        color: #111827;
        line-height: 1.8;
        white-space: pre-wrap;
        word-break: break-word;
        margin-bottom: 10px;
    }
    .q8-copy-kminfo {
        border: 0 !important;
        border-radius: 999px !important;
        height: 34px !important;
        min-width: 128px !important;
        background: linear-gradient(135deg,#1677ff,#22c4c8) !important;
        font-weight: 900 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1 !important;
        padding: 0 18px !important;
        color: #fff !important;
    }
    .layui-layer.layui-layer-rim {
        border: 0 !important;
        border-radius: 14px !important;
        overflow: hidden !important;
        box-shadow: 0 24px 60px rgba(15,23,42,.22) !important;
        background: #fff !important;
    }
    .layui-layer.layui-layer-rim .layui-layer-title {
        height: 48px !important;
        line-height: 48px !important;
        border: 0 !important;
        background: linear-gradient(135deg,#1677ff,#22c4c8) !important;
        color: #fff !important;
        font-weight: 900 !important;
        padding-left: 18px !important;
    }
    .layui-layer-page .layui-layer-content {
        max-height: calc(100vh - 120px);
        overflow: auto !important;
        -webkit-overflow-scrolling: touch;
        background: #f7fbff;
    }
    @media (max-width: 768px) {
        .wrapper {
            padding: 10px !important;
        }
        .col-sm-12 {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .q8-order-search {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 12px;
        }
        .q8-order-search label,
        .q8-order-search .form-control {
            grid-column: 1 / -1;
            width: 100%;
        }
        .q8-order-search .btn {
            width: 100% !important;
            min-width: 0;
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 0;
        }
        .table-responsive table {
            min-width: 760px;
        }
        #orderItem {
            padding: 8px;
            border-spacing: 0 7px;
        }
        #orderItem tbody,
        #orderItem tr,
        #orderItem td {
            display: block;
            width: 100% !important;
        }
        #orderItem .orderTitle {
            border-radius: 10px 10px 0 0;
            padding: 8px 10px !important;
        }
        #orderItem .orderContent {
            border-radius: 0 0 10px 10px;
            padding: 10px !important;
        }
        .q8-order-modal-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .q8-order-modal-actions .btn {
            width: 100% !important;
            min-width: 0;
        }
        .layui-layer {
            max-width: 94vw !important;
        }
        .layui-layer-page .layui-layer-content {
            max-height: calc(100vh - 96px) !important;
        }
    }


</style>
<div class="wrapper">
    <div class="col-sm-12">
        <div class="panel panel-default">
            <div class="modal fade" id="search2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                 aria-hidden="true" style="display: none;">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">
						&times;
					</span>
                                <span class="sr-only">
						Close
					</span>
                            </button>
                            <h4 class="modal-title" id="myModalLabel">
                                订单状态说明
                            </h4>
                        </div>
                        <div class="modal-body">
                            <?php
                            if(!empty(trim($conf['gg_search']))){
                                echo $conf['gg_search'];
                            }else{
                                echo '<div class="list-group" style="margin-bottom:0">
                                    <div class="list-group-item"><b>&#24453;&#22788;&#29702;</b>&#65306;&#35746;&#21333;&#24050;&#25552;&#20132;&#65292;&#31561;&#24453;&#31995;&#32479;&#25110;&#20379;&#24212;&#31449;&#22788;&#29702;&#12290;</div>
                                    <div class="list-group-item"><b>&#22788;&#29702;&#20013;</b>&#65306;&#35746;&#21333;&#27491;&#22312;&#25191;&#34892;&#65292;&#35831;&#32784;&#24515;&#31561;&#24453;&#32467;&#26524;&#26356;&#26032;&#12290;</div>
                                    <div class="list-group-item"><b>&#24050;&#23436;&#25104;</b>&#65306;&#35746;&#21333;&#24050;&#22788;&#29702;&#23436;&#25104;&#12290;</div>
                                    <div class="list-group-item"><b>&#24322;&#24120;</b>&#65306;&#21487;&#30003;&#35831;&#21806;&#21518;&#25110;&#25353;&#39029;&#38754;&#25552;&#31034;&#30003;&#35831;&#36864;&#27454;&#12290;</div>
                                    <div class="list-group-item"><b>&#24050;&#36864;&#27454;</b>&#65306;&#27454;&#39033;&#24050;&#36864;&#22238;&#21040;&#29992;&#25143;&#20313;&#39069;&#12290;</div>
                                </div>';
                            }
                            ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                关闭
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="search" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                 aria-hidden="true" style="display: none;">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">
						&times;
					</span>
                                <span class="sr-only">
						Close
					</span>
                            </button>
                            <h4 class="modal-title" id="myModalLabel">
                                搜索订单
                            </h4>
                        </div>
                        <div class="modal-body">
                            <form action="list.php" method="GET">
                                <input type="text" class="form-control" name="kw" placeholder="请输入下单账号">
                                <br/>
                                <input type="submit" class="btn btn-primary btn-block" value="搜索">
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                关闭
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            function display_zt($zt)
            {
                if ($zt == 1) {
                    return '<font color=green>已完成</font>';
                } elseif ($zt == 2) {
                    return '<font color=orange>正在处理</font>';
                } elseif ($zt == 3) {
                    return '<font color=red>异常</font>';
                } elseif ($zt == 4) {
                    return '<font color=grey>已退款</font>';
                } else {
                    return '<font color=blue>待处理</font>';
                }
            }

            $sqls = $userrow['power'] > 0 ? "A.zid='{$userrow['zid']}'" : "A.userid='{$userrow['zid']}'";

            if (isset($_GET['kw']) && !empty($_GET['kw'])) {
                $kw      = daddslashes($_GET['kw']);
                $sql     = " (A.`input`='{$kw}' OR A.`id`='{$kw}' OR A.`tradeno`='{$kw}') AND {$sqls}";
                $numrows = $DB->getColumn("SELECT count(*) FROM pre_orders A WHERE{$sql}");
                $con     = '
	<div class="panel-heading font-bold"> ' . $_GET['kw'] . ' 订单查询 - [<a href="list.php" style="color:#fff00f">查看全部</a>]</div>
	<div class="well well-sm" style="margin: 0;">包含 ' . $_GET['kw'] . ' 的共有 <b>' . $numrows . '</b> 个订单</div>
	<div class="wrapper">';
                $link    = '&kw=' . $_GET['kw'];
            } else {
                $sql     = " {$sqls}";
                $numrows = $DB->getColumn("SELECT count(*) FROM pre_orders A WHERE{$sql}");
                $ondate  = $DB->getColumn("SELECT count(*) FROM pre_orders A WHERE status=1 AND{$sql}");
                $ondate2 = $DB->getColumn("SELECT count(*) FROM pre_orders A WHERE status=2 AND{$sql}");
                $con     = '
	<div class="panel-heading font-bold">订单查询</div>
	<div class="well well-sm" style="margin: 0;">共有 <b>' . $numrows . '</b> 个订单，其中已完成的有 <b>' . $ondate . '</b> 个，正在处理的有 <b>' . $ondate2 . '</b> 个。</div>
	<div class="wrapper">';
            }
            echo $con;
            ?>
            <form action="./list.php" method="GET" class="q8-order-search">
                <label><b>&#25628;&#32034;&#35746;&#21333;</b></label>
                <input type="text" class="form-control" name="kw" placeholder="&#35831;&#36755;&#20837;&#19979;&#21333;&#36134;&#21495;&#25110;&#35746;&#21333;&#21495;" value="">
                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i><span>&#25628;&#32034;</span></button>
                <a href="#" data-toggle="modal" data-target="#search2" id="q8-order-status-help" class="btn btn-success"><i class="fa fa-exclamation-circle"></i><span>&#35746;&#21333;&#29366;&#24577;&#35828;&#26126;</span></a>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-striped b-t b-light">
                <thead>
                <tr>
                    <th>
                        操作
                    </th>
                    <th>
                        订单ID
                    </th>
                    <th>
                        商品名称
                    </th>
                    <th>
                        下单信息
                    </th>
                    <th>
                        份数
                    </th>
                    <th>
                        下单时间
                    </th>
                    <th>
                        状态
                    </th>
                </tr>
                </thead>
                <tbody>

                <?php
                $pagesize = 30;
                $pages    = ceil($numrows / $pagesize);
                $page     = isset($_GET['page']) ? intval($_GET['page']) : 1;
                $offset   = $pagesize * ($page - 1);

                $rs = $DB->query("SELECT A.*,B.name FROM pre_orders A left join pre_tools B on A.tid=B.tid WHERE{$sql} ORDER BY id DESC LIMIT $offset,$pagesize");
                while ($res = $rs->fetch()) {
                    if ($res['djzt'] == 3 && $res['userid'] != $userrow['zid']) {
                        $input = '****' . substr($res['input'], 4);
                    } else {
                        $input = $res['input'];
                    }
                    echo '<tr>
	<td class="q8-order-action-cell">
								' . ($res['userid'] == $userrow['zid'] ? '<a href="javascript:showOrder(' . $res['id'] . ',\'' . md5($res['id'] . SYS_KEY . $res['id']) . '\')" title="查看订单详细" class="btn btn-info btn-xs q8-order-action-btn">详细</a>' : '<a href="javascript:;" class="btn btn-info btn-xs q8-order-action-btn" disabled title="不是你支付的订单">详细</a>') . '
							</td>
							<td>
								' . $res['id'] . '
							</td>
							<td>
								' . $res['name'] . '
							</td>
							<td class="wbreak">
								' . $input . '
							</td>
							<td>
								' . $res['value'] . '
							</td>
							<td>
								' . $res['addtime'] . '
							</td>
							<td>
								<font color=green>
									' . display_zt($res['status']) . '
								</font>
							</td>
						</tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
        <center>
            <?php
            echo '<ul class="pagination"  style="margin-left:1em">';
            $first = 1;
            $prev  = $page - 1;
            $next  = $page + 1;
            $last  = $pages;
            if ($page > 1) {
                echo '<li><a href="list.php?page=' . $first . $link . '">首页</a></li>';
                echo '<li><a href="list.php?page=' . $prev . $link . '">&laquo;</a></li>';
            } else {
                echo '<li class="disabled"><a>首页</a></li>';
                echo '<li class="disabled"><a>&laquo;</a></li>';
            }
            $start = $page - 10 > 1 ? $page - 10 : 1;
            $end   = $page + 10 < $pages ? $page + 10 : $pages;
            for ($i = $start; $i < $page; $i++) {
                echo '<li><a href="list.php?page=' . $i . $link . '">' . $i . '</a></li>';
            }
            echo '<li class="disabled"><a>' . $page . '</a></li>';
            for ($i = $page + 1; $i <= $end; $i++) {
                echo '<li><a href="list.php?page=' . $i . $link . '">' . $i . '</a></li>';
            }
            if ($page < $pages) {
                echo '<li><a href="list.php?page=' . $next . $link . '">&raquo;</a></li>';
                echo '<li><a href="list.php?page=' . $last . $link . '">尾页</a></li>';
            } else {
                echo '<li class="disabled"><a>&raquo;</a></li>';
                echo '<li class="disabled"><a>尾页</a></li>';
            }
            echo '</ul></center>';
            #分页
            ?>
    </div>
</div>
<?php include './foot.php'; ?>
<script src="<?php echo $cdnpublic ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script>var homepage = false;</script>
<script src="/assets/js/main.js?ver=<?php echo VERSION ?>"></script>


<script>
    function q8CopyListKminfo(){
        var box = document.getElementById('q8-list-kminfo-copy-text');
        if(!box) return false;
        var text = box.innerText || box.textContent || '';
        if(navigator.clipboard && navigator.clipboard.writeText){
            navigator.clipboard.writeText(text).then(function(){ layer.msg('\u5361\u5bc6\u4fe1\u606f\u5df2\u590d\u5236'); });
        }else{
            var ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            layer.msg('\u5361\u5bc6\u4fe1\u606f\u5df2\u590d\u5236');
        }
        return false;
    }

    function showOrder(id, skey) {
        var ii = layer.load(2, {shade: [0.1, '#fff']});
        var status = [
            '<span class="label label-primary">&#24453;&#22788;&#29702;</span>',
            '<span class="label label-success">&#24050;&#23436;&#25104;</span>',
            '<span class="label label-warning">&#22788;&#29702;&#20013;</span>',
            '<span class="label label-danger">&#24322;&#24120;</span>',
            '<font color="red">&#24050;&#36864;&#27454;</font>'
        ];
        function row(label, value) {
            return '<tr><td class="info orderTitle">' + label + '</td><td colspan="5" class="orderContent">' + (value || '') + '</td></tr>';
        }
        function full(title, value) {
            return '<tr><td colspan="6" class="orderTitle" style="text-align:center"><b>' + title + '</b></td></tr>' +
                '<tr><td colspan="6" class="orderContent">' + (value || '') + '</td></tr>';
        }
        $.ajax({
            type: "POST",
            url: "../ajax.php?act=order",
            data: {id: id, skey: skey},
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    var item = '<table class="table table-condensed table-hover" id="orderItem">';
                    item += '<tr><td colspan="6" class="orderTitle" style="text-align:center"><b>&#35746;&#21333;&#22522;&#26412;&#20449;&#24687;</b></td></tr>';
                    item += row('&#35746;&#21333;&#32534;&#21495;', id);
                    item += row('&#21830;&#21697;&#21517;&#31216;', data.name);
                    item += row('&#35746;&#21333;&#37329;&#39069;', data.money + '&#20803;');
                    item += row('&#36141;&#20080;&#26102;&#38388;', data.date);
                    item += row('&#19979;&#21333;&#20449;&#24687;', data.inputs);
                    item += row('&#35746;&#21333;&#29366;&#24577;', status[data.status] || data.status);
                    if (data.complain) {
                        item += '<tr><td class="info orderTitle">&#35746;&#21333;&#25805;&#20316;</td><td colspan="5" class="orderContent">' +
                            '<div class="q8-order-modal-actions">' +
                            '<a href="./workorder.php?my=add&orderid=' + id + '&skey=' + skey + '" target="_blank" onclick="return checklogin(' + data.islogin + ')" class="btn btn-default">&#25237;&#35785;&#35746;&#21333;</a>' +
                            '<a href="javascript:;" onclick="return q8ApplyOrderRefund(' + data.islogin + ',' + id + ',\'' + skey + '\')" class="btn btn-danger">&#30003;&#35831;&#36864;&#27454;</a>' +
                            '</div></td></tr>';
                    }
                    if (data.list && data.list.order_state) {
                        item += '<tr><td colspan="6" class="orderTitle" style="text-align:center"><b>&#35746;&#21333;&#23454;&#26102;&#29366;&#24577;</b></td></tr>' +
                            '<tr><td class="warning">&#19979;&#21333;&#25968;&#37327;</td><td>' + data.list.num + '</td><td class="warning">&#19979;&#21333;&#26102;&#38388;</td><td colspan="3">' + data.list.add_time + '</td></tr>' +
                            '<tr><td class="warning">&#21021;&#22987;&#25968;&#37327;</td><td>' + data.list.start_num + '</td><td class="warning">&#24403;&#21069;&#25968;&#37327;</td><td>' + data.list.now_num + '</td><td class="warning">&#35746;&#21333;&#29366;&#24577;</td><td><font color="blue">' + data.list.order_state + '</font></td></tr>';
                    } else if (data.kminfo) {
                        var kmBlock = '<div class="q8-kminfo-box" id="q8-list-kminfo-copy-text">' + data.kminfo + '</div>' +
                            '<button type="button" class="btn btn-primary btn-sm q8-copy-kminfo" onclick="return q8CopyListKminfo()">&#22797;&#21046;&#21345;&#23494;&#20449;&#24687;</button>';
                        item += full('&#20197;&#19979;&#26159;&#20320;&#30340;&#21345;&#23494;&#20449;&#24687;', kmBlock);
                    } else if (data.result) {
                        item += full('&#22788;&#29702;&#32467;&#26524;', data.result);
                    }
                    if (data.desc) {
                        item += full('&#21830;&#21697;&#31616;&#20171;', data.desc);
                    }
                    item += '</table>';
                    layer.open({
                        type: 1,
                        area: [$(window).width() > 480 ? '520px' : '92%', 'auto'],
                        title: '&#35746;&#21333;&#35814;&#32454;&#20449;&#24687;',
                        skin: 'layui-layer-rim',
                        closeBtn: 1,
                        shadeClose: true,
                        content: item
                    });
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function () {
                layer.close(ii);
                layer.alert('\u8ba2\u5355\u8be6\u60c5\u8bf7\u6c42\u5931\u8d25\uff0c\u8bf7\u7a0d\u540e\u91cd\u8bd5\u3002', {icon: 2});
            }
        });
    }

    function q8ApplyOrderRefund(islogin, id, skey) {
        if (!checklogin(islogin)) return false;
        layer.confirm('\u5f85\u5904\u7406\u6216\u5f02\u5e38\u72b6\u6001\u8ba2\u5355\u53ef\u4ee5\u7533\u8bf7\u9000\u6b3e\uff0c\u9000\u6b3e\u4e4b\u540e\u8d44\u91d1\u4f1a\u9000\u5230\u7528\u6237\u4f59\u989d\uff0c\u662f\u5426\u786e\u8ba4\u9000\u6b3e\uff1f', {
            btn: ['\u786e\u8ba4\u9000\u6b3e', '\u53d6\u6d88']
        }, function(){
            var ii = layer.load(2, {shade:[0.1,'#fff']});
            $.ajax({
                type: "POST",
                url: "../ajax.php?act=apply_refund",
                data: {id: id, skey: skey},
                dataType: "json",
                success: function(data) {
                    layer.close(ii);
                    if (data.code == 0) {
                        layer.alert('\u6210\u529f\u9000\u6b3e' + data.money + '\u5143\u5230\u4f59\u989d\uff01', {icon:1}, function(){ window.location.reload(); });
                    } else {
                        layer.alert(data.msg, {icon:2});
                    }
                },
                error: function() {
                    layer.close(ii);
                    layer.alert('\u9000\u6b3e\u8bf7\u6c42\u5931\u8d25\uff0c\u8bf7\u7a0d\u540e\u91cd\u8bd5\u6216\u8054\u7cfb\u7ba1\u7406\u5458\u3002', {icon:2});
                }
            });
        });
        return false;
    }

</script>
</body>
</html>

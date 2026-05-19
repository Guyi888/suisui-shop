<?php
include("./includes/common.php");
$title = '商品动态';
$toolLogsToday = (new DateTimeImmutable('now', new DateTimeZone('Asia/Shanghai')))->format('Y-m-d');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo htmlspecialchars($title); ?> - <?php echo htmlspecialchars($conf['sitename']); ?></title>
    <link rel="stylesheet" href="//lib.baomitu.com/layui/2.9.3/css/layui.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: #f6f7fb;
            color: #20242c;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Microsoft YaHei", Arial, sans-serif;
        }
        .page-wrap {
            width: min(980px, calc(100% - 24px));
            margin: 0 auto;
            padding: 28px 0 56px;
        }
        .page-title {
            margin: 0 0 18px;
            font-size: 30px;
            line-height: 1.25;
            font-weight: 800;
            text-align: center;
            color: #1f2937;
            letter-spacing: 0;
        }
        .tab-bar {
            width: fit-content;
            max-width: 100%;
            margin: 0 auto 22px;
            padding: 4px;
            display: flex;
            gap: 4px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        }
        .tab-item {
            min-width: 112px;
            padding: 10px 18px;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
            line-height: 20px;
            color: #64748b;
            cursor: pointer;
            user-select: none;
            transition: background .18s ease, color .18s ease;
        }
        .tab-item.active {
            color: #ffffff;
            background: #2563eb;
        }
        .tab-item.active-off {
            color: #ffffff;
            background: #ea580c;
        }
        .log-panel {
            overflow: hidden;
            margin-bottom: 14px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
        }
        .panel-heading {
            min-height: 58px;
            padding: 0 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            cursor: pointer;
            background: #ffffff;
        }
        .date-text {
            font-weight: 800;
            font-size: 16px;
            color: #111827;
        }
        .item-count {
            margin-left: 8px;
            font-size: 13px;
            color: #64748b;
        }
        .today-badge {
            margin-left: 8px;
            padding: 2px 7px;
            border-radius: 999px;
            background: #dcfce7;
            color: #15803d;
            font-size: 12px;
        }
        .toggle-btn {
            flex: 0 0 auto;
            border: 0;
            background: transparent;
            color: #64748b;
            font-size: 13px;
            cursor: pointer;
        }
        .panel-body {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            border-top: 1px solid transparent;
            transition: max-height .22s ease, opacity .18s ease;
        }
        .panel-expanded .panel-body {
            max-height: none;
            overflow: visible;
            opacity: 1;
            border-top-color: #eef2f7;
        }
        .items-container {
            display: grid;
            gap: 8px;
            padding: 14px 18px 18px;
        }
        .goods-row {
            min-height: 42px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 10px;
            background: #f8fafc;
            border: 1px solid #eef2f7;
            border-radius: 6px;
        }
        .left {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .tag {
            flex: 0 0 auto;
            min-width: 48px;
            padding: 3px 8px;
            border-radius: 5px;
            color: #ffffff;
            font-size: 12px;
            line-height: 18px;
            text-align: center;
        }
        .tag-on { background: #2563eb; }
        .tag-off { background: #ea580c; }
        .text-overflow {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 14px;
            line-height: 22px;
        }
        .goods-link {
            color: #1d4ed8;
            text-decoration: none;
        }
        .goods-link:hover { color: #0f766e; }
        .goods-name { color: #334155; }
        .load-more-btn {
            width: 180px;
            height: 42px;
            margin: 20px auto 0;
            display: none;
            border: 0;
            border-radius: 6px;
            background: #111827;
            color: #ffffff;
            cursor: pointer;
        }
        .load-more-btn:disabled {
            cursor: default;
            opacity: .65;
        }
        .loading-box,
        .empty {
            margin: 42px auto;
            padding: 24px;
            text-align: center;
            color: #64748b;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .floating-button {
            position: fixed;
            left: 22px;
            top: 24px;
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #111827;
            color: #ffffff;
            text-decoration: none;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .18);
        }
        @media (max-width: 640px) {
            .page-wrap { width: calc(100% - 18px); padding-top: 20px; }
            .page-title { font-size: 24px; }
            .tab-bar { width: 100%; }
            .tab-item { flex: 1; min-width: 0; padding: 9px 10px; }
            .panel-heading { padding: 0 12px; }
            .items-container { padding: 12px; }
            .text-overflow { white-space: normal; overflow-wrap: anywhere; }
            .floating-button { left: 14px; top: 14px; }
        }
    </style>
</head>
<body>
<div class="page-wrap">
    <h1 class="page-title">商品动态</h1>
    <div class="tab-bar">
        <div class="tab-item active" data-tab="on">上架日志</div>
        <div class="tab-item" data-tab="off">下架日志</div>
    </div>
    <div id="toolLog-flow"><div class="loading-box">&#21152;&#36733;&#20013;...</div></div>
    <div id="offLog-flow" style="display:none;"><div class="loading-box">&#21152;&#36733;&#20013;...</div></div>
    <button id="load-more" class="load-more-btn"><span>加载更多</span></button>
    <a class="floating-button" href="./"><i class="layui-icon layui-icon-return"></i></a>
</div>
<script src="//lib.baomitu.com/jquery/3.6.0/jquery.min.js"></script>
<script src="//lib.baomitu.com/layui/2.9.3/layui.js"></script>
<script>
(function () {
    var today = <?php echo json_encode($toolLogsToday, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    var toggleOpenText = '\u5c55\u5f00 <span>\u25bc</span>';
    var toggleCloseText = '\u6536\u8d77 <span>\u25b2</span>';
    var loadingText = '\u52a0\u8f7d\u4e2d...';
    var loadMoreText = '\u52a0\u8f7d\u66f4\u591a';
    var loadFailText = '\u52a0\u8f7d\u5931\u8d25';
    var todayBadgeText = '\u4eca\u65e5';
    var emptyText = '\u6682\u65e0\u8bb0\u5f55';
    var emptyOnText = '\u6682\u65e0\u4e0a\u67b6\u8bb0\u5f55';
    var emptyOffText = '\u6682\u65e0\u4e0b\u67b6\u8bb0\u5f55';
    var recordCountSuffix = ' &#26465;&#35760;&#24405;';
    var state = {
        on: { page: 1, loading: false, more: true, endpoint: 'ajax.php?act=toollogsgroup' },
        off: { page: 1, loading: false, more: true, endpoint: 'ajax.php?act=toollogsoffline&limit=5' }
    };
    var currentTab = 'on';

    function escapeHtml(text) {
        return String(text || '').replace(/[&<>"']/g, function (s) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[s];
        });
    }

    function togglePanel(header) {
        var panel = $(header).closest('.log-panel');
        var open = panel.hasClass('panel-expanded');
        panel.toggleClass('panel-expanded', !open).toggleClass('panel-collapsed', open);
        panel.find('.toggle-btn').html(open ? toggleOpenText : toggleCloseText);
    }

    function panelHtml(item) {
        var count = item.list ? item.list.length : 0;
        var isToday = String(item.time || '') === today;
        var content = item.content || '<div class="empty">' + emptyText + '</div>';
        return '<div class="log-panel ' + (isToday ? 'panel-expanded' : 'panel-collapsed') + '">' +
            '<div class="panel-heading">' +
                '<span><span class="date-text">' + escapeHtml(item.time) + '</span>' +
                (isToday ? '<span class="today-badge">' + todayBadgeText + '</span>' : '') +
                '<span class="item-count">(' + count + recordCountSuffix + ')</span></span>' +
                '<button type="button" class="toggle-btn">' + (isToday ? toggleCloseText : toggleOpenText) + '</button>' +
            '</div>' +
            '<div class="panel-body"><div class="items-container">' + content + '</div></div>' +
        '</div>';
    }

    function loadLogs(tab) {
        var s = state[tab];
        if (s.loading || !s.more) return;
        var flow = tab === 'on' ? '#toolLog-flow' : '#offLog-flow';
        if (s.page === 1) $(flow).html('<div class="loading-box">' + loadingText + '</div>');
        s.loading = true;
        $('#load-more').prop('disabled', true).find('span').text(loadingText);
        $.getJSON(s.endpoint + '&page=' + s.page, function (res) {
            if (res.code === 0 && res.data && res.data.length) {
                if (s.page === 1) $(flow).empty();
                var html = '';
                $.each(res.data, function (_, item) {
                    html += panelHtml(item);
                });
                $(flow).append(html);
                s.more = s.page < Number(res.page || 0);
                s.page += 1;
            } else {
                if (s.page === 1) $(flow).html('<div class="empty">' + (tab === 'on' ? emptyOnText : emptyOffText) + '</div>');
                s.more = false;
            }
        }).fail(function () {
            if (layui.layer) {
                layui.layer.msg(loadFailText);
            }
        }).always(function () {
            s.loading = false;
            $('#load-more').prop('disabled', false).find('span').text(loadMoreText);
            $('#load-more').toggle(state[currentTab].more);
        });
    }

    $(document).on('click', '.panel-heading', function () {
        togglePanel(this);
    });

    $('.tab-item').on('click', function () {
        currentTab = $(this).data('tab');
        $('.tab-item').removeClass('active active-off');
        $(this).addClass(currentTab === 'on' ? 'active' : 'active-off');
        $('#toolLog-flow').toggle(currentTab === 'on');
        $('#offLog-flow').toggle(currentTab === 'off');
        $('#load-more').toggle(state[currentTab].more);
        if (state[currentTab].page === 1) {
            loadLogs(currentTab);
        }
    });

    $('#load-more').on('click', function () {
        loadLogs(currentTab);
    });

    loadLogs('on');
})();
</script>
</body>
</html>

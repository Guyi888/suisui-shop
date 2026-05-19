var pagesize = 30;

function listTable(query) {
    var url = window.document.location.href.toString();
    var queryString = url.split("?")[1];
    query = query || queryString;
    if (query === 'start' || query === undefined) {
        query = '';
    }
    history.replaceState({}, null, './seckill.php?' + query);
    layer.closeAll();
    var loading = layer.load(2, {shade: [0.1, '#fff']});
    $.ajax({
        type: 'GET',
        url: 'seckill-table.php?num=' + pagesize + '&' + query,
        dataType: 'html',
        cache: false,
        success: function (data) {
            layer.close(loading);
            $("#listTable").html(data);
            var title = $("#listTable").find('.admin-ops-table-meta').data('title');
            if (title) {
                $("#blocktitle").html(title);
            }
        },
        error: function () {
            layer.msg('\u670d\u52a1\u5668\u9519\u8bef');
        }
    });
}

function searchItem() {
    var kw = $("input[name='kw']").val();
    if (kw === '') {
        listTable('start');
    } else {
        listTable('kw=' + encodeURIComponent(kw));
    }
    return false;
}

function setActive(id, active) {
    $.ajax({
        type: 'GET',
        url: 'ajax.php?act=setSeckill&id=' + encodeURIComponent(id) + '&active=' + encodeURIComponent(active),
        dataType: 'json',
        success: function () {
            listTable();
        },
        error: function () {
            layer.msg('\u670d\u52a1\u5668\u9519\u8bef');
        }
    });
}

function delTool(id) {
    var confirmobj = layer.confirm('\u786e\u5b9a\u5220\u9664\u8be5\u79d2\u6740\u5546\u54c1\u5417\uff1f', {
        btn: ['\u786e\u5b9a', '\u53d6\u6d88']
    }, function () {
        $.ajax({
            type: 'GET',
            url: 'ajax.php?act=delSeckill&id=' + encodeURIComponent(id),
            dataType: 'json',
            success: function (data) {
                if (data.code == 0) {
                    layer.msg('\u5220\u9664\u6210\u529f');
                    listTable();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function () {
                layer.msg('\u670d\u52a1\u5668\u9519\u8bef');
            }
        });
    }, function () {
        layer.close(confirmobj);
    });
}

$(document).ready(function () {
    listTable();
    $("#pagesize").change(function () {
        pagesize = $(this).val();
        listTable();
    });
    $("#seckillSearchForm").on('submit', function () {
        return searchItem();
    });
    $("#seckillRefresh").on('click', function () {
        listTable('start');
    });
    $("#listTable").on('click', '[data-seckill-query]', function () {
        listTable($(this).data('seckill-query'));
    });
    $("#listTable").on('click', '.js-seckill-active', function () {
        setActive($(this).data('id'), $(this).data('active'));
    });
    $("#listTable").on('click', '.js-seckill-delete', function () {
        delTool($(this).data('id'));
    });
});

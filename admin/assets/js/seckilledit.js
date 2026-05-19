$(document).ready(function () {
    $("#cid").change(function () {
        var cid = $(this).val();
        var loading = layer.load(2, {shade: [0.1, '#fff']});
        $("#tid").empty().append('<option value="0">\u8bf7\u9009\u62e9\u5546\u54c1</option>');
        $.ajax({
            type: "GET",
            url: "./ajax.php?act=gettool&cid=" + encodeURIComponent(cid),
            dataType: 'json',
            success: function (data) {
                layer.close(loading);
                if (data.code == 0) {
                    var num = 0;
                    $.each(data.data, function (i, res) {
                        $("#tid").append('<option value="' + res.tid + '">' + res.name + '</option>');
                        num++;
                    });
                    $("#tid").val(0);
                    if (num == 0 && cid != 0) {
                        $("#tid").html('<option value="0">\u8be5\u5206\u7c7b\u4e0b\u6ca1\u6709\u5546\u54c1</option>');
                    }
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function () {
                layer.msg('\u670d\u52a1\u5668\u9519\u8bef');
                return false;
            }
        });
    });
    if ($("#cid").length > 0) {
        $("#cid").change();
    }
    var items = $("select[default]");
    for (var i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default") || 0);
    }
});

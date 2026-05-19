<?php
include("../includes/common.php");
$title = 'CF IP白名单';
$mod = 'cf_ip_whitelist';
if ($islogin == 1) {
} else {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission('set', 1);
include './head.php';

$cf_api_token = isset($conf['cf_api_token']) ? $conf['cf_api_token'] : '';
$cf_zone_id = isset($conf['cf_zone_id']) ? $conf['cf_zone_id'] : '';
$cf_zone_name = isset($conf['cf_zone_name']) && $conf['cf_zone_name'] ? $conf['cf_zone_name'] : (isset($_SERVER['HTTP_HOST']) ? preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']) : '');
?>
<div class="col-sm-12 col-md-10 center-block" style="float: none;">
    <div class="block">
        <div class="block-title"><h3 class="panel-title">CF IP白名单</h3></div>
        <div class="card">
            <div class="card-body">
                <div class="alert alert-info">
                    <p><strong>用途：</strong>把对接 q8 的服务器 IP 加入 Cloudflare 白名单，避免接口请求被防护页拦截。</p>
                    <p><strong>建议：</strong>只添加自己控制的服务器 IP，不要添加客户 IP。</p>
                </div>
                <form onsubmit="return saveCfConfig(this)" method="post" class="form-horizontal" role="form">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">API Token</label>
                        <div class="col-sm-9">
                            <input type="password" name="cf_api_token" value="<?php echo htmlspecialchars($cf_api_token); ?>" class="form-control" autocomplete="off" placeholder="Cloudflare API Token">
                        </div>
                    </div>
                    <br/>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Zone名称</label>
                        <div class="col-sm-9">
                            <input type="text" name="cf_zone_name" value="<?php echo htmlspecialchars($cf_zone_name); ?>" class="form-control" placeholder="example.com">
                        </div>
                    </div>
                    <br/>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Zone ID</label>
                        <div class="col-sm-9">
                            <input type="text" name="cf_zone_id" value="<?php echo htmlspecialchars($cf_zone_id); ?>" class="form-control" placeholder="可留空，系统会按Zone名称自动获取">
                            <span class="help-block">留空时会自动查询并保存 Zone ID。</span>
                        </div>
                    </div>
                    <br/>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i> 保存配置</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="block">
        <div class="block-title"><h3 class="panel-title">添加白名单IP</h3></div>
        <div class="card">
            <div class="card-body">
                <form onsubmit="return addCfIp(this)" method="post" class="form-horizontal" role="form">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">服务器IP</label>
                        <div class="col-sm-9">
                            <input type="text" name="ip" class="form-control" placeholder="例如：154.222.30.169" required>
                        </div>
                    </div>
                    <br/>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">备注</label>
                        <div class="col-sm-9">
                            <input type="text" name="note" class="form-control" placeholder="例如：对接服务器">
                        </div>
                    </div>
                    <br/>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <button type="submit" class="btn btn-success btn-block"><i class="fa fa-plus"></i> 添加到CF白名单</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="block">
        <div class="block-title"><h3 class="panel-title">当前CF白名单</h3></div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>IP</th>
                            <th>备注</th>
                            <th>添加时间</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody id="cf-ip-list">
                        <tr><td colspan="4" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-default" onclick="loadCfIps()"><i class="fa fa-refresh"></i> 刷新列表</button>
            </div>
        </div>
    </div>
</div>

<?php include './foot.php';?>
<script src="<?php echo $cdnpublic?>layer/2.3/layer.js"></script>
<script>
function cfMsg(text, icon){
    layer.msg(text, {icon: icon || 1});
}
function saveCfConfig(obj){
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type: 'POST',
        url: 'ajax_cf_ip_whitelist.php?act=save_config',
        data: $(obj).serialize(),
        dataType: 'json',
        success: function(data){
            layer.close(ii);
            if(data.code == 0){
                cfMsg(data.msg, 1);
                setTimeout(function(){ location.reload(); }, 800);
            }else{
                layer.alert(data.msg, {icon: 2});
            }
        },
        error: function(){
            layer.msg('\u670d\u52a1\u5668\u9519\u8bef');
        }
    });
    return false;
}
function addCfIp(obj){
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type: 'POST',
        url: 'ajax_cf_ip_whitelist.php?act=add',
        data: $(obj).serialize(),
        dataType: 'json',
        success: function(data){
            layer.close(ii);
            if(data.code == 0){
                cfMsg(data.msg, 1);
                obj.reset();
                loadCfIps();
            }else{
                layer.alert(data.msg, {icon: 2});
            }
        },
        error: function(){
            layer.msg('\u670d\u52a1\u5668\u9519\u8bef');
        }
    });
    return false;
}
function delCfIp(id){
    layer.confirm('\u786e\u5b9a\u5220\u9664\u8fd9\u4e2aIP\u767d\u540d\u5355\u5417\uff1f', {icon: 3}, function(index){
        layer.close(index);
        $.post('ajax_cf_ip_whitelist.php?act=del', {id: id}, function(data){
            if(data.code == 0){
                cfMsg(data.msg, 1);
                loadCfIps();
            }else{
                layer.alert(data.msg, {icon: 2});
            }
        }, 'json').fail(function(){
            layer.msg('\u670d\u52a1\u5668\u9519\u8bef');
        });
    });
}
function htmlEscape(value){
    return $('<div/>').text(value || '').html();
}
function loadCfIps(){
    $('#cf-ip-list').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
    $.getJSON('ajax_cf_ip_whitelist.php?act=list', function(data){
        if(data.code != 0){
            $('#cf-ip-list').html('<tr><td colspan="4" class="text-danger text-center">'+htmlEscape(data.msg)+'</td></tr>');
            return;
        }
        var html = '';
        if(!data.data || data.data.length == 0){
            html = '<tr><td colspan="4" class="text-center">\u6682\u65e0\u767d\u540d\u5355</td></tr>';
        }else{
            $.each(data.data, function(i, row){
                var ip = row.configuration && row.configuration.value ? row.configuration.value : '';
                html += '<tr>' +
                    '<td>'+htmlEscape(ip)+'</td>' +
                    '<td>'+htmlEscape(row.notes || '')+'</td>' +
                    '<td>'+htmlEscape(row.created_on || '')+'</td>' +
                    '<td><button type="button" class="btn btn-xs btn-danger js-cf-del" data-id="'+htmlEscape(row.id)+'"><i class="fa fa-trash"></i> &#21024;&#38500;</button></td>' +
                    '</tr>';
            });
        }
        $('#cf-ip-list').html(html);
    }).fail(function(){
        $('#cf-ip-list').html('<tr><td colspan="4" class="text-danger text-center">\u52a0\u8f7d\u5931\u8d25</td></tr>');
    });
}
$(function(){
    loadCfIps();
    $(document).on('click', '.js-cf-del', function(){
        delCfIp($(this).data('id'));
    });
});
</script>

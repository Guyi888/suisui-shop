<?php
include("../includes/common.php");
$title = 'CF IP黑名单';
$mod = 'cf_ip_blocklist';
if ($islogin == 1) {
} else {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
adminpermission('set', 1);
include './head.php';

$cf_account_email = isset($conf['cf_account_email']) ? $conf['cf_account_email'] : '';
$cf_global_api_key = isset($conf['cf_global_api_key']) ? $conf['cf_global_api_key'] : '';
$cf_account_id = isset($conf['cf_account_id']) ? $conf['cf_account_id'] : '';
?>
<div class="col-sm-12 col-md-10 center-block" style="float: none;">
    <div class="block">
        <div class="block-title"><h3 class="panel-title">CF IP黑名单</h3></div>
        <div class="card">
            <div class="card-body">
                <div class="alert alert-info">
                    <p><strong>用途：</strong>在后台添加客户 IP 后，系统会自动写入 Cloudflare 账户级 Block 规则。</p>
                    <p><strong>范围：</strong>账户级规则会影响当前 Cloudflare 账户下接入的域名。</p>
                </div>
                <form onsubmit="return saveCfBlockConfig(this)" method="post" class="form-horizontal" role="form">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">CF账号邮箱</label>
                        <div class="col-sm-9">
                            <input type="email" name="cf_account_email" value="<?php echo htmlspecialchars($cf_account_email); ?>" class="form-control" autocomplete="off" placeholder="Cloudflare 登录邮箱">
                        </div>
                    </div>
                    <br/>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Global API Key</label>
                        <div class="col-sm-9">
                            <input type="password" name="cf_global_api_key" value="<?php echo htmlspecialchars($cf_global_api_key); ?>" class="form-control" autocomplete="off" placeholder="Cloudflare Global API Key">
                            <span class="help-block">密钥只保存在后台配置中，页面以密码框显示。</span>
                        </div>
                    </div>
                    <br/>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Account ID</label>
                        <div class="col-sm-9">
                            <input type="text" name="cf_account_id" value="<?php echo htmlspecialchars($cf_account_id); ?>" class="form-control" placeholder="可留空，系统会自动获取">
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
        <div class="block-title"><h3 class="panel-title">添加黑名单IP</h3></div>
        <div class="card">
            <div class="card-body">
                <form onsubmit="return addCfBlockIp(this)" method="post" class="form-horizontal" role="form">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">客户IP</label>
                        <div class="col-sm-9">
                            <input type="text" name="ip" class="form-control" placeholder="支持 IPv4 / IPv6" required>
                        </div>
                    </div>
                    <br/>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">备注</label>
                        <div class="col-sm-9">
                            <input type="text" name="note" class="form-control" placeholder="例如：恶意下单客户">
                        </div>
                    </div>
                    <br/>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <button type="submit" class="btn btn-danger btn-block"><i class="fa fa-ban"></i> 添加到CF黑名单</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="block">
        <div class="block-title"><h3 class="panel-title">当前CF黑名单</h3></div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>IP</th>
                            <th>类型</th>
                            <th>备注</th>
                            <th>添加时间</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody id="cf-block-list">
                        <tr><td colspan="5" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-default" onclick="loadCfBlockIps()"><i class="fa fa-refresh"></i> 刷新列表</button>
            </div>
        </div>
    </div>
</div>

<?php include './foot.php';?>
<script src="<?php echo $cdnpublic?>layer/2.3/layer.js"></script>
<script>
function cfBlockMsg(text, icon){
    layer.msg(text, {icon: icon || 1});
}
function saveCfBlockConfig(obj){
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type: 'POST',
        url: 'ajax_cf_ip_blocklist.php?act=save_config',
        data: $(obj).serialize(),
        dataType: 'json',
        success: function(data){
            layer.close(ii);
            if(data.code == 0){
                cfBlockMsg(data.msg, 1);
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
function addCfBlockIp(obj){
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type: 'POST',
        url: 'ajax_cf_ip_blocklist.php?act=add',
        data: $(obj).serialize(),
        dataType: 'json',
        success: function(data){
            layer.close(ii);
            if(data.code == 0){
                cfBlockMsg(data.msg, 1);
                obj.reset();
                loadCfBlockIps();
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
function delCfBlockIp(id){
    layer.confirm('\u786e\u5b9a\u5220\u9664\u8fd9\u4e2aIP\u9ed1\u540d\u5355\u5417\uff1f', {icon: 3}, function(index){
        layer.close(index);
        $.post('ajax_cf_ip_blocklist.php?act=del', {id: id}, function(data){
            if(data.code == 0){
                cfBlockMsg(data.msg, 1);
                loadCfBlockIps();
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
function loadCfBlockIps(){
    $('#cf-block-list').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
    $.getJSON('ajax_cf_ip_blocklist.php?act=list', function(data){
        if(data.code != 0){
            $('#cf-block-list').html('<tr><td colspan="5" class="text-danger text-center">'+htmlEscape(data.msg)+'</td></tr>');
            return;
        }
        var html = '';
        if(!data.data || data.data.length == 0){
            html = '<tr><td colspan="5" class="text-center">\u6682\u65e0\u9ed1\u540d\u5355</td></tr>';
        }else{
            $.each(data.data, function(i, row){
                var ip = row.configuration && row.configuration.value ? row.configuration.value : '';
                var target = row.configuration && row.configuration.target ? row.configuration.target : '';
                html += '<tr>' +
                    '<td>'+htmlEscape(ip)+'</td>' +
                    '<td>'+htmlEscape(target === 'ip6' ? 'IPv6' : 'IPv4')+'</td>' +
                    '<td>'+htmlEscape(row.notes || '')+'</td>' +
                    '<td>'+htmlEscape(row.created_on || '')+'</td>' +
                    '<td><button type="button" class="btn btn-xs btn-success js-cf-block-del" data-id="'+htmlEscape(row.id)+'"><i class="fa fa-unlock"></i> &#35299;&#38500;</button></td>' +
                    '</tr>';
            });
        }
        $('#cf-block-list').html(html);
    }).fail(function(){
        $('#cf-block-list').html('<tr><td colspan="5" class="text-danger text-center">\u52a0\u8f7d\u5931\u8d25</td></tr>');
    });
}
$(function(){
    loadCfBlockIps();
    $(document).on('click', '.js-cf-block-del', function(){
        delCfBlockIp($(this).data('id'));
    });
});
</script>

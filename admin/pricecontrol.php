<?php
include("../includes/common.php");
if($islogin != 1) exit("<script>window.location.href='./login.php';</script>");
adminpermission('price', isset($_POST['act']) ? 2 : 1);

if (!function_exists('suisui_pricecontrol_ensure_schema')) {
    function suisui_pricecontrol_ensure_schema($DB)
    {
        $DB->exec("CREATE TABLE IF NOT EXISTS `pre_pricecontrol` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `cat_id` int(11) NOT NULL DEFAULT '0',
            `product_id` varchar(64) NOT NULL DEFAULT '',
            `product_name` varchar(255) NOT NULL DEFAULT '',
            `control_price` decimal(10,2) NOT NULL DEFAULT '0.00',
            `agent_price` decimal(10,2) DEFAULT NULL,
            `note` varchar(500) NOT NULL DEFAULT '',
            `sort` int(11) NOT NULL DEFAULT '0',
            `active` tinyint(1) NOT NULL DEFAULT '1',
            `addtime` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `cat_id` (`cat_id`),
            KEY `active_sort` (`active`,`sort`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}

suisui_pricecontrol_ensure_schema($DB);

if(isset($_POST['act'])){
    header('Content-Type: application/json');
    $act = $_POST['act'];

    if($act == 'save_item'){
        $id   = intval($_POST['id'] ?? 0);
        $data = [
            'cat_id'       => intval($_POST['cat_id'] ?? 0),
            'product_id'   => trim($_POST['product_id'] ?? ''),
            'product_name' => trim($_POST['product_name'] ?? ''),
            'control_price'=> floatval($_POST['control_price'] ?? 0),
            'agent_price'  => $_POST['agent_price']!=='' ? floatval($_POST['agent_price']) : null,
            'note'         => trim($_POST['note'] ?? ''),
            'sort'         => intval($_POST['sort'] ?? 0),
            'active'       => intval($_POST['active'] ?? 1),
        ];
        if(!$data['product_name']){ echo json_encode(['code'=>0,'msg'=>'商品名称不能为空']); exit; }
        if($id > 0){
            $DB->query("UPDATE pre_pricecontrol SET cat_id=?,product_id=?,product_name=?,control_price=?,agent_price=?,note=?,sort=?,active=? WHERE id=?",
                [$data['cat_id'],$data['product_id'],$data['product_name'],$data['control_price'],$data['agent_price'],$data['note'],$data['sort'],$data['active'],$id]);
        } else {
            $DB->query("INSERT INTO pre_pricecontrol (cat_id,product_id,product_name,control_price,agent_price,note,sort,active,addtime) VALUES (?,?,?,?,?,?,?,?,?)",
                [$data['cat_id'],$data['product_id'],$data['product_name'],$data['control_price'],$data['agent_price'],$data['note'],$data['sort'],$data['active'],date('Y-m-d H:i:s')]);
        }
        echo json_encode(['code'=>1]);

    } elseif($act == 'del_item'){
        $id = intval($_POST['id'] ?? 0);
        if($id > 0) $DB->query("DELETE FROM pre_pricecontrol WHERE id=?", [$id]);
        echo json_encode(['code'=>1]);

    } elseif($act == 'save_settings'){
        $pwd    = trim($_POST['pwd'] ?? '');
        $notice = trim($_POST['notice'] ?? '');
        if($pwd !== '') $DB->query("INSERT INTO pre_config(k,v) VALUES('pricecontrol_pwd',?) ON DUPLICATE KEY UPDATE v=?", [$pwd,$pwd]);
        $DB->query("INSERT INTO pre_config(k,v) VALUES('pricecontrol_notice',?) ON DUPLICATE KEY UPDATE v=?", [$notice,$notice]);
        echo json_encode(['code'=>1]);

    } elseif($act == 'search_product'){
        $q = trim($_POST['q'] ?? '');
        if(mb_strlen($q) < 1){ echo json_encode(['code'=>1,'list'=>[]]); exit; }
        $like = '%' . $q . '%';
        $list = $DB->getAll("SELECT tid as id, name, cid FROM pre_tools WHERE active=1 AND close=0 AND name LIKE :q ORDER BY sales DESC, tid ASC LIMIT 20", [':q' => $like]);
        echo json_encode(['code'=>1,'list'=>$list]);
    }
    exit;
}

$title = '控价管理';
include './head.php';

$items  = $DB->getAll("SELECT p.*,c.name as cat_name FROM pre_pricecontrol p LEFT JOIN pre_class c ON p.cat_id=c.cid ORDER BY p.sort ASC, p.id ASC");

// 分类：一级 + 二级
$cats_lvl1 = $DB->getAll("SELECT cid,name,sort FROM pre_class WHERE zid=1 AND active=1 AND pid=0 ORDER BY sort ASC");
$cats_lvl2 = $DB->getAll("SELECT cid,pid,name,sort FROM pre_class WHERE zid=1 AND active=1 AND pid>0 ORDER BY sort ASC");
$lvl2_by_parent = [];
foreach($cats_lvl2 as $c) $lvl2_by_parent[$c['pid']][] = $c;

$pwd    = (string)$DB->getColumn("SELECT v FROM pre_config WHERE k='pricecontrol_pwd'");
$notice = (string)$DB->getColumn("SELECT v FROM pre_config WHERE k='pricecontrol_notice'");

// build cat options for JS (含二级)
$cat_options = '<option value="0">不分类</option>';
foreach($cats_lvl1 as $c){
    $cat_options .= '<option value="'.$c['cid'].'">'.htmlspecialchars($c['name'],ENT_QUOTES,'UTF-8').'</option>';
    if(isset($lvl2_by_parent[$c['cid']])){
        foreach($lvl2_by_parent[$c['cid']] as $sub){
            $cat_options .= '<option value="'.$sub['cid'].'">└ '.htmlspecialchars($sub['name'],ENT_QUOTES,'UTF-8').'</option>';
        }
    }
}
?>
<div class="wrapper">
<div class="col-sm-12">

<!-- 设置面板 -->
<div class="panel panel-default">
    <div class="panel-heading font-bold"><i class="fa fa-cog"></i> 页面设置</div>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label>查看密码</label>
                    <input type="text" id="setPwd" class="form-control" value="<?php echo htmlspecialchars($pwd,ENT_QUOTES,'UTF-8')?>" placeholder="代理输入此密码才能查看">
                </div>
            </div>
            <div class="col-sm-8">
                <div class="form-group">
                    <label>顶部公告（支持换行）</label>
                    <textarea id="setNotice" class="form-control" rows="2" placeholder="控价说明、罚则等..."><?php echo htmlspecialchars($notice,ENT_QUOTES,'UTF-8')?></textarea>
                </div>
            </div>
        </div>
        <button class="btn btn-primary" onclick="saveSettings()"><i class="fa fa-save"></i> 保存设置</button>
    </div>
</div>

<!-- 控价列表 -->
<div class="panel panel-default">
    <div class="panel-heading font-bold">
        <i class="fa fa-shield"></i> 控价列表
        <button class="btn btn-success btn-xs pull-right" onclick="openModal(0)">
            <i class="fa fa-plus"></i> 添加条目
        </button>
    </div>
    <div class="panel-body" style="padding:0;">
        <table class="table table-hover" style="margin:0;font-size:13px;">
            <thead><tr>
                <th>ID</th><th>分类</th><th>商品ID</th><th>商品名称</th>
                <th>控价</th><th>代理价</th><th>备注</th><th>状态</th><th>操作</th>
            </tr></thead>
            <tbody>
            <?php if(empty($items)){ ?>
            <tr><td colspan="9" class="text-center text-muted" style="padding:30px;">暂无数据</td></tr>
            <?php }else{ foreach($items as $t){ ?>
            <tr>
                <td><?php echo $t['id']?></td>
                <td><?php echo $t['cat_name']?htmlspecialchars($t['cat_name'],ENT_QUOTES,'UTF-8'):'-'?></td>
                <td><?php echo $t['product_id']?htmlspecialchars($t['product_id'],ENT_QUOTES,'UTF-8'):'-'?></td>
                <td><?php echo htmlspecialchars($t['product_name'],ENT_QUOTES,'UTF-8')?></td>
                <td style="color:#e74c3c;font-weight:700;">¥<?php echo number_format($t['control_price'],2)?></td>
                <td style="color:#27ae60;"><?php echo $t['agent_price']!==null?'¥'.number_format($t['agent_price'],2):'-'?></td>
                <td style="color:#999;font-size:12px;"><?php echo htmlspecialchars($t['note']??'',ENT_QUOTES,'UTF-8')?></td>
                <td><?php echo $t['active']?'<span class="label label-success">上架</span>':'<span class="label label-default">下架</span>'?></td>
                <td>
                    <button class="btn btn-xs btn-info" onclick='openModal(<?php echo json_encode($t)?>)'><i class="fa fa-edit"></i></button>
                    <button class="btn btn-xs btn-danger" onclick="delItem(<?php echo $t['id']?>)"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
            <?php }}?>
            </tbody>
        </table>
    </div>
</div>

</div>
</div>

<!-- 表单 -->
<div id="pcForm" style="display:none;padding:15px;">
    <input type="hidden" id="editId">

    <!-- 商品搜索 -->
    <div class="form-group">
        <label><i class="fa fa-search"></i> 搜索现有商品 <small class="text-muted">（选择后自动填入下方信息）</small></label>
        <div style="position:relative;">
            <div class="input-group">
                <input type="text" id="productSearch" class="form-control" placeholder="输入商品名称关键词..." autocomplete="off"
                    oninput="pcSearchDelay()" onkeydown="if(event.key==='Enter'){event.preventDefault();pcDoSearch();}">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default" onclick="pcDoSearch()"><i class="fa fa-search"></i> 搜索</button>
                </span>
            </div>
            <div id="productDropdown" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:9999;background:#fff;border:1px solid #ddd;border-radius:4px;max-height:220px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.12);"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>商品名称 <span class="text-danger">*</span></label>
                <input type="text" id="editName" class="form-control" placeholder="商品名称">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>商品ID</label>
                <input type="text" id="editProductId" class="form-control" placeholder="可留空">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>所属分类</label>
                <select id="editCat" class="form-control">
                    <?php echo $cat_options?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-group">
                <label>商品控价 (¥)</label>
                <input type="number" step="0.01" id="editControlPrice" class="form-control" placeholder="0.00">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>代理价格 (¥) <small class="text-muted">选填</small></label>
                <input type="number" step="0.01" id="editAgentPrice" class="form-control" placeholder="留空则不显示">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>排序</label>
                <input type="number" id="editSort" class="form-control" value="0">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>状态</label>
                <select id="editActive" class="form-control">
                    <option value="1">上架</option>
                    <option value="0">下架</option>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>备注</label>
        <input type="text" id="editNote" class="form-control" placeholder="如：发现低价举报可">
    </div>
</div>

<script>
var PAGE_URL = 'pricecontrol.php';
var layerIdx = null;

function saveSettings(){
    $.post(PAGE_URL, {act:'save_settings', pwd:$('#setPwd').val(), notice:$('#setNotice').val()}, function(r){
        if(r.code==1) layer.msg('保存成功', {icon:1});
        else layer.msg(r.msg||'保存失败', {icon:2});
    }, 'json').fail(function(){ layer.msg('请求失败', {icon:2}); });
}

function openModal(data){
    $('#productSearch').val('');
    $('#productDropdown').hide();
    if(!data || data===0){
        $('#editId,#editProductId,#editName,#editNote').val('');
        $('#editControlPrice,#editAgentPrice').val('');
        $('#editSort').val(0);
        $('#editCat').val(0);
        $('#editActive').val(1);
    } else {
        $('#editId').val(data.id);
        $('#editName').val(data.product_name);
        $('#editProductId').val(data.product_id||'');
        $('#editCat').val(data.cat_id||0);
        $('#editControlPrice').val(data.control_price);
        $('#editAgentPrice').val(data.agent_price!==null?data.agent_price:'');
        $('#editNote').val(data.note||'');
        $('#editSort').val(data.sort||0);
        $('#editActive').val(data.active);
    }
    layerIdx = layer.open({
        type:1, title: (data&&data!==0)?'编辑条目':'添加条目',
        content:$('#pcForm'), area:['660px','auto'], shade:0,
        btn:['保存','取消'],
        yes: function(){ saveItem(); },
        btn2: function(){ layer.close(layerIdx); }
    });
}

function saveItem(){
    var d = {
        act:'save_item', id:$('#editId').val(),
        product_name:$('#editName').val().trim(),
        product_id:$('#editProductId').val().trim(),
        cat_id:$('#editCat').val(),
        control_price:$('#editControlPrice').val(),
        agent_price:$('#editAgentPrice').val(),
        note:$('#editNote').val().trim(),
        sort:$('#editSort').val(),
        active:$('#editActive').val()
    };
    if(!d.product_name){ layer.msg('商品名称不能为空',{icon:2}); return; }
    $.post(PAGE_URL, d, function(r){
        if(r.code==1){ layer.msg('保存成功',{icon:1}); layer.close(layerIdx); setTimeout(function(){ location.reload(); },800); }
        else layer.msg(r.msg||'保存失败',{icon:2});
    },'json').fail(function(){ layer.msg('请求失败',{icon:2}); });
}

function delItem(id){
    layer.confirm('确认删除此条目？', function(index){
        layer.close(index);
        $.post(PAGE_URL, {act:'del_item',id:id}, function(r){
            if(r.code==1){ layer.msg('已删除',{icon:1}); setTimeout(function(){ location.reload(); },600); }
            else layer.msg('删除失败',{icon:2});
        },'json');
    });
}

// 商品搜索
var _pcTimer = null;
function pcSearchDelay(){
    clearTimeout(_pcTimer);
    _pcTimer = setTimeout(pcDoSearch, 400);
}
function pcDoSearch(){
    clearTimeout(_pcTimer);
    var q = document.getElementById('productSearch').value.trim();
    if(!q){ $('#productDropdown').hide(); return; }
    $('#productDropdown').html('<div style="padding:10px 14px;color:#999;font-size:13px;"><i class="fa fa-spinner fa-spin"></i> 搜索中...</div>').show();
    $.post(PAGE_URL, {act:'search_product', q:q}, function(r){
        if(!r.list || !r.list.length){
            $('#productDropdown').html('<div style="padding:10px 14px;color:#999;font-size:13px;">无匹配商品</div>').show();
            return;
        }
        var html = '';
        for(var i=0; i<r.list.length; i++){
            var item = r.list[i];
            var eName = $('<span>').text(item.name).html();
            html += '<div class="pc-prod-item" data-id="'+item.id+'" data-name="'+item.name+'" data-cid="'+(item.cid||0)+'"'
                  + ' style="padding:8px 14px;cursor:pointer;font-size:13px;border-bottom:1px solid #f5f5f5;display:flex;justify-content:space-between;align-items:center;"'
                  + ' onmouseover="this.style.background=\'#f5f7ff\'" onmouseout="this.style.background=\'\'">'
                  + '<span>'+eName+'</span>'
                  + '<span style="color:#bbb;font-size:11px;margin-left:8px;white-space:nowrap;">ID: '+item.id+'</span>'
                  + '</div>';
        }
        $('#productDropdown').html(html).show();
    }, 'json').fail(function(){ $('#productDropdown').html('<div style="padding:10px 14px;color:#e74c3c;font-size:13px;">请求失败</div>').show(); });
}

$(document).on('click', '.pc-prod-item', function(){
    var name = $(this).data('name');
    var id   = $(this).data('id');
    var cid  = $(this).data('cid');
    $('#editName').val(name);
    $('#editProductId').val(id);
    if(cid > 0) $('#editCat').val(cid);
    $('#productSearch').val(name);
    $('#productDropdown').hide();
});

$(document).on('mousedown', function(e){
    if(!$(e.target).closest('#productSearch,#productDropdown,.input-group-btn').length) $('#productDropdown').hide();
});
</script>
<?php include './foot.php'; ?>

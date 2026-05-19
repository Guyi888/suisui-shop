<?php
include("../includes/common.php");
if($islogin != 1) exit("<script>window.location.href='./login.php';</script>");
adminpermission('shop', isset($_POST['act']) ? 2 : 1);

if (!function_exists('suisui_agenttool_ensure_schema')) {
    function suisui_agenttool_ensure_schema($DB)
    {
        $DB->exec("CREATE TABLE IF NOT EXISTS `pre_agenttool` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(120) NOT NULL DEFAULT '',
            `desc` text,
            `icon` varchar(80) NOT NULL DEFAULT 'fa fa-download',
            `color` varchar(20) NOT NULL DEFAULT '#3498db',
            `version` varchar(50) NOT NULL DEFAULT '',
            `download_url` varchar(500) NOT NULL DEFAULT '',
            `is_free` tinyint(1) NOT NULL DEFAULT '1',
            `sort` int(11) NOT NULL DEFAULT '0',
            `active` tinyint(1) NOT NULL DEFAULT '1',
            `addtime` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `active_sort` (`active`,`sort`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}

suisui_agenttool_ensure_schema($DB);

// AJAX 操作
if(isset($_POST['act'])){
    header('Content-Type: application/json');
    $act = $_POST['act'];

    if($act == 'save_tool'){
        $id   = intval($_POST['id'] ?? 0);
        $data = [
            'name'         => trim($_POST['name'] ?? ''),
            'desc'         => trim($_POST['desc'] ?? ''),
            'icon'         => trim($_POST['icon'] ?? 'fa fa-download'),
            'color'        => trim($_POST['color'] ?? '#3498db'),
            'version'      => trim($_POST['version'] ?? ''),
            'download_url' => trim($_POST['download_url'] ?? ''),
            'is_free'      => intval($_POST['is_free'] ?? 1),
            'sort'         => intval($_POST['sort'] ?? 0),
            'active'       => intval($_POST['active'] ?? 1),
        ];
        if(!$data['name']){ echo json_encode(['code'=>0,'msg'=>'工具名称不能为空']); exit; }
        if($id > 0){
            $DB->query("UPDATE pre_agenttool SET name=?, `desc`=?, icon=?, color=?, version=?, download_url=?, is_free=?, sort=?, active=? WHERE id=?",
                [$data['name'], $data['desc'], $data['icon'], $data['color'], $data['version'], $data['download_url'], $data['is_free'], $data['sort'], $data['active'], $id]);
        } else {
            $data['addtime'] = date('Y-m-d H:i:s');
            $cols = implode(',', array_map(fn($k)=>"`$k`", array_keys($data)));
            $vals = implode(',', array_fill(0, count($data), '?'));
            $DB->query("INSERT INTO pre_agenttool ($cols) VALUES ($vals)", array_values($data));
        }
        echo json_encode(['code'=>1]);

    } elseif($act == 'del_tool'){
        $id = intval($_POST['id'] ?? 0);
        if($id > 0) $DB->query("DELETE FROM pre_agenttool WHERE id=?", [$id]);
        echo json_encode(['code'=>1]);

    } elseif($act == 'save_pwd'){
        $pwd = trim($_POST['pwd'] ?? '');
        if($pwd === ''){ echo json_encode(['code'=>0,'msg'=>'密码不能为空']); exit; }
        $DB->query("INSERT INTO pre_config (k,v) VALUES ('agenttool_pwd',?) ON DUPLICATE KEY UPDATE v=?", [$pwd, $pwd]);
        echo json_encode(['code'=>1]);
    }
    exit;
}

$title = '代理工具管理';
include './head.php';

$tools      = $DB->getAll("SELECT * FROM pre_agenttool ORDER BY sort ASC, id ASC");
$unlock_pwd = $DB->getColumn("SELECT v FROM pre_config WHERE k='agenttool_pwd'");
?>
<div class="wrapper">
<div class="col-sm-12">

<!-- 密码设置 -->
<div class="panel panel-default">
    <div class="panel-heading font-bold"><i class="fa fa-key"></i> &#35299;&#38145;&#23494;&#30721;&#35774;&#32622;</div>
    <div class="panel-body">
        <div class="form-inline">
            <label style="margin-right:10px;">&#32479;&#19968;&#35299;&#38145;&#23494;&#30721;&#65306;</label>
            <input type="text" id="unlockPwdInput" class="form-control" style="width:200px;margin-right:10px;"
                   value="<?php echo htmlspecialchars($unlock_pwd,ENT_QUOTES,'UTF-8')?>" placeholder="&#35774;&#32622;&#35299;&#38145;&#23494;&#30721;">
            <button class="btn btn-primary" onclick="savePwd()"><i class="fa fa-save"></i> &#20445;&#23384;</button>
            <span style="color:#999;font-size:12px;margin-left:10px;">&#20195;&#29702;&#36755;&#20837;&#27492;&#23494;&#30721;&#21363;&#21487;&#35299;&#38145;&#20184;&#36153;&#24037;&#20855;</span>
        </div>
    </div>
</div>

<!-- 工具列表 -->
<div class="panel panel-default">
    <div class="panel-heading font-bold">
        <i class="fa fa-wrench"></i> &#24037;&#20855;&#21015;&#34920;
        <button class="btn btn-success btn-xs pull-right" onclick="openModal(0)">
            <i class="fa fa-plus"></i> &#28155;&#21152;&#24037;&#20855;
        </button>
    </div>
    <div class="panel-body" style="padding:0;">
        <table class="table table-hover" style="margin:0;">
            <thead><tr>
                <th>ID</th><th>&#24037;&#20855;&#21517;</th><th>&#29256;&#26412;</th>
                <th>&#31867;&#22411;</th><th>&#25490;&#24207;</th><th>&#29366;&#24577;</th><th>&#25805;&#20316;</th>
            </tr></thead>
            <tbody>
            <?php if(empty($tools)){ ?>
            <tr><td colspan="7" class="text-center text-muted" style="padding:30px;">&#26242;&#26080;&#24037;&#20855;</td></tr>
            <?php }else{ foreach($tools as $t){ ?>
            <tr>
                <td><?php echo $t['id']?></td>
                <td>
                    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?php echo htmlspecialchars($t['color'],ENT_QUOTES,'UTF-8')?>;margin-right:6px;"></span>
                    <?php echo htmlspecialchars($t['name'],ENT_QUOTES,'UTF-8')?>
                </td>
                <td><?php echo htmlspecialchars($t['version']??'',ENT_QUOTES,'UTF-8')?></td>
                <td><?php echo $t['is_free']?'<span class="label label-success">免费</span>':'<span class="label label-warning">付费</span>'?></td>
                <td><?php echo intval($t['sort'])?></td>
                <td><?php echo $t['active']?'<span class="label label-primary">上架</span>':'<span class="label label-default">下架</span>'?></td>
                <td>
                    <button class="btn btn-xs btn-info" onclick='openModal(<?php echo json_encode($t)?>)'>
                        <i class="fa fa-edit"></i> &#32534;&#36753;
                    </button>
                    <button class="btn btn-xs btn-danger" onclick="delTool(<?php echo $t['id']?>)">
                        <i class="fa fa-trash"></i> &#21024;&#38500;
                    </button>
                </td>
            </tr>
            <?php }} ?>
            </tbody>
        </table>
    </div>
</div>

</div>
</div>

<!-- 编辑/新增表单（隐藏，由layer调用） -->
<div id="toolForm" style="display:none;padding:15px;">
    <input type="hidden" id="editId">
    <div class="form-group">
        <label>工具名称 <span class="text-danger">*</span></label>
        <input type="text" id="editName" class="form-control" placeholder="工具名称">
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>版本号</label>
                <input type="text" id="editVersion" class="form-control" placeholder="1.0.0">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>类型</label>
                <select id="editFree" class="form-control">
                    <option value="1">免费</option>
                    <option value="0">付费（需解锁）</option>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>工具描述</label>
        <textarea id="editDesc" class="form-control" rows="2" placeholder="简短描述该工具的功能"></textarea>
    </div>
    <div class="form-group">
        <label>下载地址</label>
        <input type="text" id="editUrl" class="form-control" placeholder="https://...">
    </div>
    <div class="row">
        <div class="col-sm-5">
            <div class="form-group">
                <label>图标类 <small class="text-muted">fa fa-xxx</small></label>
                <input type="text" id="editIcon" class="form-control" placeholder="fa fa-download">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>主题颜色</label>
                <input type="color" id="editColor" class="form-control" value="#3498db">
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                <label>排序</label>
                <input type="number" id="editSort" class="form-control" value="0">
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                <label>状态</label>
                <select id="editActive" class="form-control">
                    <option value="1">上架</option>
                    <option value="0">下架</option>
                </select>
            </div>
        </div>
    </div>
</div>

<script>
var PAGE_URL = 'agenttool.php';

function savePwd(){
    var pwd = $('#unlockPwdInput').val().trim();
    if(!pwd){ layer.msg('密码不能为空'); return; }
    $.post(PAGE_URL, {act:'save_pwd', pwd:pwd}, function(r){
        if(r.code==1) layer.msg('密码已保存', {icon:1});
        else layer.msg(r.msg||'保存失败', {icon:2});
    }, 'json').fail(function(){ layer.msg('请求失败，请重试', {icon:2}); });
}

var layerIdx = null;
function openModal(data){
    if(!data || data===0){
        $('#editId').val('');
        $('#editName,#editDesc,#editUrl,#editVersion').val('');
        $('#editIcon').val('fa fa-download');
        $('#editColor').val('#3498db');
        $('#editSort').val(0);
        $('#editFree').val(1);
        $('#editActive').val(1);
    } else {
        $('#editId').val(data.id);
        $('#editName').val(data.name);
        $('#editDesc').val(data.desc||'');
        $('#editUrl').val(data.download_url||'');
        $('#editVersion').val(data.version||'');
        $('#editIcon').val(data.icon||'fa fa-download');
        $('#editColor').val(data.color||'#3498db');
        $('#editSort').val(data.sort||0);
        $('#editFree').val(data.is_free);
        $('#editActive').val(data.active);
    }
    layerIdx = layer.open({
        type: 1,
        title: (data && data!==0) ? '编辑工具' : '添加工具',
        content: $('#toolForm'),
        area: ['560px','auto'],
        shade: 0,
        btn: ['保存','取消'],
        yes: function(){ saveTool(); },
        btn2: function(){ layer.close(layerIdx); }
    });
}

function saveTool(){
    var data = {
        act:'save_tool',
        id:$('#editId').val(),
        name:$('#editName').val().trim(),
        desc:$('#editDesc').val().trim(),
        download_url:$('#editUrl').val().trim(),
        version:$('#editVersion').val().trim(),
        icon:$('#editIcon').val().trim(),
        color:$('#editColor').val(),
        sort:$('#editSort').val(),
        is_free:$('#editFree').val(),
        active:$('#editActive').val()
    };
    if(!data.name){ layer.msg('工具名称不能为空', {icon:2}); return; }
    $.post(PAGE_URL, data, function(r){
        if(r.code==1){ layer.msg('保存成功', {icon:1}); layer.close(layerIdx); setTimeout(function(){ location.reload(); }, 800); }
        else layer.msg(r.msg||'保存失败', {icon:2});
    }, 'json').fail(function(){ layer.msg('请求失败，请重试', {icon:2}); });
}

function delTool(id){
    layer.confirm('确认删除此工具？', function(index){
        layer.close(index);
        $.post(PAGE_URL, {act:'del_tool', id:id}, function(r){
            if(r.code==1){ layer.msg('已删除', {icon:1}); setTimeout(function(){ location.reload(); }, 600); }
            else layer.msg('删除失败', {icon:2});
        }, 'json');
    });
}
</script>
<?php include './foot.php'; ?>

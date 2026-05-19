<?php
include("../includes/common.php");
if($islogin2!=1) exit("<script language='javascript'>window.location.href='./login.php';</script>");
if($userrow['power'] < 1) exit("<script language='javascript'>window.location.href='./index.php';</script>");

// AJAX 解锁
if(isset($_POST['act']) && $_POST['act'] == 'unlock'){
    header('Content-Type: application/json');
    $pwd     = trim($_POST['pwd'] ?? '');
    $correct = (string)$DB->getColumn("SELECT v FROM pre_config WHERE k='pricecontrol_pwd'");
    if($correct !== '' && $pwd === $correct){
        $_SESSION['pricecontrol_unlocked'] = 1;
        echo json_encode(['code'=>1]);
    } else {
        echo json_encode(['code'=>0,'msg'=>'密码错误，请联系站长获取']);
    }
    exit;
}

$title = '控价公示';
include './head.php';

$unlocked = !empty($_SESSION['pricecontrol_unlocked']);
$notice   = (string)$DB->getColumn("SELECT v FROM pre_config WHERE k='pricecontrol_notice'");

$cat_filter = intval($_GET['cat'] ?? 0);
$search     = trim($_GET['q'] ?? '');

$params = [];
$where = " WHERE active=1";
if($cat_filter > 0) $where .= " AND cat_id=" . $cat_filter;
if($search !== ''){
    $where .= " AND (product_name LIKE :sq OR product_id LIKE :sq)";
    $params[':sq'] = '%' . $search . '%';
}

$items = $unlocked ? $DB->getAll("SELECT * FROM pre_pricecontrol" . $where . " ORDER BY sort ASC, id ASC", $params ?: null) : [];
$total = $unlocked ? $DB->getColumn("SELECT COUNT(*) FROM pre_pricecontrol" . $where, $params ?: null) : 0;

// 构建分类下拉：只显示有商品的分类（含一二级）
$filter_cats = [];
if($unlocked){
    $active_cids_q = $DB->getAll("SELECT DISTINCT cat_id FROM pre_pricecontrol WHERE active=1 AND cat_id>0");
    $active_cids = array_column($active_cids_q, 'cat_id');
    if(!empty($active_cids)){
        $cid_in = implode(',', array_map('intval', $active_cids));
        $cats_raw = $DB->getAll("SELECT cid,pid,name,sort FROM pre_class WHERE cid IN($cid_in) AND active=1");
        $cats_map = [];
        foreach($cats_raw as $c) $cats_map[$c['cid']] = $c;
        $active_set = array_flip($active_cids);

        // 找出所有涉及到的一级分类（含本身是一级的 + 二级的父级）
        $lvl1_needed = [];
        $lvl2_by_parent = [];
        foreach($cats_raw as $c){
            if($c['pid'] == 0){
                $lvl1_needed[$c['cid']] = $c;
            } else {
                $lvl2_by_parent[$c['pid']][] = $c;
                // 如果父级还未加载，需要查询
                if(!isset($lvl1_needed[$c['pid']]) && !isset($cats_map[$c['pid']])){
                    $lvl1_needed[$c['pid']] = null; // placeholder
                }
            }
        }

        // 补齐缺失的父级
        $missing_pids = array_keys(array_filter($lvl1_needed, fn($v) => $v === null));
        if($missing_pids){
            $mp_in = implode(',', array_map('intval', $missing_pids));
            $parent_rows = $DB->getAll("SELECT cid,pid,name,sort FROM pre_class WHERE cid IN($mp_in)");
            foreach($parent_rows as $p){
                $lvl1_needed[$p['cid']] = $p;
                $cats_map[$p['cid']] = $p;
            }
        }

        // 移除仍为null的（找不到父级，直接用cid显示）
        $lvl1_needed = array_filter($lvl1_needed);

        // 按sort排序一级
        usort($lvl1_needed, fn($a,$b) => ($a['sort']??10) <=> ($b['sort']??10) ?: $a['cid'] <=> $b['cid']);

        foreach($lvl1_needed as $c){
            // 只有本身有商品的一级才作为可选项
            if(isset($active_set[$c['cid']])){
                $filter_cats[] = ['cid'=>$c['cid'],'name'=>$c['name'],'level'=>1];
            }
            // 其下有商品的二级
            if(isset($lvl2_by_parent[$c['cid']])){
                $subs = $lvl2_by_parent[$c['cid']];
                usort($subs, fn($a,$b) => ($a['sort']??10) <=> ($b['sort']??10) ?: $a['cid'] <=> $b['cid']);
                foreach($subs as $sub){
                    $filter_cats[] = ['cid'=>$sub['cid'],'name'=>$sub['name'],'level'=>2];
                }
            }
        }
    }
}
?>
<link rel="stylesheet" href="./public/css/blue_theme.css">
<style>
.pc-lock-wrap{
    display:flex;align-items:center;justify-content:center;
    min-height:340px;
}
.pc-lock-box{
    background:#fff;border-radius:16px;padding:36px 32px;width:340px;
    box-shadow:0 4px 24px rgba(0,0,0,.10);text-align:center;
}
.pc-lock-box .lock-icon{font-size:48px;color:#d0d0d0;margin-bottom:16px;}
.pc-lock-box h4{font-size:17px;font-weight:700;margin:0 0 6px;color:#1a1a2e;}
.pc-lock-box p{font-size:13px;color:#999;margin:0 0 20px;}
.pc-lock-box input{
    width:100%;border:1.5px solid #e0e0e0;border-radius:8px;
    padding:10px 14px;font-size:14px;outline:none;box-sizing:border-box;
    margin-bottom:10px;transition:border-color .2s;text-align:center;
}
.pc-lock-box input:focus{border-color:#667eea;}
.pc-lock-btn{
    width:100%;padding:11px;border-radius:8px;font-size:15px;font-weight:700;
    border:none;cursor:pointer;
    background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;
    transition:opacity .2s;
}
.pc-lock-btn:hover{opacity:.88;}
.pc-lock-err{font-size:12px;color:#e74c3c;margin-bottom:8px;display:none;}
.pc-notice{
    background:#fff8e6;border-left:4px solid #fa8c16;
    border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#7c4a00;
    line-height:1.8;
}
.pc-toolbar{
    display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px;
}
.pc-cat-select{
    border:1px solid #e0e0e0;border-radius:8px;padding:6px 10px;
    font-size:13px;outline:none;background:#fff;color:#333;cursor:pointer;
    min-width:140px;
}
.pc-search{display:flex;gap:6px;flex:1;justify-content:flex-end;}
.pc-search input{
    border:1px solid #e0e0e0;border-radius:8px;padding:6px 12px;
    font-size:13px;outline:none;width:160px;
}
.pc-search button{
    background:#667eea;color:#fff;border:none;border-radius:8px;
    padding:6px 14px;cursor:pointer;font-size:13px;
}
.pc-count{font-size:13px;color:#999;margin-bottom:10px;}
/* 桌面表格 */
.pc-table{width:100%;border-collapse:collapse;font-size:13px;}
.pc-table th{background:#f7f8fc;color:#888;font-weight:600;padding:10px 12px;text-align:left;border-bottom:2px solid #f0f0f0;}
.pc-table td{padding:10px 12px;border-bottom:1px solid #f5f5f5;vertical-align:middle;}
.pc-table tr:hover td{background:#fafbff;}
.pc-id-badge{background:#667eea;color:#fff;border-radius:6px;padding:2px 8px;font-size:12px;font-weight:700;}
.pc-control-price{color:#e74c3c;font-weight:700;font-size:14px;}
.pc-agent-price{color:#27ae60;font-weight:600;}
.pc-note{color:#bbb;font-size:12px;}
/* 手机卡片 */
@media(max-width:640px){
    .pc-table-wrap{display:none;}
    .pc-cards{display:block;}
    .pc-card{
        background:#fff;border-radius:10px;padding:14px;margin-bottom:10px;
        box-shadow:0 2px 8px rgba(0,0,0,.06);border:1px solid #f0f0f0;
    }
    .pc-card-top{display:flex;align-items:flex-start;gap:10px;margin-bottom:8px;}
    .pc-card-name{font-size:14px;font-weight:600;color:#1a1a2e;flex:1;}
    .pc-card-row{display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;}
    .pc-card-label{color:#999;}
    .pc-search input{width:120px;}
    .pc-toolbar{flex-direction:column;align-items:stretch;}
    .pc-search{justify-content:flex-start;}
}
@media(min-width:641px){
    .pc-cards{display:none;}
}
.pc-empty{text-align:center;color:#ccc;padding:40px 0;font-size:14px;}
</style>

<div class="wrapper">
<div class="col-sm-12">
<div class="panel panel-default">
    <div class="panel-heading font-bold">
        <i class="fa fa-shield"></i>&nbsp;控价公示
    </div>
    <div class="panel-body">

    <?php if(!$unlocked){ ?>
    <!-- 密码锁定页 -->
    <div class="pc-lock-wrap">
        <div class="pc-lock-box">
            <div class="lock-icon"><i class="fa fa-lock"></i></div>
            <h4>控价内容已加密</h4>
            <p>请输入站长提供的查看密码</p>
            <div class="pc-lock-err" id="pcErr"></div>
            <input type="password" id="pcPwd" placeholder="请输入密码" onkeydown="if(event.key==='Enter')doUnlock()">
            <button class="pc-lock-btn" onclick="doUnlock()">
                <i class="fa fa-unlock-alt"></i> 查看控价内容
            </button>
        </div>
    </div>

    <?php }else{ ?>
    <!-- 已解锁内容 -->
    <?php if($notice){ ?>
    <div class="pc-notice"><i class="fa fa-warning"></i>&nbsp;<?php echo nl2br(htmlspecialchars($notice,ENT_QUOTES,'UTF-8'))?></div>
    <?php } ?>

    <div class="pc-toolbar">
        <select class="pc-cat-select" onchange="goCat(this.value)">
            <option value="0"<?php echo $cat_filter==0?' selected':''?>>全部分类</option>
            <?php foreach($filter_cats as $fc){ ?>
            <option value="<?php echo $fc['cid']?>"<?php echo $cat_filter==$fc['cid']?' selected':''?>>
                <?php echo $fc['level']==2?'└ ':''; echo htmlspecialchars($fc['name'],ENT_QUOTES,'UTF-8')?>
            </option>
            <?php }?>
        </select>
        <form class="pc-search" method="get">
            <?php if($cat_filter){ ?><input type="hidden" name="cat" value="<?php echo $cat_filter?>"><?php }?>
            <input type="text" name="q" value="<?php echo htmlspecialchars($search,ENT_QUOTES,'UTF-8')?>" placeholder="搜索商品名称/ID">
            <button type="submit"><i class="fa fa-search"></i></button>
        </form>
    </div>

    <div class="pc-count">控价公示（共 <b><?php echo $total?></b> 条）</div>

    <?php if(empty($items)){ ?>
    <div class="pc-empty"><i class="fa fa-inbox fa-3x" style="display:block;margin-bottom:10px;"></i>暂无控价数据</div>
    <?php }else{ ?>

    <!-- 桌面表格 -->
    <div class="pc-table-wrap">
    <table class="pc-table">
        <thead><tr>
            <th>商品ID</th>
            <th>商品名称</th>
            <th>商品控价</th>
            <th>代理价格</th>
            <th>备注</th>
        </tr></thead>
        <tbody>
        <?php foreach($items as $item){ ?>
        <tr>
            <td><?php if($item['product_id']){ ?><span class="pc-id-badge"><?php echo htmlspecialchars($item['product_id'],ENT_QUOTES,'UTF-8')?></span><?php }else{ ?>-<?php }?></td>
            <td><?php echo htmlspecialchars($item['product_name'],ENT_QUOTES,'UTF-8')?></td>
            <td class="pc-control-price">¥<?php echo number_format($item['control_price'],2)?></td>
            <td><?php echo $item['agent_price']!==null?'<span class="pc-agent-price">¥'.number_format($item['agent_price'],2).'</span>':'-'?></td>
            <td class="pc-note"><?php echo htmlspecialchars($item['note']??'',ENT_QUOTES,'UTF-8')?></td>
        </tr>
        <?php }?>
        </tbody>
    </table>
    </div>

    <!-- 手机卡片 -->
    <div class="pc-cards">
    <?php foreach($items as $item){ ?>
    <div class="pc-card">
        <div class="pc-card-top">
            <?php if($item['product_id']){ ?><span class="pc-id-badge"><?php echo htmlspecialchars($item['product_id'],ENT_QUOTES,'UTF-8')?></span><?php }?>
            <span class="pc-card-name"><?php echo htmlspecialchars($item['product_name'],ENT_QUOTES,'UTF-8')?></span>
        </div>
        <div class="pc-card-row">
            <span class="pc-card-label">商品控价</span>
            <span class="pc-control-price">¥<?php echo number_format($item['control_price'],2)?></span>
        </div>
        <div class="pc-card-row">
            <span class="pc-card-label">代理价格</span>
            <span class="pc-agent-price"><?php echo $item['agent_price']!==null?'¥'.number_format($item['agent_price'],2):'-'?></span>
        </div>
        <?php if($item['note']){ ?>
        <div style="font-size:12px;color:#bbb;margin-top:4px;"><?php echo htmlspecialchars($item['note'],ENT_QUOTES,'UTF-8')?></div>
        <?php }?>
    </div>
    <?php }?>
    </div>

    <?php }?>
    <?php }?>

    </div>
</div>
</div>
</div>

<script>
function goCat(cid){
    var q = '<?php echo addslashes($search)?>';
    var url = 'pricecontrol.php';
    var params = [];
    if(cid > 0) params.push('cat=' + cid);
    if(q) params.push('q=' + encodeURIComponent(q));
    if(params.length) url += '?' + params.join('&');
    window.location.href = url;
}
function doUnlock(){
    var pwd = document.getElementById('pcPwd').value.trim();
    if(!pwd){ showErr('请输入密码'); return; }
    $.post('pricecontrol.php', {act:'unlock', pwd:pwd}, function(r){
        if(r.code==1){ location.reload(); }
        else { showErr(r.msg||'密码错误'); }
    }, 'json').fail(function(){ showErr('网络错误，请重试'); });
}
function showErr(msg){ var e=document.getElementById('pcErr'); e.textContent=msg; e.style.display='block'; }
document.addEventListener('DOMContentLoaded', function(){
    var inp = document.getElementById('pcPwd');
    if(inp) inp.focus();
});
</script>
<?php include './foot.php'; ?>

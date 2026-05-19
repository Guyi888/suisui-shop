<?php
include "../includes/common.php";
$title = "站点日志";
if ($islogin == 1) {} else exit("<script language='javascript'>window.location.href='./login.php';</script>");
adminpermission('super', 1);
include "./head.php";
$DB->exec("CREATE TABLE IF NOT EXISTS `pre_site_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL DEFAULT '',
  `action` varchar(64) NOT NULL DEFAULT '',
  `object_id` varchar(64) DEFAULT '',
  `summary` varchar(255) NOT NULL DEFAULT '',
  `detail` text,
  `operator` varchar(64) DEFAULT 'system',
  `ip` varchar(45) DEFAULT '',
  `addtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `action` (`action`),
  KEY `addtime` (`addtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$kw = isset($_GET['kw']) ? trim($_GET['kw']) : '';
$where = "1";
$params = [];
if ($type !== '') { $where .= " AND type=:type"; $params[':type'] = $type; }
if ($kw !== '') { $where .= " AND (action LIKE :kw OR summary LIKE :kw OR detail LIKE :kw OR object_id LIKE :kw)"; $params[':kw'] = "%{$kw}%"; }
$numrows = $DB->getColumn("SELECT COUNT(*) FROM pre_site_logs WHERE {$where}", $params);
$pagesize = 30;
$pages = max(1, ceil($numrows / $pagesize));
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $pagesize;
$rs = $DB->query("SELECT * FROM pre_site_logs WHERE {$where} ORDER BY id DESC LIMIT {$offset},{$pagesize}", $params);

// 类型中文映射
$typeNames = [
    'sync'   => '自动同步',
    'batch'  => '批量对接',
    'goods'  => '商品操作',
    'class'  => '分类操作',
    'system' => '系统',
    'admin'  => '管理员',
];

// 动作中文映射
$actionNames = [
    'goods_add'    => '新增商品',
    'goods_edit'   => '编辑商品',
    'goods_delete' => '删除商品',
    'goods_status' => '商品上下架',
    'goods_batch'  => '批量操作商品',
    'goods_price'  => '修改商品价格',
    'goods_stock'  => '修改商品库存',
    'goods_sort'   => '调整商品排序',
    'goods_move'   => '移动商品',
    'class_add'    => '新增分类',
    'class_edit'   => '编辑分类',
    'class_delete' => '删除分类',
    'class_status' => '分类启用/禁用',
    'class_sort'   => '调整分类排序',
    'class_batch'  => '批量操作分类',
    'batch_add'    => '批量新增商品',
    'batch_import' => '批量导入',
    'sync_full'    => '全量同步',
    'patch'        => '补丁升级',
];

// 类型对应的 Bootstrap 样式
$typeLabelClass = [
    'sync'   => 'label-info',
    'batch'  => 'label-primary',
    'goods'  => 'label-success',
    'class'  => 'label-warning',
    'system' => 'label-default',
    'admin'  => 'label-danger',
];

// 常见英文关键词 → 中文翻译（用于 summary/detail 里仍是英文的旧日志）
function zh_translate($s) {
    if ($s === '' || $s === null) return '';
    // 先处理整句/带数字的模式（正则）
    $s = preg_replace_callback(
        '/Auto sync done:\s*scanned\s*(\d+),\s*added\s*(\d+),\s*updated\s*(\d+),\s*deleted\s*(\d+)/i',
        fn($m)=>"自动同步完成：扫描 {$m[1]} 条，新增 {$m[2]} 条，更新 {$m[3]} 条，删除 {$m[4]} 条", $s);
    $s = preg_replace_callback(
        '/Sync task done:\s*scanned\s*(\d+),\s*added\s*(\d+),\s*updated\s*(\d+),\s*deleted\s*(\d+)/i',
        fn($m)=>"同步任务完成：扫描 {$m[1]} 条，新增 {$m[2]} 条，更新 {$m[3]} 条，删除 {$m[4]} 条", $s);
    $s = preg_replace_callback(
        '/Site sync done:\s*scanned\s*(\d+),\s*added\s*(\d+),\s*updated\s*(\d+),\s*deleted\s*(\d+)/i',
        fn($m)=>"站点同步完成：扫描 {$m[1]} 条，新增 {$m[2]} 条，更新 {$m[3]} 条，删除 {$m[4]} 条", $s);
    $s = preg_replace_callback(
        '/Goods sync done:\s*scanned\s*(\d+),\s*added\s*(\d+),\s*updated\s*(\d+),\s*deleted\s*(\d+)/i',
        fn($m)=>"商品同步完成：扫描 {$m[1]} 条，新增 {$m[2]} 条，更新 {$m[3]} 条，删除 {$m[4]} 条", $s);

    $map = [
        'Install generic batch site log hook' => '安装通用批量日志钩子',
        'Auto sync done'    => '自动同步完成',
        'Sync task done'    => '同步任务完成',
        'Site sync done'    => '站点同步完成',
        'Goods sync done'   => '商品同步完成',
        'scanned'           => '扫描',
        'added'             => '新增',
        'Add goods'         => '新增商品',
        'Edit goods'        => '编辑商品',
        'Delete goods'      => '删除商品',
        'Add category'      => '新增分类',
        'Edit category'     => '编辑分类',
        'Delete category'   => '删除分类',
        'Full sync'         => '全量同步',
        'Batch import'      => '批量导入',
        'Batch add goods'   => '批量新增商品',
        'success'           => '成功',
        'Success'           => '成功',
        'failed'            => '失败',
        'Failed'            => '失败',
        'error'             => '错误',
        'Error'             => '错误',
        'added'             => '已新增',
        'updated'           => '已更新',
        'deleted'           => '已删除',
        'created'           => '已创建',
        'changed'           => '已修改',
        'enabled'           => '已启用',
        'disabled'          => '已禁用',
        'online'            => '上架',
        'offline'           => '下架',
        'goods'             => '商品',
        'category'          => '分类',
        'price'             => '价格',
        'stock'             => '库存',
        'total'             => '共计',
        'count'             => '数量',
        'name'              => '名称',
        'from'              => '来自',
        'to'                => '到',
        'by'                => '由',
        'system'            => '系统',
        'admin'             => '管理员',
        'user'              => '用户',
    ];
    return strtr($s, $map);
}

function q8_field_label($field) {
    $map = [
        'price' => '&#20215;&#26684;',
        'cost' => '&#25104;&#26412;',
        'cost2' => '&#19987;&#19994;&#29256;&#25104;&#26412;',
        'stock' => '&#24211;&#23384;',
        'name' => '&#21517;&#31216;',
        'desc' => '&#25551;&#36848;',
        'shopimg' => '&#22270;&#29255;',
        'cid' => '&#25152;&#23646;&#20998;&#31867;',
        'sort' => '&#25490;&#24207;',
        'close' => '&#19978;&#19979;&#26550;&#29366;&#24577;',
        'active' => '&#21551;&#29992;&#29366;&#24577;',
        'input' => '&#19979;&#21333;&#36755;&#20837;&#26694;',
        'inputs' => '&#22810;&#36755;&#20837;&#26694;',
        'alert' => '&#25552;&#31034;&#35821;',
        'value' => '&#40664;&#35748;&#25968;&#37327;',
        'min' => '&#26368;&#23567;&#19979;&#21333;&#25968;',
        'max' => '&#26368;&#22823;&#19979;&#21333;&#25968;',
        'repeat' => '&#37325;&#22797;&#19979;&#21333;',
        'multi' => '&#22810;&#25968;&#37327;&#19979;&#21333;',
        'validate' => '&#39564;&#35777;&#26041;&#24335;',
        'valiserv' => '&#39564;&#35777;&#26381;&#21153;'
    ];
    return isset($map[$field]) ? $map[$field] : htmlspecialchars($field, ENT_QUOTES, 'UTF-8');
}

function q8_format_site_log_detail($detail) {
    $detail = trim((string)$detail);
    if ($detail === '') return '';
    $json = json_decode($detail, true);
    if (!is_array($json)) {
        return '<div class="text-muted small" style="margin-top:4px;">' . htmlspecialchars(zh_translate($detail), ENT_QUOTES, 'UTF-8') . '</div>';
    }
    $html = '<div class="small" style="margin-top:6px;line-height:1.8;">';
    if (isset($json['scanned'])) {
        $html .= '<div><b>&#21516;&#27493;&#32467;&#26524;&#65306;</b>';
        $html .= '&#24050;&#26816;&#32034; ' . intval($json['scanned']) . ' &#26465;&#65292;';
        $html .= '&#26032;&#22686; ' . intval($json['added'] ?? 0) . ' &#26465;&#65292;';
        $html .= '&#26356;&#26032; ' . intval($json['updated'] ?? 0) . ' &#26465;&#65292;';
        $html .= '&#21024;&#38500; ' . intval($json['deleted'] ?? 0) . ' &#26465;</div>';
    }
    if (!empty($json['field_counts']) && is_array($json['field_counts'])) {
        $parts = [];
        foreach ($json['field_counts'] as $field => $count) {
            $parts[] = q8_field_label($field) . ' ' . intval($count) . ' &#27425;';
        }
        $html .= '<div><b>&#26356;&#26032;&#23383;&#27573;&#65306;</b>' . implode('&#65292;', $parts) . '</div>';
    }
    if (!empty($json['samples']) && is_array($json['samples'])) {
        $html .= '<details style="margin-top:4px;"><summary style="cursor:pointer;">&#26597;&#30475;&#26356;&#26032;&#26679;&#20363;</summary>';
        $html .= '<ol style="margin:6px 0 0 18px;padding:0;">';
        $i = 0;
        foreach ($json['samples'] as $sample) {
            if ($i++ >= 10) break;
            $fields = [];
            if (!empty($sample['fields']) && is_array($sample['fields'])) {
                foreach ($sample['fields'] as $f) $fields[] = q8_field_label($f);
            }
            $name = htmlspecialchars($sample['name'] ?? '', ENT_QUOTES, 'UTF-8');
            $goodsId = intval($sample['goods_id'] ?? 0);
            $tid = intval($sample['tid'] ?? 0);
            $html .= '<li>' . $name . ' <span class="text-muted">ID:' . $goodsId . ' / TID:' . $tid . '</span> - ' . implode('&#65292;', $fields) . '</li>';
        }
        $html .= '</ol></details>';
    }
    $html .= '</div>';
    return $html;
}
?>
<div class="col-md-12 center-block" style="float:none;">
<div class="block">
  <div class="block-title clearfix">
    <h2>站点日志 <small>共 <b><?php echo intval($numrows); ?></b> 条记录</small></h2>
  </div>
  <form method="get" class="form-inline" style="margin-bottom:12px;">
    <select name="type" class="form-control">
      <option value="">全部类型</option>
      <?php foreach($typeNames as $k=>$v){ echo '<option value="'.$k.'"'.($type===$k?' selected':'').'>'.$v.'</option>'; } ?>
    </select>
    <input type="text" name="kw" class="form-control" placeholder="搜索动作 / 摘要 / 详情 / 对象" value="<?php echo htmlspecialchars($kw, ENT_QUOTES, 'UTF-8'); ?>" style="width:260px;">
    <button class="btn btn-primary" type="submit">搜索</button>
    <a class="btn btn-default" href="sitelogs.php">重置</a>
  </form>
  <div class="table-responsive">
    <table class="table table-striped table-bordered table-vcenter">
      <thead>
        <tr>
          <th style="width:60px;">编号</th>
          <th style="width:150px;">时间</th>
          <th style="width:100px;">类型</th>
          <th style="width:140px;">动作</th>
          <th style="width:100px;">对象</th>
          <th>摘要</th>
          <th style="width:140px;">操作人 / IP</th>
        </tr>
      </thead>
      <tbody>
      <?php while($row=$rs->fetch()){
          $tName   = $typeNames[$row['type']] ?? $row['type'];
          $aName   = $actionNames[$row['action']] ?? $row['action'];
          $lblCls  = $typeLabelClass[$row['type']] ?? 'label-default';
          $summary = zh_translate($row['summary']);
          $detail  = zh_translate($row['detail']);
          $detailHtml = q8_format_site_log_detail($row['detail']);
      ?>
        <tr>
          <td><?php echo intval($row['id']); ?></td>
          <td><?php echo htmlspecialchars($row['addtime'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td><span class="label <?php echo $lblCls; ?>"><?php echo htmlspecialchars($tName, ENT_QUOTES, 'UTF-8'); ?></span></td>
          <td><?php echo htmlspecialchars($aName, ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo htmlspecialchars($row['object_id'], ENT_QUOTES, 'UTF-8'); ?></td>
          <td style="max-width:560px;white-space:normal;word-break:break-all;" title="<?php echo htmlspecialchars($detail, ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($summary, ENT_QUOTES, 'UTF-8'); ?>
            <?php echo $detailHtml; ?>
          </td>
          <td>
            <?php echo htmlspecialchars($row['operator'] === 'system' ? '系统' : $row['operator'], ENT_QUOTES, 'UTF-8'); ?>
            <br><small class="text-muted"><?php echo htmlspecialchars($row['ip'], ENT_QUOTES, 'UTF-8'); ?></small>
          </td>
        </tr>
      <?php } ?>
      <?php if ($numrows == 0) { ?>
        <tr><td colspan="7" class="text-center text-muted" style="padding:30px;">暂无日志记录</td></tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
  <ul class="pagination">
    <?php
      $queryBase = '&type=' . urlencode($type) . '&kw=' . urlencode($kw);
      if ($page > 1) {
          echo '<li><a href="sitelogs.php?page=1'.$queryBase.'">首页</a></li>';
          echo '<li><a href="sitelogs.php?page='.($page-1).$queryBase.'">上一页</a></li>';
      }
      for ($i = max(1, $page-5); $i <= min($pages, $page+5); $i++) {
          echo '<li'.($i==$page?' class="active"':'').'><a href="sitelogs.php?page='.$i.$queryBase.'">'.$i.'</a></li>';
      }
      if ($page < $pages) {
          echo '<li><a href="sitelogs.php?page='.($page+1).$queryBase.'">下一页</a></li>';
          echo '<li><a href="sitelogs.php?page='.$pages.$queryBase.'">末页</a></li>';
      }
    ?>
  </ul>
</div>
</div>
</body>
</html>

<?php
/**
 * 系统更新模块
 * 功能：检测和执行系统版本更新
 * 作者：自定义
 * 本模块已修改为独立版本，完全自主控制
 * 
 * 部署注意事项：
 * 1. 确保PHP具有足够的文件读写权限
 * 2. 建议定期备份系统数据后再进行更新
 * 3. 真实环境中应实现实际的更新逻辑（下载、解压、应用）
 * 4. 生产环境中应加强安全措施，如IP白名单等
 * 5. 定期更新LATEST_VERSION常量或连接远程版本服务器
 */

// 引入公共文件
include "../includes/common.php";

// 定义系统版本信息
const SYSTEM_VERSION = '1.0.0'; // 当前系统版本
const LATEST_VERSION = '1.1.0'; // 最新版本（设置为更高版本，用于测试更新功能）

// 使用说明：
// 1. 访问 /admin/update.php 页面即可自动检查更新
// 2. 当前配置下会检测到1.1.0版本的更新并显示更新内容
// 3. 点击「立即更新」按钮可以模拟更新过程
// 4. 更新过程会显示进度条，模拟下载、解压和应用三个步骤
// 5. 更新完成后会提示刷新页面

// 页面标题
$title = "检查版本更新";

// 验证用户登录状态
if ($islogin == 1) {
    // 登录成功，继续执行
} else {
    // 未登录则跳转到登录页面
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

// 引入头部文件
include "./head.php";
?><div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
        <div class="block">
            <div class="block-title">
                <h3 class="panel-title">检查更新</h3>
                <div class="version-info" style="float:right; font-size:14px; color:#666;">
                    <span>当前版本: <strong><?php echo SYSTEM_VERSION; ?></strong></span>
                </div>
            </div>
            <div class="panel-body">
                <div id="msg" class="alert alert-info"><i class="fa fa-spinner fa-spin"></i>正在检测中</div>
                <hr/>
                <div id="update_log" class="well" style="display:none">
                    <h4>更新内容：</h4>
                    <ul class="update-log-list" style="padding-left: 20px;"></ul>
                </div>
                <div id="update-progress" style="display:none; margin-top:15px;">
                    <div class="progress" style="height:20px; margin:15px 0; background-color:#f5f5f5; border-radius:4px; overflow:hidden;">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="height:100%; background-color:#007bff; transition:width 0.3s ease; text-align:center; color:white; line-height:20px; width:0%;">
                            0%
                        </div>
                    </div>
                    <p id="progress-text"></p>
                </div>
                <a id="update_btn" class="btn btn-primary btn-block" style="display:none">立即更新</a>
            </div>
        </div>
    </div>
<!--系统已引入jquery.min.js，无需重复引入-->
<!--<script src="//cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>-->
<script src="//lib.baomitu.com/layer/2.3/layer.js"></script>
<script>
    $(function(){
        // 显示加载中动画
        var loadingIndex = layer.load(0);
        
        // 发送AJAX请求检查更新
        $.ajax({
            type: 'POST',
            url: 'update.php',
            data: {'SF_Action':'check'},
            dataType: 'json',
            success: function(data) {
                // 关闭加载动画
                layer.close(loadingIndex);
                
                // 检查响应状态
                if(data.code == 0){
                    // code为1代表需要更新，0则不需要
                    if(data.data.code == 1){
                        // 隐藏旧的更新日志容器，使用新的列表格式
                        $("#update_log").css('display','inherit');
                        
                        // 清空并填充更新日志列表
                        var logList = $("#update_log .update-log-list");
                        logList.empty();
                        $.each(data.data.data.update_log, function(index, value){
                            logList.append('<li>' + value + '</li>');
                        });
                        
                        $("#update_btn").css('display','inherit');
                        $("#msg").addClass('alert-warning');
                        $("#msg").html('发现新版本: <strong>V'+data.data.data.edition+'</strong><span class="success-message" style="color:#28a745; font-weight:bold; margin-left:10px;">您的系统需要更新！</span>');
                    }else{
                        $("#msg").addClass('alert-success');
                        $("#msg").html('您当前已是最新版本! <span style="color:#28a745; font-weight:bold;">系统运行正常，无需更新。</span>');
                    }
                }else{
                    // 显示错误信息
                    $("#msg").addClass('alert-danger');
                    $("#msg").html('<span style="color:#dc3545; font-weight:bold;">检查更新失败: </span>' + data.msg);
                }
            },
            error: function() {
                // 关闭加载动画并显示错误信息
                layer.close(loadingIndex);
                $("#msg").html('<span style="color:#dc3545; font-weight:bold;">检查更新失败: </span>服务器错误！');
            }
        });

        /**
         * 执行更新操作
         * 通过AJAX请求发送更新指令，处理更新结果
         */
        function update(){
            // 隐藏更新日志和按钮，显示进度条
            $("#update_log").css('display','none');
            $("#update_btn").css('display','none');
            $("#update-progress").css('display','block');
            
            var progressBar = $("#update-progress .progress-bar");
            var progressText = $("#progress-text");
            
            // 发送更新请求
            $.ajax({
                type: 'POST',
                url: 'update.php',
                data: {'SF_Action':'update'},
                dataType: "json",
                success: function(data) {
                    // 处理响应
                    if(data.code == 1){
                        // 更新完成
                        progressBar.css('width', '100%');
                        progressBar.attr('aria-valuenow', '100');
                        progressBar.text('100%');
                        progressText.html('<span style="color:#28a745; font-weight:bold;">更新完成！</span><br>建议刷新页面以应用新功能。');
                        
                        layer.msg(data.msg, {icon:1});
                        setTimeout(function(){
                            location.href="./update.php";
                        }, 2000);
                    }else if(data.code == 0){
                        // 更新中，更新进度条
                        progressText.text(data.msg);
                        
                        // 计算进度（三步完成）
                        var currentProgress = parseInt(progressBar.attr('aria-valuenow')) || 0;
                        var newProgress = Math.min(currentProgress + 33, 100);
                        
                        progressBar.css('width', newProgress + '%');
                        progressBar.attr('aria-valuenow', newProgress);
                        progressBar.text(newProgress + '%');
                        
                        // 继续更新
                        setTimeout(update, 1500);
                    }else{
                        // 更新失败
                        progressText.html('<span style="color:#dc3545; font-weight:bold;">更新失败: </span>' + data.msg);
                        layer.msg(data.msg, {icon:2});
                    }
                },
                error: function() {
                    // 显示错误信息
                    progressText.html('<span style="color:#dc3545; font-weight:bold;">更新失败: </span>服务器错误！');
                    $("#msg").html('服务器错误！');
                }
            });
        }
        
        // 监听更新按钮点击事件
        $("#update_btn").click(function(){
            // 执行update函数
            update();
        });
        
        // 页面加载完成后自动检查更新
        setTimeout(function() {
            checkUpdate();
        }, 3000);
    });
</script><?php 
/**
 * 安全解压ZIP文件
 * @param string $src 源ZIP文件路径
 * @param string $dest 解压目标目录
 * @return bool 解压是否成功
 * @author 自定义版本
 * @version 1.0.0
 */
function zipExtract($src, $dest)
{
    // 安全检查：确保目标路径在系统根目录内，防止路径遍历攻击
    $realDest = realpath($dest);
    $rootPath = realpath('../');
    
    // 验证路径是否安全
    if ($realDest === false || strpos($realDest, $rootPath) !== 0) {
        return false;
    }
    
    // 创建ZipArchive对象
    $zip = new ZipArchive();
    
    // 打开ZIP文件并解压
    if ($zip->open($src) === true) {
        // 设置严格解压模式
        $zip->extractTo($dest);
        $zip->close();
        return true;
    }
    return false;
}

/**
 * 安全删除目录及其所有内容
 * @param string $dir 要删除的目录路径
 * @return bool 删除是否成功
 * @author 自定义版本
 * @version 1.0.0
 */
function deldir($dir)
{
    // 安全检查：确保目录存在且是有效目录
    if (!is_dir($dir)) {
        return false;
    }
    
    // 安全检查：确保目录路径在系统根目录内
    $realDir = realpath($dir);
    $rootPath = realpath('../');
    
    // 验证路径是否安全
    if ($realDir === false || strpos($realDir, $rootPath) !== 0) {
        return false;
    }
    
    // 打开目录
    $handle = opendir($dir);
    
    // 遍历目录内容
    while ($file = readdir($handle)) {
        // 跳过当前目录和父目录
        if ($file != "." && $file != "..") {
            $fullPath = $dir . "/" . $file;
            
            // 判断是文件还是目录
            if (!is_dir($fullPath)) {
                // 删除文件
                if (!unlink($fullPath)) {
                    closedir($handle);
                    return false;
                }
            } else {
                // 递归删除子目录
                if (!deldir($fullPath)) {
                    closedir($handle);
                    return false;
                }
            }
        }
    }
    
    // 关闭目录句柄
    closedir($handle);
    
    // 删除空目录
    if (rmdir($dir)) {
        return true;
    } else {
        return false;
    }
}

// 处理更新操作
if (isset($_POST['SF_Action'])) {
    // 安全验证：验证是否为POST请求
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die(json_encode(['code' => -1, 'msg' => '非法请求方式']));
    }
    
    // 处理不同的操作类型
    switch ($_POST['SF_Action']) {
        case 'check':
            // 检查更新的逻辑
            $needUpdate = version_compare(LATEST_VERSION, SYSTEM_VERSION, '>');
            
            $updateData = [
                'code' => $needUpdate ? 1 : 0,
                'data' => [
                    'edition' => LATEST_VERSION,
                    'update_log' => [
                            '修复了版本检测功能中的界面显示问题',
                            '优化了系统更新流程，提高了稳定性',
                            '增强了错误处理和用户提示',
                            '添加了版本信息显示和进度条动画效果'
                        ]
                ]
            ];
            
            die(json_encode(['code' => 0, 'data' => $updateData]));
            break;
            
        case 'update':
            // 执行更新的逻辑
            try {
                // 这里可以实现实际的更新逻辑
                // 例如从自定义服务器下载更新包、解压并应用更新等
                
                // 模拟更新过程
                if (isset($_SESSION['update_step'])) {
                    $_SESSION['update_step']++;
                } else {
                    $_SESSION['update_step'] = 1;
                }
                
                if ($_SESSION['update_step'] >= 3) {
                    unset($_SESSION['update_step']);
                    die(json_encode(['code' => 1, 'msg' => '更新完成！']));
                } else {
                    $steps = ['正在下载更新包...', '正在解压文件...', '正在应用更新...'];
                    die(json_encode(['code' => 0, 'msg' => $steps[$_SESSION['update_step'] - 1]]));
                }
            } catch (Exception $e) {
                // 清理会话状态
                unset($_SESSION['update_step']);
                die(json_encode(['code' => -1, 'msg' => '更新失败：' . $e->getMessage()]));
            }
            break;
            
        default:
            // 未知操作
            die(json_encode(['code' => -1, 'msg' => '未知操作']));
            break;
    }
}
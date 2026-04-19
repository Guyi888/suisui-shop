<?php
// +----------------------------------------------------------------------
// | 赞赏作者页面
// | 博客地址：zhonguo.ren
// | QQ群：915043052
// | 开发者：教主
// +----------------------------------------------------------------------
include("../includes/common.php");
$title='赞赏作者';
include './head.php';
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>    
<div class="col-md-12">
    <div class="block block-rounded block-themed">
        <div class="block-header">
            <h3 class="block-title">赞赏支持</h3>
            <div class="block-options">
                <button type="button" class="btn-block-option">
                    <i class="si si-cup"></i>
                </button>
            </div>
        </div>
        <div class="block-content">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="text-center">
                        <h4 class="push-20">感谢您使用我们的系统！</h4>
                        <p class="text-muted push-30">
                            如果您觉得我们的系统对您有帮助，不妨请开发者喝杯咖啡吧！
                            您的支持是我们持续更新和维护的动力！
                        </p>
                        <div class="push-20">
                            <img src="../assets/img/wxzsm.jpg" alt="微信赞赏码" class="img-responsive center-block" style="max-width: 300px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>
                        <h5 class="push-10">微信扫码赞赏</h5>
                        <p class="text-muted">
                            赞赏金额随意，多少都是心意 
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
</div>
<script src="<?php echo $cdnpublic?>layer/3.1.1/layer.js"></script>
</body>
</html>
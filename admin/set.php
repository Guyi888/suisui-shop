<?php


//请勿删除版权信息，否则出现问题将不再保持售后修复！
include "../includes/common.php";
$title = "后台管理";
include "./head.php";
if ($islogin == 1) {
} else {
	exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
?><div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">

	<?php
$mod = isset($_GET["mod"]) ? $_GET["mod"] : null;
if ($mod == "cleanbom") {
	adminpermission("set", 1);
	$filename = ROOT . "config.php";
	$contents = file_get_contents($filename);
	$charset[1] = substr($contents, 0, 1);
	$charset[2] = substr($contents, 1, 1);
	$charset[3] = substr($contents, 2, 1);
	if (ord($charset[1]) == 239 && ord($charset[2]) == 187) {
		$rest = substr($contents, 3);
		file_put_contents($filename, $rest);
		showmsg("找到BOM并已自动去除", 1);
	} else {
		showmsg("没有找到BOM", 2);
	}
	echo "\t";
} elseif ($mod == "bind") {
	adminpermission("set", 1);
	?>		<div class="block">
			<div class="block-title">
				<h3 class="panel-title">管理员后台微信/QQ扫码登录</h3>
			</div>
			<div class="">
				<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
					<div class="form-group">
						<label class="col-sm-2 control-label">开启扫码登录</label>
						<div class="col-sm-10"><select class="form-control" name="thirdlogin_open" default="<?php echo $conf["thirdlogin_open"];?>">
								<option value="0">否</option>
								<option value="1">是</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">绑定微信</label>
						<div class="col-sm-10">
							<div class="input-group"><input type="text" name="thirdlogin_wx" value="<?php echo $conf["thirdlogin_wx"];?>" class="form-control" disabled /><span class="input-group-btn"><a href="javascript:thirdloginbind('wx')" class="btn btn-success">绑定</a><a href="javascript:thirdloginunbind('wx')" class="btn btn-danger">解绑</a></span></div>
						</div>
					</div><br />
					<div class="form-group">
						<label class="col-sm-2 control-label">绑定QQ</label>
						<div class="col-sm-10">
							<div class="input-group"><input type="text" name="thirdlogin_qq" value="<?php echo $conf["thirdlogin_qq"];?>" class="form-control" disabled /><span class="input-group-btn"><a href="javascript:thirdloginbind('qq')" class="btn btn-success">绑定</a><a href="javascript:thirdloginunbind('qq')" class="btn btn-danger">解绑</a></span></div>
						</div>
					</div><br />
					<div class="form-group">
						<label class="col-sm-2 control-label">关闭密码登录</label>
						<div class="col-sm-10"><select class="form-control" name="thirdlogin_closepwd" default="<?php echo $conf["thirdlogin_closepwd"];?>">
								<option value="0">否</option>
								<option value="1">是</option>
							</select></div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" /><br />
						</div>
					</div>
				</form>
			</div>
			<br/>
			<div class="block">
				<div class="block-title">
					<h3 class="panel-title">高级防CC功能设置（IP重定向）</h3>
				</div>
				<div class="">
					<form action="./set.php?mod=defend_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
						<div class="form-group">
							<label class="col-sm-2 control-label">启用高级防CC</label>
							<div class="col-sm-10"><select class="form-control" name="cc_protect_enabled" default="<?php echo isset($conf['cc_protect_enabled']) ? $conf['cc_protect_enabled'] : '0';?>">
									<option value="0">关闭</option>
									<option value="1">开启</option>
								</select>
								<font color="green">开启后会自动检测异常IP访问频率并执行重定向</font>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">最大请求数</label>
							<div class="col-sm-10"><input type="number" name="cc_max_requests" value="<?php echo isset($conf['cc_max_requests']) ? $conf['cc_max_requests'] : '30';?>" class="form-control" />
								<font color="green">检测时间窗口内允许的最大请求数</font>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">检测时间窗口</label>
							<div class="col-sm-10"><input type="number" name="cc_check_time" value="<?php echo isset($conf['cc_check_time']) ? $conf['cc_check_time'] : '30';?>" class="form-control" />
								<font color="green">单位：秒</font>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">封禁时长</label>
							<div class="col-sm-10"><input type="number" name="cc_block_time" value="<?php echo isset($conf['cc_block_time']) ? $conf['cc_block_time'] : '300';?>" class="form-control" />
								<font color="green">单位：秒</font>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">重定向网址</label>
							<div class="col-sm-10"><input type="text" name="cc_redirect_url" value="<?php echo isset($conf['cc_redirect_url']) ? $conf['cc_redirect_url'] : 'https://www.baidu.com';?>" class="form-control" />
								<font color="green">被攻击时将恶意IP重定向到的网址，请输入完整URL（包含http://或https://）</font>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="保存设置" class="btn btn-primary btn-block" /><br />
							</div>
						</div>
					</form>
				</div>
				<div class="panel-footer">
					<span class="fa fa-info-circle"></span>高级防CC功能说明<br />
					此功能可自动检测异常IP访问频率，当发现恶意访问时将其重定向到指定网站<br />
					建议设置合理的参数，避免误拦截正常用户<br />
					默认将恶意IP重定向到百度，您可以设置为自己的其他网站或警告页面
				</div>
			</div>
			<div class="panel-footer">
				<span class="fa fa-info-circle"></span>
				此功能可以使用微信/QQ扫码快捷登录到后台。微信、QQ可以只绑定其中一个。登录的时候二维码可以同时被微信和QQ扫描。
			</div>
		</div>
		<script>
			$("select[name='thirdlogin_closepwd']").change(function() {
				if ($(this).val() == 1) {
					if ($("select[name='thirdlogin_open']").val() == '0') {
						alert('需要先开启快捷登录，才能关闭账号密码登录');
						$(this).val('0');
						return false;
					}
					if ($("input[name='thirdlogin_wx']").val() == '' && $("input[name='thirdlogin_qq']").val() == '') {
						alert('需要先绑定微信或QQ，才能关闭账号密码登录');
						$(this).val('0');
						return false;
					}
				}
			});
		</script>
        <?php
} elseif ($mod == "sup_n" && $_POST["do"] == "submit") {
	$sup_bond = $_POST["sup_bond"];
	$sup_reg = $_POST["sup_reg"];
	$pass_sup_bond = $_POST["pass_sup_bond"];
	$sup_audit_free = $_POST["sup_audit_free"];
	$sup_tixian = $_POST["sup_tixian"];
	saveSetting("sup_bond", $sup_bond);
	saveSetting("sup_reg", $sup_reg);
	saveSetting("pass_sup_bond", $pass_sup_bond);
	saveSetting("sup_audit_free", $sup_audit_free);
	saveSetting("sup_tixian", $sup_tixian);
	$ad = $CACHE->clear();
	if ($ad) {
		showmsg("修改供货商配置成功！", 1);
	} else {
		showmsg("修改供货商配置失败！<br/>" . $DB->error(), 4);
	}
} elseif ($mod == "sup") {
	?>        <div class="block">
            <div class="block-title">
                <h3 class="panel-title">供货商相关配置</h3>
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#fenzhan_func" data-toggle="tab" aria-expanded="true">功能相关</a></li>
                    <li><a href="#fenzhan_tixian" data-toggle="tab" aria-expanded="true">提现相关</a></li>
<!--                    <li><a href="#fenzhan_recharge" data-toggle="tab" aria-expanded="true">充值相关</a></li>-->
<!--                    <li><a href="#fenzhan_workorder" data-toggle="tab" aria-expanded="true">工单相关</a></li>-->
                </ul>
            </div>
            <div class="">

                <div id="myTabContent" class="tab-content">
                    <div class="tab-pane fade active in" id="fenzhan_func">
                        <form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">上架商品保证金</label>
                                <div class="col-sm-10"><input type="number" name="sup_bond" value="<?php echo $conf["sup_bond"];?>" class="form-control" required placeholder="保证金缴纳达到该数额即可上架商品"/></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">免审上架保证金</label>
                                <div class="col-sm-10"><input type="number" name="pass_sup_bond" value="<?php echo $conf["pass_sup_bond"];?>" class="form-control" required placeholder="保证金缴纳达到该数额即可免审核上架商品"/></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">免审核上架</label>
                                <div class="col-sm-10"><select class="form-control" name="sup_audit_free" default="<?php echo $conf["sup_audit_free"];?>">
                                        <option value="0">关闭</option>
                                        <option value="1">开启</option>
                                    </select></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">自助注册</label>
                                <div class="col-sm-10"><select class="form-control" name="sup_reg" default="<?php echo $conf["sup_reg"];?>">
                                        <option value="0">关闭</option>
                                        <option value="1">开启</option>
                                    </select></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">提现开关</label>
                                <div class="col-sm-10"><select class="form-control" name="sup_tixian" default="<?php echo $conf["sup_tixian"];?>">
                                        <option value="0">关闭</option>
                                        <option value="1">开启</option>
                                    </select></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">联系方式</label>
                                <div class="col-sm-10"><textarea class="form-control" name="sup_report" rows="8" style="width:100%;" placeholder="格式："跳转链接|按钮标题"，如"http://xxx/|联系QQ"，多个联系方式请换行"><?php echo htmlspecialchars($conf["sup_report"]);?></textarea></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">后台公告</label>
                                <div class="col-sm-10"><textarea class="form-control" name="sup_notice" rows="8" style="width:100%;" placeholder=""><?php echo htmlspecialchars($conf["sup_notice"]);?></textarea></div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-9"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade in" id="fenzhan_tixian">
                        <form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">开启提现</label>
                                <div class="col-sm-9"><select class="form-control" name="sup_tixian" default="<?php echo $conf["sup_tixian"];?>">
                                        <option value="0">关闭</option>
                                        <option value="1">开启</option>
                                    </select></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">支付宝提现方式</label>
                                <div class="col-sm-9"><select class="form-control" name="sup_tixian_alipay" default="<?php echo $conf["sup_tixian_alipay"];?>">
                                        <option value="0">关闭</option>
                                        <option value="1">开启</option>
                                    </select></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">微信提现方式</label>
                                <div class="col-sm-9"><select class="form-control" name="sup_tixian_wx" default="<?php echo $conf["sup_tixian_wx"];?>">
                                        <option value="0">关闭</option>
                                        <option value="1">开启</option>
                                    </select></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">QQ提现方式</label>
                                <div class="col-sm-9"><select class="form-control" name="sup_tixian_qq" default="<?php echo $conf["sup_tixian_qq"];?>">
                                        <option value="0">关闭</option>
                                        <option value="1">开启</option>
                                    </select></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">是否启用收款图</label>
                                <div class="col-sm-9"><select class="form-control" name="sup_skimg" default="<?php echo $conf["sup_skimg"];?>">
                                        <option value="0">关闭</option>
                                        <option value="1">开启</option>
                                    </select></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">是否启用代付接口提现</label>
                                <div class="col-sm-9"><select class="form-control" name="sup_daifu" default="<?php echo $conf["sup_daifu"];?>">
                                        <option value="0">关闭</option>
                                        <option value="1">开启</option>
                                    </select></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">提现余额比例</label>
                                <div class="col-sm-9"><input type="text" name="sup_tixian_rate" value="<?php echo $conf["sup_tixian_rate"];?>" class="form-control" placeholder="填写百分数" /></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">提现最低余额</label>
                                <div class="col-sm-9"><input type="text" name="sup_tixian_min" value="<?php echo $conf["sup_tixian_min"];?>" class="form-control" /></div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-9"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
                                </div>
                            </div>
                        </form>
                    </div>
<!--                    <div class="tab-pane fade in" id="fenzhan_recharge">-->
<!--                        <form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">-->
<!--                            <div class="form-group">-->
<!--                                <label class="col-sm-3 control-label">开启加款卡充值</label>-->
<!--                                <div class="col-sm-9"><select class="form-control" name="sup_jiakuanka" default="--><!--">-->
<!--                                        <option value="0">关闭</option>-->
<!--                                        <option value="1">开启</option>-->
<!--                                    </select><a href="./kmlist.php">加款卡密列表</a></div>-->
<!--                            </div>-->
<!--                            <div class="form-group">-->
<!--                                <label class="col-sm-3 control-label">充值赠送规则</label>-->
<!--                                <div class="col-sm-9"><input type="text" name="sup_gift" value="--><!--" class="form-control" placeholder="" />-->
<!--                                    <pre>例如：100:3|200:5|500:10 就是一次性充值100元以上，赠送3%，200元以上赠送5%，500元以上赠送10%（所有符号都是英文状态输入的，不会的请勿乱填！）</pre>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="form-group">-->
<!--                                <label class="col-sm-3 control-label">最低充值金额</label>-->
<!--                                <div class="col-sm-9"><input type="text" name="recharge_min" value="--><!--" class="form-control" placeholder="不填写则不限制" /></div>-->
<!--                            </div>-->
<!--                            <div class="form-group">-->
<!--                                <div class="col-sm-offset-3 col-sm-9"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </form>-->
<!--                    </div>-->
<!---->
<!--                    <div class="tab-pane fade in" id="fenzhan_workorder">-->
<!--                        <form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">-->
<!--                            <div class="form-group">-->
<!--                                <label class="col-sm-3 control-label">开启工单功能</label>-->
<!--                                <div class="col-sm-9"><select class="form-control" name="workorder_open" default="--><!--">-->
<!--                                        <option value="1">开启</option>-->
<!--                                        <option value="0">关闭</option>-->
<!--                                    </select></div>-->
<!--                            </div>-->
<!--                            <div class="form-group">-->
<!--                                <label class="col-sm-3 control-label">提交工单可上传图片</label>-->
<!--                                <div class="col-sm-9"><select class="form-control" name="workorder_pic" default="--><!--">-->
<!--                                        <option value="0">关闭</option>-->
<!--                                        <option value="1">开启</option>-->
<!--                                    </select></div>-->
<!--                            </div>-->
<!--                            <div class="form-group">-->
<!--                                <label class="col-sm-3 control-label">工单可选问题类型</label>-->
<!--                                <div class="col-sm-9"><input type="text" name="workorder_type" value="--><!--" class="form-control" placeholder="多个类型用|隔开" /></div>-->
<!--                            </div>-->
<!--                            <div class="form-group">-->
<!--                                <div class="col-sm-offset-3 col-sm-9"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </form>-->
<!--                    </div>-->
                </div>

            </div>
        </div>

        <script>
            $("select[name='fenzhan_buy']").change(function() {
                if ($(this).val() == 1) {
                    $("#frame_set1").css("display", "inherit");
                } else {
                    $("#frame_set1").css("display", "none");
                }
            });
            $("select[name='tixian_limit']").change(function() {
                if ($(this).val() == 1) {
                    $("#frame_set2").css("display", "inherit");
                } else {
                    $("#frame_set2").css("display", "none");
                }
            });
            $("select[name='fenzhan_page']").change(function() {
                if ($(this).val() == 1) {
                    alert('开启后必须保证主站预留域名已正确填写，否则主站将出现404无法访问。注意！！www.qq.com与qq.com是两个域名！！');
                }
            });
        </script>
	<?php
} elseif ($mod == "account_n" && $_POST["do"] == "submit") {
	if ($admintypeid == 1) {
		$user = $_POST["user"];
		$oldpwd = $_POST["oldpwd"];
		$newpwd = $_POST["newpwd"];
		$newpwd2 = $_POST["newpwd2"];
		if ($user == NULL) {
			showmsg("用户名不能为空！", 3);
		}
		if (!empty($newpwd) && !empty($newpwd2)) {
			if ($oldpwd != $adminuserrow["password"]) {
				showmsg("旧密码不正确！", 3);
			}
			if ($newpwd != $newpwd2) {
				showmsg("两次输入的密码不一致！", 3);
			}
			$sql2 = ",`password`='" . $newpwd . "'";
		}
		$params = array(':username' => $user, ':id' => $adminuserrow["id"]);
		$sql = $DB->exec("UPDATE `pre_account` SET `username`=:username" . $sql2 . " WHERE `id`=:id", $params);
		if ($sql) {
			showmsg("修改成功！请重新登录", 1);
		} else {
			showmsg("修改失败！<br/>" . $DB->error(), 4);
		}
	} else {
		$user = $_POST["user"];
		$oldpwd = $_POST["oldpwd"];
		$newpwd = $_POST["newpwd"];
		$newpwd2 = $_POST["newpwd2"];
		if ($user == NULL) {
			showmsg("用户名不能为空！", 3);
		}
		saveSetting("admin_user", $user);
		if (!empty($newpwd) && !empty($newpwd2)) {
			if ($oldpwd != $conf["admin_pwd"]) {
				showmsg("旧密码不正确！", 3);
			}
			if ($newpwd != $newpwd2) {
				showmsg("两次输入的密码不一致！", 3);
			}
			saveSetting("admin_pwd", $newpwd);
		}
		$ad = $CACHE->clear();
		if ($ad) {
			showmsg("修改成功！请重新登录", 1);
		} else {
			showmsg("修改失败！<br/>" . $DB->error(), 4);
		}
	}
} elseif ($mod == "account") {
	if ($admintypeid == 1) {
		$user = $adminuserrow["username"];
	} else {
		$user = $conf["admin_user"];
	}
	?>		<div class="block">
			<div class="block-title">
				<h3 class="panel-title"><?php echo $admintypeid == 1 ? "员工" : "管理员";?>账号配置</h3>
			</div>
			<div class="">
				<form action="./set.php?mod=account_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
					<div class="form-group">
						<label class="col-sm-2 control-label">用户名</label>
						<div class="col-sm-10"><input type="text" name="user" value="<?php echo $user;?>" class="form-control" required /></div>
					</div><br />
					<div class="form-group">
						<label class="col-sm-2 control-label">旧密码</label>
						<div class="col-sm-10"><input type="password" name="oldpwd" value="" class="form-control" placeholder="请输入当前的管理员密码" /></div>
					</div><br />
					<div class="form-group">
						<label class="col-sm-2 control-label">新密码</label>
						<div class="col-sm-10"><input type="password" name="newpwd" value="" class="form-control" placeholder="不修改请留空" /></div>
					</div><br />
					<div class="form-group">
						<label class="col-sm-2 control-label">重输密码</label>
						<div class="col-sm-10"><input type="password" name="newpwd2" value="" class="form-control" placeholder="不修改请留空" /></div>
					</div><br />
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" /><br />
						</div>
					</div>
				</form>
			</div>
			</div>

			<!-- 高级防CC功能配置 -->
			<div class="block" style="margin-top: 20px;">
				<div class="block-title">
					<h3 class="panel-title">高级防CC功能设置（IP重定向）</h3>
				</div>
				<div class="">
					<form action="./set.php?mod=defend_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
						<input type="hidden" name="defendid" value="<?php echo CC_Defender;?>" />

						<div class="form-group">
							<label class="col-sm-3 control-label">启用高级防CC功能</label>
							<div class="col-sm-9">
								<select class="form-control" name="cc_protect_enabled" default="<?php echo $conf["cc_protect_enabled"];?>">
									<option value="0">关闭</option>
									<option value="1">开启</option>
								</select>
							</div>
						</div><br />

						<div class="form-group">
							<label class="col-sm-3 control-label">最大请求数</label>
							<div class="col-sm-9">
								<input type="text" name="cc_max_requests" value="<?php echo isset($conf["cc_max_requests"]) ? $conf["cc_max_requests"] : '30';?>" class="form-control" />
								<span class="help-block">检测时间窗口内允许的最大请求数，默认30</span>
							</div>
						</div><br />

						<div class="form-group">
							<label class="col-sm-3 control-label">检测时间窗口（秒）</label>
							<div class="col-sm-9">
								<input type="text" name="cc_check_time" value="<?php echo isset($conf["cc_check_time"]) ? $conf["cc_check_time"] : '30';?>" class="form-control" />
								<span class="help-block">单位：秒，默认30秒</span>
							</div>
						</div><br />

						<div class="form-group">
							<label class="col-sm-3 control-label">封禁时长（秒）</label>
							<div class="col-sm-9">
								<input type="text" name="cc_block_time" value="<?php echo isset($conf["cc_block_time"]) ? $conf["cc_block_time"] : '300';?>" class="form-control" />
								<span class="help-block">单位：秒，默认300秒（5分钟）</span>
							</div>
						</div><br />

						<div class="form-group">
							<label class="col-sm-3 control-label">重定向网址</label>
							<div class="col-sm-9">
								<input type="text" name="cc_redirect_url" value="<?php echo isset($conf["cc_redirect_url"]) ? $conf["cc_redirect_url"] : 'https://www.baidu.com';?>" class="form-control" />
								<span class="help-block">发现CC攻击时重定向到的网址，默认百度</span>
							</div>
						</div><br />

						<div class="form-group">
							<div class="col-sm-offset-3 col-sm-9">
								<input type="submit" name="submit" value="保存设置" class="btn btn-primary btn-block" />
							</div>
						</div>
					</form>
				</div>
				<div class="panel-footer">
					<span class="fa fa-info-circle"></span>高级防CC功能说明：<br />
					- 该功能通过检测IP访问频率识别CC攻击<br />
					- 当检测到异常访问时，自动将恶意IP重定向到指定网站<br />
					- 后台页面默认跳过检测，不影响管理员操作<br />
					- 支持搜索引擎爬虫自动识别并放行<br />
					- 采用文件缓存机制，不增加数据库负担<br />
				</div>
			</div>
	<?php
} elseif ($mod == "site") {
	adminpermission("set", 1);
	$q8DefaultFaviconValue = '/assets/img/favicon/favicon.ico';
	$q8FaviconPreview = function_exists('q8_brand_favicon_href') ? q8_brand_favicon_href() : $q8DefaultFaviconValue;
	?>		<link rel="stylesheet" href="<?php echo $cdnpublic;?>bootstrap-colorpicker/2.5.3/css/bootstrap-colorpicker.min.css">
		<script src="<?php echo $cdnpublic;?>bootstrap-colorpicker/2.5.3/js/bootstrap-colorpicker.min.js"></script>
		<style>
			.colorpicker.dropdown-menu {
				-webkit-transform: scale(1) !important;
				transform: scale(1) !important;
				-webkit-transition: none !important;
				transition: none !important;
				min-width: 0 !important
			}

			.lt-ie10 .colorpicker.dropdown-menu.colorpicker-visible {
				display: block !important
			}
		</style>
		<div class="block">
			<div class="block-title">
				<h3 class="panel-title">网站信息配置</h3>
			</div>
			<div class="">
				<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
					<div class="form-group">
						<label class="col-sm-2 control-label">网站名称</label>
						<div class="col-sm-10"><input type="text" name="sitename" value="<?php echo $conf["sitename"];?>" class="form-control" required /></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">网站图标</label>
						<div class="col-sm-10">
							<input type="text" id="favicon_url" name="favicon" value="<?php echo htmlspecialchars(!empty($conf["favicon"]) ? $conf["favicon"] : $q8DefaultFaviconValue, ENT_QUOTES, 'UTF-8');?>" class="form-control" placeholder="/assets/img/favicon/favicon.ico" />
							<span class="help-block">默认使用本地 Q8 图标：/assets/img/favicon/favicon.ico，前台和后台会自动追加缓存版本号。</span>
							<div id="favicon_preview" style="margin-top:10px;">
							<img src="<?php echo htmlspecialchars(!empty($conf['favicon']) ? $conf['favicon'] : $q8FaviconPreview, ENT_QUOTES, 'UTF-8');?>" style="max-width:64px;max-height:64px;" alt="favicon">
							</div>
							<script type="text/javascript">
							$(document).ready(function(){
								// 当输入框内容变化时，更新预览
								$('#favicon_url').on('input', function(){
									var url = $(this).val();
									if(url){
										$('#favicon_preview').html('<img src="'+url+'" style="max-width:64px;max-height:64px;">');
									}else{
										$('#favicon_preview').html('');
									}
								});
							});
							</script>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">标题栏后缀</label>
						<div class="col-sm-10"><input type="text" name="title" value="<?php echo $conf["title"];?>" class="form-control" /></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">关键字</label>
						<div class="col-sm-10"><input type="text" name="keywords" value="<?php echo $conf["keywords"];?>" class="form-control" /></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">网站描述</label>
						<div class="col-sm-10"><input type="text" name="description" value="<?php echo $conf["description"];?>" class="form-control" /></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">首页标题栏只显示后缀</label>
						<div class="col-sm-10"><select class="form-control" name="sitename_hide" default="<?php echo $conf["sitename_hide"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">客服联系方式</label>
						<div class="col-sm-10"><input type="text" name="kfqq" value="<?php echo $conf["kfqq"];?>" class="form-control" /></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">客服微信</label>
						<div class="col-sm-10">
							<div class="admin-site-qrcode-field">
								<input type="text" name="kfwx" value="<?php echo $conf["kfwx"];?>" class="form-control" placeholder="部分模板才显示" />
								<a href="./set.php?mod=upwxqrcode" class="btn btn-default admin-site-qrcode-btn"><i class="fa fa-qrcode"></i> 上传二维码</a>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">下单验证模块</label>
						<div class="col-sm-10"><select class="form-control" name="verify_open" default="<?php echo $conf["verify_open"];?>">
								<option value="1">开启</option>
								<option value="0">关闭</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">显示搜索商品</label>
						<div class="col-sm-10"><select class="form-control" name="search_open" default="<?php echo $conf["search_open"];?>">
								<option value="1">开启</option>
								<option value="0">关闭</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">发卡商品下单标题</label>
						<div class="col-sm-10"><select class="form-control" name="faka_input" default="<?php echo $conf["faka_input"];?>">
								<option value="0">你的邮箱</option>
								<option value="1">手机号码</option>
								<option value="2">你的ＱＱ</option>
								<option value="4">取卡密码</option>
								<option value="5">自定义</option>
								<option value="3">(不填写内容)</option>
							</select></div>
					</div>
					<div class="form-group" id="frame_set3" style="<?php echo $conf["faka_input"] == 5 ? "display:none;" : NULL;?>">
						<label class="col-sm-2 control-label">发卡商品下单标题</label>
						<div class="col-sm-10"><input type="text" name="faka_inputname" value="<?php echo $conf["faka_inputname"];?>" class="form-control" /></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">发卡库存显示</label>
						<div class="col-sm-10"><select class="form-control" name="faka_showleft" default="<?php echo $conf["faka_showleft"];?>">
								<option value="0">精确数量</option>
								<option value="1">大概情况</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">订单详情显示投诉订单按钮</label>
						<div class="col-sm-10"><select class="form-control" name="show_complain" default="<?php echo $conf["show_complain"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">订单详情显示修改密码按钮</label>
						<div class="col-sm-10"><select class="form-control" name="show_changepwd" default="<?php echo $conf["show_changepwd"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">首页显示订单金额统计信息</label>
						<div class="col-sm-10"><select class="form-control" name="hide_tongji" default="<?php echo $conf["hide_tongji"];?>">
								<option value="0">开启</option>
								<option value="1">关闭</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">首页统计数据缓存时间(秒)</label>
						<div class="col-sm-10"><input type="text" name="tongji_time" value="<?php echo $conf["tongji_time"];?>" class="form-control" placeholder="留空或0则不缓存，设置缓存可提升网页打开速度" /></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">下单黑名单</label>
						<div class="col-sm-10"><input type="text" name="blacklist" value="<?php echo $conf["blacklist"];?>" class="form-control" placeholder="多个账号之间用|隔开" /></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">网站首页背景</label>
						<div class="col-sm-10"><select class="form-control" name="ui_bing" default="<?php echo $conf["ui_bing"];?>">
								<option value="0">自定义背景图片</option>
								<option value="1">随机美图</option>
								<option value="2">Bing每日壁纸</option>
								<option value="3">渐变背景色</option>
							</select></div>
					</div>
					<div id="frame_set1" style="<?php echo $conf["ui_bing"] == 0 ? "display:none;" : NULL;?>">
						<div class="form-group">
							<label class="col-sm-2 control-label">背景图显示效果</label>
							<div class="col-sm-10"><select class="form-control" name="ui_background" default="<?php echo $conf["ui_background"];?>">
									<option value="0">纵向和横向重复</option>
									<option value="1">横向重复,纵向拉伸</option>
									<option value="2">纵向重复,横向拉伸</option>
									<option value="3">不重复,全屏拉伸</option>
								</select><a href="./set.php?mod=upbgimg">点此上传背景图</a></div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-2 control-label">首页下单显示模式</label>
						<div class="col-sm-10"><select class="form-control" name="ui_shop" default="<?php echo $conf["ui_shop"];?>">
								<option value="0">经典模式</option>
								<option value="1">分类图片宫格</option>
								<option value="2">分类图片列表</option>
								<option value="3">分类图片宫格2</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">首页显示文章条数</label>
						<div class="col-sm-10"><input type="number" name="articlenum" value="<?php echo $conf["articlenum"];?>" class="form-control" placeholder="0或留空则不显示文章列表" /></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">分站后台界面风格</label>
						<div class="col-sm-10"><select class="form-control" name="ui_user" default="<?php echo $conf["ui_user"];?>">
								<option value="0">明亮风格（默认）</option>
								<option value="1">黑色风格</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">开启购物车功能</label>
						<div class="col-sm-10"><select class="form-control" name="shoppingcart" default="<?php echo $conf["shoppingcart"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">网站创建时间</label>
						<div class="col-sm-10"><input type="date" name="build" value="<?php echo $conf["build"];?>" class="form-control" /></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">QQ等级代挂开通网址</label>
						<div class="col-sm-10"><input type="text" name="daiguaurl" value="<?php echo $conf["daiguaurl"];?>" class="form-control" placeholder="填写后将在首页显示代挂功能，没有请留空" /></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">手机QQ微信打开网站跳转浏览器</label>
						<div class="col-sm-10"><select class="form-control" name="qqjump" default="<?php echo $conf["qqjump"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">API对接密钥</label>
						<div class="col-sm-10"><input type="text" name="apikey" value="<?php echo $conf["apikey"];?>" class="form-control" placeholder="用于下单软件，随便填写即可" /></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">商品分类不可售地区设置</label>
						<div class="col-sm-10"><select class="form-control" name="classblock" default="<?php echo $conf["classblock"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
								<option value="2">仅电脑访问开启</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">开启只能登录下单</label>
						<div class="col-sm-10"><select class="form-control" name="forcelogin" default="<?php echo $conf["forcelogin"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">开启只能使用余额下单</label>
						<div class="col-sm-10"><select class="form-control" name="forcermb" default="<?php echo $conf["forcermb"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">开启登录后才能访问首页</label>
						<div class="col-sm-10"><select class="form-control" name="forceloginhome" default="<?php echo $conf["forceloginhome"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">开启用户后台批量下单</label>
						<div class="col-sm-10"><select class="form-control" name="openbatchorder" default="<?php echo $conf["openbatchorder"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">只能查询本人订单</label>
						<div class="col-sm-10"><select class="form-control" name="queryorderlimit" default="<?php echo $conf["queryorderlimit"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select>
							<font color="green">开启后首页查询订单只能查到本人的，会根据浏览器缓存判断，会导致未注册用户1~7天前的订单无法查询</font>
						</div>
					</div>
					<div class="form-group">
					<label class="col-sm-2 control-label">商品简介编辑富文本输入框</label>
					<div class="col-sm-10"><select class="form-control" name="shopdesc_editor" default="<?php echo $conf["shopdesc_editor"];?>">
							<option value="0">关闭</option>
							<option value="1">开启</option>
						</select></div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">开启卡密兑换功能</label>
					<div class="col-sm-10"><select class="form-control" name="iskami" default="<?php echo isset($conf["iskami"]) ? $conf["iskami"] : 0;?>">
							<option value="0">关闭</option>
							<option value="1">开启</option>
						</select>
						<font color="green">开启后用户可以使用卡密兑换商品</font>
					</div>
				</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">待处理和异常订单允许自助退款</label>
						<div class="col-sm-10"><select class="form-control" name="selfrefund" default="<?php echo $conf["selfrefund"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select></div>
					</div>
					<div class="form-group">
					<label class="col-sm-2 control-label">硬防墙</label>
					<div class="col-sm-10"><select class="form-control" name="parapet" default="<?php echo $conf["parapet"];?>">
							<option value="0">关闭</option>
							<option value="1">开启</option>
						</select>
						<font color="green">不影响SEO</font>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">免费商品登录领取</label>
					<div class="col-sm-10"><select class="form-control" name="free_goods_login_receive" default="<?php echo isset($conf["free_goods_login_receive"]) ? $conf["free_goods_login_receive"] : 0;?>">
							<option value="0">关闭</option>
							<option value="1">开启</option>
						</select>
						<font color="green">开启后免费商品需要登录后才能领取</font>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">获取空间说说列表接口</label>
					<div class="col-sm-10"><input type="text" name="sslist_api" value="<?php echo $conf["sslist_api"];?>" class="form-control" placeholder="留空则不启用" /></div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">获取空间日志列表接口</label>
					<div class="col-sm-10"><input type="text" name="rzlist_api" value="<?php echo $conf["rzlist_api"];?>" class="form-control" placeholder="留空则不启用" /></div>
				</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
						</div>
					</div>
					<div class="form-group">
						高级功能：<a href="./set.php?mod=cleanbom">清理BOM头部</a>｜<a href="./set.php?mod=defend">防CC模块设置</a>｜<a href="./set.php?mod=proxy">代理服务器设置</a>
					</div>
				</form>
			</div>
		</div>
		<script>
			$("select[name='ui_bing']").change(function() {
				if ($(this).val() == 0) {
					$("#frame_set1").css("display", "inherit");
					$("#frame_set2").css("display", "none");
				} else if ($(this).val() == 3) {
					$("#frame_set1").css("display", "none");
					$("#frame_set2").css("display", "inherit");
				} else {
					$("#frame_set1").css("display", "none");
					$("#frame_set2").css("display", "none");
				}
			});
			$("select[name='faka_input']").change(function() {
				if ($(this).val() == 5) {
					$("#frame_set3").css("display", "inherit");
				} else {
					$("#frame_set3").css("display", "none");
				}
			});
			$('.input-colorpicker').colorpicker({
				format: 'hex'
			});
		</script>
	<?php
} elseif ($mod == "defend_n" && $_POST["do"] == "submit") {
	adminpermission("set", 1);
	$defendid = $_POST["defendid"];
	$file = "<?php\r\n//防CC模块设置\r\ndefine('CC_Defender', " . $defendid . ");\r\n?>";
	file_put_contents(SYSTEM_ROOT . "base.php", $file);

	// 保存高级防CC设置
	saveSetting("cc_protect_enabled", $_POST["cc_protect_enabled"]);
	saveSetting("cc_max_requests", $_POST["cc_max_requests"]);
	saveSetting("cc_check_time", $_POST["cc_check_time"]);
	saveSetting("cc_block_time", $_POST["cc_block_time"]);
	saveSetting("cc_redirect_url", $_POST["cc_redirect_url"]);

	$ad = $CACHE->clear();
	showmsg("修改成功！", 1);
} elseif ($mod == "defend") {
	adminpermission("set", 1);
	?>		<div class="block">
			<div class="block-title">
				<h3 class="panel-title">防CC模块设置</h3>
			</div>
			<div class="">
				<form action="./set.php?mod=defend_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
					<div class="form-group">
						<label class="col-sm-2 control-label">CC防护等级</label>
						<div class="col-sm-10"><select class="form-control" name="defendid" default="<?php echo CC_Defender;?>">
								<option value="0">关闭</option>
								<option value="1">低(推荐)</option>
								<option value="2">中</option>
								<option value="3">高</option>
								<option value="4">滑动验证码</option>
							</select></div>
					</div><br />
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" /><br />
						</div>
					</div>
				</form>
			</div>
			<div class="panel-footer">
				<span class="fa fa-info-circle"></span>CC防护说明<br />
				滑动验证码：全局开启滑动验证码，验证通过后才能访问<br />
				高：全局使用防CC，会影响网站APP和对接软件的正常使用<br />
				中：会影响搜索引擎的收录，建议仅在正在受到CC攻击且防御不佳时开启<br />
				低：用户首次访问进行验证（推荐）<br />
			</div>
		</div>
		<br/>
		<div class="block">
			<div class="block-title">
				<h3 class="panel-title">高级防CC功能设置（IP重定向）</h3>
			</div>
			<div class="">
				<form action="./set.php?mod=defend_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
					<div class="form-group">
						<label class="col-sm-2 control-label">启用高级防CC</label>
						<div class="col-sm-10"><select class="form-control" name="cc_protect_enabled" default="<?php echo isset($conf['cc_protect_enabled']) ? $conf['cc_protect_enabled'] : '0';?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select>
							<font color="green">开启后会自动检测异常IP访问频率并执行重定向</font>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">最大请求数</label>
						<div class="col-sm-10"><input type="number" name="cc_max_requests" value="<?php echo isset($conf['cc_max_requests']) ? $conf['cc_max_requests'] : '30';?>" class="form-control" />
							<font color="green">检测时间窗口内允许的最大请求数</font>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">检测时间窗口</label>
						<div class="col-sm-10"><input type="number" name="cc_check_time" value="<?php echo isset($conf['cc_check_time']) ? $conf['cc_check_time'] : '30';?>" class="form-control" />
							<font color="green">单位：秒</font>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">封禁时长</label>
						<div class="col-sm-10"><input type="number" name="cc_block_time" value="<?php echo isset($conf['cc_block_time']) ? $conf['cc_block_time'] : '300';?>" class="form-control" />
							<font color="green">单位：秒</font>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">重定向网址</label>
						<div class="col-sm-10"><input type="text" name="cc_redirect_url" value="<?php echo isset($conf['cc_redirect_url']) ? $conf['cc_redirect_url'] : 'https://www.baidu.com';?>" class="form-control" />
							<font color="green">被攻击时将恶意IP重定向到的网址，请输入完整URL（包含http://或https://）</font>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="保存设置" class="btn btn-primary btn-block" /><br />
						</div>
					</div>
				</form>
			</div>
			<div class="panel-footer">
				<span class="fa fa-info-circle"></span>高级防CC功能说明<br />
				此功能可自动检测异常IP访问频率，当发现恶意访问时将其重定向到指定网站<br />
				建议设置合理的参数，避免误拦截正常用户<br />
				默认将恶意IP重定向到百度，您可以设置为自己的其他网站或警告页面
			</div>
		</div>
	<?php
} elseif ($mod == "proxy_n" && $_POST["do"] == "submit") {
	adminpermission("set", 1);
	$proxy = $_POST["proxy"];
	$server_hash = md5($_SERVER["SERVER_SOFTWARE"] . $_SERVER["SERVER_ADDR"]);
	$proxy_server = $_POST["proxy_server"];
	$proxy_port = $_POST["proxy_port"];
	$proxy_user = $_POST["proxy_user"];
	$proxy_pwd = $_POST["proxy_pwd"];
	$proxy_port = $_POST["proxy_port"];
	saveSetting("proxy", $proxy);
	saveSetting("server_hash", $server_hash);
	saveSetting("proxy_server", $proxy_server);
	saveSetting("proxy_port", $proxy_port);
	saveSetting("proxy_user", $proxy_user);
	saveSetting("proxy_pwd", $proxy_pwd);
	saveSetting("proxy_type", $proxy_type);
	$ad = $CACHE->clear();
	if ($ad) {
		showmsg("修改成功！", 1);
	} else {
		showmsg("修改失败！<br/>" . $DB->error(), 4);
	}
} elseif ($mod == "proxy") {
	adminpermission("set", 1);
	$server_hash = md5($_SERVER["SERVER_SOFTWARE"] . $_SERVER["SERVER_ADDR"]);
	if ($server_hash === $conf["server_hash"] && $conf["proxy"] == 1) {
		$is_proxy = 1;
	} else {
		$is_proxy = 0;
	}
	?>		<div class="block">
			<div class="block-title">
				<h3 class="panel-title">代理服务器设置</h3>
			</div>
			<div class="">
				<form action="./set.php?mod=proxy_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
					<?php
	if (check_china() == true) {
		?>						<div class="alert alert-info">你当前的服务器位于国内，无需使用代理服务器。</div>
					<?php
	} else {
		?>						<div class="form-group">
							<label class="col-sm-2 control-label">代理服务器开关</label>
							<div class="col-sm-10"><select class="form-control" name="proxy" default="<?php echo $conf["proxy"];?>">
									<option value="0">关闭</option>
									<option value="1">开启</option>
								</select></div>
						</div><br />
						<div id="frame_set1" style="display:none;">
							<div class="form-group">
								<label class="col-sm-2 control-label">代理IP</label>
								<div class="col-sm-10"><input type="text" name="proxy_server" value="<?php echo $conf["proxy_server"];?>" class="form-control" /></div>
							</div><br />
							<div class="form-group">
								<label class="col-sm-2 control-label">代理端口</label>
								<div class="col-sm-10"><input type="text" name="proxy_port" value="<?php echo $conf["proxy_port"];?>" class="form-control" /></div>
							</div><br />
							<div class="form-group">
								<label class="col-sm-2 control-label">代理账号</label>
								<div class="col-sm-10"><input type="text" name="proxy_user" value="<?php echo $conf["proxy_user"];?>" class="form-control" /></div>
							</div><br />
							<div class="form-group">
								<label class="col-sm-2 control-label">代理密码</label>
								<div class="col-sm-10"><input type="text" name="proxy_pwd" value="<?php echo $conf["proxy_pwd"];?>" class="form-control" /></div>
							</div><br />
							<div class="form-group">
								<label class="col-sm-2 control-label">代理协议</label>
								<div class="col-sm-10"><select class="form-control" name="proxy_type" default="<?php echo $conf["proxy_type"];?>">
										<option value="http">HTTP</option>
										<option value="https">HTTPS</option>
										<option value="sock4">SOCK4</option>
										<option value="sock5">SOCK5</option>
									</select></div>
							</div><br />
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary form-control" /><br />
							</div>
						</div>
					<?php
	}
	?>				</form>
			</div>
			<div class="panel-footer">
				<span class="fa fa-info-circle"></span>本功能适用于国外服务器对接一些屏蔽国外访问的社区或者卡盟，开启后使用国内代理服务器进行对接。<br />
				自定义代理可以使用Windows服务器+CCProxy软件搭建<br />
				<b>注意：如果网站更换主机之后需要重新修改当前配置。</b><br />
			</div>
		</div>
		<script>
			$("select[name='proxy']").change(function() {
				if ($(this).val() == 1) {
					$("#frame_set1").css("display", "inherit");
				} else {
					$("#frame_set1").css("display", "none");
				}
			});
		</script>
	<?php
} elseif ($mod == "rebaterecharge") {
	adminpermission("set", 1);
	$rebate_enabled = isset($conf["recharge_rebate_enabled"]) ? $conf["recharge_rebate_enabled"] : "0";
	$rebate_min = isset($conf["recharge_rebate_min"]) ? $conf["recharge_rebate_min"] : "0";
	$rebate_rate = isset($conf["recharge_rebate_rate"]) ? $conf["recharge_rebate_rate"] : "0";
	$rebate_rules = isset($conf["recharge_rebate_rules"]) ? $conf["recharge_rebate_rules"] : "";
	$recharge_min = isset($conf["recharge_min"]) ? $conf["recharge_min"] : "0";
	$fenzhan_jiakuanka = isset($conf["fenzhan_jiakuanka"]) ? $conf["fenzhan_jiakuanka"] : "0";
	?>		<div class="block">
			<div class="block-title">
				<h3 class="panel-title"><i class="fa fa-gift"></i> 充值返利设置</h3>
			</div>
			<div class="">
				<div class="alert alert-info">
					<i class="fa fa-info-circle"></i>
					本页统一管理在线充值、加款卡充值、后台手动加款的返利规则。后台扣除余额不会触发返利。
				</div>
				<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
					<div class="form-group">
						<label class="col-sm-3 control-label">开启充值返利</label>
						<div class="col-sm-9">
							<select class="form-control" name="recharge_rebate_enabled" default="<?php echo htmlspecialchars($rebate_enabled, ENT_QUOTES, 'UTF-8');?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">返利最低金额</label>
						<div class="col-sm-9">
							<input type="number" step="0.01" min="0" name="recharge_rebate_min" value="<?php echo htmlspecialchars($rebate_min, ENT_QUOTES, 'UTF-8');?>" class="form-control" placeholder="例如：100">
							<span class="help-block">未填写阶梯规则时使用此门槛；充值金额低于该值不返利。</span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">固定返利比例</label>
						<div class="col-sm-9">
							<div class="input-group">
								<input type="number" step="0.01" min="0" name="recharge_rebate_rate" value="<?php echo htmlspecialchars($rebate_rate, ENT_QUOTES, 'UTF-8');?>" class="form-control" placeholder="例如：1">
								<span class="input-group-addon">%</span>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">阶梯返利规则</label>
						<div class="col-sm-9">
							<textarea name="recharge_rebate_rules" rows="3" class="form-control" placeholder="例如：100:1|200:2|500:5"><?php echo htmlspecialchars($rebate_rules, ENT_QUOTES, 'UTF-8');?></textarea>
							<span class="help-block">格式：金额:比例，多个规则用英文竖线分隔。填写后优先按阶梯规则计算，例如 100:1 表示满 100 元送 1%。</span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">开启加款卡充值</label>
						<div class="col-sm-9">
							<select class="form-control" name="fenzhan_jiakuanka" default="<?php echo htmlspecialchars($fenzhan_jiakuanka, ENT_QUOTES, 'UTF-8');?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select>
							<span class="help-block"><a href="./kmlist.php"><i class="fa fa-credit-card"></i> 管理加款卡密</a></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">最低充值金额</label>
						<div class="col-sm-9">
							<input type="number" step="0.01" min="0" name="recharge_min" value="<?php echo htmlspecialchars($recharge_min, ENT_QUOTES, 'UTF-8');?>" class="form-control" placeholder="不填写则不限制">
							<span class="help-block">用于限制用户中心在线充值的最低金额。</span>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-3 col-sm-9">
							<button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i> 保存设置</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	<?php
} elseif ($mod == "fenzhan") {
	adminpermission("set", 1);
	?>		<div class="block">
			<div class="block-title">
				<h3 class="panel-title">分站相关配置</h3>
				<ul class="nav nav-tabs">
					<li class="active"><a href="#fenzhan_func" data-toggle="tab" aria-expanded="true">功能相关</a></li>
					<li><a href="#fenzhan_tixian" data-toggle="tab" aria-expanded="true">提现相关</a></li>
					<li><a href="#fenzhan_recharge" data-toggle="tab" aria-expanded="true">充值相关</a></li>
					<li><a href="#fenzhan_workorder" data-toggle="tab" aria-expanded="true">工单相关</a></li>
				</ul>
			</div>
			<div class="">

				<div id="myTabContent" class="tab-content">
					<div class="tab-pane fade active in" id="fenzhan_func">
						<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
							<div class="form-group">
								<label class="col-sm-3 control-label">分站排行榜</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_rank" default="<?php echo $conf["fenzhan_rank"];?>">
										<option value="1">开启</option>
										<option value="0">关闭</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">用户注册功能</label>
								<div class="col-sm-9"><select class="form-control" name="user_open" default="<?php echo $conf["user_open"];?>">
										<option value="0">关闭</option>
										<option value="1">开启</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">新注册用户价格等级</label>
								<div class="col-sm-9"><select class="form-control" name="user_level" default="<?php echo $conf["user_level"];?>">
										<option value="0">商品售价</option>
										<option value="1">普及版价格</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">自助开通分站</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_buy" default="<?php echo $conf["fenzhan_buy"];?>">
										<option value="1">开启</option>
										<option value="0">关闭</option>
									</select></div>
							</div>
							<div id="frame_set1" style="<?php echo $conf["fenzhan_buy"] == 1 ? NULL : "display:none;";?>">
								<div class="form-group">
									<label class="col-sm-3 control-label">开通分站界面提示语</label>
									<div class="col-sm-9"><select class="form-control" name="fenzhan_regalert" default="<?php echo $conf["fenzhan_regalert"];?>">
											<option value="0">关闭</option>
											<option value="1">开启</option>
										</select></div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">二级域名随机生成</label>
									<div class="col-sm-9"><select class="form-control" name="fenzhan_regrand" default="<?php echo $conf["fenzhan_regrand"];?>">
											<option value="0">关闭</option>
											<option value="1">开启</option>
										</select></div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">分站默认有效期</label>
									<div class="col-sm-9"><input type="text" name="fenzhan_expiry" value="<?php echo $conf["fenzhan_expiry"];?>" class="form-control" />
										<pre>填写月数，如果为0则是永久不过期</pre>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">专业版价格</label>
									<div class="col-sm-9"><input type="text" name="fenzhan_price2" value="<?php echo $conf["fenzhan_price2"];?>" class="form-control" /></div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">普及版价格</label>
									<div class="col-sm-9"><input type="text" name="fenzhan_price" value="<?php echo $conf["fenzhan_price"];?>" class="form-control" /></div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">专业版成本价格</label>
									<div class="col-sm-9"><input type="text" name="fenzhan_cost2" value="<?php echo $conf["fenzhan_cost2"];?>" class="form-control" />
										<pre>注意：分站成本价格请勿低于初始赠送余额</pre>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">普及版成本价格</label>
									<div class="col-sm-9"><input type="text" name="fenzhan_cost" value="<?php echo $conf["fenzhan_cost"];?>" class="form-control" />
										<pre>注意：分站成本价格请勿低于初始赠送余额</pre>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">初始赠送余额</label>
									<div class="col-sm-9"><input type="text" name="fenzhan_free" value="<?php echo $conf["fenzhan_free"];?>" class="form-control" /></div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">普及版升级价格</label>
									<div class="col-sm-9"><input type="text" name="fenzhan_upgrade" value="<?php echo $conf["fenzhan_upgrade"];?>" class="form-control" placeholder="不填写则不开启自助升级专业版功能" /></div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">自助修改域名价格</label>
									<div class="col-sm-9"><input type="text" name="fenzhan_editd" value="<?php echo $conf["fenzhan_editd"];?>" class="form-control" placeholder="不填写则不开启自助修改域名" /></div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">分站可选择域名</label>
									<div class="col-sm-9"><input type="text" name="fenzhan_domain" value="<?php echo $conf["fenzhan_domain"];?>" class="form-control" />
										<pre>多个域名用,隔开！</pre>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">主站预留域名</label>
								<div class="col-sm-9"><input type="text" name="fenzhan_remain" value="<?php echo $conf["fenzhan_remain"];?>" class="form-control" />
									<pre>主站预留域名无法被分站绑定，多个域名用,隔开！</pre>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">未绑定域名显示404页面</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_page" default="<?php echo $conf["fenzhan_page"];?>">
										<option value="0">关闭</option>
										<option value="1">开启</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">允许分站修改公告等排版</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_edithtml" default="<?php echo $conf["fenzhan_edithtml"];?>">
										<option value="1">开启</option>
										<option value="0">关闭</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">分站可更换首页模板</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_template" default="<?php echo $conf["fenzhan_template"];?>">
										<option value="0">关闭</option>
										<option value="1">开启</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">允许专业版后台添加分站</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_adds" default="<?php echo $conf["fenzhan_adds"];?>">
										<option value="1">开启</option>
										<option value="0">关闭</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">允许分站设置自己客服联系方式</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_kfqq" default="<?php echo $conf["fenzhan_kfqq"];?>">
										<option value="0">关闭</option>
										<option value="1">开启</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">分站设置商品价格限制</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_pricelimit" default="<?php echo $conf["fenzhan_pricelimit"];?>">
										<option value="0">关闭</option>
										<option value="1">开启</option>
									</select>
									<font color="green">开启后分站设置商品售价不能超过主站售价的2倍，低于1元的商品售价不能超过2元。</font>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-9"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
								</div>
							</div>
						</form>
					</div>
					<div class="tab-pane fade in" id="fenzhan_tixian">
						<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
							<div class="form-group">
								<label class="col-sm-3 control-label">开启提现</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_tixian" default="<?php echo $conf["fenzhan_tixian"];?>">
										<option value="0">关闭</option>
										<option value="1">开启</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">支付宝提现方式</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_tixian_alipay" default="<?php echo $conf["fenzhan_tixian_alipay"];?>">
										<option value="0">关闭</option>
										<option value="1">开启</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">微信提现方式</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_tixian_wx" default="<?php echo $conf["fenzhan_tixian_wx"];?>">
										<option value="0">关闭</option>
										<option value="1">开启</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">QQ提现方式</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_tixian_qq" default="<?php echo $conf["fenzhan_tixian_qq"];?>">
										<option value="0">关闭</option>
										<option value="1">开启</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">是否启用收款图</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_skimg" default="<?php echo $conf["fenzhan_skimg"];?>">
										<option value="0">关闭</option>
										<option value="1">开启</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">是否启用代付接口提现</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_daifu" default="<?php echo $conf["fenzhan_daifu"];?>">
										<option value="0">关闭</option>
										<option value="1">开启</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">提现余额比例</label>
								<div class="col-sm-9"><input type="text" name="tixian_rate" value="<?php echo $conf["tixian_rate"];?>" class="form-control" placeholder="填写百分数" /></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">提现最低余额</label>
								<div class="col-sm-9"><input type="text" name="tixian_min" value="<?php echo $conf["tixian_min"];?>" class="form-control" /></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">仅限提成部分可提现</label>
								<div class="col-sm-9"><select class="form-control" name="tixian_limit" default="<?php echo $conf["tixian_limit"];?>">
										<option value="0">关闭</option>
										<option value="1">开启</option>
									</select>
									<font color="green">开启后只有提成才可以提现，充值以及赠送的余额无法提现</font>
								</div>
							</div>
							<div id="frame_set2" style="<?php echo $conf["tixian_limit"] == 1 ? "display:none;" : NULL;?>">
								<div class="form-group">
									<label class="col-sm-3 control-label">提成收入多少天后可提现</label>
									<div class="col-sm-9"><input type="text" name="tixian_days" value="<?php echo $conf["tixian_days"];?>" class="form-control" placeholder="留空或0为可立即提现" />
										<font color="green">0为可立即提现，大于0则需要额外监控提成统计来实现提成入账</font>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-9"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
								</div>
							</div>
						</form>
					</div>
					<div class="tab-pane fade in" id="fenzhan_recharge">
						<div class="alert alert-info" style="margin:15px 0;">
							<i class="fa fa-info-circle"></i>
							&#20805;&#20540;&#36820;&#21033;&#12289;&#21152;&#27454;&#21345;&#20805;&#20540;&#21644;&#26368;&#20302;&#20805;&#20540;&#37329;&#39069;&#24050;&#32479;&#19968;&#21512;&#24182;&#21040;&#12300;&#20805;&#20540;&#36820;&#21033;&#35774;&#32622;&#12301;&#65292;&#35831;&#22312;&#35813;&#39029;&#38754;&#32479;&#19968;&#32500;&#25252;&#12290;
						</div>
						<p>
							<a class="btn btn-primary" href="./set.php?mod=rebaterecharge"><i class="fa fa-gift"></i> &#21069;&#24448;&#20805;&#20540;&#36820;&#21033;&#35774;&#32622;</a>
							<a class="btn btn-default" href="./kmlist.php"><i class="fa fa-credit-card"></i> &#21152;&#27454;&#21345;&#23494;&#21015;&#34920;</a>
						</p>
						<div style="display:none;">
							<div class="form-group">
								<label class="col-sm-3 control-label">开启加款卡充值</label>
								<div class="col-sm-9"><select class="form-control" name="fenzhan_jiakuanka" default="<?php echo $conf["fenzhan_jiakuanka"];?>">
										<option value="0">关闭</option>
										<option value="1">开启</option>
									</select><a href="./kmlist.php">加款卡密列表</a></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">充值赠送规则</label>
								<div class="col-sm-9"><input type="text" name="fenzhan_gift" value="<?php echo $conf["fenzhan_gift"];?>" class="form-control" placeholder="" />
									<pre>例如：100:3|200:5|500:10 就是一次性充值100元以上，赠送3%，200元以上赠送5%，500元以上赠送10%（所有符号都是英文状态输入的，不会的请勿乱填！）</pre>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">最低充值金额</label>
								<div class="col-sm-9"><input type="text" name="recharge_min" value="<?php echo $conf["recharge_min"];?>" class="form-control" placeholder="不填写则不限制" /></div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-9"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
								</div>
							</div>
						</div>
					</div>

					<div class="tab-pane fade in" id="fenzhan_workorder">
						<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
							<div class="form-group">
								<label class="col-sm-3 control-label">开启工单功能</label>
								<div class="col-sm-9"><select class="form-control" name="workorder_open" default="<?php echo $conf["workorder_open"];?>">
										<option value="1">开启</option>
										<option value="0">关闭</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">提交工单可上传图片</label>
								<div class="col-sm-9"><select class="form-control" name="workorder_pic" default="<?php echo $conf["workorder_pic"];?>">
										<option value="0">关闭</option>
										<option value="1">开启</option>
									</select></div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">工单可选问题类型</label>
								<div class="col-sm-9"><input type="text" name="workorder_type" value="<?php echo $conf["workorder_type"];?>" class="form-control" placeholder="多个类型用|隔开" /></div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-3 col-sm-9"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
								</div>
							</div>
						</form>
					</div>
				</div>

			</div>
		</div>

		<script>
			$("select[name='fenzhan_buy']").change(function() {
				if ($(this).val() == 1) {
					$("#frame_set1").css("display", "inherit");
				} else {
					$("#frame_set1").css("display", "none");
				}
			});
			$("select[name='tixian_limit']").change(function() {
				if ($(this).val() == 1) {
					$("#frame_set2").css("display", "inherit");
				} else {
					$("#frame_set2").css("display", "none");
				}
			});
			$("select[name='fenzhan_page']").change(function() {
				if ($(this).val() == 1) {
					alert('开启后必须保证主站预留域名已正确填写，否则主站将出现404无法访问。注意！！www.qq.com与qq.com是两个域名！！');
				}
			});
		</script>
	<?php
} elseif ($mod == "gonggao") {
	adminpermission("set", 1);
	?>		<div class="block">
			<div class="block-title">
				<h3 class="panel-title">网站公告配置</h3>
			</div>
			<div class="panel-body">
				<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal" role="form">
					<div class="form-group">
						<label>首页公告（非弹窗）</label>
						<p class="help-block">用于普通首页公告区域，当前 suisui 前台弹窗不会读取这里。</p>
						<textarea class="form-control" name="anounce" rows="8" style="width:100%;"><?php echo htmlspecialchars($conf["anounce"]);?></textarea>
					</div>
					<div class="form-group">
						<label>首页弹出公告</label>
						<select class="form-control" name="modal_show_type" style="width:auto;display:inline;margin-left:10px;" default="<?php echo isset($conf['modal_show_type']) ? $conf['modal_show_type'] : '0';?>">
							<option value="0">每次进网站都弹</option>
							<option value="1">只弹一次</option>
						</select>
						<p class="help-block">前台首页弹窗读取这里，修改弹窗内容请改这个文本框。</p>
						<textarea class="form-control" name="modal" rows="5" style="width:100%;"><?php echo htmlspecialchars($conf["modal"]);?></textarea>
					</div>
					<div class="form-group">
						<label>站点工具/友情链接（部分模板显示）</label><br />
						<textarea class="form-control" name="bottom" rows="5" style="width:100%;"><?php echo htmlspecialchars($conf["bottom"]);?></textarea>
					</div>
					<div class="form-group">
						<label>在线下单提示（部分模板显示）</label><br />
						<textarea class="form-control" name="alert" rows="5" style="width:100%;"><?php echo htmlspecialchars($conf["alert"]);?></textarea>
					</div>
					<div class="form-group">
						<label>订单查询页面公告</label><br />
						<textarea class="form-control" name="gg_search" rows="5" style="width:100%;"><?php echo htmlspecialchars($conf["gg_search"]);?></textarea>
					</div>
					<div class="form-group">
						<label>分站后台公告</label><br />
						<textarea class="form-control" name="gg_panel" rows="5" style="width:100%;"><?php echo htmlspecialchars($conf["gg_panel"]);?></textarea>
					</div>
					<div class="form-group">
						<label>所有分站显示首页公告</label><br />
						<textarea class="form-control" name="gg_announce" rows="5" placeholder="此处公告内容将在所有分站首页公告显示。顺序是先显示此公告再显示分站自定义公告" style="width:100%;"><?php echo htmlspecialchars($conf["gg_announce"]);?></textarea>
					</div>
					<div class="form-group">
						<label>首页底部排版</label><br />
						<textarea class="form-control" name="footer" rows="3" style="width:100%;" placeholder="可用于统计代码或备案号等"><?php echo htmlspecialchars($conf["footer"]);?></textarea>
					</div>
					<div class="form-group">
						<label>支付方式选择页面提示</label><br />
						<textarea class="form-control" name="paymsg" rows="3" style="width:100%;" placeholder=""><?php echo htmlspecialchars($conf["paymsg"]);?></textarea>
					</div>
					<div class="form-group">
						<label>APP下载地址</label><br />
						<input type="text" name="appurl" value="<?php echo $conf["appurl"];?>" class="form-control" placeholder="没有请留空" />
					</div>
					<div class="form-group">
						<label>APP启动弹出内容</label><br />
						<textarea class="form-control" name="appalert" rows="3"><?php echo htmlspecialchars($conf["appalert"]);?></textarea>
					</div>
					<div class="form-group">
						<label>首页背景音乐</label><br />
						<input type="text" name="musicurl" value="<?php echo $conf["musicurl"];?>" class="form-control" placeholder="填写音乐的URL" />
					</div>
					<div class="form-group">
						<input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" /><br />
					</div>
				</form>
			</div>
			<div class="panel-footer">
				<span class="fa fa-info-circle"></span>
				实用工具：<a href="set.php?mod=copygg">一键复制其他站点排版</a>｜<a href="http://www.runoob.com/runcode" target="_blank" rel="noreferrer">HTML在线测试</a>｜<a href="https://sm.ms/" target="_blank" rel="noreferrer">SM.MS图床</a>｜<a href="https://imgbb.com/" target="_blank" rel="noreferrer">ImgBB图床</a>｜<a href="https://link.hhtjim.com/" target="_blank" rel="noreferrer">音乐外链1</a>｜<a href="http://www.musictool.top/" target="_blank" rel="noreferrer">音乐外链2</a>
			</div>
		</div>
	<?php
} elseif ($mod == "copygg_n") {
	$url = $_POST["url"];
	$content = $_POST["content"];
	$url_arr = parse_url($url);
	if ($url_arr["host"] == $_SERVER["HTTP_HOST"]) {
		showmsg("无法自己复制自己", 3);
	}
	$data = get_curl($url . "api.php?act=siteinfo");
	$arr = json_decode($data, true);
	if (array_key_exists("sitename", $arr)) {
		if (in_array("anounce", $content)) {
			saveSetting("anounce", str_replace($arr["kfqq"], $conf["kfqq"], $arr["anounce"]));
		}
		if (in_array("modal", $content)) {
			saveSetting("modal", str_replace($arr["kfqq"], $conf["kfqq"], $arr["modal"]));
		}
		if (in_array("bottom", $content)) {
			saveSetting("bottom", str_replace($arr["kfqq"], $conf["kfqq"], $arr["bottom"]));
		}
		if (in_array("alert", $content)) {
			saveSetting("alert", str_replace($arr["kfqq"], $conf["kfqq"], $arr["alert"]));
		}
		if (in_array("gg_search", $content)) {
			saveSetting("gg_search", str_replace($arr["kfqq"], $conf["kfqq"], $arr["gg_search"]));
		}
		if (in_array("gg_panel", $content)) {
			saveSetting("gg_panel", str_replace($arr["kfqq"], $conf["kfqq"], $arr["gg_panel"]));
		}
		$ad = $CACHE->clear();
		if ($ad) {
			showmsg("修改成功！", 1);
		} else {
			showmsg("修改失败！<br/>" . $DB->error(), 4);
		}
	} else {
		showmsg("获取数据失败，对方网站无法连接或存在金盾或云锁等防火墙。", 4);
	}
} elseif ($mod == "copygg") {
	adminpermission("set", 1);
	?>		<div class="block">
			<div class="block-title">
				<h3 class="panel-title">一键复制其他站点排版</h3>
			</div>
			<div class="">
				<form action="./set.php?mod=copygg_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
					<div class="form-group">
						<label class="col-sm-2 control-label">站点URL</label>
						<div class="col-sm-10"><input type="text" name="url" value="" class="form-control" placeholder="http://www.qq.com/" required /></div>
					</div><br />
					<div class="form-group">
						<label class="col-sm-2 control-label">复制内容</label>
						<div class="col-sm-10"><label><input name="content[]" type="checkbox" value="anounce" checked /> 首页公告</label><br /><label><input name="content[]" type="checkbox" value="modal" checked /> 弹出公告</label><br /><label><input name="content[]" type="checkbox" value="bottom" checked /> 底部排版</label><br /><label><input name="content[]" type="checkbox" value="alert" checked /> 下单提示</label><br /><label><input name="content[]" type="checkbox" value="gg_search" checked /> 订单查询公告</label><br /><label><input name="content[]" type="checkbox" value="gg_panel" checked /> 分站后台公告</label></div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" /><br />
						</div>
					</div>
				</form>
			</div>
		</div>
	<?php
} elseif ($mod == "mail") {
	adminpermission("set", 1);
	?>		<div class="block">
			<div class="block-title">
				<h3 class="panel-title">消息提醒设置</h3>
			</div>
			<div class="">
				<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
					<div class="form-group">
						<label class="col-sm-2 control-label">默认提醒方式</label>
						<div class="col-sm-10"><select class="form-control" name="message_type" default="<?php echo $conf["message_type"];?>">
								<option value="0">邮件</option>
								<option value="1">微信</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">对接下单失败提醒</label>
						<div class="col-sm-10"><select class="form-control" name="message_duijie" default="<?php echo $conf["message_duijie"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">全部商品下单提醒</label>
						<div class="col-sm-10"><select class="form-control" name="message_buy" default="<?php echo $conf["message_buy"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
								<option value="2">开启（不包括免费商品）</option>
							</select></div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">新工单提醒</label>
						<div class="col-sm-10"><select class="form-control" name="message_workorder" default="<?php echo $conf["message_workorder"];?>">
								<option value="0">关闭</option>
								<option value="1">开启</option>
							</select></div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="block">
			<div class="block-title">
				<h3 class="panel-title">发信邮箱设置</h3>
			</div>
			<div class="">
				<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
					<div class="form-group">
						<label class="col-sm-2 control-label">发信模式</label>
						<div class="col-sm-10"><select class="form-control" name="mail_cloud" default="<?php echo $conf["mail_cloud"];?>">
								<option value="0">SMTP发信</option>
								<option value="1">搜狐Sendcloud</option>
								<option value="2">阿里云邮件推送</option>
							</select></div>
					</div>
					<div id="frame_set1" style="<?php echo $conf["mail_cloud"] == 0 ? NULL : "display:none;";?>">
						<div class="form-group">
							<label class="col-sm-2 control-label">SMTP服务器</label>
							<div class="col-sm-10"><input type="text" name="mail_smtp" value="<?php echo $conf["mail_smtp"];?>" class="form-control" /></div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">SMTP端口</label>
							<div class="col-sm-10"><input type="text" name="mail_port" value="<?php echo $conf["mail_port"];?>" class="form-control" /></div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">邮箱账号</label>
							<div class="col-sm-10"><input type="text" name="mail_name" value="<?php echo $conf["mail_name"];?>" class="form-control" /></div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">邮箱密码</label>
							<div class="col-sm-10"><input type="text" name="mail_pwd" value="<?php echo $conf["mail_pwd"];?>" class="form-control" /></div>
						</div>
					</div>
					<div id="frame_set2" style="<?php echo $conf["mail_cloud"] >= 1 ? NULL : "display:none;";?>">
						<div class="form-group">
							<label class="col-sm-2 control-label">API_USER</label>
							<div class="col-sm-10"><input type="text" name="mail_apiuser" value="<?php echo $conf["mail_apiuser"];?>" class="form-control" /></div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">API_KEY</label>
							<div class="col-sm-10"><input type="text" name="mail_apikey" value="<?php echo $conf["mail_apikey"];?>" class="form-control" /></div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">发信邮箱</label>
							<div class="col-sm-10"><input type="text" name="mail_name2" value="<?php echo $conf["mail_name2"];?>" class="form-control" /></div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">收信邮箱</label>
						<div class="col-sm-10"><input type="text" name="mail_recv" value="<?php echo $conf["mail_recv"];?>" class="form-control" placeholder="不填默认为发信邮箱" /></div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" /><br /> </div>
					</div>
				</form>
			</div>
			<div class="panel-footer">
				<span class="fa fa-info-circle"></span>
				此功能为用户下单时给自己发邮件提醒以及发卡商品发送给用户的邮件。<br />使用普通模式发信时，建议使用QQ邮箱，SMTP服务器smtp.qq.com，端口465或587，密码不是QQ密码也不是邮箱独立密码，是QQ邮箱设置界面生成的<a href="https://service.mail.qq.com/cgi-bin/help?subtype=1&&no=1001256&&id=28" target="_blank" rel="noreferrer">授权码</a>。<br />阿里云邮件推送：<a href="https://www.aliyun.com/product/directmail" target="_blank" rel="noreferrer">点此进入</a>｜<a href="https://usercenter.console.aliyun.com/#/manage/ak" target="_blank" rel="noreferrer">获取AK/SK</a>
			</div>
		</div>
		<script>
			$("select[name='mail_cloud']").change(function() {
				if ($(this).val() == 0) {
					$("#frame_set1").css("display", "inherit");
					$("#frame_set2").css("display", "none");
				} else {
					$("#frame_set1").css("display", "none");
					$("#frame_set2").css("display", "inherit");
				}
			});
		</script>
		<div class="block">
			<div class="block-title">
				<h3 class="panel-title">微信消息设置</h3>
			</div>
			<div class="">
				<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
					<div class="form-group">
						<label class="col-sm-2 control-label">微信消息接口</label>
						<div class="col-sm-10"><select class="form-control" name="wechat_api" default="<?php echo $conf["wechat_api"];?>">
								<option value="0">ServerChan(ftqq)</option>
								<option value="1">WxPusher</option>
							</select></div>
					</div>
					<div id="frame_set3" style="<?php echo $conf["wechat_api"] == 0 ? "display:none;" : NULL;?>">
						<div class="form-group">
							<label class="col-sm-2 control-label">SCKEY</label>
							<div class="col-sm-10"><input type="text" name="wechat_sckey" value="<?php echo $conf["wechat_sckey"];?>" class="form-control" /></div>
						</div>
					</div>
					<div id="frame_set4" style="<?php echo $conf["wechat_api"] == 1 ? "display:none;" : NULL;?>">
						<div class="form-group">
							<label class="col-sm-2 control-label">appToken</label>
							<div class="col-sm-10"><input type="text" name="wechat_apptoken" value="<?php echo $conf["wechat_apptoken"];?>" class="form-control" /></div>
						</div>
						<div class="form-group">
							<label class="col-sm-2 control-label">用户UID</label>
							<div class="col-sm-10"><input type="text" name="wechat_appuid" value="<?php echo $conf["wechat_appuid"];?>" class="form-control" /></div>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
						</div>
					</div>
				</form>
			</div>
			<div class="panel-footer">
				<span class="fa fa-info-circle"></span><br />
				<b>ServerChan(ftqq)：</b>进入 <a href="http://sc.ftqq.com/" target="_blank" rel="noopener noreferrer">http://sc.ftqq.com/</a> ，登录账号 -> 绑定自己的微信号 -> 获取到SCKEY填写到上方输入框！<br />
				<b>WxPusher：</b><a href="https://wxpusher.zjiecode.com/admin/" target="_blank" rel="noopener noreferrer">点此进入</a> ，注册并且创建应用 -> 将appToken填写到上方输入框 -> 扫码关注应用 -> 在用户列表查看自己的UID填写到上方输入框<br />
			</div>
		</div>
		<script>
			$("select[name='wechat_api']").change(function() {
				if ($(this).val() == 0) {
					$("#frame_set3").css("display", "inherit");
					$("#frame_set4").css("display", "none");
				} else {
					$("#frame_set3").css("display", "none");
					$("#frame_set4").css("display", "inherit");
				}
			});
		</script>
	<?php
} elseif ($mod == "alipay_n" && $_POST["do"] == "submit") {
	adminpermission("super", 1);
	$alipay_appid = $_POST["alipay_appid"];
	$alipay_publickey = $_POST["alipay_publickey"];
	$alipay_privatekey = $_POST["alipay_privatekey"];
	saveSetting("alipay_appid", $alipay_appid);
	saveSetting("alipay_publickey", $alipay_publickey);
	saveSetting("alipay_privatekey", $alipay_privatekey);
	$ad = $CACHE->clear();
	if ($ad) {
		showmsg("修改成功！", 1);
	} else {
		showmsg("修改失败！<br/>" . $DB->error(), 4);
	}
} elseif ($mod == "alipay2_n" && $_POST["do"] == "submit") {
	adminpermission("super", 1);
	$alipay_getway = $_POST["alipay_getway"];
	$alipay_pid = $_POST["alipay_pid"];
	$alipay_key = $_POST["alipay_key"];
	saveSetting("alipay_getway", $alipay_getway);
	saveSetting("alipay_pid", $alipay_pid);
	saveSetting("alipay_key", $alipay_key);
	$ad = $CACHE->clear();
	if ($ad) {
		showmsg("修改成功！", 1);
	} else {
		showmsg("修改失败！<br/>" . $DB->error(), 4);
	}
} elseif ($mod == "qqpay_n" && $_POST["do"] == "submit") {
	adminpermission("super", 1);
	$qqpay_mchid = $_POST["qqpay_mchid"];
	$qqpay_key = $_POST["qqpay_key"];
	saveSetting("qqpay_mchid", $qqpay_mchid);
	saveSetting("qqpay_key", $qqpay_key);
	$ad = $CACHE->clear();
	if ($ad) {
		showmsg("修改成功！", 1);
	} else {
		showmsg("修改失败！<br/>" . $DB->error(), 4);
	}
} elseif ($mod == "wxpay_n" && $_POST["do"] == "submit") {
	adminpermission("super", 1);
	$wxpay_appid = $_POST["wxpay_appid"];
	$wxpay_mchid = $_POST["wxpay_mchid"];
	$wxpay_key = $_POST["wxpay_key"];
	$wxpay_appsecret = $_POST["wxpay_appsecret"];
	$wxpay_domain = $_POST["wxpay_domain"];
	saveSetting("wxpay_appid", $wxpay_appid);
	saveSetting("wxpay_mchid", $wxpay_mchid);
	saveSetting("wxpay_key", $wxpay_key);
	saveSetting("wxpay_appsecret", $wxpay_appsecret);
	saveSetting("wxpay_domain", $wxpay_domain);
	$ad = $CACHE->clear();
	if ($ad) {
		showmsg("修改成功！", 1);
	} else {
		showmsg("修改失败！<br/>" . $DB->error(), 4);
	}
} elseif ($mod == "codepay_n" && $_POST["do"] == "submit") {
	adminpermission("super", 1);
	$codepay_id = $_POST["codepay_id"];
	$codepay_key = $_POST["codepay_key"];
	saveSetting("codepay_id", $codepay_id);
	saveSetting("codepay_key", $codepay_key);
	$ad = $CACHE->clear();
	if ($ad) {
		showmsg("修改成功！", 1);
	} else {
		showmsg("修改失败！<br/>" . $DB->error(), 4);
	}
} elseif ($mod == "micropay_n" && $_POST["do"] == "submit") {
	adminpermission("super", 1);
	$micropayapi = $_POST["micropayapi"];
	$micropay_pid = $_POST["micropay_pid"];
	$micropay_key = $_POST["micropay_key"];
	$micropay_mchid = $_POST["micropay_mchid"];
	saveSetting("micropayapi", $micropayapi);
	saveSetting("micropay_pid", $micropay_pid);
	saveSetting("micropay_key", $micropay_key);
	saveSetting("micropay_mchid", $micropay_mchid);
	$ad = $CACHE->clear();
	if ($ad) {
		showmsg("修改成功！", 1);
	} else {
		showmsg("修改失败！<br/>" . $DB->error(), 4);
	}
} elseif ($mod == "epay_n" && $_POST["do"] == "submit") {
	adminpermission("super", 1);
	$payapi = $_POST["payapi"];
	$epay_url = $_POST["epay_url"];
	//if (!check_pay_api($epay_url) && !empty($epay_url)) {
	//	showmsg("未经过官方认证的支付接口存在重大安全隐患,请谨慎使用！", 3);
	//}
	$epay_pid = $_POST["epay_pid"];
	$epay_key = $_POST["epay_key"];
	if (($conf["alipay_api"] == 2 || $conf["qqpay_api"] == 2 || $conf["wxpay_api"] == 2) && $payapi == -1) {
		if (empty($epay_url)) {
			showmsg("请填写接口网址", 3);
		}
		$conf["payapi"] = -1;
		$conf["epay_url"] = $epay_url;
	}
	$payapi2 = $_POST["payapi2"];
	$epay_url2 = $_POST["epay_url2"];
	//if (!check_pay_api($epay_url2) && !empty($epay_url2)) {
	//	showmsg("未经过官方认证的支付接口存在重大安全隐患,请谨慎使用！", 3);
	//}
	$epay_pid2 = $_POST["epay_pid2"];
	$epay_key2 = $_POST["epay_key2"];
	if (($conf["qqpay_api"] == 8 || $conf["wxpay_api"] == 8) && $payapi2 == -1) {
		if (empty($epay_url2)) {
			showmsg("请填写接口网址", 3);
		}
		$conf["payapi2"] = -1;
		$conf["epay_url2"] = $epay_url2;
	}
	$payapi3 = $_POST["payapi3"];
	$epay_url3 = $_POST["epay_url3"];
	//if (!check_pay_api($epay_url3) && !empty($epay_url3)) {
	//	showmsg("未经过官方认证的支付接口存在重大安全隐患,请谨慎使用！", 3);
	//}
	$epay_pid3 = $_POST["epay_pid3"];
	$epay_key3 = $_POST["epay_key3"];
	if ($conf["wxpay_api"] == 9 && $payapi3 == -1) {
		if (empty($epay_url3)) {
			showmsg("请填写接口网址", 3);
		}
		$conf["payapi3"] = -1;
		$conf["epay_url3"] = $epay_url3;
	}
	$payapi4 = $_POST["payapi4"];
	$epay_url4 = $_POST["epay_url4"];
	//if (!check_pay_api($epay_url4) && !empty($epay_url4)) {
	//	showmsg("未经过官方认证的支付接口存在重大安全隐患,请谨慎使用！", 3);
	//}
	$epay_pid4 = $_POST["epay_pid4"];
	$epay_key4 = $_POST["epay_key4"];
	if (($conf["alipay_api"] == 10 || $conf["qqpay_api"] == 10 || $conf["wxpay_api"] == 10) && $payapi4 == -1) {
		if (empty($epay_url4)) {
			showmsg("请填写接口网址", 3);
		}
		$conf["payapi4"] = -1;
		$conf["epay_url4"] = $epay_url4;
	}
	if($payapi){
		saveSetting("payapi", $payapi);
	}
	if($epay_url){
		saveSetting("epay_url", $epay_url);
	}
	if($epay_pid){
		saveSetting("epay_pid", $epay_pid);
	}
	if($epay_key){
		saveSetting("epay_key", $epay_key);
	}
	if($payapi2){
		saveSetting("payapi2", $payapi2);
	}
	if($epay_url2){
		saveSetting("epay_url2", $epay_url2);
	}
	if($epay_pid2){
		saveSetting("epay_pid2", $epay_pid2);
	}
	if($epay_key2){
		saveSetting("epay_key2", $epay_key2);
	}
	if($payapi3){
	saveSetting("payapi3", $payapi3);
}
if($epay_url3){
	saveSetting("epay_url3", $epay_url3);
}
if($epay_pid3){
	saveSetting("epay_pid3", $epay_pid3);
}
if($epay_key3){
	saveSetting("epay_key3", $epay_key3);
}
if($payapi4){
	saveSetting("payapi4", $payapi4);
}
if($epay_url4){
	saveSetting("epay_url4", $epay_url4);
}
if($epay_pid4){
	saveSetting("epay_pid4", $epay_pid4);
}
if($epay_key4){
	saveSetting("epay_key4", $epay_key4);
}


	$ad = $CACHE->clear();

	if ($ad) {
		showmsg("修改成功！", 1);
	} else {
		showmsg("修改失败！<br/>" . $DB->error(), 4);
	}
} elseif ($mod == "pay") {
	adminpermission("super", 1);
	//$pay_api_list = $authorization->getPayList();
	?>		<style>
			.block-options .btn {
				opacity: 0.8;
				font-weight: 600;
				color: #f36b0a
			}
		</style>
		<div class="block">
			<div class="block-title">
				<h3 class="panel-title">支付接口配置</h3>
				<div class="block-options pull-right"><a href="./payorder.php" class="btn btn-default">支付记录</a></div>
			</div>
			<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal" role="form">
				<div class="form-group">
					<label class="col-lg-3 control-label">支付宝支付接口</label>
					<div class="col-lg-8">
						<select class="form-control" name="alipay_api" default="<?php echo $conf["alipay_api"];?>">
							<option value="0">关闭</option>
							<option value="1">支付宝电脑+手机网站支付</option>
							<option value="3">支付宝当面付扫码支付</option>
							<option value="2">易支付接口</option>
							<option value="10">易支付接口（新）</option>
							<option value="6">支付宝小微支付接口</option>
							<option value="7">卡易信笔笔清支付宝接口</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-3 control-label">QQ钱包支付接口</label>
					<div class="col-lg-8">
						<select class="form-control" name="qqpay_api" default="<?php echo $conf["qqpay_api"];?>">
							<option value="0">关闭</option>
							<option value="1">QQ钱包官方支付接口</option>
							<option value="2">易支付接口</option>
							<option value="8">易支付接口(备用1)</option>
							<option value="10">易支付接口（新）</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-3 control-label">微信支付接口</label>
					<div class="col-lg-8">
						<select class="form-control" name="wxpay_api" default="<?php echo $conf["wxpay_api"];?>">
							<option value="0">关闭</option>
							<option value="1">微信官方扫码+公众号支付接口</option>
							<option value="3">微信官方扫码+H5支付接口</option>
							<option value="2">易支付接口</option>
							<option value="8">易支付接口(备用1)</option>
							<option value="9">易支付接口(备用2)</option>
							<option value="10">易支付接口（新）</option>
							<option value="6">微信小微支付接口</option>
						</select>
					</div>
				</div>
				<div class="form-group">
				<div class="col-sm-offset-3 col-sm-8">
					<input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" /><br />
				</div>
			</div>
		</form>
	</div>
	<?php
	// USDT-TRC20支付配置入口
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">USDT-TRC20支付设置</h3>
		</div>
		<div class="panel-body">
			<p>USDT-TRC20支付需要单独配置，<a href="./set.php?mod=codepay">点击这里进入USDT-TRC20支付配置页面</a></p>
		</div>
	</div>
		<?php
	if ($conf["alipay_api"] == 1 || $conf["alipay_api"] == 3) {
		?>			<div class="block">
				<div class="block-title">
					<h3 class="panel-title">支付宝官方支付接口配置</h3>
					<div class="block-options pull-right"><a href="https://b.alipay.com/signing/productSetV2.htm" rel="noreferrer" target="_blank" class="btn btn-default">申请地址</a><a href="https://openhome.alipay.com/dev/workspace/key-manage" rel="noreferrer" target="_blank" class="btn btn-default">密钥查看</a></div>
				</div>
				<form action="./set.php?mod=alipay_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
					<div class="form-group">
						<label class="col-lg-3 control-label">应用APPID</label>
						<div class="col-lg-8">
							<input type="text" name="alipay_appid" class="form-control" value="<?php echo $conf["alipay_appid"];?>" required="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">支付宝公钥(RSA2)</label>
						<div class="col-lg-8">
							<textarea id="appkey" name="alipay_publickey" rows="2" class="form-control" required="" placeholder="是支付宝公钥不是商户公钥！填错会无法回调"><?php echo $conf["alipay_publickey"];?></textarea>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">应用私钥(RSA2)</label>
						<div class="col-lg-8">
							<textarea id="appkey" name="alipay_privatekey" rows="2" class="form-control" required="" placeholder=""><?php echo $conf["alipay_privatekey"];?></textarea>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-3 col-sm-8"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
						</div>
					</div>
				</form>
			</div>
		<?php
	}
	?>		<?php
	if ($conf["alipay_api"] == 7) {
		?>			<div class="block">
				<div class="block-title">
					<h3 class="panel-title">卡易信支付宝接口配置</h3>
					<div class="block-options pull-right"><a href="http://qdd.kayixin.com/reg1.htm" rel="noreferrer" target="_blank" class="btn btn-default">申请地址</a></div>
				</div>
				<form action="./set.php?mod=alipay2_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
					<div class="form-group">
						<label class="col-lg-3 control-label">接口域名</label>
						<div class="col-lg-8">
							<input type="text" name="alipay_getway" class="form-control" value="<?php echo $conf["alipay_getway"];?>" required="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">商户号</label>
						<div class="col-lg-8">
							<input type="text" name="alipay_pid" class="form-control" value="<?php echo $conf["alipay_pid"];?>" required="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">商户密钥</label>
						<div class="col-lg-8">
							<input type="text" name="alipay_key" class="form-control" value="<?php echo $conf["alipay_key"];?>" required="">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-3 col-sm-8"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
						</div>
					</div>
				</form>
			</div>
		<?php
	}
	?>		<?php
	if ($conf["qqpay_api"] == 1) {
		?>			<div class="block">
				<div class="block-title">
					<h3 class="panel-title">QQ钱包官方接口配置</h3>
					<div class="block-options pull-right"><a href="https://qpay.qq.com/" rel="noreferrer" target="_blank" class="btn btn-default">申请地址</a></div>
				</div>
				<form action="./set.php?mod=qqpay_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
					<div class="form-group">
						<label class="col-lg-3 control-label">QQ钱包商户号</label>
						<div class="col-lg-8">
							<input type="text" name="qqpay_mchid" class="form-control" value="<?php echo $conf["qqpay_mchid"];?>" required="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">QQ钱包API密钥</label>
						<div class="col-lg-8">
							<input type="text" name="qqpay_key" class="form-control" value="<?php echo $conf["qqpay_key"];?>" required="">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-3 col-sm-8"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
						</div>
					</div>
				</form>
			</div>
		<?php
	}
	?>		<?php
	if ($conf["wxpay_api"] == 1 || $conf["wxpay_api"] == 3) {
		?>			<div class="block">
				<div class="block-title">
					<h3 class="panel-title">微信支付官方接口配置</h3>
					<div class="block-options pull-right"><a href="https://pay.weixin.qq.com/" rel="noreferrer" target="_blank" class="btn btn-default">申请地址</a></div>
				</div>
				<form action="./set.php?mod=wxpay_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
					<div class="form-group">
						<label class="col-lg-3 control-label">微信公众号APPID</label>
						<div class="col-lg-8">
							<input type="text" name="wxpay_appid" class="form-control" value="<?php echo $conf["wxpay_appid"];?>" required="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">微信支付商户号</label>
						<div class="col-lg-8">
							<input type="text" name="wxpay_mchid" class="form-control" value="<?php echo $conf["wxpay_mchid"];?>" required="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">微信支付商户密钥</label>
						<div class="col-lg-8">
							<input type="text" name="wxpay_key" class="form-control" value="<?php echo $conf["wxpay_key"];?>" required="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">微信公众号APPSECRET</label>
						<div class="col-lg-8">
							<input type="text" name="wxpay_appsecret" class="form-control" value="<?php echo $conf["wxpay_appsecret"];?>" placeholder="仅公众号支付需要填写">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">微信支付指定域名</label>
						<div class="col-lg-8">
							<input type="text" name="wxpay_domain" class="form-control" value="<?php echo $conf["wxpay_domain"];?>" placeholder="用于微信公众号支付与H5支付接口，限制域名的情况下">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-3 col-sm-8"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
						</div>
					</div>
				</form>
			</div>
		<?php
	}
	?>		<?php
	if ($conf["alipay_api"] == 2 || $conf["qqpay_api"] == 2 || $conf["wxpay_api"] == 2) {
		?>			<div class="block">
				<div class="block-title">
					<h3 class="panel-title">易支付配置</h3>
					<div class="block-options pull-right"></div>
					<div class="block-options pull-right"><a href="#" rel="noreferrer" target="_blank" class="btn btn-default" id="epayurl" style="display:none;">进入易支付网站</a></div>
				</div>
				<form action="./set.php?mod=epay_n" method="post" class="form-horizontal" role="form" onsubmit="return checkepayurl('payapi','epay_url')"><input type="hidden" name="do" value="submit" />
					<div class="form-group">
						<label class="col-lg-3 control-label">易支付接入商</label>
						<div class="col-lg-8">
							<select class="form-control" name="payapi" default="<?php echo $conf["payapi"];?>">
							    <?php echo $pay_api_list;?>								<option value="-1">其它（手动输入）</option>
							</select>
						</div>
					</div>
					<div class="form-group" id="payapi_07" style="<?php echo $conf["payapi"] == -1 ? "display:none;" : NULL;?>">
						<label class="col-lg-3 control-label">易支付接口网址</label>
						<div class="col-lg-8">
							<input type="text" name="epay_url" class="form-control" value="<?php echo $conf["epay_url"];?>" placeholder="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">易支付商户ID</label>
						<div class="col-lg-8">
							<input type="text" name="epay_pid" class="form-control" value="<?php echo $conf["epay_pid"];?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">易支付商户密钥</label>
						<div class="col-lg-8">
							<input type="text" name="epay_key" class="form-control" value="<?php echo $conf["epay_key"];?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-3 col-sm-8"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" /><br /><br />
							<a href="set.php?mod=epay">进入易支付结算设置及订单查询页面</a>
						</div>
					</div>
				</form>
			</div>
		<?php
	}
	?>		<?php
	if ($conf["alipay_api"] == 6 || $conf["wxpay_api"] == 6) {
		?>			<div class="block">
				<div class="block-title">
					<h3 class="panel-title">支付宝/微信小微支付配置</h3>
					<div class="block-options pull-right"><a href="#" rel="noreferrer" target="_blank" class="btn btn-default" id="micropayurl" style="display:none;">申请地址</a></div>
				</div>
				<form action="./set.php?mod=micropay_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
					<div class="form-group">
						<label class="col-lg-3 control-label">小微支付接入商</label>
						<div class="col-lg-8">
							<select class="form-control" name="micropayapi" default="<?php echo $conf["micropayapi"];?>">
								<option value="0">风吹雨</option>
								<option value="1">微通付</option>
								<option value="2">微易付</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">小微支付APPID</label>
						<div class="col-lg-8">
							<input type="text" name="micropay_pid" class="form-control" value="<?php echo $conf["micropay_pid"];?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">小微支付密钥</label>
						<div class="col-lg-8">
							<input type="text" name="micropay_key" class="form-control" value="<?php echo $conf["micropay_key"];?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">小微支付MCHID</label>
						<div class="col-lg-8">
							<input type="text" name="micropay_mchid" class="form-control" value="<?php echo $conf["micropay_mchid"];?>">
							<font color="green">此处可同时填写支付宝和微信的，用英文逗号,隔开</font>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-3 col-sm-8"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
						</div>
					</div>
				</form>
			</div>
		<?php
	}
	?>		<?php
	if ($conf["qqpay_api"] == 8 || $conf["wxpay_api"] == 8) {
		?>			<div class="block">
				<div class="block-title">
					<h3 class="panel-title">易支付（备用1）配置</h3>
					<div class="block-options pull-right"><a href="#" rel="noreferrer" target="_blank" class="btn btn-default" id="epayurl2" style="display:none;">进入易支付网站</a></div>
				</div>
				<form action="./set.php?mod=epay_n" method="post" class="form-horizontal" role="form" onsubmit="return checkepayurl('payapi2','epay_url2')"><input type="hidden" name="do" value="submit" />
					<div class="form-group">
						<label class="col-lg-3 control-label">易支付接入商</label>
						<div class="col-lg-8">
							<select class="form-control" name="payapi2" default="<?php echo $conf["payapi2"];?>">
								<?php echo $pay_api_list;?>								<option value="-1">其它（手动输入）</option>
							</select>
						</div>
					</div>
					<div class="form-group" id="payapi_08" style="<?php echo $conf["payapi2"] == -1 ? "display:none;" : NULL;?>">
						<label class="col-lg-3 control-label">易支付接口网址</label>
						<div class="col-lg-8">
							<input type="text" name="epay_url2" class="form-control" value="<?php echo $conf["epay_url2"];?>" placeholder="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">易支付商户ID</label>
						<div class="col-lg-8">
							<input type="text" name="epay_pid2" class="form-control" value="<?php echo $conf["epay_pid2"];?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">易支付商户密钥</label>
						<div class="col-lg-8">
							<input type="text" name="epay_key2" class="form-control" value="<?php echo $conf["epay_key2"];?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-3 col-sm-8"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
						</div>
					</div>
				</form>
			</div>
		<?php
	}
	?>		<?php
	if ($conf["wxpay_api"] == 9) {
		?>			<div class="block">
				<div class="block-title">
					<h3 class="panel-title">易支付（备用2）配置</h3>
					<div class="block-options pull-right"><a href="#" rel="noreferrer" target="_blank" class="btn btn-default" id="epayurl3" style="display:none;">进入易支付网站</a></div>
				</div>
				<form action="./set.php?mod=epay_n" method="post" class="form-horizontal" role="form" onsubmit="return checkepayurl('payapi3','epay_url3')"><input type="hidden" name="do" value="submit" />
					<div class="form-group">
						<label class="col-lg-3 control-label">易支付接入商</label>
						<div class="col-lg-8">
							<select class="form-control" name="payapi3" default="<?php echo $conf["payapi3"];?>">
								<?php echo $pay_api_list;?>								<option value="-1">其它（手动输入）</option>
							</select>
						</div>
					</div>
					<div class="form-group" id="payapi_09" style="<?php echo $conf["payapi3"] == -1 ? "display:none;" : NULL;?>">
						<label class="col-lg-3 control-label">易支付接口网址</label>
						<div class="col-lg-8">
							<input type="text" name="epay_url3" class="form-control" value="<?php echo $conf["epay_url3"];?>" placeholder="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">易支付商户ID</label>
						<div class="col-lg-8">
							<input type="text" name="epay_pid3" class="form-control" value="<?php echo $conf["epay_pid3"];?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">易支付商户密钥</label>
						<div class="col-lg-8">
							<input type="text" name="epay_key3" class="form-control" value="<?php echo $conf["epay_key3"];?>">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-3 col-sm-8"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
						</div>
					</div>
				</form>
			</div>
		<?php
	}
	?>		<?php
	if ($conf["alipay_api"] == 10 || $conf["qqpay_api"] == 10 || $conf["wxpay_api"] == 10) {
			?>				<div class="block">
					<div class="block-title">
						<h3 class="panel-title">易支付（新）配置</h3>
						<div class="block-options pull-right"><a href="#" rel="noreferrer" target="_blank" class="btn btn-default" id="epayurl4" style="display:none;">进入易支付网站</a></div>
					</div>
					<form action="./set.php?mod=epay_n" method="post" class="form-horizontal" role="form" onsubmit="return checkepayurl('payapi4','epay_url4')"><input type="hidden" name="do" value="submit" />
						<div class="form-group">
							<label class="col-lg-3 control-label">易支付接入商</label>
							<div class="col-lg-8">
								<select class="form-control" name="payapi4" default="<?php echo $conf["payapi4"];?>">
									<?php echo $pay_api_list;?>									<option value="-1">其它（手动输入）</option>
								</select>
							</div>
						</div>
						<div class="form-group" id="payapi_10" style="<?php echo $conf["payapi4"] == -1 ? "display:none;" : NULL;?>">
							<label class="col-lg-3 control-label">易支付接口网址</label>
							<div class="col-lg-8">
								<input type="text" name="epay_url4" class="form-control" value="<?php echo $conf["epay_url4"];?>" placeholder="">
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-3 control-label">易支付商户ID</label>
							<div class="col-lg-8">
								<input type="text" name="epay_pid4" class="form-control" value="<?php echo $conf["epay_pid4"];?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-lg-3 control-label">易支付商户密钥</label>
							<div class="col-lg-8">
								<input type="text" name="epay_key4" class="form-control" value="<?php echo $conf["epay_key4"];?>">
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-3 col-sm-8"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
							</div>
						</div>
					</form>
				</div>
			<?php
			}
			?>
			<?php
			if ($conf["alipay_api"] == 1 || $conf["alipay_api"] == 3 || $conf["qqpay_api"] == 1 || $conf["wxpay_api"] == 1 || $conf["wxpay_api"] == 3) {
			?>				<div class="block">
					<div class="block-title">
						<h3 class="panel-title">其他设置</h3>
					</div>
				<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal" role="form">
					<div class="form-group">
						<label class="col-lg-3 control-label">商品名称自定义</label>
						<div class="col-lg-8">
							<input type="text" name="ordername" class="form-control" value="<?php echo $conf["ordername"];?>">
							<font color="green">此选项可以替换官方支付接口的商品名称（不支持易支付），留空使用原商品名称。支持变量值：[time]当前时间戳，[order]支付订单号，[name]原商品名称</font>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-3 col-sm-8"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
						</div>
					</div>
				</form>
			</div>
		<?php
	}
	?></div>
<script>
	var items = $("select[default]");
	for (i = 0; i < items.length; i++) {
		$(items[i]).val($(items[i]).attr("default") || 0);
	}

	function checkURL(obj) {
		var url = $(obj).val();

		if (url.indexOf(" ") >= 0) {
			url = url.replace(/ /g, "");
		}
		if (url.toLowerCase().indexOf("http://") < 0 && url.toLowerCase().indexOf("https://") < 0) {
			url = "http://" + url;
		}
		if (url.slice(url.length - 1) != "/") {
			url = url + "/";
		}
		$(obj).val(url);
	}

	function checkepayurl(var1, var2) {
		if ($("select[name='" + var1 + "']").val() == -1) {
			checkURL("input[name='" + var2 + "']");
		}
		return true;
	}
	$("select[name='payapi']").change(function() {
       // console.log($("input[name='epay_url']").val());
        if ($(this).val() == null){
            $("#epayurl").hide();
            $("#payapi_07").css("display", "none");
        }else if ($(this).val() == -1) {
			$("#payapi_07").css("display", "inherit");
			$("#epayurl").hide();
		} else {
			$("#payapi_07").css("display", "none");
			$("input[name='epay_url']").val($("select[name='payapi']").val());
            $("#epayurl").attr("href", $("input[name='epay_url']").val());
			$("#epayurl").html('点此进入' + $("select[name='payapi'] option:selected").html() + '');
			$("#epayurl").show();
		}

	});
	$("select[name='payapi2']").change(function() {
	    if ($(this).val() == null){
            $("#epayurl2").hide();
            $("#payapi_08").css("display", "none");
		}else if ($(this).val() == -1) {
			$("#payapi_08").css("display", "inherit");
			$("#epayurl2").hide();
		} else {
			$("#payapi_08").css("display", "none");
			$("input[name='epay_url2']").val($("select[name='payapi2']").val());
			$("#epayurl2").attr("href", $("select[name='payapi2']").val());
			$("#epayurl2").html('点此进入' + $("select[name='payapi2'] option:selected").html() + '');
			$("#epayurl2").show();
		}
	});
	$("select[name='payapi3']").change(function() {
	    if ($(this).val() == null){
            $("#epayurl3").hide();
            $("#payapi_09").css("display", "none");
		}else if ($(this).val() == -1) {
			$("#payapi_09").css("display", "inherit");
			$("#epayurl3").hide();
		} else {
			$("#payapi_09").css("display", "none");
			$("input[name='epay_url3']").val($("select[name='payapi3']").val());
			$("#epayurl3").attr("href", $("select[name='payapi3']").val());
			$("#epayurl3").html('点此进入' + $("select[name='payapi3'] option:selected").html() + '');
			$("#epayurl3").show();
		}
	});
	if ($("#epayurl").length > 0 && $("#epayurl").is(':hidden')) {
		$("select[name='payapi']").change();
	}
	if ($("#epayurl2").length > 0 && $("#epayurl2").is(':hidden')) {
		$("select[name='payapi2']").change();
	}
	$('select[name="payapi4"]').change(function() {
		if ($(this).val() == null){
			$('#epayurl4').hide();
			$('#payapi_10').css('display', 'none');
		}else if ($(this).val() == -1) {
			$('#payapi_10').css('display', 'inherit');
			$('#epayurl4').hide();
		} else {
			$('#payapi_10').css('display', 'none');
			$('input[name="epay_url4"]').val($('select[name="payapi4"]').val());
			$('#epayurl4').attr('href', $('select[name="payapi4"]').val());
			$('#epayurl4').html('点此进入' + $('select[name="payapi4"] option:selected').html() + '');
			$('#epayurl4').show();
		}
	});
	if ($("#epayurl3").length > 0 && $("#epayurl3").is(':hidden')) {
		$('select[name="payapi3"]').change();
	}
	if ($("#epayurl4").length > 0 && $("#epayurl4").is(':hidden')) {
		$('select[name="payapi4"]').change();
	}
	$("select[name='micropayapi']").change(function() {
		$.ajax({
			type: "GET",
			url: "ajax.php?act=micropayurl&id=" + $(this).val(),
			dataType: 'json',
			success: function(data) {
				if (data.code == 0) {
					$("#micropayurl").attr("href", data.url);
					$("#micropayurl").html('进入' + $("select[name='micropayapi'] option:selected").html() + '商户申请页面');
					$("#micropayurl").show();
				} else {
					$("#micropayurl").hide();
				}
			}
		});
	});
	if ($("#micropayurl").length > 0 && $("#micropayurl").is(':hidden')) {
		$("select[name='micropayapi']").change();
	}
</script>
<?php
} elseif ($mod == "epay_nn") {
	adminpermission("super", 1);
	$payapi = pay_api(true);
	$account = $_POST["account"];
	$username = $_POST["username"];
	if ($account == NULL || $username == NULL) {
		showmsg("保存错误,请确保每项都不为空!", 3);
	} else {
		$data = get_curl($payapi . "api.php?act=change&pid=" . $conf["epay_pid"] . "&key=" . $conf["epay_key"] . "&account=" . $account . "&username=" . $username . "&url=" . $_SERVER["HTTP_HOST"]);
		$arr = json_decode($data, true);
		if ($arr["code"] == 1) {
			showmsg("修改成功!", 1);
		} else {
			showmsg($arr["msg"]);
		}
	}
} elseif ($mod == "codepay") {
	adminpermission("super", 1);
	?>		<div class="block">
			<div class="block-title">
				<h3 class="panel-title">USDT-TRC20支付配置</h3>
			</div>
			<form action="./set.php?mod=codepay_n" method="post" class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
				<div class="form-group">
					<label class="col-lg-3 control-label">USDT钱包地址</label>
					<div class="col-lg-8">
						<input type="text" name="codepay_key" class="form-control" value="<?php echo $conf["codepay_key"];?>" required>
						<font color="green">请填写您的TRC20格式USDT钱包地址</font>
					</div>
				</div>
				<div class="form-group">
					<label class="col-lg-3 control-label">支付设置</label>
					<div class="col-lg-8">
						<input type="text" name="codepay_id" class="form-control" value="<?php echo $conf["codepay_id"];?>" placeholder="格式：有效期|汇率" required>
						<font color="green">有效期单位为分钟，汇率为USDT对人民币的汇率，例如：30|7.2</font>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-8">
						<input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" /><br />
					</div>
				</div>
			</form>
		</div>
		<?php
} elseif ($mod == "epay") {
	adminpermission("super", 1);
	if (!empty($conf["epay_pid"]) && !empty($conf["epay_key"])) {
		$payapi = pay_api(true);
		$data = get_curl($payapi . "api.php?act=query&pid=" . $conf["epay_pid"] . "&key=" . $conf["epay_key"] . "&url=" . $_SERVER["HTTP_HOST"]);
		$arr = json_decode($data, true);
		if ($arr["code"] == -2) {
			showmsg("易支付KEY校验失败！");
		} elseif (!array_key_exists("code", $arr)) {
			showmsg("获取失败，请刷新重试！");
		}
		$stype = $arr["stype"] ?: "支付宝";
	} else {
		showmsg("你还未填写易支付商户ID和密钥，请返回填写！");
	}
	if (array_key_exists("active", $arr) && $arr["active"] == 0) {
		showmsg("该商户已被封禁");
	}
	$key = substr($arr["key"], 0, 8) . "****************" . substr($arr["key"], 24, 32);
	if (!file_exists("pay.lock")) {
		file_put_contents("pay.lock", "pay.lock");
	}
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">易支付设置</h3>
		</div>
		<div class="">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#">易支付设置</a></li>
				<li><a href="./set.php?mod=epay_order">订单记录</a></li>
				<li><a href="./set.php?mod=epay_settle">结算记录</a></li>
			</ul>
			<form action="./set.php?mod=epay_nn" method="post" class="form-horizontal" role="form">
				<h4>商户信息查看：</h4>
				<div class="form-group">
					<label class="col-sm-2 control-label">商户ID</label>
					<div class="col-sm-10"><input type="text" name="pid" value="<?php echo $arr["pid"];?>" class="form-control" disabled /></div>
				</div><br />
				<div class="form-group">
					<label class="col-sm-2 control-label">商户KEY</label>
					<div class="col-sm-10"><input type="text" name="key" value="<?php echo $key;?>" class="form-control" disabled /></div>
				</div><br />
				<div class="form-group">
					<label class="col-sm-2 control-label">商户余额</label>
					<div class="col-sm-10"><input type="text" name="money" value="<?php echo $arr["money"];?>" class="form-control" disabled /></div>
				</div><br />
				<h4>收款账号设置：</h4>
				<div class="form-group">
					<label class="col-sm-2 control-label">结算方式</label>
					<div class="col-sm-10"><input type="text" name="type" value="<?php echo $stype;?>" class="form-control" disabled /></div>
				</div><br />
				<div class="form-group">
					<label class="col-sm-2 control-label">结算账号</label>
					<div class="col-sm-10"><input type="text" name="account" value="<?php echo $arr["account"];?>" class="form-control" /></div>
				</div><br />
				<div class="form-group">
					<label class="col-sm-2 control-label">真实姓名</label>
					<div class="col-sm-10"><input type="text" name="username" value="<?php echo $arr["username"];?>" class="form-control" /></div>
				</div><br />
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="确定修改" class="btn btn-primary form-control" /><br />
					</div>
				</div>
				<h4><span class="fa fa-info-circle"></span> 注意事项</h4>
				1.结算账号和真实姓名请仔细核对，一旦错误将无法结算到账！<br />2.每笔交易会有<?php echo 100 - $arr["money_rate"];?>%的手续费，这个手续费是支付宝、微信和财付通收取的，非本接口收取。<br />3.结算为T+1规则，当天满<?php echo $arr["settle_money"];?>元在第二天会自动结算
			</form>
		</div>
	</div>
<?php
} elseif ($mod == "epay_settle") {
	adminpermission("set", 1);
	$payapi = pay_api(true);
	$data = get_curl($payapi . "api.php?act=settle&pid=" . $conf["epay_pid"] . "&key=" . $conf["epay_key"] . "&limit=20&url=" . $_SERVER["HTTP_HOST"]);
	$arr = json_decode($data, true);
	if ($arr["code"] == -2) {
		showmsg("易支付KEY校验失败！");
	}
	?>	<div class="block">
		<div class="block-title w h">
			<h3 class="panel-title">易支付结算记录</h3>
		</div>
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>ID</th>
						<th>结算账号</th>
						<th>结算金额</th>
						<th>手续费</th>
						<th>结算时间</th>
					</tr>
				</thead>
				<tbody>
					<?php
	foreach ($arr["data"] as $res) {
		echo "<tr><td><b>" . $res["id"] . "</b></td><td>" . $res["account"] . "</td><td><b>" . $res["money"] . "</b></td><td><b>" . $res["fee"] . "</b></td><td>" . $res["time"] . "</td></tr>";
	}
	?>				</tbody>
			</table>
		</div>
	</div>
<?php
} elseif ($mod == "epay_order") {
	adminpermission("set", 1);
	$payapi = pay_api(true);
	$data = get_curl($payapi . "api.php?act=orders&pid=" . $conf["epay_pid"] . "&key=" . $conf["epay_key"] . "&limit=30&url=" . $_SERVER["HTTP_HOST"]);
	$arr = json_decode($data, true);
	if ($arr["code"] == -2) {
		showmsg("易支付KEY校验失败！");
	}
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">易支付订单记录</h3>
		</div>订单只展示前30条[<a href="set.php?mod=epay">返回</a>]
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>交易号/商户订单号</th>
						<th>付款方式</th>
						<th>商品名称/金额</th>
						<th>创建时间/完成时间</th>
						<th>状态</th>
					</tr>
				</thead>
				<tbody>
					<?php
	foreach ($arr["data"] as $res) {
		echo "<tr><td>" . $res["trade_no"] . "<br/>" . $res["out_trade_no"] . "</td><td>" . $res["type"] . "</td><td>" . $res["name"] . "<br/>￥ <b>" . $res["money"] . "</b></td><td>" . $res["addtime"] . "<br/>" . $res["endtime"] . "</td><td>" . ($res["status"] == 1 ? "<font color=green>已完成</font>" : "<font color=red>未完成</font>") . "</td></tr>";
	}
	?>				</tbody>
			</table>
		</div>
	</div>
<?php
} elseif ($mod == "template") {
	adminpermission("set", 1);
	$mblist = \lib\Template::getList();
	?>	<style>
		.mblist {
			margin-bottom: 20px;
		}

		.mblist img {
			height: 110px;
			margin: 0 auto;
		}
	</style>
	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">首页模板设置</h3>
		</div>
		<div class="">
			<ul class="nav nav-tabs">
				<li style="width: 50%;" align="center" class="active">
					<a href="./set.php?mod=template">切换模板</a>
				</li>
				<li style="width: 50%;" align="center" class="">
					<a href="./set.php?mod=template2">模板设置</a>
				</li>
			</ul>
			<h4>当前使用模板：</h4>
			<div class="row text-center">
				<div class="col-xs-6 col-sm-4">
					<img class="img-responsive img-thumbnail img-rounded" src="../template/<?php echo $conf["template"];?>/preview.png" loading="lazy" onerror="this.src='../assets/img/NoImg.png'" style="min-height: 150px; background: #f5f5f5; display: block;">
				</div>
				<div class="col-xs-6 col-sm-4">
					<p>模板名称：<?php echo \lib\Template::getDisplayName($conf["template"]);?></p>
					<p><a href="./set.php?mod=template2">进入模板设置</a></p>
				</div>
			</div>
			<hr />
			<h4>更换模板：</h4>
			<div class="row text-center">
				<?php
	foreach ($mblist as $row) {
		$templateDisplayName = \lib\Template::getDisplayName($row);
		?>					<div class="col-xs-6 col-sm-4 mblist">
						<a href="javascript:changeTemplate('<?php echo $row;?>')"><img class="img-responsive img-thumbnail img-rounded" src="../template/<?php echo $row;?>/preview.png" loading="lazy" onerror="this.src='../assets/img/NoImg.png'" title="点击更换到该模板" style="min-height: 150px; background: #f5f5f5; display: block;"><br /><strong><?php echo $templateDisplayName;?></strong></a>
					</div>
				<?php
	}
	?>			</div>
		</div>
		<div class="panel-footer">
			<span class="fa fa-info-circle"></span>
			网站模板对应template目录里面的名称，会自动获取
		</div>
	</div>
<?php
} elseif ($mod == "template2") {
	adminpermission("set", 1);
	$mblist = \lib\Template::getList();
	$template_settings = \lib\Template::loadSetting();
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">首页模板设置</h3>
		</div>
		<div class="">
			<ul class="nav nav-tabs">
				<li style="width: 50%;" align="center" class="">
					<a href="./set.php?mod=template">切换模板</a>
				</li>
				<li style="width: 50%;" align="center" class="active">
					<a href="./set.php?mod=template2">模板设置</a>
				</li>
			</ul>
			<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
				<div class="form-group">
					<label class="col-sm-2 control-label">公共静态资源CDN</label>
					<div class="col-sm-10"><select class="form-control" name="cdnpublic" default="<?php echo $conf["cdnpublic"];?>">
							<option value="0">七牛云CDN</option>
							<option value="1">360CDN</option>
							<option value="2">BootCDN</option>
							<option value="4">今日头条CDN</option>
						</select></div>
				</div>
				<div class="form-group">
					<label class="col-lg-3 control-label">专有静态资源CDN域名</label>
					<div class="col-lg-8">
						<input type="text" name="staticurl" class="form-control" value="<?php echo $conf["staticurl"];?>" placeholder="直接填写域名即可，不使用请留空" />
						<pre>不懂请勿乱填，否则会导致页面显示不正常</pre>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">手机使用模板</label>
					<div class="col-sm-10"><select class="form-control" name="template_m" default="<?php echo $conf["template_m"];?>">
							<option value="0">与电脑版相同（默认）</option>
							<?php
	foreach ($mblist as $row) {
		?>								<option value="<?php echo $row;?>"><?php echo \lib\Template::getDisplayName($row);?></option>
							<?php
	}
	?>						</select></div>
				</div>
				<?php
	if ($template_settings) {
		foreach ($template_settings as $k => $v) {
			if ($v["type"]) {
				?>							<div class="form-group">
								<label class="col-sm-2 control-label"><?php echo $v["name"];?></label>
								<div class="col-sm-10">
									<?php
				if ($v["type"] == "input") {
					?>										<input type="text" name="<?php echo $k;?>" value="﻿<?php echo $conf[$k];?>" class="form-control" placeholder="<?php echo $v["note"];?>" />
									<?php
				} elseif ($v["type"] == "textarea") {
					?>										<textarea class="form-control" name="<?php echo $k;?>" rows="5" style="width:100%;" placeholder="<?php echo $v["note"];?>"><?php echo htmlspecialchars($conf[$k]);?></textarea>
									<?php
				} elseif ($v["type"] == "select") {
					?>										<select class="form-control" name="<?php echo $k;?>" default="<?php echo isset($conf[$k]) && $conf[$k] !== '' ? $conf[$k] : $v["default"];?>">
											<?php
					foreach ($v["options"] as $optionk => $optionv) {
						?>												<option value="<?php echo $optionk;?>"><?php echo $optionv;?></option>
											<?php
					}
					?>										</select>
									<?php
				}
				?>								</div>
							</div>
				<?php
			}
		}
	}
	?>				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
					</div>
				</div>
			</form>
		</div>
	</div>
<?php
} elseif ($mod == "oauth") {
	adminpermission("set", 1);
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">快捷登录配置</h3>
		</div>
		<div class="">
			<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
				<div class="form-group">
					<label class="col-sm-3 control-label">QQ快捷登录方式</label>
					<div class="col-sm-9"><select class="form-control" name="login_qq" default="<?php echo $conf["login_qq"];?>">
							<option value="0">关闭</option>
							<option value="1">安享聚合登录</option>
							<option value="2">手机QQ扫码登录</option>
						</select></div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">微信快捷登录方式</label>
					<div class="col-sm-9"><select class="form-control" name="login_wx" default="<?php echo $conf["login_wx"];?>">
							<option value="0">关闭</option>
							<option value="1">安享聚合登录</option>
						</select></div>
				</div>
				<div id="frame_set3" style="display:none;">
					<div class="form-group">
						<label class="col-sm-3 control-label">API接口地址</label>
						<div class="col-sm-9"><input type="text" name="login_apiurl" value="<?php echo $conf["login_apiurl"];?>" class="form-control" placeholder="API地址要以http://或https://开头，以/结尾" /></div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">应用APPID</label>
						<div class="col-sm-9"><input type="text" name="login_appid" value="<?php echo $conf["login_appid"];?>" class="form-control" /></div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">应用APPKEY</label>
						<div class="col-sm-9"><input type="text" name="login_appkey" value="<?php echo $conf["login_appkey"];?>" class="form-control" /></div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-9"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
					</div>
			</form>
		</div>
		<div class="panel-footer">
			<span class="fa fa-info-circle"></span>
			QQ快捷登录接口是使用安享聚合登录系统搭建的站点，并非QQ互联官方接口。<br />
			QQ快捷登录开启后请勿随意更换登录API站点，否则会导致之前以QQ快捷登录注册的用户全部无法登录。<br />
			手机QQ扫码登录使用更方便，登录凭证以用户注册时填写的QQ为准
		</div>
	</div>
	<script>
		$("select[name='login_qq']").change(function() {
			if ($("select[name='login_qq']").val() == 1 || $("select[name='login_wx']").val() == 1) {
				$("#frame_set3").css("display", "inherit");
			} else {
				$("#frame_set3").css("display", "none");
			}
		});
		$("select[name='login_wx']").change(function() {
			if ($("select[name='login_qq']").val() == 1 || $("select[name='login_wx']").val() == 1) {
				$("#frame_set3").css("display", "inherit");
			} else {
				$("#frame_set3").css("display", "none");
			}
		});
	</script>
<?php
} elseif ($mod == "captcha_n" && $_POST["do"] == "submit") {
	adminpermission("set", 1);
	$captcha_open = $_POST["captcha_open"];
	$captcha_id = $_POST["captcha_id"];
	$captcha_key = $_POST["captcha_key"];
	$captcha_open_free = $_POST["captcha_open_free"];
	$captcha_open_reg = $_POST["captcha_open_reg"];
	$captcha_open_login = $_POST["captcha_open_login"];
	$login_limit_enable = $_POST["login_limit_enable"];
	$login_limit_max = $_POST["login_limit_max"];
	$login_limit_time = $_POST["login_limit_time"];
	saveSetting("captcha_open", $captcha_open);
	saveSetting("captcha_id", $captcha_id);
	saveSetting("captcha_key", $captcha_key);
	saveSetting("captcha_open_free", $captcha_open_free);
	saveSetting("captcha_open_reg", $captcha_open_reg);
	saveSetting("captcha_open_login", $captcha_open_login);
	saveSetting("login_limit_enable", $login_limit_enable);
	saveSetting("login_limit_max", $login_limit_max);
	saveSetting("login_limit_time", $login_limit_time);
	$ad = $CACHE->clear();
	if ($ad) {
		showmsg("修改成功！", 1);
	} else {
		showmsg("修改失败！<br/>" . $DB->error(), 4);
	}
} elseif ($mod == "captcha") {
	adminpermission("set", 1);
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">滑动验证码设置</h3>
		</div>
		<div class="">
			<form action="./set.php?mod=captcha_n" method="post" class="form-horizontal form-bordered" role="form"><input type="hidden" name="do" value="submit" />
				<div class="form-group">
					<label class="col-sm-2 control-label">验证码选择</label>
					<div class="col-sm-10"><select class="form-control" name="captcha_open" default="<?php echo $conf["captcha_open"];?>">
							<option value="0">关闭</option>
							<option value="1">极限滑动验证码</option>
							<option value="2">顶象滑动验证码</option>
							<option value="3">VAPTCHA手势验证码</option>
						</select></div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">接口ID</label>
					<div class="col-sm-10"><input type="text" name="captcha_id" value="<?php echo $conf["captcha_id"];?>" class="form-control" /></div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">接口KEY</label>
					<div class="col-sm-10"><input type="text" name="captcha_key" value="<?php echo $conf["captcha_key"];?>" class="form-control" /></div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">开启验证场景</label>
					<div class="col-sm-10">
						<label><input name="captcha_open_free" type="checkbox" value="1" <?php echo $conf["captcha_open_free"] == 1 ? " checked" : null;?> /> 购买免费商品</label><br />
						<label><input name="captcha_open_reg" type="checkbox" value="1" <?php echo $conf["captcha_open_reg"] == 1 ? " checked" : null;?> /> 用户注册</label><br />
						<label><input name="captcha_open_login" type="checkbox" value="1" <?php echo $conf["captcha_open_login"] == 1 ? " checked" : null;?> /> 用户登录</label><br />
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
					</div>
				</div>
			</form>
		</div>
		<div class="panel-footer">
			<span class="fa fa-info-circle"></span>
			极限验证码：<a href="https://www.geetest.com/Register" rel="noreferrer" target="_blank">点击进入</a> （免费版每小时限流，需人工审核）<br />
			顶象验证码：<a href="https://www.dingxiang-inc.com/business/captcha" rel="noreferrer" target="_blank">点击进入</a> （收费的，可免费试用）<br />
			VAPTCHA手势验证码：<a href="https://www.vaptcha.com/" rel="noreferrer" target="_blank">点击进入</a> （目前完全免费）<br />
			选择极限验证码，然后ID和KEY留空保存，即可直接免费使用公共接口(测试中)
		</div>
	</div>
	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">登录失败限制设置</h3>
		</div>
		<div class="">
			<form action="./set.php?mod=captcha_n" method="post" class="form-horizontal form-bordered" role="form"><input type="hidden" name="do" value="submit" />
				<div class="form-group">
					<label class="col-sm-3 control-label">启用登录失败限制</label>
					<div class="col-sm-9"><select class="form-control" name="login_limit_enable" default="<?php echo isset($conf['login_limit_enable'])?$conf['login_limit_enable']:0;?>">
							<option value="0">关闭</option>
							<option value="1">开启</option>
						</select>
						<span class="help-block">开启后，当用户登录密码连续错误达到指定次数时，将限制该IP一段时间内不能登录</span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">允许密码错误次数</label>
					<div class="col-sm-9"><input type="number" name="login_limit_max" value="<?php echo isset($conf['login_limit_max'])?$conf['login_limit_max']:5;?>" class="form-control" min="1" max="100" />
						<span class="help-block">连续密码错误达到此次数后，将限制登录。建议设置为3-10次</span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">限制登录时长（分钟）</label>
					<div class="col-sm-9"><input type="number" name="login_limit_time" value="<?php echo isset($conf['login_limit_time'])?$conf['login_limit_time']:30;?>" class="form-control" min="1" max="1440" />
						<span class="help-block">密码错误次数达到后，限制登录的时长。单位：分钟，建议设置为15-60分钟</span>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-9"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
					</div>
				</div>
			</form>
		</div>
		<div class="panel-footer">
			<span class="fa fa-info-circle"></span>
			登录失败限制功能说明：<br />
			- 根据IP地址进行限制，同一IP在规定时间内密码连续错误达到次数后将无法登录<br />
			- 限制时间到期后会自动解除限制<br />
			- 建议根据网站安全需求合理设置次数和时长
		</div>
	</div>
	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">用户IP地址获取设置</h3>
		</div>
		<div class="">
			<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
				<div class="form-group">
					<label class="col-sm-2 control-label">用户IP地址获取方式</label>
					<div class="col-sm-10"><select class="form-control" name="ip_type" default="<?php echo $conf["ip_type"];?>"></select></div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
					</div>
				</div>
			</form>
		</div>
		<div class="panel-footer">
			<span class="fa fa-info-circle"></span>
			此功能设置用于防止用户伪造IP请求造成免费商品无限制下单。<br />
			X_FORWARDED_FOR：之前的获取真实IP方式，极易被伪造IP<br />
			X_REAL_IP：在网站使用CDN的情况下选择此项，在不使用CDN的情况下也会被伪造<br />
			REMOTE_ADDR：直接获取真实请求IP，无法被伪造，但可能获取到的是CDN节点IP<br />
			<b>你可以从中选择一个能显示你真实地址的IP，优先选下方的选项。</b>
		</div>
	</div>
	<script>
		$(document).ready(function() {
			$.ajax({
				type: "GET",
				url: "ajax.php?act=iptype",
				dataType: 'json',
				async: true,
				success: function(data) {
					$("select[name='ip_type']").empty();
					var defaultv = $("select[name='ip_type']").attr('default');
					$.each(data, function(k, item) {
						$("select[name='ip_type']").append('<option value="' + k + '" ' + (defaultv == k ? 'selected' : '') + '>' + item.name + ' - ' + item.ip + ' ' + item.city + '</option>');
					})
				}
			});
		})
	</script>
<?php
} elseif ($mod == "delwxqrcode") {
	adminpermission("set", 1);
	if (file_exists(ROOT . "assets/img/wxqrcode.png")) {
		unlink(ROOT . "assets/img/wxqrcode.png");
	}
	exit("<script language='javascript'>alert('删除成功');window.location.href='./set.php?mod=upwxqrcode';</script>");
} elseif ($mod == "upwxqrcode") {
	adminpermission("set", 1);
	if ($_POST["s"] == 1) {
		// 检查文件类型
		$allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
		$file_type = $_FILES["file"]["type"];
		$file_ext = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
		$allowed_exts = array('jpg', 'jpeg', 'png', 'gif', 'webp');

		if (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_exts)) {
			showmsg("只允许上传JPG、PNG、GIF、WEBP格式的图片文件！", 4);
		}

		// 检查文件大小（限制为2MB）
		$max_size = 2 * 1024 * 1024;
		if ($_FILES["file"]["size"] > $max_size) {
			showmsg("文件大小不能超过2MB！", 4);
		}

		// 检查文件是否为真实图片
		$image_info = getimagesize($_FILES["file"]["tmp_name"]);
		if (!$image_info) {
			showmsg("请上传真实的图片文件！", 4);
		}

		// 保存文件，使用固定文件名
		if (move_uploaded_file($_FILES["file"]["tmp_name"], ROOT . "assets/img/wxqrcode.png")) {
			showmsg("上传成功！", 1);
		} else {
			showmsg("上传失败！", 4);
		}
	}
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">上传客服微信二维码</h3>
		</div>
		<div class="admin-upload-card">

			<form action="set.php?mod=upwxqrcode" method="POST" enctype="multipart/form-data" class="admin-upload-form">
				<label for="file" class="admin-upload-label">选择客服微信二维码图片</label>
				<input type="file" name="file" id="file" accept="image/*" class="form-control-file" />
				<input type="hidden" name="s" value="1" />
				<div class="admin-upload-actions">
					<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> 确认上传</button>
					<a href="./set.php?mod=delwxqrcode" class="btn btn-danger"><i class="fa fa-trash"></i> 删除图片</a>
				</div>
			</form><br>
			<div class="admin-upload-preview">现在的客服微信二维码：<br><img src="../assets/img/wxqrcode.png?r=<?php echo rand(10000, 99999);?>"></div>
		</div>
	</div>
<?php
} elseif ($mod == "upimg") {
	adminpermission("set", 1);
	$q8DefaultLogoHref = function_exists('q8_brand_logo_href') ? q8_brand_logo_href() : '/assets/img/logo.png?r=74129';
	if ($_POST["s"] == 1) {
		// 检查文件类型
		$allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
		$file_type = $_FILES["file"]["type"];
		$file_ext = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
		$allowed_exts = array('jpg', 'jpeg', 'png', 'gif', 'webp');

		if (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_exts)) {
			showmsg("只允许上传JPG、PNG、GIF、WEBP格式的图片文件！", 4);
		}

		// 检查文件大小（限制为2MB）
		$max_size = 2 * 1024 * 1024;
		if ($_FILES["file"]["size"] > $max_size) {
			showmsg("文件大小不能超过2MB！", 4);
		}

		// 检查文件是否为真实图片
		$image_info = getimagesize($_FILES["file"]["tmp_name"]);
		if (!$image_info) {
			showmsg("请上传真实的图片文件！", 4);
		}

		// 保存文件，使用固定文件名
		if (move_uploaded_file($_FILES["file"]["tmp_name"], ROOT . "assets/img/logo.png")) {
			showmsg("上传成功！", 1);
		} else {
			showmsg("上传失败！", 4);
		}
	}
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">更改首页LOGO</h3>
			<div class="block-options pull-right"><a class="btn btn-default" href="set.php?mod=upbgimg">更改背景图</a></div>
		</div>
		<div class="">
			<form action="set.php?mod=upimg" method="POST" enctype="multipart/form-data">
				<label for="file"></label>
				<input type="file" name="file" id="file" accept="image/*" />
				<input type="hidden" name="s" value="1" /><br>
				<input type="submit" class="btn btn-primary btn-block" value="确认上传" />
			</form>
			<br>现在的图片：<br>
			<img src="<?php echo htmlspecialchars($q8DefaultLogoHref, ENT_QUOTES, 'UTF-8');?>" style="max-width:100%" alt="默认Logo">
		</div>
	</div>
<?php
} elseif ($mod == "upbgimg") {
	adminpermission("set", 1);
	if ($_POST["s"] == 1) {
		// 检查文件类型
		$allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
		$file_type = $_FILES["file"]["type"];
		$file_ext = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
		$allowed_exts = array('jpg', 'jpeg', 'png', 'gif', 'webp');

		if (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_exts)) {
			showmsg("只允许上传JPG、PNG、GIF、WEBP格式的图片文件！", 4);
		}

		// 检查文件大小（限制为5MB）
		$max_size = 5 * 1024 * 1024;
		if ($_FILES["file"]["size"] > $max_size) {
			showmsg("文件大小不能超过5MB！", 4);
		}

		// 检查文件是否为真实图片
		$image_info = getimagesize($_FILES["file"]["tmp_name"]);
		if (!$image_info) {
			showmsg("请上传真实的图片文件！", 4);
		}

		// 保存文件，使用固定文件名
		if (move_uploaded_file($_FILES["file"]["tmp_name"], ROOT . "assets/img/bj.png")) {
			showmsg("上传成功！", 1);
		} else {
			showmsg("上传失败！", 4);
		}
	}
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">更改首页背景图</h3>
			<div class="block-options pull-right"><a class="btn btn-default" href="set.php?mod=upimg">更改LOGO</a></div>
		</div>
		<div class="">

			<form action="set.php?mod=upbgimg" method="POST" enctype="multipart/form-data">
				<label for="file"></label>
				<input type="file" name="file" id="file" accept="image/*" />
				<input type="hidden" name="s" value="1" /><br>
				<input type="submit" class="btn btn-primary btn-block" value="确认上传" />
			</form><br>现在的图片：<br><img src="../assets/img/bj.png?r=<?php echo rand(10000, 99999);?>" style="max-width:100%">
		</div>
	</div>
<?php
} elseif ($mod == "cron") {
	adminpermission("set", 1);
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">计划任务配置</h3>
		</div>
		<div class="">
			<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal" role="form">
				<div class="form-group">
					<label class="col-sm-3 control-label">监控密钥</label>
					<div class="col-sm-9"><input type="text" name="cronkey" value="<?php echo $conf["cronkey"];?>" class="form-control" placeholder="" /></div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-9"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" /><br />
					</div><br />
				</div>
			</form>
		</div>
	</div>
	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">计划任务列表</h3>
		</div>
		<div class="">
			<p>请按自己的需要监控以下网址。只能在一个地方监控，千万不要多节点监控或在多处监控，否则会导致数据错乱！</p>
			<p>每日数据库维护+排行榜奖励发放+提成余额延迟到账（每天0点后执行2次）：</p>
			<li class="list-group-item"><?php echo $siteurl;?>cron.php?do=daily&amp;key=<?php echo $conf["cronkey"];?></li>
			</br />
			<p>社区价格监控（10到60分钟一次）：</p>
			<li class="list-group-item"><?php echo $siteurl;?>cron.php?do=pricejk&amp;key=<?php echo $conf["cronkey"];?></li>
			</br />
			<p>易支付订单补单监控（如果易支付订单已完成，但本站无订单可以监控以下url补单，大约5分钟一次。如果易支付订单也显示未完成，或者其他情况使用该监控均无法补单）：</p>
			<li class="list-group-item"><?php echo $siteurl;?>cron.php?key=<?php echo $conf["cronkey"];?>&id=1</li>
			<li class="list-group-item"><?php echo $siteurl;?>cron.php?key=<?php echo $conf["cronkey"];?>&id=2</li>
			</br />
			<p>订单状态检测：</p>
			<li class="list-group-item"><?php echo $siteurl;?>cron.php?do=updatestatus&amp;key=<?php echo $conf["cronkey"];?></li>
			</br />
		</div>
	</div>
<?php
} elseif ($mod == "qiandao") {
	adminpermission("set", 1);
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">签到模块设置</h3>
		</div>
		<div class="">
			<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal form-bordered" role="form">
				<div class="form-group">
					<label class="col-sm-2 control-label">奖励余额初始值</label>
					<div class="col-sm-10"><input type="text" name="qiandao_reward" value="<?php echo $conf["qiandao_reward"];?>" class="form-control" /></div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">每日递增倍数</label>
					<div class="col-sm-10"><input type="text" name="qiandao_mult" value="<?php echo $conf["qiandao_mult"];?>" class="form-control" /></div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">最多递增天数</label>
					<div class="col-sm-10"><input type="text" name="qiandao_day" value="<?php echo $conf["qiandao_day"];?>" class="form-control" /></div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">是否限制每个IP签到一次</label>
					<div class="col-sm-10"><select class="form-control" name="qiandao_limitip" default="<?php echo $conf["qiandao_limitip"];?>">
							<option value="0">否</option>
							<option value="1">是</option>
						</select></div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">是否开启虚拟签到消息</label>
					<div class="col-sm-10"><select class="form-control" name="qiandao_virtual" default="<?php echo $conf["qiandao_virtual"];?>">
							<option value="0">否</option>
							<option value="1">是</option>
						</select></div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
					</div>
				</div>
			</form>
		</div>
		<div class="panel-footer">
			<span class="fa fa-info-circle"></span>奖励余额初始值填写一个值代表所有类型分站都一样，填写3个值并用|隔开代表不同类型分站不一样，例如0.01|0.02|0.03 分别是普通用户、普及版分站、专业版分站的奖励余额初始值。
		</div>
	</div>
	<div class="block">
		<div class="block-title">
			<h3 class="panel-title" id="title">签到统计</h3>
		</div>
		<div class="">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<th style="font-size: 13px;" class="text-center">
							<i class="fa fa-user-circle-o"></i> 今日签到<br><span id="count1"></span>人
						</th>
						<th style="font-size: 13px;" class="text-center">
							<i class="fa fa-user-circle"></i> 昨日签到<br><span id="count2"></span>人
						</th>
						<th style="font-size: 13px;" class="text-center">
							<i class="fa fa-pie-chart"></i> 累计签到<br><span id="count3"></span>人
						</th>
					</tr>
					<tr>
						<th style="font-size: 13px;" class="text-center">
							<i class="fa fa-money"></i> 今日送出余额<br><span id="count4"></span>元
						</th>
						<th style="font-size: 13px;" class="text-center">
							<i class="fa fa-money"></i> 昨日送出余额<br><span id="count5"></span>元
						</th>
						<th style="font-size: 13px;" class="text-center">
							<i class="fa fa-bar-chart"></i> 累计送出余额<br><span id="count6"></span>元
						</th>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<script>
		$(document).ready(function() {
			$('#title').html('正在加载数据中...');
			$.ajax({
				type: "GET",
				url: "ajax.php?act=qdcount",
				dataType: 'json',
				async: true,
				success: function(data) {
					$('#count1').html(data.count1);
					$('#count2').html(data.count2);
					$('#count3').html(data.count3);
					$('#count4').html(data.count4);
					$('#count5').html(data.count5);
					$('#count6').html(data.count6);
					$('#title').html('签到统计');
				}
			});
		})
	</script>
	<?php
	$qiandao_rows = $DB->getAll("SELECT q.*,s.user,s.power,s.sitename FROM pre_qiandao q LEFT JOIN pre_site s ON q.zid=s.zid ORDER BY q.id DESC LIMIT 50");
	?>
	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">&#26368;&#36817;&#31614;&#21040;&#35760;&#24405;</h3>
		</div>
		<div class="table-responsive">
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>ID</th>
						<th>ZID</th>
						<th>&#29992;&#25143;</th>
						<th>QQ</th>
						<th>&#22870;&#21169;</th>
						<th>&#36830;&#32493;</th>
						<th>IP</th>
						<th>&#31614;&#21040;&#26102;&#38388;</th>
					</tr>
				</thead>
				<tbody>
					<?php if(empty($qiandao_rows)){ ?>
					<tr><td colspan="8" class="text-center text-muted">&#26242;&#26080;&#31614;&#21040;&#35760;&#24405;</td></tr>
					<?php } else { foreach($qiandao_rows as $qrow) { ?>
					<tr>
						<td><?php echo intval($qrow['id']);?></td>
						<td><a href="./sitelist.php?zid=<?php echo intval($qrow['zid']);?>"><?php echo intval($qrow['zid']);?></a></td>
						<td><?php echo htmlspecialchars($qrow['user'] ? $qrow['user'] : '-', ENT_QUOTES);?><?php if($qrow['power'] > 0){ ?><span class="label label-info" style="margin-left:6px;">&#20998;&#31449;</span><?php } ?></td>
						<td><?php echo htmlspecialchars($qrow['qq'], ENT_QUOTES);?></td>
						<td><span class="text-danger">+<?php echo htmlspecialchars($qrow['reward'], ENT_QUOTES);?></span></td>
						<td><?php echo intval($qrow['continue']);?>&#22825;</td>
						<td><?php echo htmlspecialchars($qrow['ip'], ENT_QUOTES);?></td>
						<td><?php echo htmlspecialchars($qrow['time'], ENT_QUOTES);?></td>
					</tr>
					<?php }} ?>
				</tbody>
			</table>
		</div>
	</div>
<?php
} elseif ($mod == "invitegift") {
	adminpermission("set", 1);
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">邀请返利设置</h3>
		</div>
		<div class="">
			<form onsubmit="return saveSetting(this)" method="post" class="form" role="form">
				<div class="form-group">
					<label>开启邀请返利功能</label>
					<select class="form-control" name="invitegift_open" default="<?php echo $conf["invitegift_open"];?>">
						<option value="0">关闭</option>
						<option value="1">开启</option>
					</select>
				</div>
				<div class="form-group">
					<label>广告语自定义</label>
					<textarea class="form-control" name="invitegift_content" rows="5" style="width:100%;"><?php echo htmlspecialchars($conf["invitegift_content"]);?></textarea>
					<pre>其中，商品名称用[name]，商品价格用[price]，返利链接用[url]，会自动替换</pre>
				</div>
				<div class="form-group">
					<input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" /><br />
					<a href="./invitegift.php" class="btn btn-info btn-block">进入返利商品列表</a>
				</div>
			</form>
		</div>
		<div class="panel-footer">
			<span class="fa fa-info-circle"></span>
			开启后，前台分享商品海报，分享商品链接，只要是返利商品便自动切换成返利链接
		</div>
	</div>
<?php
} elseif ($mod == "invite") {
	adminpermission("set", 1);
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">推广链接设置</h3>
		</div>
		<div class="">
			<form onsubmit="return saveSetting(this)" method="post" class="form" role="form">
				<div class="form-group">
					<label>开启推广链接功能</label>
					<select class="form-control" name="invite_tid" default="<?php echo $conf["invite_tid"];?>">
						<option value="0">关闭</option>
						<option value="1">开启</option>
					</select>
				</div>
				<div class="form-group">
					<label>广告语自定义</label>
					<textarea class="form-control" name="invite_content" rows="5" style="width:100%;"><?php echo htmlspecialchars($conf["invite_content"]);?></textarea>
					<pre>其中，推广链接用[url]，会自动替换</pre>
				</div>
				<div class="form-group">
					<input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" /><br />
					<a href="./invite.php" class="btn btn-info btn-block">进入推广商品列表</a><br />
					<a href="./invitelog.php" class="btn btn-default btn-block">查看推广记录</a>
				</div>
			</form>
		</div>
		<div class="panel-footer">
			<span class="fa fa-info-circle"></span>
			推广链接生成地址：/?mod=invite<br />
			推广页面模板文件：/template/default/invite.php<br />
			如果使用累计次数推广模式，建议先设置好<a href="./set.php?mod=captcha">用户IP地址获取设置</a>，相同IP地址算一次访问。
		</div>
	</div>
<?php
} elseif ($mod == "dwz") {
	adminpermission("set", 1);
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">防红链接生成接口设置</h3>
		</div>
		<div class="">
			<form onsubmit="return saveSetting(this)" method="post" class="form" role="form">
			    <div class="form-group">
	    <label>防红接口选择：</label>
		<select class="form-control" name="fanghong_api" default="<?php echo $conf["fanghong_api"];?>">
		  <option value="0">不使用防红接口</option>
		  <option value="1">安享官方防红</option>
		  <option value="9">自定义防红接口</option>
		</select>
	</div>
	<div class="form-group" id="fanghong_gf" style="<?php echo $conf["fanghong_api"] == 9 || $conf["fanghong_api"] == 0 ? "display:none;" : NULL;?>">
	    <div class="form-group">
                        <div class="input-group">

                            <div class="input-group-addon">短网址类型</div>
                            <input type="text" name="fanghong_gftype" value="<?php echo $conf["fanghong_gftype"];?>" class="form-control" placeholder="例如：留空默认为网易163接口">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">短网址模式</div>
                            <input type="text" name="fanghong_gfpattern" value="<?php echo $conf["fanghong_gfpattern"];?>" class="form-control" placeholder="默认为3">
                        </div>
                        <div class="alert alert-info">短网址模式（普通：1，防红：2，直链：3）</div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">token</div>
                            <input type="text" name="fanghong_gftoken" value="<?php echo $conf["fanghong_gftoken"];?>" class="form-control" placeholder="token" required="required">
                        </div>
                    </div>

	    </div>
                <div class="form-group" id="fanghong_diy" style="<?php echo $conf["fanghong_api"] != 9 ? "display:none;" : NULL;?>">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">防红API</div>
						<input type="text" name="fanghong_url" value="<?php echo $conf["fanghong_url"];?>" class="form-control" placeholder="不填写则关闭防红链接生成" />
						<div class="input-group-addon" onclick="checkurl()"><small>检测地址</small></div>
					</div>
				</div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">返回值</div>
                            <input type="text" name="fanghong_key" value="<?php echo $conf["fanghong_key"];?>" class="form-control" placeholder="返回生成URL的键名，一般为url">
                        </div>
                    </div>
                 </div>
				<div class="form-group">
					<input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" /><br />
				</div>
			</form>
		</div>
		<div class="panel-footer">
			<span class="fa fa-info-circle"></span>
			一般防红接口地址为 http://防红网站域名/dwz.php?longurl= 具体可以咨询相应站长
		</div>
	</div>
	<?php
	if ($conf["fanghong_api"] != 0) {
		?>		<div class="block">
			<div class="block-title">
				<h3 class="panel-title">获取防红链接</h3>
			</div>
			<div class="">
				<div class="input-group">
					<span class="input-group-addon">防红网址</span>
					<input class="form-control" id="longurl" value="<?php echo (is_https() ? "https://" : "http://") . $_SERVER["HTTP_HOST"];?>">
				</div>
				<div class="well well-sm">如果您的网址在QQ内报毒或者打不开，您可以使用此功能生成防毒链接！</div>
				<a class="btn btn-block btn-success" id="create_url">生成我的防红链接</a>
			</div>
		</div>
	<?php
	}
	?>	<script src="<?php echo $cdnpublic;?>layer/3.1.1/layer.js"></script>
	<script src="<?php echo $cdnpublic;?>clipboard.js/1.7.1/clipboard.min.js"></script>
	<div class="modal fade in" id="fanghongurl" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
					<h4 class="modal-title">防红链接生成</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<div class="input-group">
							<div class="input-group-addon">防红链接</div><input type="text" id="target_url" value="" class="form-control" readOnly="readOnly">
						</div>
					</div>
					<center><button class="btn btn-info btn-sm" id="recreate_url">重新生成</button>&nbsp;<button class="btn btn-warning btn-sm copy-btn" id="copyurl" data-clipboard-text="">一键复制链接</button></center>
				</div>
				<div class="modal-footer"><button type="button" class="btn btn-white" data-dismiss="modal">关闭</button></div>
			</div>
		</div>
	</div>
	<script>
		function checkurl() {
			var url = $("input[name='fanghong_url']").val();
			if (url.indexOf('http') >= 0 && url.indexOf('=') >= 0) {
				var ii = layer.load(2, {
					shade: [0.1, '#fff']
				});
				$.ajax({
					type: "POST",
					url: "ajax.php?act=checkdwz",
					data: {
						url: url
					},
					dataType: 'json',
					success: function(data) {
						layer.close(ii);
						if (data.code == 1) {
							layer.msg('检测正常');
						} else if (data.code == 2) {
							layer.alert('链接无法访问或返回内容不是json格式');
						} else {
							layer.alert('该链接无法访问');
						}
					},
					error: function(data) {
						layer.close(ii);
						layer.msg('目标URL连接超时');
						return false;
					}
				});
			} else {
				layer.alert('链接地址错误');
			}
		}
		$(document).ready(function() {

$("select[name='fanghong_api']").change(function(){
	if($(this).val() == 9){
		$("#fanghong_diy").css("display","inherit");
		$("#fanghong_gf").css("display","none");

	}else if($(this).val() == 0){
		$("#fanghong_diy").css("display","none");
		$("#fanghong_gf").css("display","none");

	}else{
	    $("#fanghong_diy").css("display","none");
	    $("#fanghong_gf").css("display","inherit");
	}

});

			var clipboard = new Clipboard('.copy-btn', {
				container: document.getElementById('fanghongurl')
			});
			clipboard.on('success', function(e) {
				layer.msg('复制成功！');
			});
			clipboard.on('error', function(e) {
				layer.msg('复制失败，请长按链接后手动复制');
			});
			var clipboard = new Clipboard('.copy-btn', {
				container: document.getElementById('fanghongvip')
			});
			clipboard.on('success', function(e) {
				layer.msg('复制成功！');
			});
			clipboard.on('error', function(e) {
				layer.msg('复制失败，请长按链接后手动复制');
			});
			$("#create_url").click(function() {
				var self = $(this);
				if (self.attr("data-lock") === "true") return;
				else self.attr("data-lock", "true");
				var url = $("input[id='longurl']").val();
				if (url == '') {
					layer.alert('网址不能为空');
					return false;
				}
				var ii = layer.load(1, {
					shade: [0.1, '#fff']
				});
				$.get("ajax.php?act=create_url&force=1&longurl=" + encodeURIComponent(url), function(data) {
					layer.close(ii);
					if (data.code == 0) {
						$("#target_url").val(data.url);
						$("#copyurl").attr('data-clipboard-text', data.url);
						$('#fanghongurl').modal('show');
					} else {
						layer.alert(data.msg);
					}
					self.attr("data-lock", "false");
				}, 'json');
			});
			$("#recreate_url").click(function() {
				var self = $(this);
				if (self.attr("data-lock") === "true") return;
				else self.attr("data-lock", "true");
				var url = $("input[id='longurl']").val();
				if (url == '') {
					layer.alert('网址不能为空');
					return false;
				}
				var ii = layer.load(1, {
					shade: [0.1, '#fff']
				});
				$.get("ajax.php?act=create_url&force=1&longurl=" + encodeURIComponent(url), function(data) {
					layer.close(ii);
					if (data.code == 0) {
						layer.msg('生成链接成功');
						$("#target_url").val(data.url);
						$("#copyurl").attr('data-clipboard-text', data.url);
					} else {
						layer.alert(data.msg);
					}
					self.attr("data-lock", "false");
				}, 'json');
			});
		});
	</script>
<?php
} elseif ($mod == "mailcon_reset") {
	adminpermission("set", 1);
	$faka_mail = "<b>商品名称：</b> [name]<br/><b>购买时间：</b>[date]<br/><b>以下是你的卡密信息：</b><br/>[kmdata]<br/>----------<br/><b>使用说明：</b><br/>[alert]<br/>----------<br/>安享云商城自助下单平台<br/>[domain]";
	saveSetting("faka_mail", $faka_mail);
	$ad = $CACHE->clear();
	if ($ad) {
		showmsg("修改成功！", 1);
	} else {
		showmsg("修改失败！<br/>" . $DB->error(), 4);
	}
} elseif ($mod == "mailcon") {
	adminpermission("set", 1);
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">发信邮件模板设置</h3>
		</div>
		<div class="">
			<form onsubmit="return saveSetting(this)" method="post" class="form-horizontal" role="form">
				<div class="form-group">
					<label class="col-sm-2 control-label">发卡邮件模板</label>
					<div class="col-sm-10"><textarea class="form-control" name="faka_mail" id="faka_mail" rows="6"><?php echo htmlspecialchars($conf["faka_mail"]);?></textarea></div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" /><br /><br />
						<a href="./set.php?mod=mailcon_reset" class="btn btn-warning btn-block" onclick="return confirm('确定要重置吗？');">重置模板设置</a><br />
					</div>
				</div>
			</form>
		</div>
		<div class="panel-footer">
			<font color="green">变量代码：<br />
				<a href="#" onclick="Addstr('faka_mail','[kmdata]');return false">[kmdata]</a>&nbsp;卡密内容<br />
				<a href="#" onclick="Addstr('faka_mail','[name]');return false">[name]</a>&nbsp;商品名称<br />
				<a href="#" onclick="Addstr('faka_mail','[alert]');return false">[alert]</a>&nbsp;商品简介<br />
				<a href="#" onclick="Addstr('faka_mail','[date]');return false">[date]</a>&nbsp;购买时间<br />
				<a href="#" onclick="Addstr('faka_mail','[email]');return false">[email]</a>&nbsp;收信人邮箱<br />
			</font>
		</div>
	</div>
	<script>
		function Addstr(id, str) {
			$("#" + id).val($("#" + id).val() + str);
		}
	</script>
<?php
} elseif ($mod == "cloneset") {
	adminpermission("set", 1);
	$key = md5($password_hash . md5(SYS_KEY) . $conf["apikey"]);
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">克隆站点配置</h3>
		</div>
		<div class="">
			<form class="form-horizontal" role="form"><input type="hidden" name="do" value="submit" />
				<div class="form-group">
					<label class="col-sm-2 control-label">克隆密钥</label>
					<div class="col-sm-10"><input type="text" name="key" value="<?php echo $key;?>" class="form-control" disabled /></div>
				</div>
			</form>
		</div>
		<div class="panel-footer">
			<span class="fa fa-info-circle"></span>
			此密钥是用于其他站点克隆本站商品<br />
			提示：修改API对接密钥可同时重置克隆密钥。<br />
		</div>
	</div>
<?php
} elseif ($mod == "rewrite") {
	adminpermission("set", 1);
	$SERVER_SOFTWARE = explode("/", $_SERVER["SERVER_SOFTWARE"]);
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">伪静态设置</h3>
		</div>
		<div class="">
			<form onsubmit="return saveRewrite(this)" method="post" class="form-horizontal form-bordered" role="form">
				<div class="form-group">
					<label class="col-sm-2 control-label">伪静态开关</label>
					<div class="col-sm-10"><select class="form-control" name="article_rewrite" default="<?php echo $conf["article_rewrite"];?>">
							<option value="0">关闭</option>
							<option value="1">开启</option>
						</select></div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="修改" class="btn btn-primary btn-block" />
					</div>
				</div>
			</form>
		</div>
		<div class="panel-footer">
			<span class="fa fa-info-circle"></span>
			当前服务器Web软件为：<?php echo ucwords($SERVER_SOFTWARE[0]);?><br />
			请将以下内容添加到伪静态规则
			<pre>location / {
 if (!-e $request_filename) {
   rewrite ^/article-(.[0-9]*).html$ /index.php?mod=article&id=$1 last;
   rewrite ^/(.[a-zA-Z0-9\-\_]+).html$ /index.php?mod=$1 last;
 }
}</pre>
		</div>
	</div>

	<script>
		function saveRewrite(obj) {
			var ii = layer.load(2, {
				shade: [0.1, '#fff']
			});
			$.ajax({
				type: 'POST',
				url: 'ajax.php?act=rewrite',
				data: $(obj).serialize(),
				dataType: 'json',
				success: function(data) {
					layer.close(ii);
					if (data.code == 0) {
						layer.alert('设置伪静态成功！', {
							icon: 1,
							closeBtn: false
						}, function() {
							window.location.reload()
						});
					} else {
						layer.alert(data.msg, {
							icon: 2
						})
					}
				},
				error: function(data) {
					layer.msg('服务器错误');
					return false;
				}
			});
			return false;
		}
	</script>

<?php
} elseif ($mod == "map") {
	adminpermission("set", 1);
	?>	<div class="block">
		<div class="block-title">
			<h3 class="panel-title">网站地图生成工具</h3>
		</div>
		<div class="">
			<div class="alert alert-info">使用此功能可一键生成网站XML地图[map.xml]，方便站长自动推送网站页面，提升SEO效益</div>
			<form onsubmit="return saveMap(this)" method="post" class="form-horizontal form-bordered" role="form">
				<div class="form-group">
					<label class="col-sm-2 control-label">生成类型</label>
					<div class="col-sm-10"><select class="form-control" name="map_type">
							<option value="1">商品分类(仅上架中的)</option>
							<option value="2">商品列表(仅上架中的)</option>
							<option value="3">文章列表(仅已显示的)</option>
						</select></div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">URL规则</label>
					<div class="col-sm-10"><input type="text" name="map_urlpattern" value="http://[siteurl]/class/[cid].html" class="form-control" placeholder="http://[siteurl]/class/[cid].html" required /></div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">优先权值</label>
					<div class="col-sm-10"><input type="text" name="map_priority" value="" class="form-control" placeholder="优先权标签，优先权值0.0-1.0" required /></div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="提交操作" class="btn btn-primary btn-block" />
					</div>
				</div>
			</form>
		</div>
	</div>
	</div>
	<script>
		function saveMap(obj) {
			var map_urlpattern = $("input[name='map_urlpattern']").val();
			if (map_urlpattern.indexOf('http') >= 0) {
				var ii = layer.load(2, {
					shade: [0.1, '#fff']
				});
				$.ajax({
					type: 'POST',
					url: 'ajax.php?act=map',
					data: $(obj).serialize(),
					dataType: 'json',
					success: function(data) {
						layer.close(ii);
						if (data.code == 0) {
							layer.msg(data.msg);
						} else {
							layer.alert(data.msg);
						}
					},
					error: function(data) {
						layer.close(ii);
						layer.msg('目标URL连接超时');
						return false;
					}
				});
			} else {
				layer.alert('URL规则必须以 http:// 开头，以 / 结尾');
			}
			return false;
		}
	</script>
<?php
} elseif ($mod == "chat") {
    adminpermission("set", 1);
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['do']) && $_POST['do'] == 'save') {
        $chat_enable = intval($_POST['chat_enable']);
        $chat_mode = in_array($_POST['chat_mode'], array('link', 'qq', 'chatwoot', 'custom')) ? $_POST['chat_mode'] : 'link';
        $chat_title = trim($_POST['chat_title']);
        $chat_button_text = trim($_POST['chat_button_text']);
        $chat_button_icon = trim($_POST['chat_button_icon']);
        $chat_btn_color = trim($_POST['chat_btn_color']);
        $chat_button_text_color = trim($_POST['chat_button_text_color']);
        $chat_position = in_array($_POST['chat_position'], array('right-bottom', 'left-bottom', 'right-middle', 'left-middle')) ? $_POST['chat_position'] : 'right-bottom';
        $chat_offset_x = max(0, intval($_POST['chat_offset_x']));
        $chat_offset_y = max(0, intval($_POST['chat_offset_y']));
        $chat_mobile_show = intval($_POST['chat_mobile_show']);
        $chat_open_new = intval($_POST['chat_open_new']);
        $chat_link_url = trim($_POST['chat_link_url']);
        $chat_qq = trim($_POST['chat_qq']);
        $chatwoot_base_url = trim($_POST['chatwoot_base_url']);
        $chatwoot_website_token = trim($_POST['chatwoot_website_token']);
        $chat_custom_code = trim($_POST['chat_custom_code']);
        saveSetting('chat_enable', $chat_enable);
        saveSetting('chat_mode', $chat_mode);
        saveSetting('chat_title', $chat_title);
        saveSetting('chat_button_text', $chat_button_text);
        saveSetting('chat_button_icon', $chat_button_icon);
        saveSetting('chat_btn_color', $chat_btn_color);
        saveSetting('chat_button_text_color', $chat_button_text_color);
        saveSetting('chat_position', $chat_position);
        saveSetting('chat_offset_x', $chat_offset_x);
        saveSetting('chat_offset_y', $chat_offset_y);
        saveSetting('chat_mobile_show', $chat_mobile_show);
        saveSetting('chat_open_new', $chat_open_new);
        saveSetting('chat_link_url', $chat_link_url);
        saveSetting('chat_qq', $chat_qq);
        saveSetting('chatwoot_base_url', $chatwoot_base_url);
        saveSetting('chatwoot_website_token', $chatwoot_website_token);
        saveSetting('chat_custom_code', $chat_custom_code);
        $ad = $CACHE->clear();
        if ($ad) {
            showmsg('客服窗口配置保存成功！', 1);
        } else {
            showmsg('保存失败！<br/>' . $DB->error(), 4);
        }
    }
?>
    <div class="block">
        <div class="block-title">
            <h3 class="panel-title">浮动客服配置</h3>
        </div>
        <div class="panel panel-info">
            <div class="panel-heading">使用说明</div>
            <div class="panel-body" style="background:#e6f3fb;">
                <p>开启后，前台会按当前配置显示一个全站浮动客服按钮。客户可选择跳转链接、QQ 对话、Chatwoot 或自定义代码。</p>
                <p>自定义代码会直接输出到前台页面，请只粘贴可信客服服务商提供的代码。</p>
            </div>
        </div>
        <form method="post" class="form-horizontal form-bordered" role="form">
            <input type="hidden" name="do" value="save">
            <div class="form-group">
                <label class="col-sm-2 control-label">客服开关</label>
                <div class="col-sm-10">
                    <select class="form-control" name="chat_enable" default="<?php echo $conf['chat_enable'] ?? 0;?>">
                        <option value="1">开启</option>
                        <option value="0">关闭</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">客服方式</label>
                <div class="col-sm-10">
                    <select class="form-control" name="chat_mode" default="<?php echo htmlspecialchars($conf['chat_mode'] ?? 'link');?>">
                        <option value="link">跳转链接</option>
                        <option value="qq">QQ 对话</option>
                        <option value="chatwoot">Chatwoot 嵌入</option>
                        <option value="custom">自定义代码</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">客服标题</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="chat_title" value="<?php echo htmlspecialchars($conf['chat_title'] ?? '在线客服');?>" placeholder="在线客服">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">按钮文字</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="chat_button_text" value="<?php echo htmlspecialchars($conf['chat_button_text'] ?? '在线客服');?>" placeholder="在线客服">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">按钮图标</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="chat_button_icon" value="<?php echo htmlspecialchars($conf['chat_button_icon'] ?? 'fa-comments');?>" placeholder="fa-comments">
                    <span class="help-block">填写 FontAwesome 图标类名，例如 <code>fa-comments</code>、<code>fa-qq</code>、<code>fa-headphones</code>。</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">按钮颜色</label>
                <div class="col-sm-5">
                    <input type="text" class="form-control colorpicker" name="chat_btn_color" value="<?php echo htmlspecialchars($conf['chat_btn_color'] ?? '#2196F3');?>" placeholder="#2196F3">
                    <span class="help-block">按钮背景色。</span>
                </div>
                <div class="col-sm-5">
                    <input type="text" class="form-control colorpicker" name="chat_button_text_color" value="<?php echo htmlspecialchars($conf['chat_button_text_color'] ?? '#FFFFFF');?>" placeholder="#FFFFFF">
                    <span class="help-block">文字和图标颜色。</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">按钮位置</label>
                <div class="col-sm-10">
                    <select class="form-control" name="chat_position" default="<?php echo htmlspecialchars($conf['chat_position'] ?? 'right-bottom');?>">
                        <option value="right-bottom">右下</option>
                        <option value="left-bottom">左下</option>
                        <option value="right-middle">右中</option>
                        <option value="left-middle">左中</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">边缘距离</label>
                <div class="col-sm-5">
                    <input type="number" class="form-control" name="chat_offset_x" value="<?php echo intval($conf['chat_offset_x'] ?? 24);?>" min="0" max="300" placeholder="横向距离">
                    <span class="help-block">横向距离，单位 px。</span>
                </div>
                <div class="col-sm-5">
                    <input type="number" class="form-control" name="chat_offset_y" value="<?php echo intval($conf['chat_offset_y'] ?? 24);?>" min="0" max="300" placeholder="纵向距离">
                    <span class="help-block">纵向距离，单位 px。</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">手机端显示</label>
                <div class="col-sm-10">
                    <select class="form-control" name="chat_mobile_show" default="<?php echo $conf['chat_mobile_show'] ?? 1;?>">
                        <option value="1">显示</option>
                        <option value="0">隐藏</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">新窗口打开</label>
                <div class="col-sm-10">
                    <select class="form-control" name="chat_open_new" default="<?php echo $conf['chat_open_new'] ?? 1;?>">
                        <option value="1">是</option>
                        <option value="0">否</option>
                    </select>
                    <span class="help-block">适用于跳转链接和 QQ 对话。</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">跳转链接</label>
                <div class="col-sm-10">
                    <input type="url" class="form-control" name="chat_link_url" value="<?php echo htmlspecialchars($conf['chat_link_url'] ?? '');?>" placeholder="https://example.com/service">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">QQ 号码</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="chat_qq" value="<?php echo htmlspecialchars($conf['chat_qq'] ?? '');?>" placeholder="例如：800000000">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Chatwoot 地址</label>
                <div class="col-sm-10">
                    <input type="url" class="form-control" name="chatwoot_base_url" value="<?php echo htmlspecialchars($conf['chatwoot_base_url'] ?? '');?>" placeholder="https://app.chatwoot.com">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Chatwoot Token</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="chatwoot_website_token" value="<?php echo htmlspecialchars($conf['chatwoot_website_token'] ?? '');?>" placeholder="Website token">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">自定义代码</label>
                <div class="col-sm-10">
                    <textarea class="form-control" name="chat_custom_code" rows="8" placeholder="可粘贴第三方客服 JS 或 HTML 代码"><?php echo htmlspecialchars($conf['chat_custom_code'] ?? '');?></textarea>
                    <span class="help-block">高级模式：代码会输出到前台，请确认来源可信并自行测试。</span>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <input type="submit" value="保存设置" class="btn btn-primary btn-block">
                </div>
            </div>
        </form>
    </div>
<?php
}
?><script src="<?php echo $cdnpublic;?>layer/3.1.1/layer.js"></script>
<script>
	var items = $("select[default]");
	for (i = 0; i < items.length; i++) {
		$(items[i]).val($(items[i]).attr("default") || 0);
	}
	function saveSetting(obj) {
		if ($("input[name='fenzhan_domain']").length > 0) {
			var fenzhan_domain = $("input[name='fenzhan_domain']").val();
			$("input[name='fenzhan_domain']").val(fenzhan_domain.replace("，", ","));
		}
		if ($("input[name='fenzhan_remain']").length > 0) {
			var fenzhan_remain = $("input[name='fenzhan_remain']").val();
			$("input[name='fenzhan_remain']").val(fenzhan_remain.replace("，", ","));
		}
		var ii = layer.load(2, {
			shade: [0.1, '#fff']
		});
		$.ajax({
			type: 'POST',
			url: 'ajax.php?act=set',
			data: $(obj).serialize(),
			dataType: 'json',
			success: function(data) {
				layer.close(ii);
				if (data.code == 0) {
					layer.alert('设置保存成功！', {
						icon: 1,
						closeBtn: false
					}, function() {
						window.location.reload()
					});
				} else {
					layer.alert(data.msg, {
						icon: 2
					})
				}
			},
			error: function(data) {
				layer.msg('服务器错误');
				return false;
			}
		});
		return false;
	}

	function changeTemplate(template) {
		var ii = layer.load(2, {
			shade: [0.1, '#fff']
		});
		$.ajax({
			type: 'POST',
			url: 'ajax.php?act=set',
			data: {
				template: template,
				template_m: "0"
			},
			dataType: 'json',
			success: function(data) {
				layer.close(ii);
				if (data.code == 0) {
					layer.alert('更换模板成功！', {
						icon: 1,
						closeBtn: false
					}, function() {
						window.location.reload()
					});
				} else {
					layer.alert(data.msg, {
						icon: 2
					})
				}
			},
			error: function(data) {
				layer.msg('服务器错误');
				return false;
			}
		});
	}

	function thirdloginbind(type) {
		var typename = type == 'qq' ? 'QQ' : '微信';
		layer.open({
			type: 2,
			title: '绑定' + typename + '登录',
			shadeClose: true,
			closeBtn: 2,
			scrollbar: false,
			area: ['310px', '450px'],
			content: './bind.php?type=' + type
		});
	}

	function thirdloginunbind(type) {
		var typename = type == 'qq' ? 'QQ' : '微信';
		var confirmobj = layer.confirm('确定要解绑' + typename + '吗？', {
			btn: ['确定', '取消']
		}, function() {
			$.post("ajax.php?act=thirdloginunbind", {
				type: type
			}, function(arr) {
				if (arr.code == 0) {
					layer.alert(typename + '解绑成功！', {
						icon: 1,
						closeBtn: false
					}, function() {
						window.location.reload()
					});
				} else {
					layer.alert(data.msg, {
						icon: 2
					})
				}
			}, 'json');
		}, function() {
			layer.close(confirmobj);
		});
	}
</script>
</div>
</div>

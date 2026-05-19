<?php
include '../includes/common.php';
if ($islogin2 != 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
if (intval($userrow['power']) <= 0) {
    showmsg('你没有权限使用此功能', 3);
}

q8_price_rule_ensure_fields();

$title = '分站加价模板';
$priceRows = q8_price_rule_fetch_rows($userrow['zid']);
$currentTemplateId = isset($userrow['site_prid']) ? intval($userrow['site_prid']) : 0;
$legacyTemplateRow = null;
if ($currentTemplateId > 0 && !q8_price_rule_exists_for_owner($currentTemplateId, $userrow['zid'])) {
    $legacyTemplateRow = q8_price_rule_fetch_row($currentTemplateId);
}

function q8_user_price_escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function q8_user_price_kind_label($kind)
{
    return intval($kind) === 1 ? '固定金额' : '倍数加价';
}

$currentTemplateName = '暂不使用模板';
foreach ($priceRows as $priceRow) {
    if (intval($priceRow['id']) === $currentTemplateId) {
        $currentTemplateName = $priceRow['name'];
        break;
    }
}
if ($legacyTemplateRow) {
    $currentTemplateName = '历史主站模板：' . $legacyTemplateRow['name'];
}

include './head.php';
?>
<style>
  .q8-user-price-page .panel-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }
  .q8-user-price-summary {
    margin-bottom: 15px;
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  }
  .q8-user-price-card {
    padding: 14px 16px;
    border: 1px solid #dbeafe;
    border-radius: 12px;
    background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    box-shadow: 0 10px 24px rgba(22, 119, 255, .08);
  }
  .q8-user-price-card__label {
    color: #64748b;
    font-size: 12px;
  }
  .q8-user-price-card__value {
    margin-top: 6px;
    color: #0f172a;
    font-size: 22px;
    font-weight: 800;
  }
  .q8-user-price-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 15px;
    flex-wrap: wrap;
  }
  .q8-user-price-toolbar__actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }
  .q8-user-price-empty {
    padding: 28px 16px;
    text-align: center;
    color: #64748b;
  }
  .q8-user-price-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
  }
  .q8-user-price-badge--active {
    background: rgba(34, 197, 94, .12);
    color: #15803d;
  }
  .q8-user-price-badge--idle {
    background: rgba(148, 163, 184, .14);
    color: #475569;
  }
  .q8-user-price-table td,
  .q8-user-price-table th {
    vertical-align: middle !important;
  }
  .q8-user-price-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }
  .q8-user-price-help,
  .q8-user-price-legacy {
    margin-bottom: 15px;
  }
  .q8-user-price-help p:last-child,
  .q8-user-price-empty p:last-child {
    margin-bottom: 0;
  }
  .q8-user-price-page .modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    flex-wrap: wrap;
    padding: 14px 18px 18px;
  }
  .q8-user-price-page .modal-footer .btn {
    flex: 1 1 180px;
    min-height: 42px;
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 800;
  }
  .q8-user-price-page .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 36px;
    padding: 8px 16px;
    border-radius: 10px;
    font-weight: 700;
    line-height: 1.2;
    text-shadow: none;
    transition: .18s ease;
  }
  .q8-user-price-page .btn:hover,
  .q8-user-price-page .btn:focus {
    transform: translateY(-1px);
    text-decoration: none;
  }
  .q8-user-price-page .btn-primary,
  .q8-user-price-page .btn-primary:hover,
  .q8-user-price-page .btn-primary:focus {
    color: #fff !important;
    border-color: #1677ff !important;
    background: linear-gradient(135deg, #1677ff, #22c4c8) !important;
    box-shadow: 0 10px 24px rgba(22, 119, 255, .18);
  }
  .q8-user-price-page .btn-default,
  .q8-user-price-page .btn-default:hover,
  .q8-user-price-page .btn-default:focus {
    color: #334155 !important;
    border: 1px solid #cbd5e1 !important;
    background: #fff !important;
    box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
  }
  .q8-user-price-page .modal-footer .btn-default,
  .q8-user-price-page .modal-footer .btn-default:hover,
  .q8-user-price-page .modal-footer .btn-default:focus {
    color: #475569 !important;
    border: 1px solid #d7e2f2 !important;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%) !important;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.8), 0 8px 18px rgba(15, 23, 42, .05);
  }
  .q8-user-price-page .modal-footer .btn-primary,
  .q8-user-price-page .modal-footer .btn-primary:hover,
  .q8-user-price-page .modal-footer .btn-primary:focus {
    color: #fff !important;
    border-color: #1677ff !important;
    background: linear-gradient(135deg, #1677ff, #22c4c8) !important;
    box-shadow: 0 12px 26px rgba(22, 119, 255, .2);
  }
  .q8-user-price-page .btn-info,
  .q8-user-price-page .btn-info:hover,
  .q8-user-price-page .btn-info:focus {
    color: #fff !important;
    border-color: #0ea5e9 !important;
    background: linear-gradient(135deg, #0ea5e9, #38bdf8) !important;
  }
  .q8-user-price-page .btn-success,
  .q8-user-price-page .btn-success:hover,
  .q8-user-price-page .btn-success:focus {
    color: #fff !important;
    border-color: #16a34a !important;
    background: linear-gradient(135deg, #16a34a, #22c55e) !important;
  }
  .q8-user-price-page .btn-danger,
  .q8-user-price-page .btn-danger:hover,
  .q8-user-price-page .btn-danger:focus {
    color: #fff !important;
    border-color: #ef4444 !important;
    background: linear-gradient(135deg, #ef4444, #f97316) !important;
  }
  .q8-user-price-toolbar__actions .btn,
  .q8-user-price-empty .btn {
    min-width: 168px;
  }
  .q8-user-price-actions .btn {
    min-height: 30px;
    padding: 6px 12px;
    border-radius: 999px;
  }
  @media (max-width: 767px) {
    .q8-user-price-page .modal-footer {
      gap: 10px;
    }
    .q8-user-price-page .modal-footer .btn {
      flex-basis: 100%;
    }
    .q8-user-price-actions .btn,
    .q8-user-price-toolbar__actions .btn {
      width: 100%;
    }
    .q8-user-price-actions,
    .q8-user-price-toolbar__actions {
      width: 100%;
    }
  }
</style>

<div class="wrapper q8-user-price-page">
  <div class="col-sm-12">
    <div class="panel panel-default">
      <div class="panel-heading font-bold">
        <span>分站加价模板</span>
        <a href="./usetmoban.php?mod=site" class="btn btn-info btn-xs">返回站点设置</a>
      </div>
      <div class="panel-body">
        <div class="alert alert-info q8-user-price-help">
          <p>这里管理当前分站自己的加价模板。模板只对当前分站可见，不会和主站或其他分站共享。</p>
          <p>未单独改价的商品，会按“当前分站自己的进货价 + 当前分站自己的模板”实时计算。</p>
        </div>

        <?php if ($legacyTemplateRow) { ?>
        <div class="alert alert-warning q8-user-price-legacy">
          当前分站仍在使用历史主站模板：
          <strong><?php echo q8_user_price_escape($legacyTemplateRow['name']); ?></strong>
          。建议先创建自己的模板，再点“设为当前模板”切换。
        </div>
        <?php } ?>

        <div class="q8-user-price-summary">
          <div class="q8-user-price-card">
            <div class="q8-user-price-card__label">当前模板总数</div>
            <div class="q8-user-price-card__value"><?php echo intval(count($priceRows)); ?></div>
          </div>
          <div class="q8-user-price-card">
            <div class="q8-user-price-card__label">当前启用模板</div>
            <div class="q8-user-price-card__value"><?php echo q8_user_price_escape($currentTemplateName); ?></div>
          </div>
        </div>

        <div class="q8-user-price-toolbar">
          <div class="text-muted">专业版和普及版分站都可以在这里分别维护自己的模板库。</div>
          <div class="q8-user-price-toolbar__actions">
            <button type="button" class="btn btn-primary btn-sm" id="q8AddPriceRuleBtn">
              <i class="fa fa-plus"></i> 新建模板
            </button>
            <button type="button" class="btn btn-default btn-sm" id="q8ClearCurrentPriceRuleBtn">
              <i class="fa fa-eraser"></i> 取消当前模板
            </button>
          </div>
        </div>

        <?php if (!empty($priceRows)) { ?>
        <div class="table-responsive">
          <table class="table table-bordered table-hover q8-user-price-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>模板名称</th>
                <th>类型</th>
                <th>下级专业版</th>
                <th>下级普通版</th>
                <th>普通用户销售价</th>
                <th>当前状态</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($priceRows as $priceRow) { ?>
              <?php $isCurrent = intval($priceRow['id']) === $currentTemplateId; ?>
              <tr data-price-row="<?php echo intval($priceRow['id']); ?>">
                <td>#<?php echo intval($priceRow['id']); ?></td>
                <td><?php echo q8_user_price_escape($priceRow['name']); ?></td>
                <td><?php echo q8_user_price_escape(q8_user_price_kind_label($priceRow['kind'])); ?></td>
                <td><?php echo q8_user_price_escape($priceRow['p_2']); ?></td>
                <td><?php echo q8_user_price_escape($priceRow['p_1']); ?></td>
                <td><?php echo q8_user_price_escape($priceRow['p_0']); ?></td>
                <td>
                  <?php if ($isCurrent) { ?>
                  <span class="q8-user-price-badge q8-user-price-badge--active"><i class="fa fa-check-circle"></i> 当前启用</span>
                  <?php } else { ?>
                  <span class="q8-user-price-badge q8-user-price-badge--idle"><i class="fa fa-circle-o"></i> 未启用</span>
                  <?php } ?>
                </td>
                <td>
                  <div class="q8-user-price-actions">
                    <?php if (!$isCurrent) { ?>
                    <button type="button" class="btn btn-success btn-xs" data-price-action="use" data-id="<?php echo intval($priceRow['id']); ?>">
                      设为当前模板
                    </button>
                    <?php } ?>
                    <button type="button" class="btn btn-info btn-xs" data-price-action="edit" data-id="<?php echo intval($priceRow['id']); ?>">
                      编辑
                    </button>
                    <button type="button" class="btn btn-danger btn-xs" data-price-action="delete" data-id="<?php echo intval($priceRow['id']); ?>" data-name="<?php echo q8_user_price_escape($priceRow['name']); ?>">
                      删除
                    </button>
                  </div>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <?php } else { ?>
        <div class="q8-user-price-empty">
          <p>当前还没有创建任何分站加价模板。</p>
          <button type="button" class="btn btn-primary btn-sm" id="q8EmptyAddPriceRuleBtn">
            <i class="fa fa-plus"></i> 立即新建第一套模板
          </button>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="q8PriceRuleModal" tabindex="-1" role="dialog" aria-labelledby="q8PriceRuleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="关闭"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="q8PriceRuleModalLabel">新建分站加价模板</h4>
      </div>
      <div class="modal-body">
        <form id="q8PriceRuleForm">
          <input type="hidden" id="q8PriceRuleId" value="">
          <div class="form-group">
            <label for="q8PriceRuleName">模板名称</label>
            <input type="text" class="form-control" id="q8PriceRuleName" maxlength="30" placeholder="例如：默认零售模板">
          </div>
          <div class="form-group">
            <label for="q8PriceRuleKind">模板类型</label>
            <select class="form-control" id="q8PriceRuleKind">
              <option value="0">倍数加价</option>
              <option value="1">固定金额</option>
            </select>
          </div>
          <div class="form-group">
            <label for="q8PriceRuleP2">下级专业版</label>
            <input type="text" class="form-control" id="q8PriceRuleP2" placeholder="输入加价值">
          </div>
          <div class="form-group">
            <label for="q8PriceRuleP1">下级普通版</label>
            <input type="text" class="form-control" id="q8PriceRuleP1" placeholder="输入加价值">
          </div>
          <div class="form-group">
            <label for="q8PriceRuleP0">普通用户销售价</label>
            <input type="text" class="form-control" id="q8PriceRuleP0" placeholder="输入加价值">
          </div>
          <div class="alert alert-info" style="margin-bottom:0;">
            规则保持不变：下级专业版 ≤ 下级普通版 ≤ 普通用户销售价。
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-primary" id="q8PriceRuleSubmitBtn">保存模板</button>
      </div>
    </div>
  </div>
</div>

<script>
(function ($) {
  'use strict';

  var endpoints = {
    get: 'ajax_user.php?act=get_site_price_rule&id=',
    add: 'ajax_user.php?act=add_site_price_rule',
    edit: 'ajax_user.php?act=edit_site_price_rule',
    del: 'ajax_user.php?act=delete_site_price_rule',
    useRule: 'ajax_user.php?act=set_site_price_rule'
  };

  var MSG = {
    addTitle: '\u65b0\u5efa\u5206\u7ad9\u52a0\u4ef7\u6a21\u677f',
    editTitle: '\u7f16\u8f91\u5206\u7ad9\u52a0\u4ef7\u6a21\u677f',
    save: '\u4fdd\u5b58\u6a21\u677f',
    update: '\u4fdd\u5b58\u4fee\u6539',
    saving: '\u6b63\u5728\u4fdd\u5b58...',
    serverError: '\u670d\u52a1\u5668\u9519\u8bef',
    emptyFields: '\u8bf7\u786e\u4fdd\u5404\u9879\u4e0d\u80fd\u4e3a\u7a7a',
    deleteConfirm: '\u786e\u5b9a\u8981\u5220\u9664\u8fd9\u5957\u5206\u7ad9\u52a0\u4ef7\u6a21\u677f\u5417\uff1f',
    clearConfirm: '\u786e\u5b9a\u8981\u53d6\u6d88\u5f53\u524d\u5206\u7ad9\u52a0\u4ef7\u6a21\u677f\u5417\uff1f',
    useConfirmPrefix: '\u786e\u5b9a\u8981\u628a\u5f53\u524d\u5206\u7ad9\u5207\u6362\u5230\u6a21\u677f\u300c',
    useConfirmSuffix: '\u300d\u5417\uff1f',
    confirmTitle: '\u786e\u8ba4',
    ok: '\u786e\u5b9a',
    cancel: '\u53d6\u6d88'
  };

  function alertMessage(message, icon) {
    layer.alert(message, { icon: icon || 0 });
  }

  function toast(message, icon) {
    layer.msg(message, { icon: icon || 0, time: 2200 });
  }

  function resetForm() {
    $('#q8PriceRuleId').val('');
    $('#q8PriceRuleName').val('');
    $('#q8PriceRuleKind').val('0');
    $('#q8PriceRuleP2').val('');
    $('#q8PriceRuleP1').val('');
    $('#q8PriceRuleP0').val('');
    $('#q8PriceRuleModalLabel').text(MSG.addTitle);
    $('#q8PriceRuleSubmitBtn').text(MSG.save);
  }

  function openAddModal() {
    resetForm();
    $('#q8PriceRuleModal').modal('show');
  }

  function openEditModal(id) {
    var loadingIndex = layer.load(2, { shade: [0.08, '#fff'] });
    $.ajax({
      type: 'GET',
      url: endpoints.get + encodeURIComponent(id),
      dataType: 'json'
    }).done(function (data) {
      if (data && Number(data.code) === 0) {
        $('#q8PriceRuleId').val(data.id || '');
        $('#q8PriceRuleName').val(data.name || '');
        $('#q8PriceRuleKind').val(String(data.kind || '0'));
        $('#q8PriceRuleP2').val(data.p_2 || '');
        $('#q8PriceRuleP1').val(data.p_1 || '');
        $('#q8PriceRuleP0').val(data.p_0 || '');
        $('#q8PriceRuleModalLabel').text(MSG.editTitle);
        $('#q8PriceRuleSubmitBtn').text(MSG.update);
        $('#q8PriceRuleModal').modal('show');
      } else {
        alertMessage((data && data.msg) || MSG.serverError, 2);
      }
    }).fail(function () {
      toast(MSG.serverError, 2);
    }).always(function () {
      layer.close(loadingIndex);
    });
  }

  function saveRule() {
    var id = $('#q8PriceRuleId').val();
    var payload = {
      prid: id,
      name: $.trim($('#q8PriceRuleName').val() || ''),
      kind: $('#q8PriceRuleKind').val(),
      p_2: $.trim($('#q8PriceRuleP2').val() || ''),
      p_1: $.trim($('#q8PriceRuleP1').val() || ''),
      p_0: $.trim($('#q8PriceRuleP0').val() || '')
    };
    if (!payload.name || payload.p_2 === '' || payload.p_1 === '' || payload.p_0 === '') {
      alertMessage(MSG.emptyFields, 2);
      return;
    }
    var $button = $('#q8PriceRuleSubmitBtn');
    var originalText = $button.text();
    $button.prop('disabled', true).text(MSG.saving);
    $.ajax({
      type: 'POST',
      url: id ? endpoints.edit : endpoints.add,
      data: payload,
      dataType: 'json'
    }).done(function (data) {
      if (data && Number(data.code) === 0) {
        $('#q8PriceRuleModal').modal('hide');
        toast(data.msg, 1);
        window.setTimeout(function () {
          window.location.reload();
        }, 700);
      } else {
        alertMessage((data && data.msg) || MSG.serverError, 2);
      }
    }).fail(function () {
      toast(MSG.serverError, 2);
    }).always(function () {
      $button.prop('disabled', false).text(originalText);
    });
  }

  function setCurrentRule(id, name) {
    layer.confirm(MSG.useConfirmPrefix + name + MSG.useConfirmSuffix, {
      icon: 3,
      title: MSG.confirmTitle,
      btn: [MSG.ok, MSG.cancel]
    }, function (index) {
      layer.close(index);
      $.ajax({
        type: 'POST',
        url: endpoints.useRule,
        data: { id: id },
        dataType: 'json'
      }).done(function (data) {
        if (data && Number(data.code) === 0) {
          toast(data.msg, 1);
          window.setTimeout(function () {
            window.location.reload();
          }, 700);
        } else {
          alertMessage((data && data.msg) || MSG.serverError, 2);
        }
      }).fail(function () {
        toast(MSG.serverError, 2);
      });
    });
  }

  function clearCurrentRule() {
    layer.confirm(MSG.clearConfirm, {
      icon: 3,
      title: MSG.confirmTitle,
      btn: [MSG.ok, MSG.cancel]
    }, function (index) {
      layer.close(index);
      $.ajax({
        type: 'POST',
        url: endpoints.useRule,
        data: { id: 0 },
        dataType: 'json'
      }).done(function (data) {
        if (data && Number(data.code) === 0) {
          toast(data.msg, 1);
          window.setTimeout(function () {
            window.location.reload();
          }, 700);
        } else {
          alertMessage((data && data.msg) || MSG.serverError, 2);
        }
      }).fail(function () {
        toast(MSG.serverError, 2);
      });
    });
  }

  function deleteRule(id) {
    layer.confirm(MSG.deleteConfirm, {
      icon: 3,
      title: MSG.confirmTitle,
      btn: [MSG.ok, MSG.cancel]
    }, function (index) {
      layer.close(index);
      $.ajax({
        type: 'POST',
        url: endpoints.del,
        data: { id: id },
        dataType: 'json'
      }).done(function (data) {
        if (data && Number(data.code) === 0) {
          toast(data.msg, 1);
          window.setTimeout(function () {
            window.location.reload();
          }, 700);
        } else {
          alertMessage((data && data.msg) || MSG.serverError, 2);
        }
      }).fail(function () {
        toast(MSG.serverError, 2);
      });
    });
  }

  $('#q8AddPriceRuleBtn, #q8EmptyAddPriceRuleBtn').on('click', openAddModal);
  $('#q8ClearCurrentPriceRuleBtn').on('click', clearCurrentRule);
  $('#q8PriceRuleSubmitBtn').on('click', saveRule);

  $(document).on('click', '[data-price-action="edit"]', function () {
    openEditModal($(this).data('id'));
  });

  $(document).on('click', '[data-price-action="use"]', function () {
    var $row = $(this).closest('tr');
    setCurrentRule($(this).data('id'), $.trim($row.find('td').eq(1).text() || ''));
  });

  $(document).on('click', '[data-price-action="delete"]', function () {
    deleteRule($(this).data('id'));
  });
})(jQuery);
</script>

<?php include './foot.php'; ?>

// 现代化样式辅助JavaScript

// 全局配置变量
var currentConfig = window.modernConfig || {};

// 更新配置函数
window.updateModernConfig = function(newConfig) {
  currentConfig = $.extend({}, currentConfig, newConfig);
  applyConfigSettings();
  updateCSSVariables(); // 添加这行来更新CSS变量
};

// 设置CSS变量的函数
function setCSSVariable(variableName, value) {
  document.documentElement.style.setProperty(variableName, value);
}

// 更新所有CSS变量
function updateCSSVariables() {
  // 应用颜色配置
  if (currentConfig.colors && typeof currentConfig.colors === 'object') {
    // 基础颜色
    if (currentConfig.colors.primaryColor) {
      setCSSVariable('--primary-color', currentConfig.colors.primaryColor);
    }
    if (currentConfig.colors.secondaryColor) {
      setCSSVariable('--secondary-color', currentConfig.colors.secondaryColor);
    }
    if (currentConfig.colors.successColor) {
      setCSSVariable('--success-color', currentConfig.colors.successColor);
    }
    if (currentConfig.colors.infoColor) {
      setCSSVariable('--info-color', currentConfig.colors.infoColor);
    }
    if (currentConfig.colors.warningColor) {
      setCSSVariable('--warning-color', currentConfig.colors.warningColor);
    }
    if (currentConfig.colors.dangerColor) {
      setCSSVariable('--danger-color', currentConfig.colors.dangerColor);
    }
    if (currentConfig.colors.textColor) {
      setCSSVariable('--text-color', currentConfig.colors.textColor);
    }
    if (currentConfig.colors.textLight) {
      setCSSVariable('--text-light', currentConfig.colors.textLight);
    }
    if (currentConfig.colors.backgroundColor) {
      setCSSVariable('--background-color', currentConfig.colors.backgroundColor);
    }
    
    // 扩展颜色
    if (currentConfig.colors.colorRed) {
      setCSSVariable('--color-red', currentConfig.colors.colorRed);
    }
    if (currentConfig.colors.colorOrange) {
      setCSSVariable('--color-orange', currentConfig.colors.colorOrange);
    }
    if (currentConfig.colors.colorYellow) {
      setCSSVariable('--color-yellow', currentConfig.colors.colorYellow);
    }
    if (currentConfig.colors.colorGreen) {
      setCSSVariable('--color-green', currentConfig.colors.colorGreen);
    }
    if (currentConfig.colors.colorCyan) {
      setCSSVariable('--color-cyan', currentConfig.colors.colorCyan);
    }
    if (currentConfig.colors.colorBlue) {
      setCSSVariable('--color-blue', currentConfig.colors.colorBlue);
    }
    if (currentConfig.colors.colorPurple) {
      setCSSVariable('--color-purple', currentConfig.colors.colorPurple);
    }
  }
  
  // 应用布局配置
  if (currentConfig.layout && typeof currentConfig.layout === 'object') {
    if (currentConfig.layout.cardSpacing) {
      setCSSVariable('--card-spacing', currentConfig.layout.cardSpacing + 'px');
    }
    if (currentConfig.layout.sidebarWidth) {
      setCSSVariable('--sidebar-width', currentConfig.layout.sidebarWidth + 'px');
    }
    if (currentConfig.layout.contentPadding) {
      setCSSVariable('--content-padding', currentConfig.layout.contentPadding + 'px');
    }
  }
  
  // 应用动画配置
  if (currentConfig.animation && typeof currentConfig.animation === 'object') {
    if (currentConfig.animation.hoverDuration) {
      setCSSVariable('--hover-duration', currentConfig.animation.hoverDuration + 's');
    }
    if (currentConfig.animation.fadeInDuration) {
      setCSSVariable('--fade-in-duration', currentConfig.animation.fadeInDuration + 's');
    }
  }
  
  // 应用边框和阴影配置
  if (currentConfig.border && typeof currentConfig.border === 'object') {
    if (currentConfig.border.radiusSmall) {
      setCSSVariable('--border-radius-small', currentConfig.border.radiusSmall + 'px');
    }
    if (currentConfig.border.radiusMedium) {
      setCSSVariable('--border-radius-medium', currentConfig.border.radiusMedium + 'px');
    }
    if (currentConfig.border.radiusLarge) {
      setCSSVariable('--border-radius-large', currentConfig.border.radiusLarge + 'px');
    }
    if (currentConfig.border.radiusXl) {
      setCSSVariable('--border-radius-xl', currentConfig.border.radiusXl + 'px');
    }
  }
}

// 应用配置设置
function applyConfigSettings() {
  // 检查功能开关
  if (currentConfig.features && typeof currentConfig.features === 'object') {
    // 处理样式切换按钮
    if (currentConfig.features.enableStyleToggle === false && $('#styleToggle').length > 0) {
      $('#styleToggle').hide();
    } else if (currentConfig.features.enableStyleToggle === true && $('#styleToggle').length > 0) {
      $('#styleToggle').show();
    }
    
    // 处理动画效果
    if (currentConfig.features.enableAnimations === false) {
      $('.fade-in').removeClass('fade-in');
    }
  }
  
  // 应用布局配置
  if (currentConfig.layout && typeof currentConfig.layout === 'object') {
    // 设置侧边栏宽度
    if (currentConfig.layout.sidebarWidth) {
      $('.app-aside').css('width', currentConfig.layout.sidebarWidth + 'px');
    }
    
    // 设置内容区域边距
    if (currentConfig.layout.contentPadding) {
      $('.app-content-body').css('padding', currentConfig.layout.contentPadding + 'px');
    }
  }
}

// 等待DOM加载完成
$(document).ready(function() {
  // 移除所有浮动类
  $('.pull-left, .pull-right').removeClass('pull-left pull-right');
  
  // 转换传统布局为Flexbox
  function convertToModernLayout() {
    // 将包含.col-*的父元素转换为flex容器
    $('.wrapper > div').each(function() {
      var hasCols = $(this).find('[class*="col-"]').length > 0;
      if (hasCols && !$(this).hasClass('flex-row')) {
        $(this).addClass('flex-row');
      }
    });
    
    // 将所有.col-*元素转换为flex子项
    $('[class*="col-"]').each(function() {
      if (!$(this).hasClass('flex-col')) {
        $(this).addClass('flex-col');
      }
    });
    
    // 优化卡片布局
    var cardGroups = $('.panel-group');
    if (cardGroups.length > 0) {
      cardGroups.each(function() {
        if (!$(this).hasClass('grid-container')) {
          $(this).addClass('grid-container');
        }
      });
    }
    
    // 为所有面板添加现代化样式
    $('.panel').each(function() {
      if (!$(this).hasClass('card')) {
        $(this).addClass('card fade-in');
      }
    });
    
    // 为所有按钮添加现代化样式
    $('.btn').each(function() {
      if (!$(this).hasClass('rounded-md')) {
        $(this).addClass('rounded-md');
      }
    });
  }
  
  // 初始转换布局
  convertToModernLayout();
  
  // 添加样式切换功能
  function addStyleToggle() {
    // 检查功能是否启用
    if (currentConfig.features && currentConfig.features.enableStyleToggle === false) {
      return;
    }
    
    // 检查是否已添加切换按钮
    if ($('#styleToggle').length > 0) return;
    
    // 创建切换按钮
    var toggleButton = $('<button id="styleToggle" class="btn btn-primary btn-sm fixed-bottom" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">切换样式</button>');
    
    // 添加到页面
    $('body').append(toggleButton);
    
    // 设置按钮点击事件
    toggleButton.click(function() {
      var isModern = $('head').find('link[href="public/css/modern.css"]').length > 0;
      
      if (isModern) {
        // 切换到原始样式
        $('head').find('link[href="public/css/modern.css"]').remove();
        $('head').append('<link rel="stylesheet" href="<?php echo $cdnserver?>assets/user/css/app.css" type="text/css" />');
        $(this).text('使用现代样式');
      } else {
        // 切换到现代样式
        $('head').find('link[href="<?php echo $cdnserver?>assets/user/css/app.css"]').remove();
        $('head').append('<link rel="stylesheet" href="public/css/modern.css" type="text/css" />');
        $(this).text('使用原始样式');
        convertToModernLayout();
      }
    });
  }
  
  // 添加切换功能
  addStyleToggle();
  
  // 监听窗口大小变化，调整布局
  $(window).resize(function() {
    convertToModernLayout();
  });
  
  // 添加淡入动画效果
  function addFadeInAnimation() {
    // 检查功能是否启用
    if (currentConfig.features && currentConfig.features.enableAnimations === false) {
      return;
    }
    
    $('body').addClass('fade-in');
    
    // 为所有元素添加渐入动画
    var elements = $('div, h1, h2, h3, h4, h5, h6, p, a, button, form, table');
    var delay = 0;
    
    elements.each(function() {
      if (!$(this).hasClass('fade-in')) {
        $(this).css('animation-delay', delay + 'ms');
        $(this).addClass('fade-in');
        delay += 50;
      }
    });
  }
  
  // 添加动画效果
  addFadeInAnimation();
  
  // 优化用户体验
  function enhanceUserExperience() {
    // 添加滚动平滑效果
    if (currentConfig.features && currentConfig.features.enableSmoothScroll !== false) {
      $('a[href^="#"]').on('click', function(event) {
        event.preventDefault();
        
        var target = $(this.getAttribute('href'));
        if (target.length) {
          var duration = currentConfig.animation && currentConfig.animation.scrollDuration ? currentConfig.animation.scrollDuration : 500;
          $('html, body').stop().animate({
            scrollTop: target.offset().top
          }, duration);
        }
      });
    }
    
    // 优化按钮交互
    if (currentConfig.features && currentConfig.features.enableButtonHoverEffects !== false) {
      $('.btn').hover(function() {
        $(this).css('transform', 'translateY(-2px)');
        $(this).css('box-shadow', '0 4px 12px rgba(0, 0, 0, 0.15)');
      }, function() {
        $(this).css('transform', 'translateY(0)');
        $(this).css('box-shadow', 'none');
      });
    }
    
    // 优化表格交互
    if (currentConfig.features && currentConfig.features.enableTableHoverEffects !== false) {
      $('.table tr').hover(function() {
        $(this).css('background-color', '#f8f9fa');
      }, function() {
        $(this).css('background-color', '');
      });
    }
  }
  
  // 增强用户体验
  enhanceUserExperience();
  
  // 应用配置设置
  applyConfigSettings();
});
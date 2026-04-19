// 创建配置调整面板
function createConfigPanel() {
  if ($('#configPanel').length > 0) return;
  
  // 创建面板HTML结构
  var panelHTML = `
    <div id="configPanel" class="fixed top-0 right-0 w-80 h-screen bg-white shadow-lg z-50 transform transition-transform duration-300 translate-x-full">
      <div class="p-4 border-b">
        <h3 class="text-lg font-bold text-gray-800">样式配置</h3>
        <button id="closeConfigPanel" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <div class="p-4 overflow-y-auto h-[calc(100%-100px)]">
        <div class="mb-4">
          <h4 class="text-sm font-semibold text-gray-600 mb-2">主色调</h4>
          <input type="color" id="primaryColorPicker" class="w-full h-10 border rounded cursor-pointer">
        </div>
        <div class="mb-4">
          <h4 class="text-sm font-semibold text-gray-600 mb-2">辅助色调</h4>
          <input type="color" id="secondaryColorPicker" class="w-full h-10 border rounded cursor-pointer">
        </div>
        <div class="mb-4">
          <h4 class="text-sm font-semibold text-gray-600 mb-2">成功色</h4>
          <input type="color" id="successColorPicker" class="w-full h-10 border rounded cursor-pointer">
        </div>
        <div class="mb-4">
          <h4 class="text-sm font-semibold text-gray-600 mb-2">信息色</h4>
          <input type="color" id="infoColorPicker" class="w-full h-10 border rounded cursor-pointer">
        </div>
        <div class="mb-4">
          <h4 class="text-sm font-semibold text-gray-600 mb-2">警告色</h4>
          <input type="color" id="warningColorPicker" class="w-full h-10 border rounded cursor-pointer">
        </div>
        <div class="mb-4">
          <h4 class="text-sm font-semibold text-gray-600 mb-2">危险色</h4>
          <input type="color" id="dangerColorPicker" class="w-full h-10 border rounded cursor-pointer">
        </div>
        <div class="mb-4">
          <h4 class="text-sm font-semibold text-gray-600 mb-2">背景色</h4>
          <input type="color" id="backgroundColorPicker" class="w-full h-10 border rounded cursor-pointer">
        </div>
        <div class="mb-4">
          <h4 class="text-sm font-semibold text-gray-600 mb-2">文本色</h4>
          <input type="color" id="textColorPicker" class="w-full h-10 border rounded cursor-pointer">
        </div>
        <div class="mb-4">
          <h4 class="text-sm font-semibold text-gray-600 mb-2">侧边栏宽度 (px)</h4>
          <input type="range" id="sidebarWidthSlider" min="200" max="400" step="10" class="w-full">
          <span id="sidebarWidthValue" class="text-sm text-gray-500">250px</span>
        </div>
        <div class="mb-4">
          <h4 class="text-sm font-semibold text-gray-600 mb-2">内容内边距 (px)</h4>
          <input type="range" id="contentPaddingSlider" min="10" max="40" step="5" class="w-full">
          <span id="contentPaddingValue" class="text-sm text-gray-500">20px</span>
        </div>
        <div class="mb-4">
          <h4 class="text-sm font-semibold text-gray-600 mb-2">圆角大小</h4>
          <select id="borderRadiusSelect" class="w-full p-2 border rounded">
            <option value="4">小 (4px)</option>
            <option value="8" selected>中 (8px)</option>
            <option value="12">大 (12px)</option>
            <option value="16">超大 (16px)</option>
          </select>
        </div>
        <div class="mb-4">
          <h4 class="text-sm font-semibold text-gray-600 mb-2">功能开关</h4>
          <div class="space-y-2">
            <label class="flex items-center">
              <input type="checkbox" id="enableAnimations" class="mr-2" checked>
              <span class="text-sm">启用动画效果</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" id="enableSmoothScroll" class="mr-2" checked>
              <span class="text-sm">平滑滚动</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" id="enableButtonHoverEffects" class="mr-2" checked>
              <span class="text-sm">按钮悬停效果</span>
            </label>
          </div>
        </div>
      </div>
      <div class="p-4 border-t bg-gray-50">
        <button id="resetConfigBtn" class="w-full py-2 px-4 bg-gray-200 text-gray-700 rounded mb-2 hover:bg-gray-300 transition">
          重置配置
        </button>
        <button id="exportConfigBtn" class="w-full py-2 px-4 bg-primary text-white rounded hover:bg-primary-dark transition">
          导出配置
        </button>
      </div>
    </div>
    <button id="configPanelToggle" class="fixed top-20 right-5 bg-primary text-white p-3 rounded-full shadow-lg z-40 hover:bg-primary-dark transition transform hover:scale-110">
      <i class="fa fa-sliders"></i>
    </button>
  `;
  
  // 添加面板到页面
  $('body').append(panelHTML);
  
  // 初始化颜色选择器
  function initColorPickers() {
    var root = document.documentElement;
    $('#primaryColorPicker').val(getComputedStyle(root).getPropertyValue('--primary-color').trim());
    $('#secondaryColorPicker').val(getComputedStyle(root).getPropertyValue('--secondary-color').trim());
    $('#successColorPicker').val(getComputedStyle(root).getPropertyValue('--success-color').trim());
    $('#infoColorPicker').val(getComputedStyle(root).getPropertyValue('--info-color').trim());
    $('#warningColorPicker').val(getComputedStyle(root).getPropertyValue('--warning-color').trim());
    $('#dangerColorPicker').val(getComputedStyle(root).getPropertyValue('--danger-color').trim());
    $('#backgroundColorPicker').val(getComputedStyle(root).getPropertyValue('--background-color').trim());
    $('#textColorPicker').val(getComputedStyle(root).getPropertyValue('--text-color').trim());
  }
  
  // 初始化滑块和选择器
  function initSliders() {
    var root = document.documentElement;
    var sidebarWidth = parseInt(getComputedStyle(root).getPropertyValue('--sidebar-width').trim());
    var contentPadding = parseInt(getComputedStyle(root).getPropertyValue('--content-padding').trim());
    var borderRadius = parseInt(getComputedStyle(root).getPropertyValue('--border-radius-medium').trim());
    
    $('#sidebarWidthSlider').val(sidebarWidth);
    $('#sidebarWidthValue').text(sidebarWidth + 'px');
    
    $('#contentPaddingSlider').val(contentPadding);
    $('#contentPaddingValue').text(contentPadding + 'px');
    
    $('#borderRadiusSelect').val(borderRadius);
  }
  
  // 初始化功能开关
  function initFeatureToggles() {
    var config = window.modernConfig || {};
    var features = config.features || {};
    
    $('#enableAnimations').prop('checked', features.enableAnimations !== false);
    $('#enableSmoothScroll').prop('checked', features.enableSmoothScroll !== false);
    $('#enableButtonHoverEffects').prop('checked', features.enableButtonHoverEffects !== false);
  }
  
  // 初始化面板
  initColorPickers();
  initSliders();
  initFeatureToggles();
  
  // 添加事件监听器
  // 颜色选择器
  $('.color-picker').change(function() {
    var colorType = $(this).attr('id').replace('ColorPicker', '').toLowerCase();
    var colorValue = $(this).val();
    
    if (typeof updateModernConfig === 'function') {
      updateModernConfig({
        colors: {
          [colorType + 'Color']: colorValue
        }
      });
    }
  });
  
  // 滑块
  $('#sidebarWidthSlider').on('input', function() {
    var value = $(this).val();
    $('#sidebarWidthValue').text(value + 'px');
    
    if (typeof updateModernConfig === 'function') {
      updateModernConfig({
        layout: {
          sidebarWidth: parseInt(value)
        }
      });
    }
  });
  
  $('#contentPaddingSlider').on('input', function() {
    var value = $(this).val();
    $('#contentPaddingValue').text(value + 'px');
    
    if (typeof updateModernConfig === 'function') {
      updateModernConfig({
        layout: {
          contentPadding: parseInt(value)
        }
      });
    }
  });
  
  // 下拉选择器
  $('#borderRadiusSelect').change(function() {
    var value = parseInt($(this).val());
    
    if (typeof updateModernConfig === 'function') {
      updateModernConfig({
        border: {
          radiusSmall: value - 2,
          radiusMedium: value,
          radiusLarge: value + 4,
          radiusXl: value + 8
        }
      });
    }
  });
  
  // 功能开关
  $('.feature-toggle').change(function() {
    var featureType = $(this).attr('id').replace('enable', '').charAt(0).toLowerCase() + $(this).attr('id').replace('enable', '').slice(1);
    var isEnabled = $(this).is(':checked');
    
    if (typeof updateModernConfig === 'function') {
      updateModernConfig({
        features: {
          [featureType]: isEnabled
        }
      });
    }
  });
  
  // 面板切换
  $('#configPanelToggle').click(function() {
    $('#configPanel').toggleClass('translate-x-full');
  });
  
  // 关闭面板
  $('#closeConfigPanel').click(function() {
    $('#configPanel').addClass('translate-x-full');
  });
  
  // 重置配置
  $('#resetConfigBtn').click(function() {
    $.ajax({
      type: "GET",
      url: "public/js/modern-config.example.json",
      dataType: 'json',
      async: true,
      success: function(config) {
        window.modernConfig = config;
        if (typeof updateModernConfig === 'function') {
          updateModernConfig(config);
        }
        
        // 重新初始化控件
        initColorPickers();
        initSliders();
        initFeatureToggles();
      }
    });
  });
  
  // 导出配置
  $('#exportConfigBtn').click(function() {
    var config = window.modernConfig || {};
    var dataStr = JSON.stringify(config, null, 2);
    var dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    
    var exportFileDefaultName = 'modern-config.json';
    
    var linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
  });
  
  // 点击面板外部关闭
  $(document).click(function(event) {
    var panel = $('#configPanel');
    var toggle = $('#configPanelToggle');
    
    if (!panel.is(event.target) && panel.has(event.target).length === 0 &&
        !toggle.is(event.target) && toggle.has(event.target).length === 0) {
      if (!panel.hasClass('translate-x-full')) {
        panel.addClass('translate-x-full');
      }
    }
  });
}

// 在DOM加载完成后添加配置面板
$(document).ready(function() {
  // 检查是否启用了配置面板功能
  var config = window.modernConfig || {};
  var features = config.features || {};
  
  if (features.enableConfigPanel !== false) {
    createConfigPanel();
  }
});
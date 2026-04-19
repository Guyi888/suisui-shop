// 现代化样式配置文件
var modernConfig = {
  // 主题配置
  theme: {
    // 主色调
    primaryColor: '#667eea',
    // 辅助色调
    secondaryColor: '#764ba2',
    // 成功色
    successColor: '#4ade80',
    // 警告色
    warningColor: '#fbbf24',
    // 危险色
    dangerColor: '#f87171',
    // 信息色
    infoColor: '#60a5fa',
    // 背景色
    backgroundColor: '#f8f9fa',
    // 卡片背景色
    cardBackgroundColor: '#ffffff',
    // 侧边栏背景色
    sidebarBackgroundColor: '#2d3748',
    // 文本颜色
    textColor: '#333333',
    // 次要文本颜色
    secondaryTextColor: '#6b7280',
    // 边框颜色
    borderColor: '#e2e8f0'
  },
  
  // 布局配置
  layout: {
    // 侧边栏宽度
    sidebarWidth: 250,
    // 内容区域边距
    contentPadding: 20,
    // 卡片间距
    cardSpacing: 20,
    // 响应式断点 - 小屏幕
    smallBreakpoint: 768,
    // 响应式断点 - 中屏幕
    mediumBreakpoint: 992,
    // 响应式断点 - 大屏幕
    largeBreakpoint: 1200
  },
  
  // 动画配置
  animation: {
    // 启用动画
    enabled: true,
    // 淡入动画持续时间(毫秒)
    fadeInDuration: 500,
    // 悬停动画持续时间(毫秒)
    hoverDuration: 300,
    // 滚动动画持续时间(毫秒)
    scrollDuration: 500
  },
  
  // 字体配置
  typography: {
    // 主要字体
    fontFamily: '"Microsoft YaHei", "微软雅黑", Arial, sans-serif',
    // 标题字体大小
    headingSize: {
      h1: 2.5, // rem
      h2: 2.0, // rem
      h3: 1.75, // rem
      h4: 1.5, // rem
      h5: 1.25, // rem
      h6: 1.0 // rem
    },
    // 正文字体大小(rem)
    fontSize: 1.0,
    // 行高
    lineHeight: 1.6
  },
  
  // 圆角配置
  borderRadius: {
    // 小圆角(px)
    small: 4,
    // 中圆角(px)
    medium: 8,
    // 大圆角(px)
    large: 12,
    // 全圆角(px) - 用于圆形元素
    full: 9999
  },
  
  // 阴影配置
  shadow: {
    // 小阴影
    small: '0 2px 4px rgba(0, 0, 0, 0.05)',
    // 中阴影
    medium: '0 4px 6px rgba(0, 0, 0, 0.1)',
    // 大阴影
    large: '0 10px 15px rgba(0, 0, 0, 0.1)',
    // 悬停阴影
    hover: '0 8px 30px rgba(0, 0, 0, 0.12)'
  },
  
  // 功能开关
  features: {
    // 启用样式切换按钮
    enableStyleToggle: true,
    // 启用动画效果
    enableAnimations: true,
    // 启用滚动平滑效果
    enableSmoothScroll: true,
    // 启用按钮悬停效果
    enableButtonHoverEffects: true,
    // 启用表格悬停效果
    enableTableHoverEffects: true,
    // 启用响应式布局
    enableResponsiveLayout: true,
    // 启用深色模式切换
    enableDarkModeToggle: false
  }
};

// 应用配置到页面
function applyModernConfig() {
  // 创建样式变量
  var styleVars = `
    --primary-color: ${modernConfig.theme.primaryColor};
    --secondary-color: ${modernConfig.theme.secondaryColor};
    --success-color: ${modernConfig.theme.successColor};
    --warning-color: ${modernConfig.theme.warningColor};
    --danger-color: ${modernConfig.theme.dangerColor};
    --info-color: ${modernConfig.theme.infoColor};
    --background-color: ${modernConfig.theme.backgroundColor};
    --card-background-color: ${modernConfig.theme.cardBackgroundColor};
    --sidebar-background-color: ${modernConfig.theme.sidebarBackgroundColor};
    --text-color: ${modernConfig.theme.textColor};
    --secondary-text-color: ${modernConfig.theme.secondaryTextColor};
    --border-color: ${modernConfig.theme.borderColor};
    
    --sidebar-width: ${modernConfig.layout.sidebarWidth}px;
    --content-padding: ${modernConfig.layout.contentPadding}px;
    --card-spacing: ${modernConfig.layout.cardSpacing}px;
    
    --small-breakpoint: ${modernConfig.layout.smallBreakpoint}px;
    --medium-breakpoint: ${modernConfig.layout.mediumBreakpoint}px;
    --large-breakpoint: ${modernConfig.layout.largeBreakpoint}px;
    
    --fade-in-duration: ${modernConfig.animation.fadeInDuration}ms;
    --hover-duration: ${modernConfig.animation.hoverDuration}ms;
    --scroll-duration: ${modernConfig.animation.scrollDuration}ms;
    
    --font-family: ${modernConfig.typography.fontFamily};
    --font-size: ${modernConfig.typography.fontSize}rem;
    --line-height: ${modernConfig.typography.lineHeight};
    
    --border-radius-small: ${modernConfig.borderRadius.small}px;
    --border-radius-medium: ${modernConfig.borderRadius.medium}px;
    --border-radius-large: ${modernConfig.borderRadius.large}px;
    --border-radius-full: ${modernConfig.borderRadius.full}px;
    
    --shadow-small: ${modernConfig.shadow.small};
    --shadow-medium: ${modernConfig.shadow.medium};
    --shadow-large: ${modernConfig.shadow.large};
    --shadow-hover: ${modernConfig.shadow.hover};
  `;
  
  // 添加到根元素
  var styleTag = $('<style>:root {' + styleVars + '}</style>');
  $('head').append(styleTag);
  
  // 更新modern.js中的配置
  if (window.updateModernConfig) {
    window.updateModernConfig(modernConfig);
  }
}

// 当DOM加载完成时应用配置
$(document).ready(function() {
  applyModernConfig();
});
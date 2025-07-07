# AI智能网站优化器 - WordPress插件安装说明

## 重要提示

这是一个WordPress插件，**不是Flask应用**。必须在WordPress环境中测试和使用。

## 可用的插件版本

我已经创建了以下几个版本，从简单到复杂：

### 1. 最小测试版 (ai-optimizer-minimal.php)
- **用途**：测试WordPress是否能正常加载插件
- **功能**：仅包含一个简单的菜单项
- **推荐**：首先测试这个版本

### 2. 简化版 (ai-website-optimizer-simplified.php)
- **用途**：基本功能测试
- **功能**：包含仪表盘和设置页面
- **推荐**：如果最小版本工作正常，测试这个

### 3. 完整修复版 (ai-website-optimizer-fixed.php)
- **用途**：完整功能版本
- **功能**：包含所有页面和功能
- **推荐**：最终使用版本

## 如何在WordPress中测试

### 方法1：本地WordPress测试
1. 安装本地WordPress环境（如XAMPP、WAMP、MAMP或Local by Flywheel）
2. 将插件文件复制到 `wp-content/plugins/ai-website-optimizer/` 目录
3. 在WordPress后台的"插件"页面激活插件

### 方法2：在线WordPress测试
1. 登录您的WordPress网站后台
2. 进入"插件" > "安装插件" > "上传插件"
3. 将插件文件打包成ZIP文件上传
4. 激活插件

### 方法3：WordPress.com或其他托管服务
- 确保您的托管计划支持自定义插件
- 按照托管服务提供的说明上传和激活插件

## 插件文件结构

```
ai-website-optimizer/
├── ai-website-optimizer-fixed.php    # 主插件文件（推荐使用）
├── admin/
│   └── assets/
│       ├── css/
│       │   └── admin-style.css      # 管理后台样式
│       └── js/
│           └── admin-script.js      # 管理后台脚本
├── includes/                        # 核心功能类（可选）
├── languages/                       # 语言文件（可选）
└── README-WORDPRESS.md             # 本说明文件
```

## 常见问题

### Q: 为什么看到Flask/Gunicorn错误？
A: 这是WordPress插件，不能作为Flask应用运行。必须在WordPress环境中使用。

### Q: 激活插件时出现错误怎么办？
1. 检查PHP版本（建议7.4+）
2. 查看WordPress调试日志
3. 尝试使用最小测试版
4. 确保文件权限正确（通常是644）

### Q: 如何启用WordPress调试？
在 `wp-config.php` 中添加：
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## 插件功能

完整版插件包含：
- ✓ 仪表盘：显示网站状态概览
- ✓ 性能监控：实时监控网站性能
- ✓ SEO优化：AI驱动的SEO分析
- ✓ AI工具：内容生成功能
- ✓ 设置页面：配置API密钥和功能开关

## 需要帮助？

如果您在WordPress环境中测试插件仍然遇到问题，请提供：
1. WordPress版本
2. PHP版本
3. 具体的错误信息
4. WordPress调试日志内容

这样我可以更准确地帮您解决问题。
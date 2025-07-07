<?php
// WordPress Plugin Test Page
// 这个文件用于在Replit环境中测试WordPress插件功能

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 模拟WordPress环境
define('ABSPATH', dirname(__FILE__) . '/');
define('WP_PLUGIN_DIR', ABSPATH . 'wp-content/plugins');

// 检查插件文件
$plugin_file = 'ai-website-optimizer.php';
$plugin_exists = file_exists($plugin_file);

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI智能网站优化器 - WordPress插件</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .status-success {
            color: #28a745;
        }
        .status-error {
            color: #dc3545;
        }
        .plugin-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        pre {
            background: #2d3748;
            color: #00ff00;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h1 class="text-center mb-4">🤖 AI智能网站优化器</h1>
                
                <div class="alert alert-info">
                    <h5>📦 WordPress插件状态</h5>
                    <p class="mb-0">
                        插件文件: <strong><?php echo $plugin_file; ?></strong><br>
                        状态: <?php echo $plugin_exists ? '<span class="status-success">✓ 已找到</span>' : '<span class="status-error">✗ 未找到</span>'; ?>
                    </p>
                </div>

                <div class="plugin-info">
                    <h5>🚀 插件功能特性</h5>
                    <ul>
                        <li>📊 实时网站监控与性能分析</li>
                        <li>🔍 AI驱动的SEO优化建议</li>
                        <li>🛡️ 安全漏洞检测与修复</li>
                        <li>🎨 AI内容生成（文本、图片、视频、音频）</li>
                        <li>📝 自动发布到WordPress</li>
                        <li>🤖 集成Siliconflow API</li>
                    </ul>
                </div>

                <div class="plugin-info">
                    <h5>📋 安装说明</h5>
                    <ol>
                        <li>将整个 <code>ai-website-optimizer</code> 文件夹复制到WordPress的 <code>wp-content/plugins/</code> 目录</li>
                        <li>在WordPress后台的"插件"页面激活插件</li>
                        <li>在插件设置页面配置Siliconflow API密钥</li>
                        <li>开始使用AI功能优化您的网站！</li>
                    </ol>
                </div>

                <div class="plugin-info">
                    <h5>📁 插件文件结构</h5>
                    <pre><?php
// 显示插件文件结构
$files = [
    'ai-website-optimizer.php' => '主插件文件',
    'admin/' => '管理后台文件',
    '  ├── assets/' => 'CSS、JS资源',
    '  ├── class-*.php' => '功能类文件',
    '  └── views/' => '视图模板',
    'includes/' => '核心功能库',
    'public/' => '前端功能',
    'config/' => '配置文件'
];

foreach ($files as $file => $desc) {
    echo str_pad($file, 30) . ' # ' . $desc . "\n";
}
                    ?></pre>
                </div>

                <?php if ($plugin_exists): ?>
                    <div class="plugin-info">
                        <h5>📄 插件信息</h5>
                        <?php
                        // 读取插件头部信息
                        $plugin_data = file_get_contents($plugin_file);
                        preg_match('/Plugin Name:\s*(.+)/', $plugin_data, $name);
                        preg_match('/Version:\s*(.+)/', $plugin_data, $version);
                        preg_match('/Description:\s*(.+)/', $plugin_data, $description);
                        ?>
                        <ul>
                            <li><strong>插件名称:</strong> <?php echo $name[1] ?? '未知'; ?></li>
                            <li><strong>版本:</strong> <?php echo $version[1] ?? '未知'; ?></li>
                            <li><strong>描述:</strong> <?php echo $description[1] ?? '未知'; ?></li>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="https://github.com/your-repo/ai-website-optimizer" class="btn btn-primary" target="_blank">
                        📥 下载插件
                    </a>
                    <a href="PLUGIN-USAGE.md" class="btn btn-secondary" target="_blank">
                        📖 使用文档
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center text-white mt-4">
            <p>Powered by AI Website Optimizer v2.0 | 集成 Siliconflow API</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
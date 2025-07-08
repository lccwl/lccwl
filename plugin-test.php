<?php
/**
 * WordPress插件功能测试工具
 * 用于验证AI智能网站优化器插件的基本功能
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    // 如果不在WordPress环境中，模拟基本环境
    define('ABSPATH', dirname(__FILE__) . '/');
    define('WP_DEBUG', true);
    
    // 模拟WordPress函数
    if (!function_exists('wp_die')) {
        function wp_die($message) {
            die($message);
        }
    }
    
    if (!function_exists('current_user_can')) {
        function current_user_can($capability) {
            return true; // 测试环境假设有权限
        }
    }
    
    if (!function_exists('wp_verify_nonce')) {
        function wp_verify_nonce($nonce, $action) {
            return true; // 测试环境跳过验证
        }
    }
    
    if (!function_exists('wp_send_json_success')) {
        function wp_send_json_success($data) {
            header('Content-Type: application/json');
            echo json_encode(array('success' => true, 'data' => $data));
            exit;
        }
    }
    
    if (!function_exists('wp_send_json_error')) {
        function wp_send_json_error($data) {
            header('Content-Type: application/json');
            echo json_encode(array('success' => false, 'data' => $data));
            exit;
        }
    }
    
    if (!function_exists('sanitize_text_field')) {
        function sanitize_text_field($str) {
            return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
        }
    }
    
    if (!function_exists('get_option')) {
        function get_option($option, $default = false) {
            // 简单的选项存储
            static $options = array();
            return isset($options[$option]) ? $options[$option] : $default;
        }
    }
    
    if (!function_exists('update_option')) {
        function update_option($option, $value) {
            static $options = array();
            $options[$option] = $value;
            return true;
        }
    }
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>WordPress插件测试工具</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
            .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
            .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
            .warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
            .info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
            button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; }
            button:hover { background: #005a87; }
            input, textarea { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 3px; }
            pre { background: #f8f8f8; padding: 10px; border-radius: 3px; overflow: auto; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🧪 WordPress插件测试工具</h1>
            <div class='test-section info'>
                <h3>测试说明</h3>
                <p><strong>注意：</strong>这是一个WordPress插件，需要在WordPress环境中运行才能发挥完整功能。</p>
                <p>当前在非WordPress环境中运行，仅能测试基本的PHP语法和逻辑。</p>
            </div>";
}

/**
 * 测试类 - 验证插件基本功能
 */
class Plugin_Tester {
    
    public function __construct() {
        $this->run_tests();
    }
    
    /**
     * 运行所有测试
     */
    public function run_tests() {
        echo "<div class='test-section'>";
        echo "<h3>🔍 PHP语法检查</h3>";
        
        // 检查插件文件是否存在
        $plugin_files = array(
            'ai-website-optimizer-fixed.php' => '完全修复版（推荐）',
            'ai-website-optimizer.php' => '原始版本'
        );
        
        foreach ($plugin_files as $file => $description) {
            if (file_exists($file)) {
                echo "<div class='success'>✅ 找到插件文件: $file ($description)</div>";
                
                // 检查PHP语法
                $syntax_check = $this->check_php_syntax($file);
                if ($syntax_check['valid']) {
                    echo "<div class='success'>✅ PHP语法检查通过</div>";
                } else {
                    echo "<div class='error'>❌ PHP语法错误: " . $syntax_check['error'] . "</div>";
                }
            } else {
                echo "<div class='warning'>⚠️ 未找到插件文件: $file</div>";
            }
        }
        echo "</div>";
        
        // 测试API调用功能
        $this->test_api_functionality();
        
        // 测试基本功能
        $this->test_basic_functions();
        
        // 显示安装说明
        $this->show_installation_guide();
    }
    
    /**
     * 检查PHP语法
     */
    private function check_php_syntax($file) {
        $output = array();
        $return_code = 0;
        
        exec("php -l '$file' 2>&1", $output, $return_code);
        
        if ($return_code === 0) {
            return array('valid' => true);
        } else {
            return array('valid' => false, 'error' => implode("\n", $output));
        }
    }
    
    /**
     * 测试API功能
     */
    private function test_api_functionality() {
        echo "<div class='test-section'>";
        echo "<h3>🌐 API功能测试</h3>";
        
        if (function_exists('wp_remote_post')) {
            echo "<div class='success'>✅ WordPress HTTP API可用</div>";
        } else {
            echo "<div class='warning'>⚠️ WordPress HTTP API不可用（非WordPress环境）</div>";
            echo "<div class='info'>ℹ️ 在WordPress环境中会自动使用wp_remote_post函数</div>";
        }
        
        // 测试cURL是否可用
        if (function_exists('curl_init')) {
            echo "<div class='success'>✅ cURL扩展可用</div>";
        } else {
            echo "<div class='error'>❌ cURL扩展不可用 - 需要安装curl扩展</div>";
        }
        
        // 测试JSON功能
        if (function_exists('json_encode') && function_exists('json_decode')) {
            echo "<div class='success'>✅ JSON功能可用</div>";
        } else {
            echo "<div class='error'>❌ JSON功能不可用</div>";
        }
        
        echo "</div>";
    }
    
    /**
     * 测试基本功能
     */
    private function test_basic_functions() {
        echo "<div class='test-section'>";
        echo "<h3>⚙️ 基本功能测试</h3>";
        
        // 测试内存限制
        $memory_limit = ini_get('memory_limit');
        $memory_in_bytes = $this->convert_to_bytes($memory_limit);
        
        if ($memory_in_bytes >= 134217728) { // 128MB
            echo "<div class='success'>✅ 内存限制充足: $memory_limit</div>";
        } else {
            echo "<div class='warning'>⚠️ 内存限制较低: $memory_limit (建议至少128MB)</div>";
        }
        
        // 测试执行时间限制
        $max_execution_time = ini_get('max_execution_time');
        if ($max_execution_time >= 60 || $max_execution_time == 0) {
            echo "<div class='success'>✅ 执行时间限制合适: $max_execution_time 秒</div>";
        } else {
            echo "<div class='warning'>⚠️ 执行时间限制较短: $max_execution_time 秒</div>";
        }
        
        // 测试文件上传
        $upload_max = ini_get('upload_max_filesize');
        echo "<div class='info'>ℹ️ 文件上传限制: $upload_max</div>";
        
        echo "</div>";
    }
    
    /**
     * 显示安装说明
     */
    private function show_installation_guide() {
        echo "<div class='test-section'>";
        echo "<h3>📋 WordPress安装说明</h3>";
        echo "<div class='info'>";
        echo "<h4>1. 下载推荐版本</h4>";
        echo "<p>请使用 <code>ai-website-optimizer-fixed.php</code> 完全修复版本</p>";
        
        echo "<h4>2. 安装步骤</h4>";
        echo "<ol>";
        echo "<li>将插件文件上传到WordPress的 <code>wp-content/plugins/</code> 目录</li>";
        echo "<li>在WordPress后台进入「插件」页面</li>";
        echo "<li>找到「AI智能网站优化器」并点击「激活」</li>";
        echo "<li>配置Siliconflow API密钥</li>";
        echo "</ol>";
        
        echo "<h4>3. 获取API密钥</h4>";
        echo "<ol>";
        echo "<li>访问 <a href='https://cloud.siliconflow.cn/' target='_blank'>Siliconflow官网</a></li>";
        echo "<li>注册账户并登录</li>";
        echo "<li>在控制台获取API密钥</li>";
        echo "<li>在插件设置中配置密钥</li>";
        echo "</ol>";
        
        echo "<h4>4. 功能特点</h4>";
        echo "<ul>";
        echo "<li>✅ AI智能SEO优化分析</li>";
        echo "<li>✅ 自动化系统巡逻监控</li>";
        echo "<li>✅ 多媒体内容AI生成</li>";
        echo "<li>✅ 竞争对手分析</li>";
        echo "<li>✅ 自动化优化建议</li>";
        echo "</ul>";
        echo "</div>";
        echo "</div>";
    }
    
    /**
     * 转换内存大小为字节
     */
    private function convert_to_bytes($size) {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;
        
        switch ($last) {
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }
        
        return $size;
    }
}

// 运行测试
new Plugin_Tester();

if (!defined('ABSPATH')) {
    echo "</div></body></html>";
}
?>
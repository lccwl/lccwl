<?php
/**
 * AI Website Optimizer - 授权管理系统
 * 
 * @package AI_Website_Optimizer
 * @subpackage License_Manager
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 授权管理类
 */
class AI_Optimizer_License_Manager {
    
    /**
     * 授权服务器URL
     */
    private $license_server = 'https://license.your-domain.com/api/';
    
    /**
     * 加密密钥
     */
    private $encryption_key;
    
    /**
     * 实例
     */
    private static $instance = null;
    
    /**
     * 获取实例
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 构造函数
     */
    private function __construct() {
        $this->encryption_key = $this->get_encryption_key();
        
        // 添加定时任务检查授权
        add_action('ai_optimizer_check_license', array($this, 'verify_license'));
        
        // 每天检查一次授权
        if (!wp_next_scheduled('ai_optimizer_check_license')) {
            wp_schedule_event(time(), 'daily', 'ai_optimizer_check_license');
        }
    }
    
    /**
     * 获取加密密钥
     */
    private function get_encryption_key() {
        $key = get_option('ai_optimizer_encryption_key');
        
        if (!$key) {
            $key = wp_generate_password(64, true, true);
            update_option('ai_optimizer_encryption_key', $key);
        }
        
        return $key;
    }
    
    /**
     * 验证卡密
     */
    public function activate_license($license_key) {
        // 基础验证
        if (empty($license_key)) {
            return array('success' => false, 'message' => '请输入授权卡密');
        }
        
        // 格式验证：XXXX-XXXX-XXXX-XXXX
        if (!preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license_key)) {
            return array('success' => false, 'message' => '卡密格式不正确');
        }
        
        // 获取网站信息
        $site_data = $this->get_site_data();
        
        // 准备请求数据
        $request_data = array(
            'action' => 'activate',
            'license_key' => $license_key,
            'domain' => $site_data['domain'],
            'site_url' => $site_data['site_url'],
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'plugin_version' => AI_OPT_VERSION,
            'fingerprint' => $this->generate_fingerprint()
        );
        
        // 签名请求
        $request_data['signature'] = $this->sign_request($request_data);
        
        // 发送激活请求
        $response = wp_remote_post($this->license_server . 'activate', array(
            'body' => json_encode($request_data),
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-Plugin-Version' => AI_OPT_VERSION
            ),
            'timeout' => 30,
            'sslverify' => true
        ));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => '网络连接失败，请稍后重试');
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if ($result && $result['success']) {
            // 保存授权信息
            $this->save_license_data($result['license_data']);
            
            // 清除缓存
            $this->clear_cache();
            
            return array(
                'success' => true, 
                'message' => '授权激活成功',
                'license_data' => $result['license_data']
            );
        }
        
        return array(
            'success' => false, 
            'message' => $result['message'] ?? '授权激活失败'
        );
    }
    
    /**
     * 验证授权状态
     */
    public function verify_license() {
        $license_data = $this->get_license_data();
        
        if (!$license_data) {
            return false;
        }
        
        // 检查本地过期时间
        if (isset($license_data['expires_at']) && strtotime($license_data['expires_at']) < time()) {
            $this->deactivate_license('授权已过期');
            return false;
        }
        
        // 验证数据完整性
        if (!$this->verify_license_integrity($license_data)) {
            $this->deactivate_license('授权数据被篡改');
            return false;
        }
        
        // 定期在线验证
        if ($this->should_verify_online()) {
            return $this->verify_license_online();
        }
        
        return true;
    }
    
    /**
     * 在线验证授权
     */
    private function verify_license_online() {
        $license_data = $this->get_license_data();
        
        if (!$license_data) {
            return false;
        }
        
        $request_data = array(
            'action' => 'verify',
            'license_key' => $license_data['license_key'],
            'domain' => $this->get_site_data()['domain'],
            'fingerprint' => $this->generate_fingerprint()
        );
        
        $request_data['signature'] = $this->sign_request($request_data);
        
        $response = wp_remote_post($this->license_server . 'verify', array(
            'body' => json_encode($request_data),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            // 网络错误时使用本地缓存
            return true;
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if ($result && $result['valid']) {
            // 更新最后验证时间
            update_option('ai_optimizer_last_verify', time());
            
            // 更新授权数据
            if (isset($result['license_data'])) {
                $this->save_license_data($result['license_data']);
            }
            
            return true;
        }
        
        // 授权无效
        $this->deactivate_license($result['message'] ?? '授权验证失败');
        return false;
    }
    
    /**
     * 获取授权信息
     */
    public function get_license_info() {
        $license_data = $this->get_license_data();
        
        if (!$license_data || !$this->verify_license()) {
            return null;
        }
        
        return array(
            'type' => $license_data['type'] ?? 'basic',
            'status' => 'active',
            'expires_at' => $license_data['expires_at'] ?? null,
            'limits' => $license_data['limits'] ?? array(),
            'features' => $license_data['features'] ?? array()
        );
    }
    
    /**
     * 检查功能权限
     */
    public function has_feature($feature) {
        $license_info = $this->get_license_info();
        
        if (!$license_info) {
            return false;
        }
        
        // 检查功能列表
        if (isset($license_info['features']) && is_array($license_info['features'])) {
            return in_array($feature, $license_info['features']);
        }
        
        // 根据授权类型判断
        $feature_map = array(
            'basic' => array('monitoring', 'seo_basic'),
            'pro' => array('monitoring', 'seo_basic', 'seo_advanced', 'ai_text', 'ai_image'),
            'enterprise' => array('monitoring', 'seo_basic', 'seo_advanced', 'ai_text', 'ai_image', 'ai_video', 'ai_audio', 'api_unlimited')
        );
        
        $type = $license_info['type'] ?? 'basic';
        
        return isset($feature_map[$type]) && in_array($feature, $feature_map[$type]);
    }
    
    /**
     * 检查使用限制
     */
    public function check_limit($resource, $amount = 1) {
        $license_info = $this->get_license_info();
        
        if (!$license_info) {
            return false;
        }
        
        // 企业版无限制
        if ($license_info['type'] === 'enterprise') {
            return true;
        }
        
        // 获取当前使用量
        $usage = $this->get_usage($resource);
        $limit = $license_info['limits'][$resource] ?? 0;
        
        // 检查是否超出限制
        if ($usage + $amount > $limit) {
            return false;
        }
        
        // 记录使用量
        $this->record_usage($resource, $amount);
        
        return true;
    }
    
    /**
     * 生成设备指纹
     */
    private function generate_fingerprint() {
        $data = array(
            'server_name' => $_SERVER['SERVER_NAME'] ?? '',
            'server_addr' => $_SERVER['SERVER_ADDR'] ?? '',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'plugin_path' => AI_OPT_PLUGIN_PATH
        );
        
        return hash('sha256', serialize($data));
    }
    
    /**
     * 签名请求
     */
    private function sign_request($data) {
        ksort($data);
        $string = http_build_query($data);
        return hash_hmac('sha256', $string, $this->encryption_key);
    }
    
    /**
     * 验证授权数据完整性
     */
    private function verify_license_integrity($license_data) {
        if (!isset($license_data['hash'])) {
            return false;
        }
        
        $hash = $license_data['hash'];
        unset($license_data['hash']);
        
        $calculated_hash = hash_hmac('sha256', serialize($license_data), $this->encryption_key);
        
        return hash_equals($hash, $calculated_hash);
    }
    
    /**
     * 保存授权数据
     */
    private function save_license_data($data) {
        // 添加完整性哈希
        $data['hash'] = hash_hmac('sha256', serialize($data), $this->encryption_key);
        
        // 加密存储
        $encrypted = $this->encrypt_data($data);
        update_option('ai_optimizer_license', $encrypted);
        
        // 清除缓存
        wp_cache_delete('ai_optimizer_license_info');
    }
    
    /**
     * 获取授权数据
     */
    private function get_license_data() {
        $encrypted = get_option('ai_optimizer_license');
        
        if (!$encrypted) {
            return null;
        }
        
        return $this->decrypt_data($encrypted);
    }
    
    /**
     * 加密数据
     */
    private function encrypt_data($data) {
        $method = 'AES-256-CBC';
        $key = substr(hash('sha256', $this->encryption_key, true), 0, 32);
        $iv = openssl_random_pseudo_bytes(16);
        
        $encrypted = openssl_encrypt(serialize($data), $method, $key, 0, $iv);
        
        return base64_encode($encrypted . '::' . $iv);
    }
    
    /**
     * 解密数据
     */
    private function decrypt_data($encrypted) {
        $method = 'AES-256-CBC';
        $key = substr(hash('sha256', $this->encryption_key, true), 0, 32);
        
        list($encrypted_data, $iv) = explode('::', base64_decode($encrypted), 2);
        
        $decrypted = openssl_decrypt($encrypted_data, $method, $key, 0, $iv);
        
        return $decrypted ? unserialize($decrypted) : null;
    }
    
    /**
     * 获取网站数据
     */
    private function get_site_data() {
        return array(
            'domain' => parse_url(home_url(), PHP_URL_HOST),
            'site_url' => home_url(),
            'admin_email' => get_option('admin_email')
        );
    }
    
    /**
     * 是否需要在线验证
     */
    private function should_verify_online() {
        $last_verify = get_option('ai_optimizer_last_verify', 0);
        $interval = 12 * HOUR_IN_SECONDS; // 12小时验证一次
        
        return (time() - $last_verify) > $interval;
    }
    
    /**
     * 停用授权
     */
    public function deactivate_license($reason = '') {
        delete_option('ai_optimizer_license');
        delete_option('ai_optimizer_last_verify');
        
        // 记录停用日志
        AI_Optimizer_Utils::log('License deactivated: ' . $reason, 'warning');
        
        // 清除缓存
        $this->clear_cache();
    }
    
    /**
     * 获取使用量
     */
    private function get_usage($resource) {
        $usage_data = get_option('ai_optimizer_usage', array());
        $month_key = date('Y-m');
        
        return $usage_data[$month_key][$resource] ?? 0;
    }
    
    /**
     * 记录使用量
     */
    private function record_usage($resource, $amount) {
        $usage_data = get_option('ai_optimizer_usage', array());
        $month_key = date('Y-m');
        
        if (!isset($usage_data[$month_key])) {
            $usage_data[$month_key] = array();
        }
        
        $usage_data[$month_key][$resource] = ($usage_data[$month_key][$resource] ?? 0) + $amount;
        
        update_option('ai_optimizer_usage', $usage_data);
    }
    
    /**
     * 清除缓存
     */
    private function clear_cache() {
        wp_cache_delete('ai_optimizer_license_info');
        wp_cache_delete('ai_optimizer_features');
    }
    
    /**
     * 生成卡密
     * 供管理员使用
     */
    public static function generate_license_key() {
        $segments = array();
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        for ($i = 0; $i < 4; $i++) {
            $segment = '';
            for ($j = 0; $j < 4; $j++) {
                $segment .= $chars[rand(0, strlen($chars) - 1)];
            }
            $segments[] = $segment;
        }
        
        return implode('-', $segments);
    }
}
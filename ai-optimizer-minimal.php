<?php
/**
 * Plugin Name: AI智能优化器-最小测试版
 * Description: 最小化测试版本
 * Version: 1.0.0
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 添加菜单
add_action('admin_menu', 'ai_optimizer_minimal_menu');

function ai_optimizer_minimal_menu() {
    add_menu_page(
        'AI优化器',
        'AI优化器', 
        'manage_options',
        'ai-optimizer-minimal',
        'ai_optimizer_minimal_page'
    );
}

function ai_optimizer_minimal_page() {
    ?>
    <div class="wrap">
        <h1>AI智能优化器 - 最小测试版</h1>
        <p>插件已成功激活！</p>
    </div>
    <?php
}
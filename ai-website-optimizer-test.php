<?php
/**
 * Plugin Name: AI智能网站优化器 - 测试版
 * Description: 测试插件基本功能
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// 测试基本功能
function ai_optimizer_test_init() {
    add_action('admin_menu', 'ai_optimizer_test_menu');
}
add_action('init', 'ai_optimizer_test_init');

function ai_optimizer_test_menu() {
    add_menu_page(
        'AI优化器测试',
        'AI优化器测试',
        'manage_options',
        'ai-optimizer-test',
        'ai_optimizer_test_page'
    );
}

function ai_optimizer_test_page() {
    echo '<div class="wrap">';
    echo '<h1>AI优化器测试页面</h1>';
    echo '<p>插件基本功能正常。</p>';
    echo '</div>';
}
/**
 * AI智能网站优化器 - 管理后台脚本
 */

jQuery(document).ready(function($) {
    
    // 测试API连接
    $('#test-api-connection').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $result = $('#test-result');
        var $content = $('#test-result-content');
        
        $button.prop('disabled', true).text('测试中...');
        
        $.ajax({
            url: ai_optimizer.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_opt_test_api',
                nonce: ai_optimizer.nonce
            },
            success: function(response) {
                $result.show();
                if (response.success) {
                    $content.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                } else {
                    $content.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $result.show();
                $content.html('<div class="notice notice-error"><p>连接失败，请检查网络设置。</p></div>');
            },
            complete: function() {
                $button.prop('disabled', false).text('测试API连接');
            }
        });
    });
    
    // 保存设置
    $('#ai-optimizer-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $button = $form.find('.button-primary');
        
        $button.prop('disabled', true).text('保存中...');
        
        $.ajax({
            url: ai_optimizer.ajax_url,
            type: 'POST',
            data: {
                action: 'ai_opt_save_settings',
                nonce: ai_optimizer.nonce,
                api_key: $('#api_key').val(),
                enable_monitoring: $('input[name="enable_monitoring"]').is(':checked') ? 1 : 0,
                enable_seo: $('input[name="enable_seo"]').is(':checked') ? 1 : 0,
                enable_ai_tools: $('input[name="enable_ai_tools"]').is(':checked') ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    // 显示成功消息
                    var notice = '<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>';
                    $('.wrap > h1').after(notice);
                    
                    // 自动隐藏消息
                    setTimeout(function() {
                        $('.notice.is-dismissible').fadeOut();
                    }, 3000);
                } else {
                    alert('保存失败：' + response.data.message);
                }
            },
            error: function() {
                alert('保存失败，请稍后重试。');
            },
            complete: function() {
                $button.prop('disabled', false).text('保存设置');
            }
        });
    });
    
    // 统计卡片动画
    $('.stat-value').each(function() {
        var $this = $(this);
        var value = parseInt($this.text());
        
        $this.text('0%');
        
        $({ counter: 0 }).animate({
            counter: value
        }, {
            duration: 1000,
            easing: 'swing',
            step: function() {
                $this.text(Math.ceil(this.counter) + '%');
            }
        });
    });
    
});
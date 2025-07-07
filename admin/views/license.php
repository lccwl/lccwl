<?php
/**
 * 授权管理页面
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

$license_manager = AI_Optimizer_License_Manager::get_instance();
$license_info = $license_manager->get_license_info();
?>

<div class="ai-optimizer-admin-page">
    <div class="ai-optimizer-header">
        <h1><?php _e('授权管理', 'ai-website-optimizer'); ?></h1>
        <p><?php _e('管理您的AI网站优化器授权', 'ai-website-optimizer'); ?></p>
    </div>
    
    <?php if ($license_info): ?>
        <!-- 已激活授权 -->
        <div class="ai-optimizer-card ai-optimizer-license-active">
            <div class="ai-optimizer-card-title">
                <i class="fas fa-check-circle"></i> <?php _e('授权已激活', 'ai-website-optimizer'); ?>
            </div>
            
            <div class="ai-optimizer-license-info">
                <div class="ai-optimizer-grid ai-optimizer-grid-3">
                    <div class="ai-optimizer-info-box">
                        <div class="ai-optimizer-info-label"><?php _e('授权类型', 'ai-website-optimizer'); ?></div>
                        <div class="ai-optimizer-info-value">
                            <?php
                            $type_labels = array(
                                'basic' => '基础版',
                                'pro' => '专业版',
                                'enterprise' => '企业版'
                            );
                            echo esc_html($type_labels[$license_info['type']] ?? $license_info['type']);
                            ?>
                        </div>
                    </div>
                    
                    <div class="ai-optimizer-info-box">
                        <div class="ai-optimizer-info-label"><?php _e('授权状态', 'ai-website-optimizer'); ?></div>
                        <div class="ai-optimizer-info-value text-success">
                            <i class="fas fa-check"></i> <?php _e('正常', 'ai-website-optimizer'); ?>
                        </div>
                    </div>
                    
                    <div class="ai-optimizer-info-box">
                        <div class="ai-optimizer-info-label"><?php _e('到期时间', 'ai-website-optimizer'); ?></div>
                        <div class="ai-optimizer-info-value">
                            <?php 
                            if ($license_info['expires_at']) {
                                echo date('Y-m-d', strtotime($license_info['expires_at']));
                            } else {
                                echo '永久授权';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- 功能权限 -->
                <div class="ai-optimizer-features">
                    <h3><?php _e('功能权限', 'ai-website-optimizer'); ?></h3>
                    <div class="ai-optimizer-grid ai-optimizer-grid-2">
                        <?php
                        $all_features = array(
                            'monitoring' => '网站监控',
                            'seo_basic' => '基础SEO优化',
                            'seo_advanced' => '高级SEO优化',
                            'ai_text' => 'AI文本生成',
                            'ai_image' => 'AI图片生成',
                            'ai_video' => 'AI视频生成',
                            'ai_audio' => 'AI音频生成',
                            'api_unlimited' => '无限API调用'
                        );
                        
                        foreach ($all_features as $feature => $label):
                            $has_feature = $license_manager->has_feature($feature);
                        ?>
                        <div class="ai-optimizer-feature-item">
                            <i class="fas fa-<?php echo $has_feature ? 'check-circle text-success' : 'times-circle text-muted'; ?>"></i>
                            <span class="<?php echo $has_feature ? '' : 'text-muted'; ?>"><?php echo esc_html($label); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- 使用限制 -->
                <?php if ($license_info['type'] !== 'enterprise' && !empty($license_info['limits'])): ?>
                <div class="ai-optimizer-usage-limits">
                    <h3><?php _e('使用限制（本月）', 'ai-website-optimizer'); ?></h3>
                    <div class="ai-optimizer-grid ai-optimizer-grid-3">
                        <?php
                        $limit_labels = array(
                            'text_generation' => '文本生成',
                            'image_generation' => '图片生成',
                            'video_generation' => '视频生成',
                            'audio_generation' => '音频生成'
                        );
                        
                        foreach ($limit_labels as $resource => $label):
                            if (isset($license_info['limits'][$resource])):
                                $usage = get_option('ai_optimizer_usage', array());
                                $month_key = date('Y-m');
                                $used = $usage[$month_key][$resource] ?? 0;
                                $limit = $license_info['limits'][$resource];
                                $percentage = $limit > 0 ? ($used / $limit * 100) : 0;
                        ?>
                        <div class="ai-optimizer-usage-item">
                            <div class="ai-optimizer-usage-label"><?php echo esc_html($label); ?></div>
                            <div class="ai-optimizer-progress">
                                <div class="ai-optimizer-progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <div class="ai-optimizer-usage-text">
                                <?php echo $used; ?> / <?php echo $limit; ?>
                            </div>
                        </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="ai-optimizer-license-actions">
                    <button class="ai-optimizer-btn ai-optimizer-btn-secondary" id="deactivate-license">
                        <i class="fas fa-times"></i> <?php _e('停用授权', 'ai-website-optimizer'); ?>
                    </button>
                    <?php if ($license_info['type'] !== 'enterprise'): ?>
                    <a href="https://your-domain.com/upgrade" class="ai-optimizer-btn ai-optimizer-btn-primary" target="_blank">
                        <i class="fas fa-rocket"></i> <?php _e('升级授权', 'ai-website-optimizer'); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <!-- 未激活授权 -->
        <div class="ai-optimizer-card">
            <div class="ai-optimizer-card-title">
                <i class="fas fa-key"></i> <?php _e('激活授权', 'ai-website-optimizer'); ?>
            </div>
            
            <form id="license-activation-form">
                <div class="ai-optimizer-form-group">
                    <label class="ai-optimizer-label"><?php _e('授权卡密', 'ai-website-optimizer'); ?></label>
                    <input type="text" class="ai-optimizer-input" id="license-key" 
                           placeholder="XXXX-XXXX-XXXX-XXXX" 
                           pattern="[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}"
                           maxlength="19" required>
                    <small class="ai-optimizer-help-text">
                        <?php _e('请输入您的授权卡密，格式为：XXXX-XXXX-XXXX-XXXX', 'ai-website-optimizer'); ?>
                    </small>
                </div>
                
                <button type="submit" class="ai-optimizer-btn ai-optimizer-btn-primary">
                    <i class="fas fa-check"></i> <?php _e('激活授权', 'ai-website-optimizer'); ?>
                </button>
            </form>
        </div>
        
        <!-- 授权套餐介绍 -->
        <div class="ai-optimizer-pricing">
            <h2><?php _e('选择适合您的套餐', 'ai-website-optimizer'); ?></h2>
            
            <div class="ai-optimizer-grid ai-optimizer-grid-3">
                <!-- 基础版 -->
                <div class="ai-optimizer-pricing-card">
                    <div class="ai-optimizer-pricing-header">
                        <h3><?php _e('基础版', 'ai-website-optimizer'); ?></h3>
                        <div class="ai-optimizer-price">
                            <span class="ai-optimizer-currency">￥</span>
                            <span class="ai-optimizer-amount">99</span>
                            <span class="ai-optimizer-period">/月</span>
                        </div>
                    </div>
                    
                    <ul class="ai-optimizer-features-list">
                        <li><i class="fas fa-check"></i> 网站监控</li>
                        <li><i class="fas fa-check"></i> 基础SEO优化</li>
                        <li><i class="fas fa-check"></i> 每月100次文本生成</li>
                        <li><i class="fas fa-check"></i> 每月50张图片生成</li>
                        <li><i class="fas fa-times"></i> 视频生成</li>
                        <li><i class="fas fa-times"></i> 音频生成</li>
                    </ul>
                    
                    <a href="https://your-domain.com/purchase/basic" class="ai-optimizer-btn ai-optimizer-btn-outline" target="_blank">
                        <?php _e('立即购买', 'ai-website-optimizer'); ?>
                    </a>
                </div>
                
                <!-- 专业版 -->
                <div class="ai-optimizer-pricing-card featured">
                    <div class="ai-optimizer-badge-featured"><?php _e('推荐', 'ai-website-optimizer'); ?></div>
                    <div class="ai-optimizer-pricing-header">
                        <h3><?php _e('专业版', 'ai-website-optimizer'); ?></h3>
                        <div class="ai-optimizer-price">
                            <span class="ai-optimizer-currency">￥</span>
                            <span class="ai-optimizer-amount">299</span>
                            <span class="ai-optimizer-period">/月</span>
                        </div>
                    </div>
                    
                    <ul class="ai-optimizer-features-list">
                        <li><i class="fas fa-check"></i> 网站监控</li>
                        <li><i class="fas fa-check"></i> 高级SEO优化</li>
                        <li><i class="fas fa-check"></i> 每月500次文本生成</li>
                        <li><i class="fas fa-check"></i> 每月200张图片生成</li>
                        <li><i class="fas fa-check"></i> 每月10个视频生成</li>
                        <li><i class="fas fa-check"></i> 每月50个音频生成</li>
                    </ul>
                    
                    <a href="https://your-domain.com/purchase/pro" class="ai-optimizer-btn ai-optimizer-btn-primary" target="_blank">
                        <?php _e('立即购买', 'ai-website-optimizer'); ?>
                    </a>
                </div>
                
                <!-- 企业版 -->
                <div class="ai-optimizer-pricing-card">
                    <div class="ai-optimizer-pricing-header">
                        <h3><?php _e('企业版', 'ai-website-optimizer'); ?></h3>
                        <div class="ai-optimizer-price">
                            <span class="ai-optimizer-currency">￥</span>
                            <span class="ai-optimizer-amount">999</span>
                            <span class="ai-optimizer-period">/月</span>
                        </div>
                    </div>
                    
                    <ul class="ai-optimizer-features-list">
                        <li><i class="fas fa-check"></i> 所有功能</li>
                        <li><i class="fas fa-check"></i> 无限文本生成</li>
                        <li><i class="fas fa-check"></i> 无限图片生成</li>
                        <li><i class="fas fa-check"></i> 无限视频生成</li>
                        <li><i class="fas fa-check"></i> 无限音频生成</li>
                        <li><i class="fas fa-check"></i> 优先技术支持</li>
                    </ul>
                    
                    <a href="https://your-domain.com/purchase/enterprise" class="ai-optimizer-btn ai-optimizer-btn-outline" target="_blank">
                        <?php _e('立即购买', 'ai-website-optimizer'); ?>
                    </a>
                </div>
            </div>
            
            <div class="ai-optimizer-pricing-note">
                <p><?php _e('所有套餐均支持：', 'ai-website-optimizer'); ?></p>
                <ul>
                    <li>✓ 7天免费试用</li>
                    <li>✓ 随时取消订阅</li>
                    <li>✓ 免费技术支持</li>
                    <li>✓ 定期功能更新</li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.ai-optimizer-license-active {
    border-top: 3px solid var(--ai-success);
}

.ai-optimizer-license-info {
    padding: 20px;
}

.ai-optimizer-info-box {
    text-align: center;
    padding: 20px;
    background: var(--ai-card-bg);
    border-radius: 8px;
}

.ai-optimizer-info-label {
    font-size: 14px;
    color: var(--ai-text-muted);
    margin-bottom: 10px;
}

.ai-optimizer-info-value {
    font-size: 20px;
    font-weight: 600;
}

.ai-optimizer-features {
    margin-top: 30px;
}

.ai-optimizer-feature-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
}

.ai-optimizer-usage-limits {
    margin-top: 30px;
}

.ai-optimizer-usage-item {
    background: var(--ai-card-bg);
    padding: 20px;
    border-radius: 8px;
}

.ai-optimizer-usage-label {
    font-weight: 600;
    margin-bottom: 10px;
}

.ai-optimizer-progress {
    height: 8px;
    background: var(--ai-border);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 5px;
}

.ai-optimizer-progress-bar {
    height: 100%;
    background: var(--ai-primary);
    transition: width 0.3s ease;
}

.ai-optimizer-usage-text {
    font-size: 12px;
    color: var(--ai-text-muted);
}

.ai-optimizer-license-actions {
    margin-top: 30px;
    display: flex;
    gap: 10px;
}

/* 定价卡片 */
.ai-optimizer-pricing {
    margin-top: 40px;
}

.ai-optimizer-pricing-card {
    background: white;
    border: 1px solid var(--ai-border);
    border-radius: 12px;
    padding: 30px;
    text-align: center;
    position: relative;
    transition: all 0.3s ease;
}

.ai-optimizer-pricing-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.ai-optimizer-pricing-card.featured {
    border-color: var(--ai-primary);
    transform: scale(1.05);
}

.ai-optimizer-badge-featured {
    position: absolute;
    top: -15px;
    right: 20px;
    background: var(--ai-primary);
    color: white;
    padding: 5px 20px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.ai-optimizer-pricing-header {
    margin-bottom: 30px;
}

.ai-optimizer-price {
    margin: 20px 0;
}

.ai-optimizer-currency {
    font-size: 24px;
    vertical-align: top;
}

.ai-optimizer-amount {
    font-size: 48px;
    font-weight: 700;
}

.ai-optimizer-period {
    font-size: 18px;
    color: var(--ai-text-muted);
}

.ai-optimizer-features-list {
    list-style: none;
    padding: 0;
    margin: 30px 0;
}

.ai-optimizer-features-list li {
    padding: 10px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.ai-optimizer-features-list .fa-check {
    color: var(--ai-success);
}

.ai-optimizer-features-list .fa-times {
    color: var(--ai-text-muted);
}

.ai-optimizer-pricing-note {
    margin-top: 40px;
    padding: 30px;
    background: var(--ai-card-bg);
    border-radius: 12px;
    text-align: center;
}

.ai-optimizer-pricing-note ul {
    list-style: none;
    padding: 0;
    margin: 20px 0 0;
    display: flex;
    justify-content: center;
    gap: 30px;
}

#license-key {
    text-transform: uppercase;
    letter-spacing: 2px;
    font-family: monospace;
    font-size: 18px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // 格式化卡密输入
    $('#license-key').on('input', function() {
        var value = $(this).val().toUpperCase().replace(/[^A-Z0-9]/g, '');
        var formatted = '';
        
        for (var i = 0; i < value.length && i < 16; i++) {
            if (i > 0 && i % 4 === 0) {
                formatted += '-';
            }
            formatted += value[i];
        }
        
        $(this).val(formatted);
    });
    
    // 激活授权
    $('#license-activation-form').on('submit', function(e) {
        e.preventDefault();
        
        var licenseKey = $('#license-key').val();
        var $button = $(this).find('button[type="submit"]');
        
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> 正在激活...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_opt_activate_license',
                license_key: licenseKey,
                nonce: '<?php echo wp_create_nonce('ai_opt_activate_license'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message || '激活失败，请重试');
                    $button.prop('disabled', false).html('<i class="fas fa-check"></i> 激活授权');
                }
            },
            error: function() {
                alert('网络错误，请稍后重试');
                $button.prop('disabled', false).html('<i class="fas fa-check"></i> 激活授权');
            }
        });
    });
    
    // 停用授权
    $('#deactivate-license').on('click', function() {
        if (!confirm('确定要停用当前授权吗？停用后需要重新激活才能使用高级功能。')) {
            return;
        }
        
        var $button = $(this);
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> 正在停用...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_opt_deactivate_license',
                nonce: '<?php echo wp_create_nonce('ai_opt_deactivate_license'); ?>'
            },
            success: function(response) {
                alert('授权已停用');
                location.reload();
            },
            error: function() {
                alert('操作失败，请重试');
                $button.prop('disabled', false).html('<i class="fas fa-times"></i> 停用授权');
            }
        });
    });
});
</script>
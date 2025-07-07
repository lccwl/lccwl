<?php
/**
 * Settings view
 */

if (!defined('ABSPATH')) {
    exit;
}

AI_Optimizer_Admin::render_header(
    __('AI Optimizer Settings', 'ai-website-optimizer'),
    __('Configure your AI Website Optimizer plugin settings', 'ai-website-optimizer')
);

$api_connection = AI_Optimizer_Settings::test_api_connection();
$system_status = AI_Optimizer_Settings::get_system_status();
?>

<div class="ai-optimizer-wrap">
    
    <form method="post" action="">
        <?php wp_nonce_field('ai_optimizer_settings_nonce'); ?>
        
        <div class="ai-optimizer-tabs">
            <div class="ai-optimizer-tab-nav">
                <button type="button" class="ai-optimizer-tab-button active" data-target="api-settings">
                    <i class="fas fa-key"></i> <?php _e('API Settings', 'ai-website-optimizer'); ?>
                </button>
                <button type="button" class="ai-optimizer-tab-button" data-target="monitoring-settings">
                    <i class="fas fa-chart-line"></i> <?php _e('Monitoring', 'ai-website-optimizer'); ?>
                </button>
                <button type="button" class="ai-optimizer-tab-button" data-target="seo-settings">
                    <i class="fas fa-search"></i> <?php _e('SEO Settings', 'ai-website-optimizer'); ?>
                </button>
                <button type="button" class="ai-optimizer-tab-button" data-target="content-settings">
                    <i class="fas fa-edit"></i> <?php _e('Content', 'ai-website-optimizer'); ?>
                </button>
                <button type="button" class="ai-optimizer-tab-button" data-target="security-settings">
                    <i class="fas fa-shield-alt"></i> <?php _e('Security', 'ai-website-optimizer'); ?>
                </button>
                <button type="button" class="ai-optimizer-tab-button" data-target="system-status">
                    <i class="fas fa-info-circle"></i> <?php _e('System Status', 'ai-website-optimizer'); ?>
                </button>
            </div>
            
            <!-- API Settings -->
            <div id="api-settings" class="ai-optimizer-tab-content active">
                <div class="ai-optimizer-card">
                    <div class="ai-optimizer-card-title"><?php _e('Siliconflow API Configuration', 'ai-website-optimizer'); ?></div>
                    
                    <div class="api-status-indicator">
                        <div class="status-item">
                            <span class="status-label"><?php _e('API Status:', 'ai-website-optimizer'); ?></span>
                            <span class="ai-optimizer-badge api-status-badge <?php echo $api_connection['success'] ? 'ai-optimizer-badge-success' : 'ai-optimizer-badge-error'; ?>">
                                <?php echo $api_connection['success'] ? __('Connected', 'ai-website-optimizer') : __('Disconnected', 'ai-website-optimizer'); ?>
                            </span>
                        </div>
                        <?php if ($api_connection['success'] && isset($api_connection['user_info'])): ?>
                        <div class="user-info">
                            <p><strong><?php _e('Account Info:', 'ai-website-optimizer'); ?></strong> <?php echo esc_html($api_connection['user_info']['email'] ?? 'N/A'); ?></p>
                            <p><strong><?php _e('Balance:', 'ai-website-optimizer'); ?></strong> $<?php echo esc_html($api_connection['user_info']['balance'] ?? '0'); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label" for="ai_optimizer_api_key">
                            <?php _e('Siliconflow API Key', 'ai-website-optimizer'); ?>
                            <span class="required">*</span>
                        </label>
                        <input type="password" class="ai-optimizer-input" id="ai_optimizer_api_key" name="ai_optimizer_api_key" 
                               value="<?php echo AI_Optimizer_Settings::get_api_key() ? '••••••••••••••••' : ''; ?>" 
                               placeholder="<?php _e('Enter your Siliconflow API key', 'ai-website-optimizer'); ?>">
                        <p class="description">
                            <?php printf(
                                __('Get your API key from <a href="%s" target="_blank">Siliconflow Console</a>', 'ai-website-optimizer'),
                                'https://cloud.siliconflow.cn/account/ak'
                            ); ?>
                        </p>
                    </div>
                    
                    <div class="api-actions">
                        <button type="button" class="ai-optimizer-btn ai-optimizer-btn-secondary test-api-connection">
                            <i class="fas fa-plug"></i> <?php _e('Test Connection', 'ai-website-optimizer'); ?>
                        </button>
                        <button type="button" class="ai-optimizer-btn ai-optimizer-btn-secondary" id="load-models">
                            <i class="fas fa-brain"></i> <?php _e('Load Available Models', 'ai-website-optimizer'); ?>
                        </button>
                    </div>
                    
                    <div id="available-models" class="models-container" style="display: none;">
                        <h4><?php _e('Available Models', 'ai-website-optimizer'); ?></h4>
                        <div class="models-grid"></div>
                    </div>
                </div>
            </div>
            
            <!-- Monitoring Settings -->
            <div id="monitoring-settings" class="ai-optimizer-tab-content">
                <div class="ai-optimizer-card">
                    <div class="ai-optimizer-card-title"><?php _e('Monitoring Configuration', 'ai-website-optimizer'); ?></div>
                    
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label">
                            <input type="checkbox" name="ai_optimizer_monitoring_enabled" value="1" 
                                   <?php checked(AI_Optimizer_Settings::get('monitoring_enabled'), true); ?>>
                            <?php _e('Enable Real-time Monitoring', 'ai-website-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Monitor website performance, errors, and SEO metrics automatically', 'ai-website-optimizer'); ?></p>
                    </div>
                    
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label" for="ai_optimizer_monitoring_interval">
                            <?php _e('Monitoring Interval', 'ai-website-optimizer'); ?>
                        </label>
                        <select class="ai-optimizer-select" id="ai_optimizer_monitoring_interval" name="ai_optimizer_monitoring_interval">
                            <option value="hourly" <?php selected(AI_Optimizer_Settings::get('monitoring_interval'), 'hourly'); ?>><?php _e('Every Hour', 'ai-website-optimizer'); ?></option>
                            <option value="twicedaily" <?php selected(AI_Optimizer_Settings::get('monitoring_interval'), 'twicedaily'); ?>><?php _e('Twice Daily', 'ai-website-optimizer'); ?></option>
                            <option value="daily" <?php selected(AI_Optimizer_Settings::get('monitoring_interval'), 'daily'); ?>><?php _e('Daily', 'ai-website-optimizer'); ?></option>
                        </select>
                    </div>
                    
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label">
                            <input type="checkbox" name="ai_optimizer_frontend_monitoring" value="1" 
                                   <?php checked(AI_Optimizer_Settings::get('frontend_monitoring'), true); ?>>
                            <?php _e('Enable Frontend Performance Tracking', 'ai-website-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Track real user performance metrics from the frontend', 'ai-website-optimizer'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- SEO Settings -->
            <div id="seo-settings" class="ai-optimizer-tab-content">
                <div class="ai-optimizer-card">
                    <div class="ai-optimizer-card-title"><?php _e('SEO Optimization Settings', 'ai-website-optimizer'); ?></div>
                    
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label">
                            <input type="checkbox" name="ai_optimizer_seo_auto_optimize" value="1" 
                                   <?php checked(AI_Optimizer_Settings::get('seo_auto_optimize'), true); ?>>
                            <?php _e('Enable Auto SEO Optimization', 'ai-website-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Automatically apply safe SEO optimizations based on AI recommendations', 'ai-website-optimizer'); ?></p>
                    </div>
                    
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label">
                            <input type="checkbox" name="ai_optimizer_seo_backup_before_changes" value="1" 
                                   <?php checked(AI_Optimizer_Settings::get('seo_backup_before_changes', true), true); ?>>
                            <?php _e('Create Backup Before SEO Changes', 'ai-website-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Automatically backup content before applying SEO modifications', 'ai-website-optimizer'); ?></p>
                    </div>
                    
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label" for="ai_optimizer_seo_target_keywords">
                            <?php _e('Target Keywords', 'ai-website-optimizer'); ?>
                        </label>
                        <textarea class="ai-optimizer-textarea" id="ai_optimizer_seo_target_keywords" name="ai_optimizer_seo_target_keywords" rows="4" 
                                  placeholder="<?php _e('Enter your target keywords, one per line', 'ai-website-optimizer'); ?>"><?php echo esc_textarea(AI_Optimizer_Settings::get('seo_target_keywords')); ?></textarea>
                        <p class="description"><?php _e('These keywords will be used for AI SEO optimization suggestions', 'ai-website-optimizer'); ?></p>
                    </div>
                    
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label">
                            <input type="checkbox" name="ai_optimizer_code_auto_fix" value="1" 
                                   <?php checked(AI_Optimizer_Settings::get('code_auto_fix'), true); ?>>
                            <?php _e('Enable Auto Code Fixes', 'ai-website-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Automatically fix safe code issues identified by AI analysis', 'ai-website-optimizer'); ?></p>
                    </div>
                    
                    <div class="ai-optimizer-grid ai-optimizer-grid-2">
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label">
                                <input type="checkbox" name="ai_optimizer_code_scan_plugins" value="1" 
                                       <?php checked(AI_Optimizer_Settings::get('code_scan_plugins', true), true); ?>>
                                <?php _e('Scan Plugin Code', 'ai-website-optimizer'); ?>
                            </label>
                        </div>
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label">
                                <input type="checkbox" name="ai_optimizer_code_scan_themes" value="1" 
                                       <?php checked(AI_Optimizer_Settings::get('code_scan_themes', true), true); ?>>
                                <?php _e('Scan Theme Code', 'ai-website-optimizer'); ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Settings -->
            <div id="content-settings" class="ai-optimizer-tab-content">
                <div class="ai-optimizer-card">
                    <div class="ai-optimizer-card-title"><?php _e('Content Generation Settings', 'ai-website-optimizer'); ?></div>
                    
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label">
                            <input type="checkbox" name="ai_optimizer_content_auto_publish" value="1" 
                                   <?php checked(AI_Optimizer_Settings::get('content_auto_publish'), true); ?>>
                            <?php _e('Auto-publish Generated Content', 'ai-website-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Automatically publish AI-generated content (use with caution)', 'ai-website-optimizer'); ?></p>
                    </div>
                    
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label" for="ai_optimizer_content_categories">
                            <?php _e('Default Categories for Generated Content', 'ai-website-optimizer'); ?>
                        </label>
                        <?php
                        $categories = get_categories(array('hide_empty' => false));
                        $selected_categories = AI_Optimizer_Settings::get('content_categories', array());
                        ?>
                        <select class="ai-optimizer-select" id="ai_optimizer_content_categories" name="ai_optimizer_content_categories[]" multiple>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category->term_id; ?>" 
                                    <?php echo in_array($category->term_id, $selected_categories) ? 'selected' : ''; ?>>
                                <?php echo esc_html($category->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label" for="ai_optimizer_content_sources">
                            <?php _e('Content Sources (for auto-collection)', 'ai-website-optimizer'); ?>
                        </label>
                        <textarea class="ai-optimizer-textarea" id="ai_optimizer_content_sources" name="ai_optimizer_content_sources" rows="4" 
                                  placeholder="<?php _e('Enter RSS feeds or website URLs, one per line', 'ai-website-optimizer'); ?>"><?php echo esc_textarea(AI_Optimizer_Settings::get('content_sources')); ?></textarea>
                        <p class="description"><?php _e('Sources for automatic content collection and AI rewriting', 'ai-website-optimizer'); ?></p>
                    </div>
                    
                    <div class="ai-optimizer-grid ai-optimizer-grid-3">
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label" for="ai_optimizer_video_quality">
                                <?php _e('Default Video Quality', 'ai-website-optimizer'); ?>
                            </label>
                            <select class="ai-optimizer-select" id="ai_optimizer_video_quality" name="ai_optimizer_video_quality">
                                <option value="standard" <?php selected(AI_Optimizer_Settings::get('video_quality', 'standard'), 'standard'); ?>><?php _e('Standard', 'ai-website-optimizer'); ?></option>
                                <option value="hd" <?php selected(AI_Optimizer_Settings::get('video_quality'), 'hd'); ?>><?php _e('HD', 'ai-website-optimizer'); ?></option>
                                <option value="4k" <?php selected(AI_Optimizer_Settings::get('video_quality'), '4k'); ?>><?php _e('4K', 'ai-website-optimizer'); ?></option>
                            </select>
                        </div>
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label" for="ai_optimizer_image_size">
                                <?php _e('Default Image Size', 'ai-website-optimizer'); ?>
                            </label>
                            <select class="ai-optimizer-select" id="ai_optimizer_image_size" name="ai_optimizer_image_size">
                                <option value="512x512" <?php selected(AI_Optimizer_Settings::get('image_size'), '512x512'); ?>>512×512</option>
                                <option value="1024x1024" <?php selected(AI_Optimizer_Settings::get('image_size', '1024x1024'), '1024x1024'); ?>>1024×1024</option>
                                <option value="1024x768" <?php selected(AI_Optimizer_Settings::get('image_size'), '1024x768'); ?>>1024×768</option>
                            </select>
                        </div>
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label" for="ai_optimizer_audio_voice">
                                <?php _e('Default Audio Voice', 'ai-website-optimizer'); ?>
                            </label>
                            <select class="ai-optimizer-select" id="ai_optimizer_audio_voice" name="ai_optimizer_audio_voice">
                                <option value="male" <?php selected(AI_Optimizer_Settings::get('audio_voice'), 'male'); ?>><?php _e('Male', 'ai-website-optimizer'); ?></option>
                                <option value="female" <?php selected(AI_Optimizer_Settings::get('audio_voice', 'default'), 'female'); ?>><?php _e('Female', 'ai-website-optimizer'); ?></option>
                                <option value="child" <?php selected(AI_Optimizer_Settings::get('audio_voice'), 'child'); ?>><?php _e('Child', 'ai-website-optimizer'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Security Settings -->
            <div id="security-settings" class="ai-optimizer-tab-content">
                <div class="ai-optimizer-card">
                    <div class="ai-optimizer-card-title"><?php _e('Security & Performance Settings', 'ai-website-optimizer'); ?></div>
                    
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label">
                            <input type="checkbox" name="ai_optimizer_require_approval" value="1" 
                                   <?php checked(AI_Optimizer_Settings::get('require_approval', true), true); ?>>
                            <?php _e('Require Manual Approval for Critical Changes', 'ai-website-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Require admin approval before applying critical optimizations', 'ai-website-optimizer'); ?></p>
                    </div>
                    
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label" for="ai_optimizer_backup_retention">
                            <?php _e('Backup Retention Period (days)', 'ai-website-optimizer'); ?>
                        </label>
                        <input type="number" class="ai-optimizer-input" id="ai_optimizer_backup_retention" name="ai_optimizer_backup_retention" 
                               value="<?php echo esc_attr(AI_Optimizer_Settings::get('backup_retention', 30)); ?>" min="1" max="365">
                        <p class="description"><?php _e('How long to keep backup files before automatic deletion', 'ai-website-optimizer'); ?></p>
                    </div>
                    
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label" for="ai_optimizer_log_level">
                            <?php _e('Logging Level', 'ai-website-optimizer'); ?>
                        </label>
                        <select class="ai-optimizer-select" id="ai_optimizer_log_level" name="ai_optimizer_log_level">
                            <option value="error" <?php selected(AI_Optimizer_Settings::get('log_level'), 'error'); ?>><?php _e('Errors Only', 'ai-website-optimizer'); ?></option>
                            <option value="warning" <?php selected(AI_Optimizer_Settings::get('log_level'), 'warning'); ?>><?php _e('Warnings & Errors', 'ai-website-optimizer'); ?></option>
                            <option value="info" <?php selected(AI_Optimizer_Settings::get('log_level', 'info'), 'info'); ?>><?php _e('All Information', 'ai-website-optimizer'); ?></option>
                            <option value="debug" <?php selected(AI_Optimizer_Settings::get('log_level'), 'debug'); ?>><?php _e('Debug Mode', 'ai-website-optimizer'); ?></option>
                        </select>
                    </div>
                    
                    <div class="ai-optimizer-grid ai-optimizer-grid-3">
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label" for="ai_optimizer_cache_duration">
                                <?php _e('Cache Duration (seconds)', 'ai-website-optimizer'); ?>
                            </label>
                            <input type="number" class="ai-optimizer-input" id="ai_optimizer_cache_duration" name="ai_optimizer_cache_duration" 
                                   value="<?php echo esc_attr(AI_Optimizer_Settings::get('cache_duration', 3600)); ?>" min="60">
                        </div>
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label" for="ai_optimizer_rate_limit">
                                <?php _e('API Rate Limit (requests/min)', 'ai-website-optimizer'); ?>
                            </label>
                            <input type="number" class="ai-optimizer-input" id="ai_optimizer_rate_limit" name="ai_optimizer_rate_limit" 
                                   value="<?php echo esc_attr(AI_Optimizer_Settings::get('rate_limit', 60)); ?>" min="1" max="1000">
                        </div>
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label" for="ai_optimizer_batch_size">
                                <?php _e('Batch Processing Size', 'ai-website-optimizer'); ?>
                            </label>
                            <input type="number" class="ai-optimizer-input" id="ai_optimizer_batch_size" name="ai_optimizer_batch_size" 
                                   value="<?php echo esc_attr(AI_Optimizer_Settings::get('batch_size', 10)); ?>" min="1" max="100">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- System Status -->
            <div id="system-status" class="ai-optimizer-tab-content">
                <div class="ai-optimizer-card">
                    <div class="ai-optimizer-card-title"><?php _e('System Information', 'ai-website-optimizer'); ?></div>
                    
                    <div class="system-status-grid">
                        <div class="status-item">
                            <span class="status-label"><?php _e('PHP Version:', 'ai-website-optimizer'); ?></span>
                            <span class="status-value <?php echo version_compare(PHP_VERSION, '7.4', '>=') ? 'good' : 'poor'; ?>">
                                <?php echo $system_status['php_version']; ?>
                            </span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-label"><?php _e('WordPress Version:', 'ai-website-optimizer'); ?></span>
                            <span class="status-value good"><?php echo $system_status['wp_version']; ?></span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-label"><?php _e('Plugin Version:', 'ai-website-optimizer'); ?></span>
                            <span class="status-value good"><?php echo $system_status['plugin_version']; ?></span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-label"><?php _e('Memory Limit:', 'ai-website-optimizer'); ?></span>
                            <span class="status-value"><?php echo $system_status['memory_limit']; ?></span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-label"><?php _e('Max Execution Time:', 'ai-website-optimizer'); ?></span>
                            <span class="status-value"><?php echo $system_status['max_execution_time']; ?>s</span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-label"><?php _e('Upload Max Size:', 'ai-website-optimizer'); ?></span>
                            <span class="status-value"><?php echo $system_status['upload_max_filesize']; ?></span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-label"><?php _e('API Connection:', 'ai-website-optimizer'); ?></span>
                            <span class="status-value <?php echo $system_status['api_connection']['success'] ? 'good' : 'poor'; ?>">
                                <?php echo $system_status['api_connection']['success'] ? __('Working', 'ai-website-optimizer') : __('Failed', 'ai-website-optimizer'); ?>
                            </span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-label"><?php _e('Database Tables:', 'ai-website-optimizer'); ?></span>
                            <span class="status-value <?php echo $system_status['database_tables'] ? 'good' : 'poor'; ?>">
                                <?php echo $system_status['database_tables'] ? __('OK', 'ai-website-optimizer') : __('Missing', 'ai-website-optimizer'); ?>
                            </span>
                        </div>
                        
                        <div class="status-item">
                            <span class="status-label"><?php _e('Cron Status:', 'ai-website-optimizer'); ?></span>
                            <span class="status-value <?php echo $system_status['cron_status'] === 'active' ? 'good' : 'poor'; ?>">
                                <?php echo esc_html($system_status['cron_status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="ai-optimizer-card">
                    <div class="ai-optimizer-card-title"><?php _e('Maintenance Actions', 'ai-website-optimizer'); ?></div>
                    
                    <div class="maintenance-actions">
                        <div class="action-group">
                            <h4><?php _e('Settings Management', 'ai-website-optimizer'); ?></h4>
                            <button type="button" class="ai-optimizer-btn ai-optimizer-btn-secondary export-settings">
                                <i class="fas fa-download"></i> <?php _e('Export Settings', 'ai-website-optimizer'); ?>
                            </button>
                            <button type="button" class="ai-optimizer-btn ai-optimizer-btn-secondary import-settings">
                                <i class="fas fa-upload"></i> <?php _e('Import Settings', 'ai-website-optimizer'); ?>
                            </button>
                            <button type="button" class="ai-optimizer-btn ai-optimizer-btn-secondary" id="reset-settings">
                                <i class="fas fa-undo"></i> <?php _e('Reset to Defaults', 'ai-website-optimizer'); ?>
                            </button>
                        </div>
                        
                        <div class="action-group">
                            <h4><?php _e('Database Maintenance', 'ai-website-optimizer'); ?></h4>
                            <button type="button" class="ai-optimizer-btn ai-optimizer-btn-secondary" id="cleanup-logs">
                                <i class="fas fa-trash"></i> <?php _e('Clean Old Logs', 'ai-website-optimizer'); ?>
                            </button>
                            <button type="button" class="ai-optimizer-btn ai-optimizer-btn-secondary" id="rebuild-tables">
                                <i class="fas fa-wrench"></i> <?php _e('Rebuild Tables', 'ai-website-optimizer'); ?>
                            </button>
                        </div>
                        
                        <div class="action-group">
                            <h4><?php _e('Cache Management', 'ai-website-optimizer'); ?></h4>
                            <button type="button" class="ai-optimizer-btn ai-optimizer-btn-secondary" id="clear-cache">
                                <i class="fas fa-broom"></i> <?php _e('Clear All Cache', 'ai-website-optimizer'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Save Button -->
        <div class="settings-footer">
            <button type="submit" name="submit" class="ai-optimizer-btn ai-optimizer-btn-pulse">
                <i class="fas fa-save"></i> <?php _e('Save All Settings', 'ai-website-optimizer'); ?>
            </button>
        </div>
    </form>

</div>

<style>
.api-status-indicator {
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid var(--ai-border);
}

.status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.status-item:last-child {
    margin-bottom: 0;
}

.status-label {
    font-weight: 600;
    color: var(--ai-text-muted);
}

.status-value {
    font-weight: 600;
}

.status-value.good {
    color: var(--ai-success);
}

.status-value.poor {
    color: var(--ai-error);
}

.user-info {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid var(--ai-border);
}

.user-info p {
    margin: 5px 0;
    color: var(--ai-text-muted);
}

.api-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.models-container {
    margin-top: 20px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
    border: 1px solid var(--ai-border);
}

.models-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.model-item {
    padding: 15px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    border: 1px solid var(--ai-border);
}

.model-name {
    font-weight: 600;
    margin-bottom: 5px;
}

.model-type {
    font-size: 0.8em;
    color: var(--ai-text-muted);
    text-transform: uppercase;
}

.system-status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.required {
    color: var(--ai-error);
    margin-left: 5px;
}

.description {
    margin-top: 5px;
    font-size: 0.9em;
    color: var(--ai-text-muted);
    line-height: 1.4;
}

.maintenance-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.action-group h4 {
    margin-bottom: 15px;
    color: var(--ai-secondary);
    border-bottom: 1px solid var(--ai-border);
    padding-bottom: 10px;
}

.action-group .ai-optimizer-btn {
    display: block;
    width: 100%;
    margin-bottom: 10px;
    text-align: left;
}

.settings-footer {
    position: sticky;
    bottom: 0;
    background: var(--ai-dark);
    padding: 20px 0;
    border-top: 1px solid var(--ai-border);
    margin-top: 30px;
    text-align: center;
}

@media (max-width: 768px) {
    .api-actions {
        flex-direction: column;
    }
    
    .system-status-grid {
        grid-template-columns: 1fr;
    }
    
    .maintenance-actions {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Load models
    $('#load-models').on('click', function() {
        const button = $(this);
        const originalText = button.html();
        
        button.prop('disabled', true).html('<span class="ai-optimizer-loading"></span> Loading...');
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'get_available_models',
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success && response.data) {
                displayModels(response.data);
                $('#available-models').show();
            } else {
                alert('Failed to load models: ' + (response.data || 'Unknown error'));
            }
        })
        .fail(function() {
            alert('Network error occurred');
        })
        .always(function() {
            button.prop('disabled', false).html(originalText);
        });
    });
    
    function displayModels(models) {
        const container = $('.models-grid');
        container.empty();
        
        models.forEach(model => {
            const modelHtml = `
                <div class="model-item">
                    <div class="model-name">${model.id}</div>
                    <div class="model-type">${model.type || 'Unknown'}</div>
                </div>
            `;
            container.append(modelHtml);
        });
    }
    
    // Reset settings
    $('#reset-settings').on('click', function() {
        if (!confirm('Are you sure you want to reset all settings to defaults? This cannot be undone.')) {
            return;
        }
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'reset_settings',
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                alert('Settings reset successfully!');
                location.reload();
            } else {
                alert('Failed to reset settings: ' + response.data);
            }
        })
        .fail(function() {
            alert('Network error occurred');
        });
    });
    
    // Cleanup logs
    $('#cleanup-logs').on('click', function() {
        if (!confirm('This will delete old log entries. Continue?')) {
            return;
        }
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'cleanup_logs',
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                alert('Logs cleaned successfully!');
            } else {
                alert('Failed to clean logs: ' + response.data);
            }
        });
    });
    
    // Rebuild tables
    $('#rebuild-tables').on('click', function() {
        if (!confirm('This will rebuild database tables. Continue?')) {
            return;
        }
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'rebuild_tables',
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                alert('Tables rebuilt successfully!');
            } else {
                alert('Failed to rebuild tables: ' + response.data);
            }
        });
    });
    
    // Clear cache
    $('#clear-cache').on('click', function() {
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'clear_cache',
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                alert('Cache cleared successfully!');
            } else {
                alert('Failed to clear cache: ' + response.data);
            }
        });
    });
});
</script>

<?php
AI_Optimizer_Admin::render_footer();
?>

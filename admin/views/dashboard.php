<?php
/**
 * Dashboard view
 */

if (!defined('ABSPATH')) {
    exit;
}

AI_Optimizer_Admin::render_header(
    __('AI Website Optimizer Dashboard', 'ai-website-optimizer'),
    __('Real-time monitoring and AI-powered optimization for your WordPress site', 'ai-website-optimizer')
);

$recent_data = $monitor->get_recent_data();
$api_key_configured = !empty(AI_Optimizer_Settings::get_api_key());
?>

<div class="ai-optimizer-wrap">
    
    <?php if (!$api_key_configured): ?>
    <div class="ai-optimizer-alert ai-optimizer-alert-warning">
        <strong><?php _e('Setup Required:', 'ai-website-optimizer'); ?></strong>
        <?php printf(
            __('Please configure your Siliconflow API key in the <a href="%s">settings</a> to enable AI features.', 'ai-website-optimizer'),
            admin_url('admin.php?page=ai-optimizer-settings')
        ); ?>
    </div>
    <?php endif; ?>

    <!-- Dashboard Stats -->
    <div class="ai-optimizer-grid ai-optimizer-grid-3">
        <div class="ai-optimizer-stat-card">
            <div class="ai-optimizer-stat-value stat-load-time">
                <?php echo $recent_data ? round($recent_data['data']['performance']['load_time'] * 1000) . 'ms' : '--'; ?>
            </div>
            <div class="ai-optimizer-stat-label"><?php _e('Avg Load Time', 'ai-website-optimizer'); ?></div>
            <div class="ai-optimizer-stat-trend up">↗ <?php _e('Improved', 'ai-website-optimizer'); ?></div>
        </div>

        <div class="ai-optimizer-stat-card">
            <div class="ai-optimizer-stat-value stat-seo-score">
                <?php 
                $seo = new AI_Optimizer_SEO();
                echo $seo->get_current_score(); 
                ?>
            </div>
            <div class="ai-optimizer-stat-label"><?php _e('SEO Score', 'ai-website-optimizer'); ?></div>
            <div class="ai-optimizer-stat-trend up">↗ <?php _e('Optimizing', 'ai-website-optimizer'); ?></div>
        </div>

        <div class="ai-optimizer-stat-card">
            <div class="ai-optimizer-stat-value stat-uptime">
                <?php echo isset($stats['uptime_percentage']) ? round($stats['uptime_percentage']) . '%' : '100%'; ?>
            </div>
            <div class="ai-optimizer-stat-label"><?php _e('Uptime (24h)', 'ai-website-optimizer'); ?></div>
            <div class="ai-optimizer-stat-trend up">✓ <?php _e('Stable', 'ai-website-optimizer'); ?></div>
        </div>

        <div class="ai-optimizer-stat-card">
            <div class="ai-optimizer-stat-value stat-memory-usage">
                <?php echo $recent_data ? round($recent_data['data']['performance']['memory_usage'] / 1024 / 1024) . 'MB' : '--'; ?>
            </div>
            <div class="ai-optimizer-stat-label"><?php _e('Memory Usage', 'ai-website-optimizer'); ?></div>
            <div class="ai-optimizer-stat-trend down">↘ <?php _e('Optimized', 'ai-website-optimizer'); ?></div>
        </div>

        <div class="ai-optimizer-stat-card">
            <div class="ai-optimizer-stat-value stat-errors">
                <?php echo isset($stats['error_count']) ? $stats['error_count'] : '0'; ?>
            </div>
            <div class="ai-optimizer-stat-label"><?php _e('Errors (24h)', 'ai-website-optimizer'); ?></div>
            <div class="ai-optimizer-stat-trend down">↘ <?php _e('Reduced', 'ai-website-optimizer'); ?></div>
        </div>

        <div class="ai-optimizer-stat-card">
            <div class="ai-optimizer-stat-value">
                <?php 
                $analyzer = new AI_Optimizer_Code_Analyzer();
                echo $analyzer->get_health_score(); 
                ?>%
            </div>
            <div class="ai-optimizer-stat-label"><?php _e('Code Health', 'ai-website-optimizer'); ?></div>
            <div class="ai-optimizer-stat-trend up">↗ <?php _e('Improving', 'ai-website-optimizer'); ?></div>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="ai-optimizer-grid ai-optimizer-grid-2">
        
        <!-- Performance Chart -->
        <div class="ai-optimizer-card">
            <div class="ai-optimizer-card-title">
                <?php _e('Performance Metrics', 'ai-website-optimizer'); ?>
                <button class="ai-optimizer-btn ai-optimizer-btn-secondary refresh-data" style="float: right;">
                    <i class="fas fa-sync-alt"></i> <?php _e('Refresh', 'ai-website-optimizer'); ?>
                </button>
            </div>
            <div class="ai-optimizer-chart-container">
                <canvas id="performance-chart"></canvas>
            </div>
        </div>

        <!-- SEO Score -->
        <div class="ai-optimizer-card">
            <div class="ai-optimizer-card-title">
                <?php _e('SEO Optimization Score', 'ai-website-optimizer'); ?>
                <a href="<?php echo admin_url('admin.php?page=ai-optimizer-seo'); ?>" class="ai-optimizer-btn ai-optimizer-btn-secondary" style="float: right;">
                    <?php _e('View Details', 'ai-website-optimizer'); ?>
                </a>
            </div>
            <div class="ai-optimizer-chart-container">
                <canvas id="seo-score-chart"></canvas>
            </div>
        </div>

    </div>

    <!-- Quick Actions -->
    <div class="ai-optimizer-card">
        <div class="ai-optimizer-card-title"><?php _e('Quick Actions', 'ai-website-optimizer'); ?></div>
        <div class="ai-optimizer-grid ai-optimizer-grid-3">
            <button class="ai-optimizer-btn ai-optimizer-btn-pulse run-analysis">
                <i class="fas fa-brain"></i> <?php _e('Run AI Analysis', 'ai-website-optimizer'); ?>
            </button>
            <a href="<?php echo admin_url('admin.php?page=ai-optimizer-ai-tools'); ?>" class="ai-optimizer-btn">
                <i class="fas fa-magic"></i> <?php _e('Generate Content', 'ai-website-optimizer'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=ai-optimizer-monitor'); ?>" class="ai-optimizer-btn">
                <i class="fas fa-chart-line"></i> <?php _e('View Monitoring', 'ai-website-optimizer'); ?>
            </a>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="ai-optimizer-grid ai-optimizer-grid-2">
        
        <!-- Recent Activities -->
        <div class="ai-optimizer-card">
            <div class="ai-optimizer-card-title"><?php _e('Recent Activities', 'ai-website-optimizer'); ?></div>
            <div class="activities-list">
                <?php 
                $activities = $monitor->get_recent_activities();
                if (!empty($activities)): 
                    foreach (array_slice($activities, 0, 5) as $activity): 
                ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-<?php echo $this->get_activity_icon($activity['type']); ?>"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title"><?php echo esc_html($activity['title']); ?></div>
                        <div class="activity-time"><?php echo human_time_diff(strtotime($activity['created_at'])); ?> ago</div>
                    </div>
                    <div class="activity-status">
                        <span class="ai-optimizer-badge ai-optimizer-badge-<?php echo $activity['status']; ?>">
                            <?php echo esc_html($activity['status']); ?>
                        </span>
                    </div>
                </div>
                <?php 
                    endforeach;
                else:
                ?>
                <div class="ai-optimizer-text-center ai-optimizer-text-muted">
                    <i class="fas fa-clock" style="font-size: 48px; opacity: 0.3; margin-bottom: 20px;"></i>
                    <p><?php _e('No recent activities', 'ai-website-optimizer'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Active Alerts -->
        <div class="ai-optimizer-card">
            <div class="ai-optimizer-card-title"><?php _e('Active Alerts', 'ai-website-optimizer'); ?></div>
            <div class="alerts-list">
                <?php 
                $alerts = $monitor->get_active_alerts();
                if (!empty($alerts)): 
                    foreach ($alerts as $alert): 
                ?>
                <div class="alert-item">
                    <div class="alert-icon">
                        <i class="fas fa-<?php echo $alert['type'] === 'error' ? 'exclamation-triangle' : 'exclamation-circle'; ?>"></i>
                    </div>
                    <div class="alert-content">
                        <div class="alert-message"><?php echo esc_html($alert['message']); ?></div>
                        <div class="alert-actions">
                            <button class="ai-optimizer-btn ai-optimizer-btn-secondary" data-action="<?php echo esc_attr($alert['action']); ?>">
                                <?php _e('Fix Now', 'ai-website-optimizer'); ?>
                            </button>
                        </div>
                    </div>
                    <div class="alert-type">
                        <span class="ai-optimizer-badge ai-optimizer-badge-<?php echo $alert['type']; ?>">
                            <?php echo esc_html($alert['type']); ?>
                        </span>
                    </div>
                </div>
                <?php 
                    endforeach;
                else:
                ?>
                <div class="ai-optimizer-text-center ai-optimizer-text-muted">
                    <i class="fas fa-shield-alt" style="font-size: 48px; opacity: 0.3; margin-bottom: 20px; color: var(--ai-success);"></i>
                    <p><?php _e('No active alerts - everything looks good!', 'ai-website-optimizer'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- System Status -->
    <div class="ai-optimizer-card">
        <div class="ai-optimizer-card-title"><?php _e('System Status', 'ai-website-optimizer'); ?></div>
        <div class="ai-optimizer-grid ai-optimizer-grid-3">
            <div class="status-item">
                <div class="status-label"><?php _e('API Connection', 'ai-website-optimizer'); ?></div>
                <div class="status-value">
                    <span class="ai-optimizer-badge api-status <?php echo $api_key_configured ? 'ai-optimizer-badge-success' : 'ai-optimizer-badge-error'; ?>">
                        <?php echo $api_key_configured ? __('Connected', 'ai-website-optimizer') : __('Not configured', 'ai-website-optimizer'); ?>
                    </span>
                </div>
            </div>
            <div class="status-item">
                <div class="status-label"><?php _e('Monitoring', 'ai-website-optimizer'); ?></div>
                <div class="status-value">
                    <span class="ai-optimizer-badge ai-optimizer-badge-success">
                        <?php echo get_option('ai_optimizer_monitoring_enabled') ? __('Active', 'ai-website-optimizer') : __('Disabled', 'ai-website-optimizer'); ?>
                    </span>
                </div>
            </div>
            <div class="status-item">
                <div class="status-label"><?php _e('Auto Optimization', 'ai-website-optimizer'); ?></div>
                <div class="status-value">
                    <span class="ai-optimizer-badge ai-optimizer-badge-info">
                        <?php echo get_option('ai_optimizer_seo_auto_optimize') ? __('Enabled', 'ai-website-optimizer') : __('Manual', 'ai-website-optimizer'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.activities-list, .alerts-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item, .alert-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid var(--ai-border);
}

.activity-item:last-child, .alert-item:last-child {
    border-bottom: none;
}

.activity-icon, .alert-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    background: var(--ai-primary);
    color: white;
}

.alert-icon {
    background: var(--ai-error);
}

.activity-content, .alert-content {
    flex: 1;
}

.activity-title, .alert-message {
    font-weight: 600;
    margin-bottom: 5px;
}

.activity-time {
    font-size: 0.85em;
    color: var(--ai-text-muted);
}

.alert-actions {
    margin-top: 10px;
}

.activity-status, .alert-type {
    margin-left: 15px;
}

.status-item {
    text-align: center;
    padding: 20px;
}

.status-label {
    font-weight: 600;
    margin-bottom: 10px;
    color: var(--ai-text-muted);
}

.status-value {
    font-size: 1.1em;
}
</style>

<?php
AI_Optimizer_Admin::render_footer();

// Helper method for activity icons
function get_activity_icon($type) {
    $icons = array(
        'analysis' => 'brain',
        'optimization' => 'magic',
        'monitoring' => 'chart-line',
        'error' => 'exclamation-triangle',
        'success' => 'check-circle',
        'warning' => 'exclamation-circle',
        'info' => 'info-circle',
    );
    
    return isset($icons[$type]) ? $icons[$type] : 'cog';
}
?>

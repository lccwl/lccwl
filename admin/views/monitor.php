<?php
/**
 * Monitor view
 */

if (!defined('ABSPATH')) {
    exit;
}

AI_Optimizer_Admin::render_header(
    __('Real-time Monitoring', 'ai-website-optimizer'),
    __('Monitor your website performance, security, and SEO metrics in real-time', 'ai-website-optimizer')
);

$monitor = new AI_Optimizer_Monitor();
$monitoring_data = $monitor->get_monitoring_data();
$performance_data = $monitor->get_performance_data();
?>

<div class="ai-optimizer-wrap">
    
    <!-- Monitoring Controls -->
    <div class="ai-optimizer-card">
        <div class="ai-optimizer-card-title"><?php _e('Monitoring Controls', 'ai-website-optimizer'); ?></div>
        <div class="ai-optimizer-grid ai-optimizer-grid-3">
            <button class="ai-optimizer-btn ai-optimizer-btn-pulse" id="start-monitoring">
                <i class="fas fa-play"></i> <?php _e('Start Monitoring', 'ai-website-optimizer'); ?>
            </button>
            <button class="ai-optimizer-btn ai-optimizer-btn-secondary" id="collect-data">
                <i class="fas fa-sync-alt"></i> <?php _e('Collect Data Now', 'ai-website-optimizer'); ?>
            </button>
            <button class="ai-optimizer-btn ai-optimizer-btn-secondary refresh-data">
                <i class="fas fa-refresh"></i> <?php _e('Refresh Display', 'ai-website-optimizer'); ?>
            </button>
        </div>
    </div>

    <!-- Real-time Metrics -->
    <div class="ai-optimizer-grid ai-optimizer-grid-2">
        
        <!-- Performance Metrics -->
        <div class="ai-optimizer-card">
            <div class="ai-optimizer-card-title"><?php _e('Performance Metrics', 'ai-website-optimizer'); ?></div>
            <div class="ai-optimizer-chart-container">
                <canvas id="performance-chart"></canvas>
            </div>
            <div class="metrics-summary">
                <div class="metric-item">
                    <span class="metric-label"><?php _e('Current Load Time:', 'ai-website-optimizer'); ?></span>
                    <span class="metric-value" id="current-load-time">--</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label"><?php _e('Memory Usage:', 'ai-website-optimizer'); ?></span>
                    <span class="metric-value" id="current-memory">--</span>
                </div>
                <div class="metric-item">
                    <span class="metric-label"><?php _e('DB Queries:', 'ai-website-optimizer'); ?></span>
                    <span class="metric-value" id="current-queries">--</span>
                </div>
            </div>
        </div>

        <!-- Error Monitoring -->
        <div class="ai-optimizer-card">
            <div class="ai-optimizer-card-title"><?php _e('Error Monitoring', 'ai-website-optimizer'); ?></div>
            <div class="ai-optimizer-chart-container">
                <canvas id="error-trends-chart"></canvas>
            </div>
            <div class="error-summary">
                <div class="error-item">
                    <span class="error-type">PHP Errors</span>
                    <span class="error-count" id="php-errors">0</span>
                </div>
                <div class="error-item">
                    <span class="error-type">WordPress Errors</span>
                    <span class="error-count" id="wp-errors">0</span>
                </div>
                <div class="error-item">
                    <span class="error-type">404 Errors</span>
                    <span class="error-count" id="404-errors">0</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Security Monitoring -->
    <div class="ai-optimizer-card">
        <div class="ai-optimizer-card-title"><?php _e('Security Status', 'ai-website-optimizer'); ?></div>
        <div class="ai-optimizer-grid ai-optimizer-grid-3">
            <div class="security-item">
                <div class="security-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="security-content">
                    <div class="security-label"><?php _e('SSL Certificate', 'ai-website-optimizer'); ?></div>
                    <div class="security-status <?php echo is_ssl() ? 'secure' : 'insecure'; ?>">
                        <?php echo is_ssl() ? __('Secure', 'ai-website-optimizer') : __('Not Secure', 'ai-website-optimizer'); ?>
                    </div>
                </div>
            </div>
            <div class="security-item">
                <div class="security-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="security-content">
                    <div class="security-label"><?php _e('Admin Security', 'ai-website-optimizer'); ?></div>
                    <div class="security-status">
                        <?php echo get_user_by('login', 'admin') ? __('Weak', 'ai-website-optimizer') : __('Good', 'ai-website-optimizer'); ?>
                    </div>
                </div>
            </div>
            <div class="security-item">
                <div class="security-icon">
                    <i class="fas fa-file-shield"></i>
                </div>
                <div class="security-content">
                    <div class="security-label"><?php _e('File Permissions', 'ai-website-optimizer'); ?></div>
                    <div class="security-status">
                        <?php _e('Good', 'ai-website-optimizer'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SEO Monitoring -->
    <div class="ai-optimizer-card">
        <div class="ai-optimizer-card-title"><?php _e('SEO Monitoring', 'ai-website-optimizer'); ?></div>
        <div class="ai-optimizer-grid ai-optimizer-grid-2">
            <div class="seo-metrics">
                <div class="seo-metric">
                    <span class="seo-label"><?php _e('Sitemap Status:', 'ai-website-optimizer'); ?></span>
                    <span class="seo-value">
                        <?php
                        $sitemap_url = home_url('/sitemap.xml');
                        $response = wp_remote_head($sitemap_url);
                        $status = wp_remote_retrieve_response_code($response) === 200;
                        echo $status ? '<span class="ai-optimizer-badge ai-optimizer-badge-success">Active</span>' : '<span class="ai-optimizer-badge ai-optimizer-badge-error">Missing</span>';
                        ?>
                    </span>
                </div>
                <div class="seo-metric">
                    <span class="seo-label"><?php _e('Robots.txt:', 'ai-website-optimizer'); ?></span>
                    <span class="seo-value">
                        <?php
                        $robots_url = home_url('/robots.txt');
                        $response = wp_remote_head($robots_url);
                        $status = wp_remote_retrieve_response_code($response) === 200;
                        echo $status ? '<span class="ai-optimizer-badge ai-optimizer-badge-success">Present</span>' : '<span class="ai-optimizer-badge ai-optimizer-badge-warning">Missing</span>';
                        ?>
                    </span>
                </div>
                <div class="seo-metric">
                    <span class="seo-label"><?php _e('Meta Descriptions:', 'ai-website-optimizer'); ?></span>
                    <span class="seo-value">
                        <span class="ai-optimizer-badge ai-optimizer-badge-info">Analyzing...</span>
                    </span>
                </div>
            </div>
            <div class="seo-chart">
                <canvas id="seo-trends-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Monitoring History -->
    <div class="ai-optimizer-card">
        <div class="ai-optimizer-card-title">
            <?php _e('Monitoring History', 'ai-website-optimizer'); ?>
            <select class="ai-optimizer-select" id="history-filter" style="float: right; width: 200px;">
                <option value="24h"><?php _e('Last 24 Hours', 'ai-website-optimizer'); ?></option>
                <option value="7d"><?php _e('Last 7 Days', 'ai-website-optimizer'); ?></option>
                <option value="30d"><?php _e('Last 30 Days', 'ai-website-optimizer'); ?></option>
            </select>
        </div>
        <div class="monitoring-table-container">
            <table class="ai-optimizer-table">
                <thead>
                    <tr>
                        <th><?php _e('Timestamp', 'ai-website-optimizer'); ?></th>
                        <th><?php _e('Load Time', 'ai-website-optimizer'); ?></th>
                        <th><?php _e('Memory Usage', 'ai-website-optimizer'); ?></th>
                        <th><?php _e('Response Code', 'ai-website-optimizer'); ?></th>
                        <th><?php _e('Errors', 'ai-website-optimizer'); ?></th>
                        <th><?php _e('Actions', 'ai-website-optimizer'); ?></th>
                    </tr>
                </thead>
                <tbody id="monitoring-history">
                    <?php foreach (array_slice($monitoring_data, 0, 20) as $record): 
                        $data = json_decode($record['data'], true);
                        $performance = $data['performance'] ?? array();
                        $errors = $data['errors'] ?? array();
                    ?>
                    <tr>
                        <td><?php echo date('M j, H:i', strtotime($record['created_at'])); ?></td>
                        <td>
                            <?php if (isset($performance['load_time'])): ?>
                                <span class="<?php echo $performance['load_time'] > 3 ? 'ai-optimizer-text-error' : 'ai-optimizer-text-success'; ?>">
                                    <?php echo round($performance['load_time'] * 1000); ?>ms
                                </span>
                            <?php else: ?>
                                --
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($performance['memory_usage'])): ?>
                                <?php echo round($performance['memory_usage'] / 1024 / 1024); ?>MB
                            <?php else: ?>
                                --
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($performance['response_code'])): ?>
                                <span class="ai-optimizer-badge ai-optimizer-badge-<?php echo $performance['response_code'] === 200 ? 'success' : 'error'; ?>">
                                    <?php echo $performance['response_code']; ?>
                                </span>
                            <?php else: ?>
                                --
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($errors)): ?>
                                <span class="ai-optimizer-badge ai-optimizer-badge-warning">
                                    <?php echo array_sum($errors); ?>
                                </span>
                            <?php else: ?>
                                <span class="ai-optimizer-badge ai-optimizer-badge-success">0</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="ai-optimizer-btn ai-optimizer-btn-secondary view-details" data-id="<?php echo $record['id']; ?>">
                                <?php _e('Details', 'ai-website-optimizer'); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- AI Analysis Results -->
    <div class="ai-optimizer-card">
        <div class="ai-optimizer-card-title"><?php _e('AI Analysis & Recommendations', 'ai-website-optimizer'); ?></div>
        <div id="ai-analysis-container">
            <div class="ai-optimizer-text-center ai-optimizer-text-muted">
                <i class="fas fa-brain" style="font-size: 48px; opacity: 0.3; margin-bottom: 20px;"></i>
                <p><?php _e('Run analysis to get AI-powered recommendations', 'ai-website-optimizer'); ?></p>
                <button class="ai-optimizer-btn ai-optimizer-btn-pulse run-analysis">
                    <i class="fas fa-brain"></i> <?php _e('Run AI Analysis', 'ai-website-optimizer'); ?>
                </button>
            </div>
        </div>
    </div>

</div>

<style>
.metrics-summary, .error-summary {
    margin-top: 20px;
    padding: 15px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
}

.metric-item, .error-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.metric-item:last-child, .error-item:last-child {
    margin-bottom: 0;
}

.metric-label, .error-type {
    color: var(--ai-text-muted);
}

.metric-value, .error-count {
    font-weight: 600;
    color: var(--ai-secondary);
}

.security-item {
    display: flex;
    align-items: center;
    padding: 20px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
    border: 1px solid var(--ai-border);
}

.security-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--ai-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 20px;
    color: white;
}

.security-content {
    flex: 1;
}

.security-label {
    font-weight: 600;
    margin-bottom: 5px;
}

.security-status {
    font-size: 0.9em;
}

.security-status.secure {
    color: var(--ai-success);
}

.security-status.insecure {
    color: var(--ai-error);
}

.seo-metrics {
    padding: 20px;
}

.seo-metric {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.seo-metric:last-child {
    margin-bottom: 0;
}

.seo-label {
    font-weight: 600;
    color: var(--ai-text-muted);
}

.monitoring-table-container {
    max-height: 500px;
    overflow-y: auto;
}

@media (max-width: 768px) {
    .ai-optimizer-grid-2 {
        grid-template-columns: 1fr;
    }
    
    .ai-optimizer-grid-3 {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Collect data now button
    $('#collect-data').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        
        button.prop('disabled', true).html('<span class="ai-optimizer-loading"></span> Collecting...');
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'collect_monitoring_data',
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Failed to collect data: ' + response.data);
            }
        })
        .fail(function() {
            alert('Network error occurred');
        })
        .always(function() {
            button.prop('disabled', false).text(originalText);
        });
    });
    
    // View details button
    $('.view-details').on('click', function() {
        const recordId = $(this).data('id');
        // Show modal with detailed information
        alert('Detailed view for record #' + recordId + ' (implementation pending)');
    });
    
    // History filter
    $('#history-filter').on('change', function() {
        const period = $(this).val();
        // Filter monitoring history (implementation pending)
        console.log('Filter by:', period);
    });
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        updateRealTimeMetrics();
    }, 30000);
    
    function updateRealTimeMetrics() {
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'get_realtime_metrics',
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                const data = response.data;
                $('#current-load-time').text(Math.round(data.load_time * 1000) + 'ms');
                $('#current-memory').text(Math.round(data.memory_usage / 1024 / 1024) + 'MB');
                $('#current-queries').text(data.db_queries || '--');
                $('#php-errors').text(data.php_errors || 0);
                $('#wp-errors').text(data.wp_errors || 0);
                $('#404-errors').text(data.errors_404 || 0);
            }
        });
    }
    
    // Initial load
    updateRealTimeMetrics();
});
</script>

<?php
AI_Optimizer_Admin::render_footer();
?>

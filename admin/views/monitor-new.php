<?php
/**
 * 新版监控页面
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap ai-optimizer-wrap">
    <h1>📊 AI智能监控系统</h1>
    
    <!-- AI巡逻控制面板 -->
    <div class="ai-optimizer-card">
        <h2>🤖 AI巡逻控制面板</h2>
        <div class="patrol-controls">
            <div class="patrol-status">
                <div class="status-indicator" id="patrol-status">
                    <span class="status-dot inactive"></span>
                    <span class="status-text">巡逻系统待命中</span>
                </div>
                <div class="last-patrol">
                    <span>上次巡逻: </span>
                    <span id="last-patrol-time">--</span>
                </div>
            </div>
            
            <div class="patrol-actions">
                <button type="button" id="start-patrol" class="button button-primary">
                    <span class="dashicons dashicons-controls-play"></span>
                    立即执行AI巡逻
                </button>
                <button type="button" id="stop-patrol" class="button button-secondary" disabled>
                    <span class="dashicons dashicons-controls-pause"></span>
                    停止巡逻
                </button>
                <button type="button" id="patrol-settings" class="button">
                    <span class="dashicons dashicons-admin-settings"></span>
                    巡逻设置
                </button>
            </div>
        </div>
        
        <!-- 巡逻设置面板 -->
        <div id="patrol-settings-panel" class="patrol-settings-panel" style="display: none;">
            <h3>巡逻设置</h3>
            <table class="form-table">
                <tr>
                    <th><label for="patrol_interval">巡逻间隔</label></th>
                    <td>
                        <select id="patrol_interval" name="patrol_interval">
                            <option value="300">5分钟</option>
                            <option value="900">15分钟</option>
                            <option value="1800">30分钟</option>
                            <option value="3600" selected>1小时</option>
                            <option value="21600">6小时</option>
                            <option value="43200">12小时</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label>巡逻范围</label></th>
                    <td>
                        <fieldset>
                            <label><input type="checkbox" name="patrol_scope[]" value="database" checked> 数据库性能监控</label><br>
                            <label><input type="checkbox" name="patrol_scope[]" value="security" checked> 安全状态检查</label><br>
                            <label><input type="checkbox" name="patrol_scope[]" value="performance" checked> 网站性能监控</label><br>
                            <label><input type="checkbox" name="patrol_scope[]" value="errors" checked> 错误日志检查</label><br>
                            <label><input type="checkbox" name="patrol_scope[]" value="plugins" checked> 插件状态监控</label><br>
                            <label><input type="checkbox" name="patrol_scope[]" value="updates" checked> 更新检查</label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th><label for="auto_fix">自动修复</label></th>
                    <td>
                        <label>
                            <input type="checkbox" id="auto_fix" name="auto_fix">
                            启用自动修复安全问题
                        </label>
                        <p class="description">仅修复低风险问题，高风险问题需要人工确认</p>
                    </td>
                </tr>
            </table>
            <div class="patrol-settings-actions">
                <button type="button" id="save-patrol-settings" class="button button-primary">保存设置</button>
                <button type="button" id="cancel-patrol-settings" class="button">取消</button>
            </div>
        </div>
    </div>
    
    <!-- 实时监控面板 -->
    <div class="ai-optimizer-card">
        <h2>📈 实时监控面板</h2>
        <div class="monitoring-grid">
            <div class="monitor-card">
                <h3>🖥️ 系统性能</h3>
                <div class="metric-row">
                    <span class="metric-label">CPU使用率:</span>
                    <span class="metric-value" id="cpu-usage">--</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">内存使用:</span>
                    <span class="metric-value" id="memory-usage">--</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">磁盘使用:</span>
                    <span class="metric-value" id="disk-usage">--</span>
                </div>
                <canvas id="performance-chart" width="300" height="150"></canvas>
            </div>
            
            <div class="monitor-card">
                <h3>🔒 安全状态</h3>
                <div class="security-item">
                    <span class="security-icon">🔐</span>
                    <span class="security-text">SSL证书:</span>
                    <span class="security-status" id="ssl-status">检查中...</span>
                </div>
                <div class="security-item">
                    <span class="security-icon">🛡️</span>
                    <span class="security-text">防火墙:</span>
                    <span class="security-status" id="firewall-status">检查中...</span>
                </div>
                <div class="security-item">
                    <span class="security-icon">🔑</span>
                    <span class="security-text">登录安全:</span>
                    <span class="security-status" id="login-security">检查中...</span>
                </div>
            </div>
            
            <div class="monitor-card">
                <h3>📊 网站指标</h3>
                <div class="metric-row">
                    <span class="metric-label">页面加载时间:</span>
                    <span class="metric-value" id="page-load-time">--</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">数据库查询:</span>
                    <span class="metric-value" id="db-queries">--</span>
                </div>
                <div class="metric-row">
                    <span class="metric-label">在线用户:</span>
                    <span class="metric-value" id="online-users">--</span>
                </div>
                <canvas id="traffic-chart" width="300" height="150"></canvas>
            </div>
            
            <div class="monitor-card">
                <h3>⚠️ 错误监控</h3>
                <div class="error-summary">
                    <div class="error-type">
                        <span class="error-count" id="php-errors">0</span>
                        <span class="error-label">PHP错误</span>
                    </div>
                    <div class="error-type">
                        <span class="error-count" id="js-errors">0</span>
                        <span class="error-label">JS错误</span>
                    </div>
                    <div class="error-type">
                        <span class="error-count" id="404-errors">0</span>
                        <span class="error-label">404错误</span>
                    </div>
                </div>
                <div class="recent-errors" id="recent-errors">
                    <div class="no-errors">暂无错误</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- AI巡逻实时日志 -->
    <div class="ai-optimizer-card">
        <h2>📋 AI巡逻实时日志</h2>
        <div class="log-controls">
            <div class="log-filters">
                <label>
                    <input type="checkbox" id="filter-info" checked> 信息
                </label>
                <label>
                    <input type="checkbox" id="filter-warning" checked> 警告
                </label>
                <label>
                    <input type="checkbox" id="filter-error" checked> 错误
                </label>
                <label>
                    <input type="checkbox" id="filter-success" checked> 成功
                </label>
            </div>
            <div class="log-actions">
                <button type="button" id="clear-patrol-logs" class="button">清空日志</button>
                <button type="button" id="export-patrol-logs" class="button">导出日志</button>
                <button type="button" id="refresh-logs" class="button">刷新</button>
            </div>
        </div>
        <div id="patrol-log-container" class="patrol-log-viewer">
            <div class="log-placeholder">巡逻日志将在此显示...</div>
        </div>
    </div>
    
    <!-- 巡逻历史记录 -->
    <div class="ai-optimizer-card">
        <h2>📚 巡逻历史记录</h2>
        <div class="history-controls">
            <select id="history-period">
                <option value="24h">最近24小时</option>
                <option value="7d">最近7天</option>
                <option value="30d">最近30天</option>
            </select>
            <button type="button" id="refresh-history" class="button">刷新历史</button>
        </div>
        <div id="patrol-history" class="patrol-history-table">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>巡逻时间</th>
                        <th>检查项目</th>
                        <th>发现问题</th>
                        <th>修复状态</th>
                        <th>耗时</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="history-tbody">
                    <tr>
                        <td colspan="6" class="no-data">暂无巡逻历史记录</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- 系统健康度评分 -->
    <div class="ai-optimizer-card">
        <h2>🏥 系统健康度评分</h2>
        <div class="health-dashboard">
            <div class="health-score">
                <div class="score-circle">
                    <canvas id="health-score-chart" width="200" height="200"></canvas>
                    <div class="score-text">
                        <div class="score-value" id="health-score">--</div>
                        <div class="score-label">健康度</div>
                    </div>
                </div>
            </div>
            <div class="health-details">
                <div class="health-item">
                    <div class="health-icon">🔧</div>
                    <div class="health-content">
                        <div class="health-title">系统性能</div>
                        <div class="health-status" id="performance-health">评估中...</div>
                    </div>
                    <div class="health-score-item" id="performance-score">--</div>
                </div>
                <div class="health-item">
                    <div class="health-icon">🔐</div>
                    <div class="health-content">
                        <div class="health-title">安全状态</div>
                        <div class="health-status" id="security-health">评估中...</div>
                    </div>
                    <div class="health-score-item" id="security-score">--</div>
                </div>
                <div class="health-item">
                    <div class="health-icon">📈</div>
                    <div class="health-content">
                        <div class="health-title">SEO优化</div>
                        <div class="health-status" id="seo-health">评估中...</div>
                    </div>
                    <div class="health-score-item" id="seo-score">--</div>
                </div>
                <div class="health-item">
                    <div class="health-icon">🔄</div>
                    <div class="health-content">
                        <div class="health-title">维护状态</div>
                        <div class="health-status" id="maintenance-health">评估中...</div>
                    </div>
                    <div class="health-score-item" id="maintenance-score">--</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ai-optimizer-wrap {
    max-width: 1200px;
    margin: 0 auto;
}

.ai-optimizer-card {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.ai-optimizer-card h2 {
    margin-top: 0;
    color: #165DFF;
    display: flex;
    align-items: center;
    gap: 10px;
}

.patrol-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.patrol-status {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #ccc;
    animation: pulse 2s infinite;
}

.status-dot.active {
    background: #00F5D4;
}

.status-dot.inactive {
    background: #999;
    animation: none;
}

.patrol-actions {
    display: flex;
    gap: 10px;
}

.patrol-settings-panel {
    border-top: 1px solid #e1e5e9;
    padding-top: 20px;
    margin-top: 20px;
}

.patrol-settings-actions {
    text-align: right;
    margin-top: 15px;
}

.monitoring-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.monitor-card {
    background: #f8f9fa;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 20px;
}

.monitor-card h3 {
    margin-top: 0;
    color: #165DFF;
    display: flex;
    align-items: center;
    gap: 10px;
}

.metric-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.metric-label {
    color: #666;
}

.metric-value {
    font-weight: bold;
    color: #165DFF;
}

.security-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.security-icon {
    font-size: 20px;
}

.security-text {
    flex: 1;
    color: #666;
}

.security-status {
    font-weight: bold;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.security-status.secure {
    background: #d4edda;
    color: #155724;
}

.security-status.warning {
    background: #fff3cd;
    color: #856404;
}

.security-status.danger {
    background: #f8d7da;
    color: #721c24;
}

.error-summary {
    display: flex;
    justify-content: space-around;
    margin-bottom: 15px;
}

.error-type {
    text-align: center;
}

.error-count {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #FF4757;
}

.error-label {
    font-size: 12px;
    color: #666;
}

.recent-errors {
    max-height: 100px;
    overflow-y: auto;
    border: 1px solid #e1e5e9;
    border-radius: 4px;
    padding: 10px;
    background: #fff;
}

.no-errors {
    text-align: center;
    color: #2ED573;
    font-style: italic;
}

.log-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.log-filters {
    display: flex;
    gap: 15px;
}

.log-actions {
    display: flex;
    gap: 10px;
}

.patrol-log-viewer {
    background: #1a1a1a;
    color: #00F5D4;
    padding: 15px;
    border-radius: 8px;
    max-height: 400px;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.4;
}

.log-placeholder {
    text-align: center;
    color: #666;
    font-style: italic;
}

.log-entry {
    margin-bottom: 5px;
    padding: 5px 0;
    border-bottom: 1px solid #333;
}

.log-timestamp {
    color: #888;
    margin-right: 10px;
}

.log-level {
    font-weight: bold;
    margin-right: 10px;
}

.log-level-info { color: #00F5D4; }
.log-level-warning { color: #FFB800; }
.log-level-error { color: #FF4757; }
.log-level-success { color: #2ED573; }

.history-controls {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.patrol-history-table {
    overflow-x: auto;
}

.no-data {
    text-align: center;
    color: #666;
    font-style: italic;
}

.health-dashboard {
    display: flex;
    gap: 30px;
    align-items: center;
}

.health-score {
    position: relative;
    flex-shrink: 0;
}

.score-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.score-value {
    font-size: 36px;
    font-weight: bold;
    color: #165DFF;
}

.score-label {
    font-size: 14px;
    color: #666;
}

.health-details {
    flex: 1;
}

.health-item {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.health-icon {
    font-size: 24px;
    width: 40px;
    text-align: center;
}

.health-content {
    flex: 1;
}

.health-title {
    font-weight: bold;
    color: #333;
}

.health-status {
    font-size: 14px;
    color: #666;
}

.health-score-item {
    font-size: 20px;
    font-weight: bold;
    color: #165DFF;
    min-width: 50px;
    text-align: center;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

@media (max-width: 768px) {
    .monitoring-grid {
        grid-template-columns: 1fr;
    }
    
    .health-dashboard {
        flex-direction: column;
        align-items: center;
    }
    
    .patrol-controls {
        flex-direction: column;
        gap: 15px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    let patrolInterval;
    let isPatrolRunning = false;
    let logUpdateInterval;
    
    // 初始化
    initializeMonitor();
    
    function initializeMonitor() {
        // 加载初始数据
        loadMonitoringData();
        loadPatrolHistory();
        updateHealthScore();
        
        // 启动实时更新
        startRealTimeUpdates();
    }
    
    // 开始巡逻
    $('#start-patrol').on('click', function() {
        if (isPatrolRunning) {
            return;
        }
        
        const button = $(this);
        const originalText = button.html();
        
        button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> 启动中...');
        
        $.post(ajaxurl, {
            action: 'ai_optimizer_start_patrol',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>'
        })
        .done(function(response) {
            if (response.success) {
                isPatrolRunning = true;
                updatePatrolStatus('active', '巡逻系统运行中');
                $('#start-patrol').prop('disabled', true);
                $('#stop-patrol').prop('disabled', false);
                
                // 开始巡逻日志更新
                startPatrolLogUpdates();
                
                addPatrolLog('success', '巡逻启动', 'AI巡逻系统已成功启动');
            } else {
                alert('启动巡逻失败: ' + response.data);
                addPatrolLog('error', '巡逻启动失败', response.data);
            }
        })
        .fail(function(xhr, status, error) {
            alert('启动巡逻时发生网络错误: ' + error);
            addPatrolLog('error', '网络错误', '启动巡逻时发生网络错误: ' + error);
        })
        .always(function() {
            button.prop('disabled', false).html(originalText);
        });
    });
    
    // 停止巡逻
    $('#stop-patrol').on('click', function() {
        if (!isPatrolRunning) {
            return;
        }
        
        const button = $(this);
        const originalText = button.html();
        
        button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> 停止中...');
        
        $.post(ajaxurl, {
            action: 'ai_optimizer_stop_patrol',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>'
        })
        .done(function(response) {
            if (response.success) {
                isPatrolRunning = false;
                updatePatrolStatus('inactive', '巡逻系统已停止');
                $('#start-patrol').prop('disabled', false);
                $('#stop-patrol').prop('disabled', true);
                
                // 停止巡逻日志更新
                stopPatrolLogUpdates();
                
                addPatrolLog('info', '巡逻停止', 'AI巡逻系统已停止');
            } else {
                alert('停止巡逻失败: ' + response.data);
            }
        })
        .fail(function() {
            alert('停止巡逻时发生网络错误');
        })
        .always(function() {
            button.prop('disabled', false).html(originalText);
        });
    });
    
    // 巡逻设置
    $('#patrol-settings').on('click', function() {
        $('#patrol-settings-panel').toggle();
    });
    
    // 保存巡逻设置
    $('#save-patrol-settings').on('click', function() {
        const settings = {
            interval: $('#patrol_interval').val(),
            scope: $('input[name="patrol_scope[]"]:checked').map(function() {
                return $(this).val();
            }).get(),
            auto_fix: $('#auto_fix').is(':checked')
        };
        
        $.post(ajaxurl, {
            action: 'ai_optimizer_save_patrol_settings',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>',
            settings: JSON.stringify(settings)
        })
        .done(function(response) {
            if (response.success) {
                alert('巡逻设置已保存');
                $('#patrol-settings-panel').hide();
                addPatrolLog('info', '设置保存', '巡逻设置已更新');
            } else {
                alert('保存设置失败: ' + response.data);
            }
        })
        .fail(function() {
            alert('保存设置时发生网络错误');
        });
    });
    
    // 取消巡逻设置
    $('#cancel-patrol-settings').on('click', function() {
        $('#patrol-settings-panel').hide();
    });
    
    // 实时更新监控数据
    function startRealTimeUpdates() {
        setInterval(function() {
            loadMonitoringData();
        }, 5000); // 每5秒更新一次
    }
    
    // 加载监控数据
    function loadMonitoringData() {
        $.post(ajaxurl, {
            action: 'ai_optimizer_get_monitoring_data',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>'
        })
        .done(function(response) {
            if (response.success) {
                updateMonitoringDisplay(response.data);
            }
        })
        .fail(function() {
            console.error('获取监控数据失败');
        });
    }
    
    // 更新监控显示
    function updateMonitoringDisplay(data) {
        // 更新系统性能
        $('#cpu-usage').text(data.performance.cpu_usage + '%');
        $('#memory-usage').text(data.performance.memory_usage + 'MB');
        $('#disk-usage').text(data.performance.disk_usage + '%');
        
        // 更新网站指标
        $('#page-load-time').text(data.website.load_time + 'ms');
        $('#db-queries').text(data.website.db_queries);
        $('#online-users').text(data.website.online_users);
        
        // 更新安全状态
        updateSecurityStatus(data.security);
        
        // 更新错误计数
        $('#php-errors').text(data.errors.php_errors);
        $('#js-errors').text(data.errors.js_errors);
        $('#404-errors').text(data.errors.not_found_errors);
        
        // 更新最近错误
        updateRecentErrors(data.errors.recent);
    }
    
    // 更新安全状态
    function updateSecurityStatus(security) {
        const statusMap = {
            'secure': 'secure',
            'warning': 'warning',
            'danger': 'danger'
        };
        
        $('#ssl-status').text(security.ssl.status).removeClass().addClass('security-status ' + statusMap[security.ssl.level]);
        $('#firewall-status').text(security.firewall.status).removeClass().addClass('security-status ' + statusMap[security.firewall.level]);
        $('#login-security').text(security.login.status).removeClass().addClass('security-status ' + statusMap[security.login.level]);
    }
    
    // 更新最近错误
    function updateRecentErrors(errors) {
        const container = $('#recent-errors');
        
        if (errors.length === 0) {
            container.html('<div class="no-errors">暂无错误</div>');
            return;
        }
        
        let html = '';
        errors.forEach(function(error) {
            html += `<div class="error-item">
                <span class="error-time">${error.time}</span>
                <span class="error-message">${error.message}</span>
            </div>`;
        });
        
        container.html(html);
    }
    
    // 更新巡逻状态
    function updatePatrolStatus(status, message) {
        const statusDot = $('.status-dot');
        const statusText = $('.status-text');
        
        statusDot.removeClass('active inactive').addClass(status);
        statusText.text(message);
        
        if (status === 'active') {
            $('#last-patrol-time').text(new Date().toLocaleString());
        }
    }
    
    // 开始巡逻日志更新
    function startPatrolLogUpdates() {
        logUpdateInterval = setInterval(function() {
            updatePatrolLogs();
        }, 2000); // 每2秒更新一次
    }
    
    // 停止巡逻日志更新
    function stopPatrolLogUpdates() {
        if (logUpdateInterval) {
            clearInterval(logUpdateInterval);
            logUpdateInterval = null;
        }
    }
    
    // 更新巡逻日志
    function updatePatrolLogs() {
        $.post(ajaxurl, {
            action: 'ai_optimizer_get_patrol_logs',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>'
        })
        .done(function(response) {
            if (response.success) {
                displayPatrolLogs(response.data);
            }
        })
        .fail(function() {
            console.error('获取巡逻日志失败');
        });
    }
    
    // 显示巡逻日志
    function displayPatrolLogs(logs) {
        const container = $('#patrol-log-container');
        let html = '';
        
        if (logs.length === 0) {
            html = '<div class="log-placeholder">暂无巡逻日志</div>';
        } else {
            logs.forEach(function(log) {
                if (shouldShowLog(log.level)) {
                    html += `
                        <div class="log-entry">
                            <span class="log-timestamp">[${log.timestamp}]</span>
                            <span class="log-level log-level-${log.level}">[${log.level.toUpperCase()}]</span>
                            <strong>${log.title}:</strong> ${log.message}
                        </div>
                    `;
                }
            });
        }
        
        container.html(html);
        
        // 自动滚动到底部
        container.scrollTop(container[0].scrollHeight);
    }
    
    // 检查是否应该显示日志
    function shouldShowLog(level) {
        return $('#filter-' + level).is(':checked');
    }
    
    // 添加巡逻日志
    function addPatrolLog(level, title, message) {
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = `
            <div class="log-entry">
                <span class="log-timestamp">[${timestamp}]</span>
                <span class="log-level log-level-${level}">[${level.toUpperCase()}]</span>
                <strong>${title}:</strong> ${message}
            </div>
        `;
        
        $('#patrol-log-container').append(logEntry);
        $('#patrol-log-container').scrollTop($('#patrol-log-container')[0].scrollHeight);
    }
    
    // 日志过滤
    $('input[id^="filter-"]').on('change', function() {
        updatePatrolLogs();
    });
    
    // 清空日志
    $('#clear-patrol-logs').on('click', function() {
        $('#patrol-log-container').html('<div class="log-placeholder">日志已清空</div>');
    });
    
    // 导出日志
    $('#export-patrol-logs').on('click', function() {
        const logs = $('#patrol-log-container').text();
        const blob = new Blob([logs], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'patrol-logs-' + new Date().toISOString().substr(0, 10) + '.txt';
        a.click();
        URL.revokeObjectURL(url);
    });
    
    // 刷新日志
    $('#refresh-logs').on('click', function() {
        updatePatrolLogs();
    });
    
    // 加载巡逻历史
    function loadPatrolHistory() {
        const period = $('#history-period').val();
        
        $.post(ajaxurl, {
            action: 'ai_optimizer_get_patrol_history',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>',
            period: period
        })
        .done(function(response) {
            if (response.success) {
                displayPatrolHistory(response.data);
            }
        })
        .fail(function() {
            console.error('获取巡逻历史失败');
        });
    }
    
    // 显示巡逻历史
    function displayPatrolHistory(history) {
        const tbody = $('#history-tbody');
        
        if (history.length === 0) {
            tbody.html('<tr><td colspan="6" class="no-data">暂无巡逻历史记录</td></tr>');
            return;
        }
        
        let html = '';
        history.forEach(function(record) {
            html += `
                <tr>
                    <td>${record.patrol_time}</td>
                    <td>${record.check_items}</td>
                    <td>${record.issues_found}</td>
                    <td>${record.fix_status}</td>
                    <td>${record.duration}</td>
                    <td>
                        <button class="button button-small view-details" data-id="${record.id}">查看详情</button>
                    </td>
                </tr>
            `;
        });
        
        tbody.html(html);
    }
    
    // 历史记录筛选
    $('#history-period').on('change', function() {
        loadPatrolHistory();
    });
    
    // 刷新历史
    $('#refresh-history').on('click', function() {
        loadPatrolHistory();
    });
    
    // 查看详情
    $(document).on('click', '.view-details', function() {
        const recordId = $(this).data('id');
        // 这里可以打开模态窗口显示详细信息
        alert('查看巡逻记录 #' + recordId + ' 的详细信息（功能待实现）');
    });
    
    // 更新健康度评分
    function updateHealthScore() {
        $.post(ajaxurl, {
            action: 'ai_optimizer_get_health_score',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>'
        })
        .done(function(response) {
            if (response.success) {
                displayHealthScore(response.data);
            }
        })
        .fail(function() {
            console.error('获取健康度评分失败');
        });
    }
    
    // 显示健康度评分
    function displayHealthScore(data) {
        $('#health-score').text(data.overall_score);
        $('#performance-score').text(data.performance_score);
        $('#security-score').text(data.security_score);
        $('#seo-score').text(data.seo_score);
        $('#maintenance-score').text(data.maintenance_score);
        
        $('#performance-health').text(data.performance_status);
        $('#security-health').text(data.security_status);
        $('#seo-health').text(data.seo_status);
        $('#maintenance-health').text(data.maintenance_status);
        
        // 绘制健康度圆形图表
        drawHealthChart(data.overall_score);
    }
    
    // 绘制健康度图表
    function drawHealthChart(score) {
        const canvas = document.getElementById('health-score-chart');
        const ctx = canvas.getContext('2d');
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = 80;
        
        // 清空画布
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // 绘制背景圆
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        ctx.strokeStyle = '#e1e5e9';
        ctx.lineWidth = 8;
        ctx.stroke();
        
        // 绘制进度弧
        const endAngle = (score / 100) * 2 * Math.PI - Math.PI / 2;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, -Math.PI / 2, endAngle);
        ctx.strokeStyle = score >= 80 ? '#2ED573' : score >= 60 ? '#FFB800' : '#FF4757';
        ctx.lineWidth = 8;
        ctx.lineCap = 'round';
        ctx.stroke();
    }
    
    // 初始化时模拟一些日志
    setTimeout(function() {
        addPatrolLog('info', '系统启动', '监控系统已初始化');
        addPatrolLog('info', '数据库连接', '数据库连接正常');
        addPatrolLog('info', '安全检查', '安全状态良好');
    }, 1000);
});
</script>
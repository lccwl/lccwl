<?php
/**
 * 仪表盘页面类
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Dashboard {
    
    /**
     * 渲染仪表盘页面
     */
    public static function render() {
        AI_Optimizer_Admin::verify_admin_access();
        
        global $wpdb;
        
        // 获取统计数据
        $stats = self::get_dashboard_stats();
        
        ?>
        <div class="wrap ai-optimizer-dashboard">
            <h1>AI智能网站优化器 - 仪表盘</h1>
            
            <!-- 概览卡片 -->
            <div class="ai-optimizer-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
                
                <!-- 监控统计 -->
                <div class="ai-optimizer-card">
                    <div class="ai-optimizer-stat">
                        <div class="number"><?php echo esc_html($stats['monitored_pages']); ?></div>
                        <div class="label">监控页面</div>
                    </div>
                </div>
                
                <!-- 平均加载时间 -->
                <div class="ai-optimizer-card">
                    <div class="ai-optimizer-stat">
                        <div class="number"><?php echo esc_html(number_format($stats['avg_load_time'], 2)); ?>s</div>
                        <div class="label">平均加载时间</div>
                    </div>
                </div>
                
                <!-- SEO评分 -->
                <div class="ai-optimizer-card">
                    <div class="ai-optimizer-stat">
                        <div class="number"><?php echo esc_html($stats['avg_seo_score']); ?></div>
                        <div class="label">平均SEO评分</div>
                    </div>
                </div>
                
                <!-- AI生成次数 -->
                <div class="ai-optimizer-card">
                    <div class="ai-optimizer-stat">
                        <div class="number"><?php echo esc_html($stats['total_generations']); ?></div>
                        <div class="label">AI生成总数</div>
                    </div>
                </div>
                
            </div>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                
                <!-- 主要内容区域 -->
                <div>
                    <!-- 性能趋势图 -->
                    <div class="ai-optimizer-card">
                        <h3>性能趋势</h3>
                        <canvas id="performanceChart" width="400" height="200"></canvas>
                    </div>
                    
                    <!-- 最近活动 -->
                    <div class="ai-optimizer-card">
                        <h3>最近活动</h3>
                        <div id="recentActivities">
                            <?php self::render_recent_activities(); ?>
                        </div>
                    </div>
                </div>
                
                <!-- 侧边栏 -->
                <div>
                    <!-- 快速操作 -->
                    <div class="ai-optimizer-card">
                        <h3>快速操作</h3>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <button class="ai-optimizer-btn" onclick="runFullAnalysis()">
                                运行完整分析
                            </button>
                            <button class="ai-optimizer-btn" onclick="optimizeSEO()">
                                SEO优化
                            </button>
                            <button class="ai-optimizer-btn" onclick="generateContent()">
                                AI内容生成
                            </button>
                            <button class="ai-optimizer-btn" onclick="exportReport()">
                                导出报告
                            </button>
                        </div>
                    </div>
                    
                    <!-- 系统状态 -->
                    <div class="ai-optimizer-card">
                        <h3>系统状态</h3>
                        <?php self::render_system_status(); ?>
                    </div>
                    
                    <!-- 最新SEO建议 -->
                    <div class="ai-optimizer-card">
                        <h3>待处理建议</h3>
                        <?php self::render_pending_suggestions(); ?>
                    </div>
                    
                </div>
            </div>
            
        </div>
        
        <script>
        // 加载性能图表
        document.addEventListener('DOMContentLoaded', function() {
            loadPerformanceChart();
            setInterval(refreshDashboard, 30000); // 30秒刷新
        });
        
        function loadPerformanceChart() {
            const ctx = document.getElementById('performanceChart').getContext('2d');
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=ai_optimizer_chart_data&nonce=<?php echo wp_create_nonce('ai_optimizer_nonce'); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.data.labels,
                            datasets: [{
                                label: '加载时间(秒)',
                                data: data.data.load_times,
                                borderColor: '#165DFF',
                                backgroundColor: 'rgba(22, 93, 255, 0.1)',
                                tension: 0.4
                            }, {
                                label: '内存使用(MB)',
                                data: data.data.memory_usage,
                                borderColor: '#00F5D4',
                                backgroundColor: 'rgba(0, 245, 212, 0.1)',
                                tension: 0.4,
                                yAxisID: 'y1'
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: '加载时间(秒)'
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: '内存使用(MB)'
                                    },
                                    grid: {
                                        drawOnChartArea: false,
                                    },
                                }
                            }
                        }
                    });
                }
            });
        }
        
        function refreshDashboard() {
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=ai_optimizer_dashboard_stats&nonce=<?php echo wp_create_nonce('ai_optimizer_nonce'); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateDashboardStats(data.data);
                }
            });
        }
        
        function updateDashboardStats(stats) {
            // 更新统计数字
            document.querySelectorAll('.ai-optimizer-stat').forEach((stat, index) => {
                const number = stat.querySelector('.number');
                if (number) {
                    switch(index) {
                        case 0:
                            number.textContent = stats.monitored_pages;
                            break;
                        case 1:
                            number.textContent = parseFloat(stats.avg_load_time).toFixed(2) + 's';
                            break;
                        case 2:
                            number.textContent = stats.avg_seo_score;
                            break;
                        case 3:
                            number.textContent = stats.total_generations;
                            break;
                    }
                }
            });
        }
        
        function runFullAnalysis() {
            const btn = event.target;
            const originalText = btn.textContent;
            btn.textContent = '分析中...';
            btn.disabled = true;
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=ai_optimizer_run_analysis&nonce=<?php echo wp_create_nonce('ai_optimizer_nonce'); ?>'
            })
            .then(response => response.json())
            .then(data => {
                alert(data.success ? '分析完成！' : '分析失败：' + data.data);
            })
            .finally(() => {
                btn.textContent = originalText;
                btn.disabled = false;
            });
        }
        
        function optimizeSEO() {
            window.location.href = '<?php echo admin_url('admin.php?page=ai-optimizer-seo'); ?>';
        }
        
        function generateContent() {
            window.location.href = '<?php echo admin_url('admin.php?page=ai-optimizer-ai-tools'); ?>';
        }
        
        function exportReport() {
            window.open('<?php echo admin_url('admin.php?page=ai-optimizer&action=export'); ?>', '_blank');
        }
        </script>
        <?php
    }
    
    /**
     * 获取仪表盘统计数据
     */
    private static function get_dashboard_stats() {
        global $wpdb;
        
        $stats = array(
            'monitored_pages' => 0,
            'avg_load_time' => 0,
            'avg_seo_score' => 0,
            'total_generations' => 0,
            'pending_suggestions' => 0,
            'frontend_errors' => 0
        );
        
        // 监控页面数
        $monitoring_table = $wpdb->prefix . 'ai_optimizer_monitoring';
        if ($wpdb->get_var("SHOW TABLES LIKE '$monitoring_table'") === $monitoring_table) {
            $stats['monitored_pages'] = $wpdb->get_var(
                "SELECT COUNT(DISTINCT url) FROM $monitoring_table"
            ) ?: 0;
            
            $stats['avg_load_time'] = $wpdb->get_var(
                "SELECT AVG(load_time) FROM $monitoring_table 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
            ) ?: 0;
        }
        
        // SEO评分
        $seo_table = $wpdb->prefix . 'ai_optimizer_seo_analysis';
        if ($wpdb->get_var("SHOW TABLES LIKE '$seo_table'") === $seo_table) {
            $stats['avg_seo_score'] = $wpdb->get_var(
                "SELECT AVG(seo_score) FROM $seo_table"
            ) ?: 0;
        }
        
        // AI生成数量
        $generations_table = $wpdb->prefix . 'ai_optimizer_generations';
        if ($wpdb->get_var("SHOW TABLES LIKE '$generations_table'") === $generations_table) {
            $stats['total_generations'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM $generations_table"
            ) ?: 0;
        }
        
        // 待处理建议
        $suggestions_table = $wpdb->prefix . 'ai_optimizer_seo_suggestions';
        if ($wpdb->get_var("SHOW TABLES LIKE '$suggestions_table'") === $suggestions_table) {
            $stats['pending_suggestions'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM $suggestions_table WHERE status = 'pending'"
            ) ?: 0;
        }
        
        return $stats;
    }
    
    /**
     * 渲染最近活动
     */
    private static function render_recent_activities() {
        global $wpdb;
        
        $activities = array();
        
        // 获取最近的分析记录
        $seo_table = $wpdb->prefix . 'ai_optimizer_seo_analysis';
        if ($wpdb->get_var("SHOW TABLES LIKE '$seo_table'") === $seo_table) {
            $recent_seo = $wpdb->get_results(
                "SELECT url, seo_score, created_at FROM $seo_table 
                 ORDER BY created_at DESC LIMIT 5"
            );
            
            foreach ($recent_seo as $seo) {
                $activities[] = array(
                    'type' => 'seo',
                    'title' => 'SEO分析完成',
                    'description' => "分析了 {$seo->url}，评分：{$seo->seo_score}",
                    'time' => $seo->created_at
                );
            }
        }
        
        // 获取最近的AI生成
        $generations_table = $wpdb->prefix . 'ai_optimizer_generations';
        if ($wpdb->get_var("SHOW TABLES LIKE '$generations_table'") === $generations_table) {
            $recent_generations = $wpdb->get_results(
                "SELECT type, status, created_at FROM $generations_table 
                 ORDER BY created_at DESC LIMIT 5"
            );
            
            foreach ($recent_generations as $gen) {
                $activities[] = array(
                    'type' => 'generation',
                    'title' => 'AI内容生成',
                    'description' => "生成了{$gen->type}内容，状态：{$gen->status}",
                    'time' => $gen->created_at
                );
            }
        }
        
        // 按时间排序
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        
        if (empty($activities)) {
            echo '<p class="text-muted">暂无活动记录</p>';
            return;
        }
        
        echo '<div class="activities-list">';
        foreach (array_slice($activities, 0, 10) as $activity) {
            $time_ago = human_time_diff(strtotime($activity['time']));
            echo '<div class="activity-item" style="padding: 10px 0; border-bottom: 1px solid #eee;">';
            echo '<div class="activity-title" style="font-weight: bold;">' . esc_html($activity['title']) . '</div>';
            echo '<div class="activity-desc" style="color: #666; font-size: 0.9em;">' . esc_html($activity['description']) . '</div>';
            echo '<div class="activity-time" style="color: #999; font-size: 0.8em;">' . esc_html($time_ago) . '前</div>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    /**
     * 渲染系统状态
     */
    private static function render_system_status() {
        $admin = new AI_Optimizer_Admin();
        $status = $admin->get_system_status();
        
        echo '<div class="system-status">';
        
        echo '<div class="status-item" style="display: flex; justify-content: between; padding: 5px 0;">';
        echo '<span>API配置</span>';
        echo AI_Optimizer_Admin::status_indicator($status['api_key_configured']);
        echo '</div>';
        
        echo '<div class="status-item" style="display: flex; justify-content: between; padding: 5px 0;">';
        echo '<span>监控功能</span>';
        echo AI_Optimizer_Admin::status_indicator($status['monitoring_enabled']);
        echo '</div>';
        
        echo '<div class="status-item" style="display: flex; justify-content: between; padding: 5px 0;">';
        echo '<span>SEO优化</span>';
        echo AI_Optimizer_Admin::status_indicator($status['seo_enabled']);
        echo '</div>';
        
        echo '<div class="status-item" style="display: flex; justify-content: between; padding: 5px 0;">';
        echo '<span>cURL扩展</span>';
        echo AI_Optimizer_Admin::status_indicator($status['curl_enabled']);
        echo '</div>';
        
        echo '</div>';
    }
    
    /**
     * 渲染待处理建议
     */
    private static function render_pending_suggestions() {
        global $wpdb;
        
        $suggestions_table = $wpdb->prefix . 'ai_optimizer_seo_suggestions';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$suggestions_table'") !== $suggestions_table) {
            echo '<p class="text-muted">暂无数据</p>';
            return;
        }
        
        $suggestions = $wpdb->get_results(
            "SELECT title, priority, created_at FROM $suggestions_table 
             WHERE status = 'pending' 
             ORDER BY priority DESC, created_at DESC 
             LIMIT 5"
        );
        
        if (empty($suggestions)) {
            echo '<p class="text-muted">暂无待处理建议</p>';
            return;
        }
        
        echo '<div class="suggestions-list">';
        foreach ($suggestions as $suggestion) {
            $priority_class = 'priority-' . $suggestion->priority;
            $priority_text = array(
                'critical' => '紧急',
                'high' => '高',
                'medium' => '中',
                'low' => '低'
            )[$suggestion->priority] ?? '中';
            
            echo '<div class="suggestion-item" style="padding: 8px 0; border-bottom: 1px solid #eee;">';
            echo '<div style="display: flex; justify-content: between; align-items: center;">';
            echo '<span class="suggestion-title" style="flex: 1;">' . esc_html($suggestion->title) . '</span>';
            echo '<span class="priority-badge ' . esc_attr($priority_class) . '" style="padding: 2px 6px; border-radius: 3px; font-size: 0.8em; background: #ffd700; color: #333;">' . esc_html($priority_text) . '</span>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        
        $settings_url = admin_url('admin.php?page=ai-optimizer-seo');
        echo '<div style="text-align: center; margin-top: 10px;">';
        echo '<a href="' . esc_url($settings_url) . '" class="button">查看全部建议</a>';
        echo '</div>';
    }
}
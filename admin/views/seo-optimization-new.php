<?php
/**
 * 新版SEO优化分析页面
 */

if (!defined('ABSPATH')) {
    exit;
}

// 获取保存的设置
$saved_analysis_scope = get_option('ai_seo_analysis_scope', array(
    'technical' => true,
    'content' => true,
    'performance' => true,
    'competitors' => true,
    'search_latest' => true
));

$saved_optimization_strategy = get_option('ai_seo_optimization_strategy', 'comprehensive');
$saved_ai_model = get_option('ai_seo_ai_model', 'Qwen/QwQ-32B-Preview');
$saved_competitor_urls = get_option('ai_seo_competitor_urls', array());
$saved_auto_execution = get_option('ai_seo_auto_execution', false);
?>

<div class="wrap ai-optimizer-wrap">
    <h1>🚀 AI智能SEO优化分析</h1>
    
    <!-- SEO分析控制面板 -->
    <div class="ai-optimizer-card">
        <h2>🎯 AI分析控制面板</h2>
        <form id="seo-analysis-form">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="seo_ai_model">AI模型选择</label></th>
                    <td>
                        <select id="seo_ai_model" name="seo_ai_model" class="regular-text">
                            <option value="Qwen/QwQ-32B-Preview" <?php selected($saved_ai_model, 'Qwen/QwQ-32B-Preview'); ?>>Qwen/QwQ-32B (深度分析推荐)</option>
                            <option value="Qwen/Qwen2.5-7B-Instruct" <?php selected($saved_ai_model, 'Qwen/Qwen2.5-7B-Instruct'); ?>>Qwen2.5-7B (快速分析)</option>
                            <option value="meta-llama/Meta-Llama-3.1-8B-Instruct" <?php selected($saved_ai_model, 'meta-llama/Meta-Llama-3.1-8B-Instruct'); ?>>Meta-Llama-3.1-8B</option>
                            <option value="deepseek-ai/DeepSeek-V2.5" <?php selected($saved_ai_model, 'deepseek-ai/DeepSeek-V2.5'); ?>>DeepSeek-V2.5</option>
                            <option value="custom" <?php selected($saved_ai_model, 'custom'); ?>>自定义模型</option>
                        </select>
                        <div id="custom-model-config" style="display: <?php echo $saved_ai_model === 'custom' ? 'block' : 'none'; ?>; margin-top: 10px;">
                            <h4>自定义AI模型配置</h4>
                            <table class="form-table">
                                <tr>
                                    <th><label for="custom_model_name">模型名称</label></th>
                                    <td><input type="text" id="custom_model_name" name="custom_model_name" class="regular-text" placeholder="例如：gpt-4" value="<?php echo esc_attr(get_option('ai_seo_custom_model_name', '')); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label for="custom_api_endpoint">API端点</label></th>
                                    <td><input type="url" id="custom_api_endpoint" name="custom_api_endpoint" class="regular-text" placeholder="https://api.openai.com/v1/chat/completions" value="<?php echo esc_attr(get_option('ai_seo_custom_api_endpoint', '')); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label for="custom_api_key">API密钥</label></th>
                                    <td><input type="password" id="custom_api_key" name="custom_api_key" class="regular-text" placeholder="sk-..." value="<?php echo esc_attr(get_option('ai_seo_custom_api_key', '')); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label for="custom_request_format">请求格式</label></th>
                                    <td>
                                        <select id="custom_request_format" name="custom_request_format">
                                            <option value="openai" <?php selected(get_option('ai_seo_custom_request_format', 'openai'), 'openai'); ?>>OpenAI格式</option>
                                            <option value="claude" <?php selected(get_option('ai_seo_custom_request_format', 'openai'), 'claude'); ?>>Claude格式</option>
                                            <option value="huggingface" <?php selected(get_option('ai_seo_custom_request_format', 'openai'), 'huggingface'); ?>>HuggingFace格式</option>
                                            <option value="custom" <?php selected(get_option('ai_seo_custom_request_format', 'openai'), 'custom'); ?>>自定义格式</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr id="custom-format-config" style="display: <?php echo get_option('ai_seo_custom_request_format', 'openai') === 'custom' ? 'table-row' : 'none'; ?>;">
                                    <th><label for="custom_request_template">请求模板</label></th>
                                    <td>
                                        <textarea id="custom_request_template" name="custom_request_template" rows="5" class="large-text" placeholder='{"model": "模型名", "messages": [{"role": "user", "content": "PROMPT_TEXT"}]}'><?php echo esc_textarea(get_option('ai_seo_custom_request_template', '')); ?></textarea>
                                        <p class="description">使用 PROMPT_TEXT 作为占位符</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">分析范围</th>
                    <td>
                        <fieldset>
                            <label><input type="checkbox" id="analysis_technical" name="analysis_scope[]" value="technical" <?php checked(in_array('technical', $saved_analysis_scope) || $saved_analysis_scope['technical']); ?>> 技术SEO分析</label><br>
                            <label><input type="checkbox" id="analysis_content" name="analysis_scope[]" value="content" <?php checked(in_array('content', $saved_analysis_scope) || $saved_analysis_scope['content']); ?>> 内容质量分析</label><br>
                            <label><input type="checkbox" id="analysis_performance" name="analysis_scope[]" value="performance" <?php checked(in_array('performance', $saved_analysis_scope) || $saved_analysis_scope['performance']); ?>> 性能指标分析</label><br>
                            <label><input type="checkbox" id="analysis_competitors" name="analysis_scope[]" value="competitors" <?php checked(in_array('competitors', $saved_analysis_scope) || $saved_analysis_scope['competitors']); ?>> 竞争对手分析</label><br>
                            <label><input type="checkbox" id="analysis_search_latest" name="analysis_scope[]" value="search_latest" <?php checked(in_array('search_latest', $saved_analysis_scope) || $saved_analysis_scope['search_latest']); ?>> 获取最新SEO知识</label>
                        </fieldset>
                    </td>
                </tr>
                <tr id="competitors-config" style="display: <?php echo (in_array('competitors', $saved_analysis_scope) || $saved_analysis_scope['competitors']) ? 'table-row' : 'none'; ?>;">
                    <th scope="row">竞争对手网站</th>
                    <td>
                        <div id="competitor-urls">
                            <?php if (!empty($saved_competitor_urls)): ?>
                                <?php foreach ($saved_competitor_urls as $index => $url): ?>
                                    <div class="competitor-url-row">
                                        <input type="url" name="competitor_urls[]" value="<?php echo esc_attr($url); ?>" placeholder="https://example.com" class="regular-text">
                                        <button type="button" class="button remove-competitor">删除</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="competitor-url-row">
                                    <input type="url" name="competitor_urls[]" value="" placeholder="https://example.com" class="regular-text">
                                    <button type="button" class="button remove-competitor">删除</button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button type="button" id="add-competitor" class="button">添加竞争对手</button>
                        <p class="description">输入竞争对手网站URL进行对比分析</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">优化策略</th>
                    <td>
                        <select id="optimization_strategy" name="optimization_strategy" class="regular-text">
                            <option value="comprehensive" <?php selected($saved_optimization_strategy, 'comprehensive'); ?>>综合优化（推荐）</option>
                            <option value="technical_focus" <?php selected($saved_optimization_strategy, 'technical_focus'); ?>>技术优化重点</option>
                            <option value="content_focus" <?php selected($saved_optimization_strategy, 'content_focus'); ?>>内容优化重点</option>
                            <option value="speed_focus" <?php selected($saved_optimization_strategy, 'speed_focus'); ?>>速度优化重点</option>
                            <option value="mobile_focus" <?php selected($saved_optimization_strategy, 'mobile_focus'); ?>>移动端优化重点</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <div class="ai-optimizer-actions">
                <button type="button" id="start-seo-analysis" class="button button-primary button-large">
                    <span class="dashicons dashicons-analytics"></span>
                    开始AI深度分析
                </button>
                <button type="button" id="save-analysis-settings" class="button button-secondary">
                    <span class="dashicons dashicons-saved"></span>
                    保存设置
                </button>
            </div>
        </form>
    </div>
    
    <!-- 优化策略选项 -->
    <div class="ai-optimizer-card">
        <h2>🛠️ 优化策略选项</h2>
        <table class="form-table">
            <tr>
                <th scope="row">优化选项</th>
                <td>
                    <fieldset>
                        <label><input type="checkbox" id="auto_optimize_images" name="optimization_options[]" value="images" checked> 自动优化图片（添加缺失的alt属性）</label><br>
                        <label><input type="checkbox" id="auto_generate_sitemap" name="optimization_options[]" value="sitemap" checked> 自动生成/更新sitemap.xml</label><br>
                        <label><input type="checkbox" id="auto_optimize_database" name="optimization_options[]" value="database" checked> 数据库优化清理</label><br>
                        <label><input type="checkbox" id="auto_fix_meta" name="optimization_options[]" value="meta" checked> 自动修复Meta标签</label><br>
                        <label><input type="checkbox" id="auto_improve_speed" name="optimization_options[]" value="speed" checked> 自动性能优化</label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">自动执行</th>
                <td>
                    <label>
                        <input type="checkbox" id="auto_execution_enabled" name="auto_execution_enabled" <?php checked($saved_auto_execution); ?>>
                        启用自动执行最佳建议
                    </label>
                    <p class="description">启用后，系统将自动执行安全的优化建议</p>
                </td>
            </tr>
            <tr>
                <th scope="row">执行方式</th>
                <td>
                    <select id="optimization_mode" name="optimization_mode" class="regular-text">
                        <option value="manual">手动确认执行</option>
                        <option value="scheduled">定时自动执行</option>
                        <option value="immediate">立即自动执行</option>
                    </select>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- 分析结果显示区域 -->
    <div id="analysis-results" class="ai-optimizer-card" style="display: none;">
        <h2>📊 分析结果</h2>
        <div id="analysis-progress" class="progress-container">
            <div class="progress-bar"><div class="progress-fill"></div></div>
            <div class="progress-text">准备开始分析...</div>
        </div>
        <div id="analysis-content" style="display: none;">
            <!-- 详细分析结果 -->
            <div class="analysis-tabs">
                <button class="tab-button active" data-tab="overview">概览</button>
                <button class="tab-button" data-tab="technical">技术SEO</button>
                <button class="tab-button" data-tab="content">内容质量</button>
                <button class="tab-button" data-tab="performance">性能指标</button>
                <button class="tab-button" data-tab="competitors">竞争对手</button>
                <button class="tab-button" data-tab="ai-suggestions">AI建议</button>
            </div>
            
            <div class="tab-content active" id="overview-tab">
                <div class="overview-stats">
                    <div class="stat-card">
                        <div class="stat-value" id="seo-score">--</div>
                        <div class="stat-label">SEO评分</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="issues-found">--</div>
                        <div class="stat-label">发现问题</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="optimization-potential">--</div>
                        <div class="stat-label">优化潜力</div>
                    </div>
                </div>
            </div>
            
            <div class="tab-content" id="technical-tab">
                <div id="technical-details">技术SEO分析结果将显示在此...</div>
            </div>
            
            <div class="tab-content" id="content-tab">
                <div id="content-details">内容质量分析结果将显示在此...</div>
            </div>
            
            <div class="tab-content" id="performance-tab">
                <div id="performance-details">性能指标分析结果将显示在此...</div>
            </div>
            
            <div class="tab-content" id="competitors-tab">
                <div id="competitors-details">竞争对手分析结果将显示在此...</div>
            </div>
            
            <div class="tab-content" id="ai-suggestions-tab">
                <div id="ai-suggestions-details">AI优化建议将显示在此...</div>
            </div>
        </div>
    </div>
    
    <!-- 自动优化执行面板 -->
    <div id="auto-optimization-panel" class="ai-optimizer-card" style="display: none;">
        <h2>🚀 自动优化执行</h2>
        <div id="optimization-tasks">
            <!-- 优化任务列表 -->
        </div>
        <div class="optimization-actions">
            <button type="button" id="execute-optimizations" class="button button-primary">执行选中的优化</button>
            <button type="button" id="schedule-optimizations" class="button button-secondary">定时执行</button>
        </div>
    </div>
    
    <!-- 实时日志面板 -->
    <div id="analysis-logs" class="ai-optimizer-card" style="display: none;">
        <h2>📋 实时分析日志</h2>
        <div class="log-controls">
            <button type="button" id="clear-logs" class="button">清空日志</button>
            <button type="button" id="export-logs" class="button">导出日志</button>
            <label>
                <input type="checkbox" id="auto-scroll" checked> 自动滚动
            </label>
        </div>
        <div id="log-container" class="log-viewer">
            <!-- 日志内容 -->
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

.ai-optimizer-actions {
    text-align: center;
    margin-top: 20px;
}

.competitor-url-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.competitor-url-row input {
    flex: 1;
}

.progress-container {
    margin: 20px 0;
}

.progress-bar {
    width: 100%;
    height: 20px;
    background: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #165DFF 0%, #00F5D4 100%);
    width: 0%;
    transition: width 0.3s ease;
}

.progress-text {
    text-align: center;
    color: #666;
    font-size: 14px;
}

.analysis-tabs {
    display: flex;
    border-bottom: 1px solid #e1e5e9;
    margin-bottom: 20px;
}

.tab-button {
    padding: 10px 20px;
    border: none;
    background: none;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
}

.tab-button.active {
    color: #165DFF;
    border-bottom-color: #165DFF;
}

.tab-content {
    display: none;
    padding: 20px;
}

.tab-content.active {
    display: block;
}

.overview-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.stat-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    border-left: 4px solid #165DFF;
}

.stat-value {
    font-size: 36px;
    font-weight: bold;
    color: #165DFF;
    margin-bottom: 10px;
}

.stat-label {
    color: #666;
    font-size: 14px;
}

.log-viewer {
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

.log-controls {
    margin-bottom: 15px;
    display: flex;
    gap: 10px;
    align-items: center;
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

.log-level-info { color: #00F5D4; }
.log-level-warning { color: #FFB800; }
.log-level-error { color: #FF4757; }
.log-level-success { color: #2ED573; }
</style>

<script>
jQuery(document).ready(function($) {
    // 自定义模型配置显示/隐藏
    $('#seo_ai_model').on('change', function() {
        const isCustom = $(this).val() === 'custom';
        $('#custom-model-config').toggle(isCustom);
    });
    
    // 自定义请求格式配置
    $('#custom_request_format').on('change', function() {
        const isCustom = $(this).val() === 'custom';
        $('#custom-format-config').toggle(isCustom);
    });
    
    // 竞争对手分析显示/隐藏
    $('#analysis_competitors').on('change', function() {
        $('#competitors-config').toggle($(this).is(':checked'));
    });
    
    // 添加竞争对手
    $('#add-competitor').on('click', function() {
        const newRow = `
            <div class="competitor-url-row">
                <input type="url" name="competitor_urls[]" value="" placeholder="https://example.com" class="regular-text">
                <button type="button" class="button remove-competitor">删除</button>
            </div>
        `;
        $('#competitor-urls').append(newRow);
    });
    
    // 删除竞争对手
    $(document).on('click', '.remove-competitor', function() {
        $(this).closest('.competitor-url-row').remove();
    });
    
    // 保存分析设置
    $('#save-analysis-settings').on('click', function() {
        const formData = $('#seo-analysis-form').serialize();
        
        $.post(ajaxurl, {
            action: 'ai_optimizer_save_seo_settings',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>',
            form_data: formData
        })
        .done(function(response) {
            if (response.success) {
                alert('设置已保存！');
            } else {
                alert('保存失败：' + response.data);
            }
        })
        .fail(function() {
            alert('保存设置时发生网络错误');
        });
    });
    
    // 标签切换
    $('.tab-button').on('click', function() {
        const tabId = $(this).data('tab');
        
        $('.tab-button').removeClass('active');
        $('.tab-content').removeClass('active');
        
        $(this).addClass('active');
        $('#' + tabId + '-tab').addClass('active');
    });
    
    // 开始SEO分析
    $('#start-seo-analysis').on('click', function() {
        const button = $(this);
        const originalText = button.html();
        
        // 显示分析结果区域
        $('#analysis-results').show();
        $('#analysis-logs').show();
        
        // 重置进度
        $('.progress-fill').css('width', '0%');
        $('.progress-text').text('准备开始分析...');
        
        // 禁用按钮
        button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> 分析中...');
        
        // 获取表单数据
        const formData = $('#seo-analysis-form').serialize();
        
        // 开始分析
        startSEOAnalysis(formData, function(success) {
            button.prop('disabled', false).html(originalText);
            
            if (success) {
                $('#analysis-content').show();
                $('#analysis-progress').hide();
                
                // 如果启用了自动执行，显示优化面板
                if ($('#auto_execution_enabled').is(':checked')) {
                    $('#auto-optimization-panel').show();
                }
            }
        });
    });
    
    // SEO分析函数
    function startSEOAnalysis(formData, callback) {
        let progress = 0;
        const steps = [
            '获取网站基本信息',
            '分析页面结构',
            '检查技术SEO',
            '分析内容质量',
            '竞争对手分析',
            '获取最新SEO知识',
            'AI深度分析',
            '生成优化建议'
        ];
        
        function updateProgress(step, message) {
            progress = Math.min(progress + 12.5, 100);
            $('.progress-fill').css('width', progress + '%');
            $('.progress-text').text(step + ': ' + message);
            addLog('info', step, message);
        }
        
        // 发送AJAX请求
        $.post(ajaxurl, {
            action: 'ai_optimizer_run_seo_analysis',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>',
            form_data: formData
        })
        .done(function(response) {
            if (response.success) {
                updateProgress('分析完成', '所有分析步骤已完成');
                
                // 显示结果
                displayAnalysisResults(response.data);
                
                callback(true);
            } else {
                addLog('error', '分析失败', response.data || '未知错误');
                callback(false);
            }
        })
        .fail(function(xhr, status, error) {
            addLog('error', '网络错误', '请求失败：' + error);
            callback(false);
        });
        
        // 模拟进度更新
        let stepIndex = 0;
        const progressInterval = setInterval(function() {
            if (stepIndex < steps.length) {
                updateProgress(steps[stepIndex], '正在处理...');
                stepIndex++;
            } else {
                clearInterval(progressInterval);
            }
        }, 2000);
    }
    
    // 显示分析结果
    function displayAnalysisResults(data) {
        // 更新概览统计
        $('#seo-score').text(data.seo_score || '--');
        $('#issues-found').text(data.issues_count || '--');
        $('#optimization-potential').text(data.optimization_potential || '--');
        
        // 更新各个标签页内容
        $('#technical-details').html(data.technical_analysis || '暂无数据');
        $('#content-details').html(data.content_analysis || '暂无数据');
        $('#performance-details').html(data.performance_analysis || '暂无数据');
        $('#competitors-details').html(data.competitors_analysis || '暂无数据');
        $('#ai-suggestions-details').html(data.ai_suggestions || '暂无数据');
        
        // 如果有优化建议，显示优化面板
        if (data.optimization_tasks && data.optimization_tasks.length > 0) {
            displayOptimizationTasks(data.optimization_tasks);
        }
    }
    
    // 显示优化任务
    function displayOptimizationTasks(tasks) {
        let html = '<div class="optimization-tasks">';
        
        tasks.forEach(function(task, index) {
            html += `
                <div class="optimization-task">
                    <label>
                        <input type="checkbox" name="optimization_task" value="${task.id}" ${task.auto_executable ? 'checked' : ''}>
                        <strong>${task.title}</strong>
                    </label>
                    <p>${task.description}</p>
                    <div class="task-meta">
                        <span class="priority ${task.priority}">${task.priority}</span>
                        <span class="impact">${task.impact}</span>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        $('#optimization-tasks').html(html);
    }
    
    // 添加日志条目
    function addLog(level, title, message) {
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = `
            <div class="log-entry">
                <span class="log-timestamp">[${timestamp}]</span>
                <span class="log-level-${level}">[${level.toUpperCase()}]</span>
                <strong>${title}:</strong> ${message}
            </div>
        `;
        
        $('#log-container').append(logEntry);
        
        // 自动滚动到底部
        if ($('#auto-scroll').is(':checked')) {
            $('#log-container').scrollTop($('#log-container')[0].scrollHeight);
        }
    }
    
    // 清空日志
    $('#clear-logs').on('click', function() {
        $('#log-container').empty();
    });
    
    // 导出日志
    $('#export-logs').on('click', function() {
        const logs = $('#log-container').text();
        const blob = new Blob([logs], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'seo-analysis-logs-' + new Date().toISOString().substr(0, 10) + '.txt';
        a.click();
        URL.revokeObjectURL(url);
    });
    
    // 执行优化
    $('#execute-optimizations').on('click', function() {
        const selectedTasks = $('input[name="optimization_task"]:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selectedTasks.length === 0) {
            alert('请至少选择一个优化任务');
            return;
        }
        
        const button = $(this);
        const originalText = button.text();
        
        button.prop('disabled', true).text('执行中...');
        
        $.post(ajaxurl, {
            action: 'ai_optimizer_execute_optimizations',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>',
            tasks: selectedTasks
        })
        .done(function(response) {
            if (response.success) {
                alert('优化任务执行完成！');
                addLog('success', '优化执行', '已完成 ' + selectedTasks.length + ' 个优化任务');
            } else {
                alert('优化执行失败：' + response.data);
                addLog('error', '优化执行', response.data);
            }
        })
        .fail(function() {
            alert('优化执行时发生网络错误');
            addLog('error', '优化执行', '网络错误');
        })
        .always(function() {
            button.prop('disabled', false).text(originalText);
        });
    });
    
    // 初始化加载保存的设置
    loadSavedSettings();
    
    function loadSavedSettings() {
        // 从localStorage恢复复选框状态
        const savedScope = localStorage.getItem('ai_seo_analysis_scope');
        if (savedScope) {
            const scope = JSON.parse(savedScope);
            Object.keys(scope).forEach(function(key) {
                $('#analysis_' + key).prop('checked', scope[key]);
            });
        }
        
        // 触发竞争对手分析显示
        $('#analysis_competitors').trigger('change');
    }
    
    // 保存复选框状态到localStorage
    $('input[name="analysis_scope[]"]').on('change', function() {
        const scope = {};
        $('input[name="analysis_scope[]"]').each(function() {
            scope[$(this).val()] = $(this).is(':checked');
        });
        localStorage.setItem('ai_seo_analysis_scope', JSON.stringify(scope));
    });
});
</script>
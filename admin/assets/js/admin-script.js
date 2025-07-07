/**
 * AI Website Optimizer Admin JavaScript
 */

(function($) {
    'use strict';

    // Global variables
    let charts = {};
    let refreshInterval;
    let isRefreshing = false;

    // Initialize when document is ready
    $(document).ready(function() {
        initializePlugin();
        initializeCharts();
        bindEvents();
        startAutoRefresh();
    });

    /**
     * Initialize plugin components
     */
    function initializePlugin() {
        // Initialize tabs
        initializeTabs();
        
        // Initialize tooltips
        initializeTooltips();
        
        // Initialize progress bars
        animateProgressBars();
        
        // Load initial data
        loadDashboardData();
    }

    /**
     * Initialize tab navigation
     */
    function initializeTabs() {
        $('.ai-optimizer-tab-button').on('click', function() {
            const target = $(this).data('target');
            
            // Update active tab button
            $('.ai-optimizer-tab-button').removeClass('active');
            $(this).addClass('active');
            
            // Update active tab content
            $('.ai-optimizer-tab-content').removeClass('active');
            $('#' + target).addClass('active');
        });
    }

    /**
     * Initialize tooltips
     */
    function initializeTooltips() {
        $('[data-tooltip]').each(function() {
            const tooltip = $('<div class="ai-optimizer-tooltip">' + $(this).data('tooltip') + '</div>');
            $('body').append(tooltip);
            
            $(this).hover(
                function() {
                    const offset = $(this).offset();
                    tooltip.css({
                        top: offset.top - tooltip.outerHeight() - 10,
                        left: offset.left + ($(this).outerWidth() / 2) - (tooltip.outerWidth() / 2),
                        opacity: 1,
                        visibility: 'visible'
                    });
                },
                function() {
                    tooltip.css({
                        opacity: 0,
                        visibility: 'hidden'
                    });
                }
            );
        });
    }

    /**
     * Animate progress bars
     */
    function animateProgressBars() {
        $('.ai-optimizer-progress-bar').each(function() {
            const width = $(this).data('width') || 0;
            $(this).animate({ width: width + '%' }, 1000);
        });
    }

    /**
     * Initialize charts
     */
    function initializeCharts() {
        // Performance chart
        if ($('#performance-chart').length) {
            initializePerformanceChart();
        }
        
        // SEO score chart
        if ($('#seo-score-chart').length) {
            initializeSEOChart();
        }
        
        // Error trends chart
        if ($('#error-trends-chart').length) {
            initializeErrorChart();
        }
    }

    /**
     * Initialize performance chart
     */
    function initializePerformanceChart() {
        const ctx = document.getElementById('performance-chart');
        if (!ctx) return;

        charts.performance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Load Time (ms)',
                    data: [],
                    borderColor: '#00F5D4',
                    backgroundColor: 'rgba(0, 245, 212, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Memory Usage (MB)',
                    data: [],
                    borderColor: '#165DFF',
                    backgroundColor: 'rgba(22, 93, 255, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#FFFFFF'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#B0B0B0'
                        },
                        grid: {
                            color: '#333333'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: {
                            color: '#B0B0B0'
                        },
                        grid: {
                            color: '#333333'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        ticks: {
                            color: '#B0B0B0'
                        },
                        grid: {
                            drawOnChartArea: false,
                            color: '#333333'
                        }
                    }
                }
            }
        });
    }

    /**
     * Initialize SEO score chart
     */
    function initializeSEOChart() {
        const ctx = document.getElementById('seo-score-chart');
        if (!ctx) return;

        charts.seo = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Optimized', 'Needs Improvement'],
                datasets: [{
                    data: [0, 100],
                    backgroundColor: ['#00F5D4', '#333333'],
                    borderColor: ['#00F5D4', '#333333'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#FFFFFF',
                            padding: 20
                        }
                    }
                }
            }
        });
    }

    /**
     * Initialize error trends chart
     */
    function initializeErrorChart() {
        const ctx = document.getElementById('error-trends-chart');
        if (!ctx) return;

        charts.errors = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Errors',
                    data: [],
                    backgroundColor: '#FF4757',
                    borderColor: '#FF4757',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#FFFFFF'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#B0B0B0'
                        },
                        grid: {
                            color: '#333333'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#B0B0B0'
                        },
                        grid: {
                            color: '#333333'
                        }
                    }
                }
            }
        });
    }

    /**
     * Bind event handlers
     */
    function bindEvents() {
        // Run analysis button
        $('.run-analysis').on('click', handleRunAnalysis);
        
        // Apply SEO suggestion
        $('.apply-seo-suggestion').on('click', handleApplySEOSuggestion);
        
        // Generate content
        $('.generate-content').on('click', handleGenerateContent);
        
        // Test API connection
        $('.test-api-connection').on('click', handleTestAPIConnection);
        
        // Refresh data
        $('.refresh-data').on('click', handleRefreshData);
        
        // Export settings
        $('.export-settings').on('click', handleExportSettings);
        
        // Import settings
        $('.import-settings').on('click', handleImportSettings);
        
        // Video status check
        $('.check-video-status').on('click', handleCheckVideoStatus);
        
        // Bulk actions
        $('.bulk-action-apply').on('click', handleBulkAction);
    }

    /**
     * Handle run analysis
     */
    function handleRunAnalysis() {
        const button = $(this);
        const originalText = button.text();
        
        button.prop('disabled', true).html('<span class="ai-optimizer-loading"></span> Analyzing...');
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'run_analysis',
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                showNotification('Analysis completed successfully!', 'success');
                loadDashboardData();
            } else {
                showNotification('Analysis failed: ' + response.data, 'error');
            }
        })
        .fail(function() {
            showNotification('Network error occurred', 'error');
        })
        .always(function() {
            button.prop('disabled', false).text(originalText);
        });
    }

    /**
     * Handle apply SEO suggestion
     */
    function handleApplySEOSuggestion() {
        const button = $(this);
        const suggestionId = button.data('suggestion-id');
        const originalText = button.text();
        
        if (!confirm('Are you sure you want to apply this SEO suggestion?')) {
            return;
        }
        
        button.prop('disabled', true).html('<span class="ai-optimizer-loading"></span> Applying...');
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'apply_seo_suggestion',
            suggestion_id: suggestionId,
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                showNotification('SEO suggestion applied successfully!', 'success');
                button.closest('.suggestion-item').fadeOut();
            } else {
                showNotification('Failed to apply suggestion: ' + response.data, 'error');
            }
        })
        .fail(function() {
            showNotification('Network error occurred', 'error');
        })
        .always(function() {
            button.prop('disabled', false).text(originalText);
        });
    }

    /**
     * Handle generate content
     */
    function handleGenerateContent() {
        const form = $(this).closest('form');
        const contentType = form.find('[name="content_type"]').val();
        const prompt = form.find('[name="prompt"]').val();
        const button = $(this);
        const originalText = button.text();
        
        if (!prompt.trim()) {
            showNotification('Please enter a prompt', 'warning');
            return;
        }
        
        button.prop('disabled', true).html('<span class="ai-optimizer-loading"></span> Generating...');
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'generate_content',
            content_type: contentType,
            prompt: prompt,
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                showNotification('Content generated successfully!', 'success');
                displayGeneratedContent(response.data, contentType);
            } else {
                showNotification('Content generation failed: ' + response.data, 'error');
            }
        })
        .fail(function() {
            showNotification('Network error occurred', 'error');
        })
        .always(function() {
            button.prop('disabled', false).text(originalText);
        });
    }

    /**
     * Handle test API connection
     */
    function handleTestAPIConnection() {
        const button = $(this);
        const originalText = button.text();
        
        button.prop('disabled', true).html('<span class="ai-optimizer-loading"></span> Testing...');
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'test_api_connection',
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                showNotification('API connection successful!', 'success');
                $('.api-status').removeClass('error').addClass('success').text('Connected');
            } else {
                showNotification('API connection failed: ' + response.data, 'error');
                $('.api-status').removeClass('success').addClass('error').text('Failed');
            }
        })
        .fail(function() {
            showNotification('Network error occurred', 'error');
        })
        .always(function() {
            button.prop('disabled', false).text(originalText);
        });
    }

    /**
     * Handle refresh data
     */
    function handleRefreshData() {
        if (isRefreshing) return;
        
        isRefreshing = true;
        const button = $(this);
        const originalText = button.html();
        
        button.html('<span class="ai-optimizer-loading"></span>');
        
        loadDashboardData().always(function() {
            isRefreshing = false;
            button.html(originalText);
        });
    }

    /**
     * Handle export settings
     */
    function handleExportSettings() {
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'export_settings',
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                const blob = new Blob([JSON.stringify(response.data, null, 2)], {
                    type: 'application/json'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'ai-optimizer-settings.json';
                a.click();
                URL.revokeObjectURL(url);
                showNotification('Settings exported successfully!', 'success');
            } else {
                showNotification('Export failed: ' + response.data, 'error');
            }
        })
        .fail(function() {
            showNotification('Network error occurred', 'error');
        });
    }

    /**
     * Handle import settings
     */
    function handleImportSettings() {
        const input = $('<input type="file" accept=".json">');
        
        input.on('change', function() {
            const file = this.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const settings = JSON.parse(e.target.result);
                    
                    $.post(aiOptimizer.ajaxUrl, {
                        action: 'ai_optimizer_action',
                        action_type: 'import_settings',
                        settings: JSON.stringify(settings),
                        nonce: aiOptimizer.nonce
                    })
                    .done(function(response) {
                        if (response.success) {
                            showNotification('Settings imported successfully!', 'success');
                            location.reload();
                        } else {
                            showNotification('Import failed: ' + response.data, 'error');
                        }
                    })
                    .fail(function() {
                        showNotification('Network error occurred', 'error');
                    });
                } catch (error) {
                    showNotification('Invalid JSON file', 'error');
                }
            };
            reader.readAsText(file);
        });
        
        input.click();
    }

    /**
     * Handle check video status
     */
    function handleCheckVideoStatus() {
        const button = $(this);
        const requestId = button.data('request-id');
        const statusElement = button.closest('.video-request').find('.status');
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'check_video_status',
            request_id: requestId,
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                const data = response.data;
                statusElement.text(data.status);
                
                if (data.status === 'completed' && data.url) {
                    button.replaceWith('<a href="' + data.url + '" class="ai-optimizer-btn ai-optimizer-btn-success" target="_blank">Download</a>');
                } else if (data.progress) {
                    statusElement.text(data.status + ' (' + data.progress + '%)');
                }
            } else {
                showNotification('Failed to check video status: ' + response.data, 'error');
            }
        })
        .fail(function() {
            showNotification('Network error occurred', 'error');
        });
    }

    /**
     * Handle bulk actions
     */
    function handleBulkAction() {
        const form = $(this).closest('form');
        const action = form.find('[name="bulk_action"]').val();
        const selected = form.find('input[name="bulk_items[]"]:checked');
        
        if (!action) {
            showNotification('Please select an action', 'warning');
            return;
        }
        
        if (selected.length === 0) {
            showNotification('Please select items to process', 'warning');
            return;
        }
        
        if (!confirm('Are you sure you want to perform this bulk action?')) {
            return;
        }
        
        form.submit();
    }

    /**
     * Load dashboard data
     */
    function loadDashboardData() {
        return $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'get_dashboard_data',
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                updateDashboardStats(response.data);
                updateCharts(response.data);
            }
        })
        .fail(function() {
            console.error('Failed to load dashboard data');
        });
    }

    /**
     * Update dashboard stats
     */
    function updateDashboardStats(data) {
        if (data.performance) {
            $('.stat-load-time .ai-optimizer-stat-value').text(
                Math.round(data.performance.avg_load_time * 1000) + 'ms'
            );
            $('.stat-memory-usage .ai-optimizer-stat-value').text(
                Math.round(data.performance.avg_memory_usage / 1024 / 1024) + 'MB'
            );
            $('.stat-uptime .ai-optimizer-stat-value').text(
                Math.round(data.performance.uptime_percentage) + '%'
            );
        }
        
        if (data.seo_score !== undefined) {
            $('.stat-seo-score .ai-optimizer-stat-value').text(data.seo_score);
            updateSEOChart(data.seo_score);
        }
        
        if (data.error_count !== undefined) {
            $('.stat-errors .ai-optimizer-stat-value').text(data.error_count);
        }
    }

    /**
     * Update charts with new data
     */
    function updateCharts(data) {
        if (data.performance && charts.performance) {
            updatePerformanceChart(data.performance);
        }
        
        if (data.error_trends && charts.errors) {
            updateErrorChart(data.error_trends);
        }
    }

    /**
     * Update performance chart
     */
    function updatePerformanceChart(data) {
        if (!charts.performance || !data.chart_data) return;
        
        charts.performance.data.labels = data.chart_data.labels;
        charts.performance.data.datasets[0].data = data.chart_data.load_times;
        charts.performance.data.datasets[1].data = data.chart_data.memory_usage;
        charts.performance.update();
    }

    /**
     * Update SEO chart
     */
    function updateSEOChart(score) {
        if (!charts.seo) return;
        
        charts.seo.data.datasets[0].data = [score, 100 - score];
        charts.seo.update();
    }

    /**
     * Update error chart
     */
    function updateErrorChart(data) {
        if (!charts.errors || !data.labels) return;
        
        charts.errors.data.labels = data.labels;
        charts.errors.data.datasets[0].data = data.values;
        charts.errors.update();
    }

    /**
     * Display generated content
     */
    function displayGeneratedContent(data, type) {
        const container = $('#generated-content-container');
        let html = '';
        
        switch (type) {
            case 'text':
                html = '<div class="generated-content"><h4>Generated Text:</h4><div class="content-text">' + 
                       data.content.replace(/\n/g, '<br>') + '</div></div>';
                break;
            case 'image':
                html = '<div class="generated-content"><h4>Generated Image:</h4><img src="' + 
                       data.url + '" alt="Generated Image" style="max-width: 100%; height: auto;"></div>';
                break;
            case 'video':
                html = '<div class="generated-content"><h4>Video Generation Started</h4><p>' + 
                       data.message + '</p><p>Request ID: ' + data.request_id + '</p></div>';
                break;
            case 'audio':
                html = '<div class="generated-content"><h4>Generated Audio:</h4><audio controls><source src="' + 
                       data.url + '" type="audio/mpeg"></audio></div>';
                break;
            case 'code':
                html = '<div class="generated-content"><h4>Generated Code:</h4><pre><code>' + 
                       escapeHtml(data.code) + '</code></pre></div>';
                break;
        }
        
        container.html(html).fadeIn();
    }

    /**
     * Show notification
     */
    function showNotification(message, type) {
        const notification = $('<div class="ai-optimizer-notification ai-optimizer-notification-' + type + '">' + 
                              message + '</div>');
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.addClass('show');
        }, 100);
        
        setTimeout(function() {
            notification.removeClass('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 5000);
    }

    /**
     * Start auto refresh
     */
    function startAutoRefresh() {
        refreshInterval = setInterval(function() {
            if (!isRefreshing && !document.hidden) {
                loadDashboardData();
            }
        }, 30000); // Refresh every 30 seconds
    }

    /**
     * Utility function to escape HTML
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Add CSS for notifications
    const notificationCSS = `
        <style>
        .ai-optimizer-notification {
            position: fixed;
            top: 50px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 999999;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .ai-optimizer-notification.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .ai-optimizer-notification-success {
            background: linear-gradient(45deg, #00D4AA, #27ae60);
        }
        
        .ai-optimizer-notification-error {
            background: linear-gradient(45deg, #FF4757, #e74c3c);
        }
        
        .ai-optimizer-notification-warning {
            background: linear-gradient(45deg, #FFB800, #f39c12);
        }
        
        .ai-optimizer-notification-info {
            background: linear-gradient(45deg, #165DFF, #3498db);
        }
        
        .ai-optimizer-tooltip {
            position: absolute;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 999999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            pointer-events: none;
        }
        
        .ai-optimizer-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border: 5px solid transparent;
            border-top-color: rgba(0, 0, 0, 0.9);
        }
        </style>
    `;
    
    $('head').append(notificationCSS);

})(jQuery);

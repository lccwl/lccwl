<?php
/**
 * SEO Optimization view
 */

if (!defined('ABSPATH')) {
    exit;
}

AI_Optimizer_Admin::render_header(
    __('SEO Optimization', 'ai-website-optimizer'),
    __('AI-powered SEO analysis and optimization for better search engine rankings', 'ai-website-optimizer')
);

$seo = new AI_Optimizer_SEO();
$current_score = $seo->get_current_score();
$suggestions = $seo->get_suggestions();
$analysis = $seo->get_seo_analysis();
?>

<div class="ai-optimizer-wrap">
    
    <!-- SEO Score Overview -->
    <div class="ai-optimizer-grid ai-optimizer-grid-3">
        <div class="ai-optimizer-stat-card">
            <div class="ai-optimizer-stat-value">
                <?php echo $current_score; ?>
            </div>
            <div class="ai-optimizer-stat-label"><?php _e('Overall SEO Score', 'ai-website-optimizer'); ?></div>
            <div class="ai-optimizer-progress">
                <div class="ai-optimizer-progress-bar" data-width="<?php echo $current_score; ?>"></div>
            </div>
        </div>

        <div class="ai-optimizer-stat-card">
            <div class="ai-optimizer-stat-value">
                <?php echo count($suggestions); ?>
            </div>
            <div class="ai-optimizer-stat-label"><?php _e('Pending Suggestions', 'ai-website-optimizer'); ?></div>
            <div class="ai-optimizer-stat-trend up">
                <?php _e('Ready to Apply', 'ai-website-optimizer'); ?>
            </div>
        </div>

        <div class="ai-optimizer-stat-card">
            <div class="ai-optimizer-stat-value">
                <?php echo count($analysis); ?>
            </div>
            <div class="ai-optimizer-stat-label"><?php _e('Pages Analyzed', 'ai-website-optimizer'); ?></div>
            <div class="ai-optimizer-stat-trend up">
                <?php _e('Recent Analysis', 'ai-website-optimizer'); ?>
            </div>
        </div>
    </div>

    <!-- SEO Actions -->
    <div class="ai-optimizer-card">
        <div class="ai-optimizer-card-title"><?php _e('SEO Actions', 'ai-website-optimizer'); ?></div>
        <div class="ai-optimizer-grid ai-optimizer-grid-3">
            <button class="ai-optimizer-btn ai-optimizer-btn-pulse" id="run-seo-analysis">
                <i class="fas fa-search"></i> <?php _e('Run SEO Analysis', 'ai-website-optimizer'); ?>
            </button>
            <button class="ai-optimizer-btn" id="optimize-all-pages">
                <i class="fas fa-magic"></i> <?php _e('Optimize All Pages', 'ai-website-optimizer'); ?>
            </button>
            <button class="ai-optimizer-btn ai-optimizer-btn-secondary" id="generate-sitemap">
                <i class="fas fa-sitemap"></i> <?php _e('Generate Sitemap', 'ai-website-optimizer'); ?>
            </button>
        </div>
    </div>

    <!-- AI Suggestions -->
    <div class="ai-optimizer-card">
        <div class="ai-optimizer-card-title">
            <?php _e('AI Optimization Suggestions', 'ai-website-optimizer'); ?>
            <div class="suggestions-controls" style="float: right;">
                <select class="ai-optimizer-select" id="suggestions-filter">
                    <option value="all"><?php _e('All Priorities', 'ai-website-optimizer'); ?></option>
                    <option value="high"><?php _e('High Priority', 'ai-website-optimizer'); ?></option>
                    <option value="medium"><?php _e('Medium Priority', 'ai-website-optimizer'); ?></option>
                    <option value="low"><?php _e('Low Priority', 'ai-website-optimizer'); ?></option>
                </select>
                <button class="ai-optimizer-btn ai-optimizer-btn-secondary" id="bulk-apply-suggestions">
                    <?php _e('Apply All', 'ai-website-optimizer'); ?>
                </button>
            </div>
        </div>
        
        <div class="suggestions-container">
            <?php if (!empty($suggestions)): ?>
                <form method="post" action="">
                    <?php wp_nonce_field('ai_optimizer_bulk_nonce'); ?>
                    <input type="hidden" name="ai_optimizer_bulk_action" value="apply_seo_suggestions">
                    
                    <div class="suggestions-list">
                        <?php foreach ($suggestions as $suggestion): ?>
                        <div class="suggestion-item" data-priority="<?php echo esc_attr($suggestion['priority']); ?>">
                            <div class="suggestion-checkbox">
                                <input type="checkbox" name="bulk_items[]" value="<?php echo $suggestion['id']; ?>" id="suggestion-<?php echo $suggestion['id']; ?>">
                            </div>
                            <div class="suggestion-content">
                                <div class="suggestion-title">
                                    <label for="suggestion-<?php echo $suggestion['id']; ?>">
                                        <?php echo esc_html($suggestion['title'] ?? $suggestion['description']); ?>
                                    </label>
                                    <span class="ai-optimizer-badge ai-optimizer-badge-<?php echo $suggestion['priority'] === 'high' ? 'error' : ($suggestion['priority'] === 'medium' ? 'warning' : 'info'); ?>">
                                        <?php echo esc_html($suggestion['priority']); ?>
                                    </span>
                                </div>
                                <div class="suggestion-description">
                                    <?php echo esc_html($suggestion['description']); ?>
                                </div>
                                <div class="suggestion-meta">
                                    <span class="suggestion-type"><?php _e('Type:', 'ai-website-optimizer'); ?> <?php echo esc_html($suggestion['type']); ?></span>
                                    <span class="suggestion-page"><?php _e('Page:', 'ai-website-optimizer'); ?> <?php echo esc_html($suggestion['page_title'] ?? 'All Pages'); ?></span>
                                </div>
                            </div>
                            <div class="suggestion-actions">
                                <button type="button" class="ai-optimizer-btn ai-optimizer-btn-secondary apply-seo-suggestion" data-suggestion-id="<?php echo $suggestion['id']; ?>">
                                    <?php _e('Apply', 'ai-website-optimizer'); ?>
                                </button>
                                <button type="button" class="ai-optimizer-btn ai-optimizer-btn-secondary preview-suggestion" data-suggestion-id="<?php echo $suggestion['id']; ?>">
                                    <?php _e('Preview', 'ai-website-optimizer'); ?>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="bulk-actions-footer">
                        <label>
                            <input type="checkbox" id="select-all-suggestions"> <?php _e('Select All', 'ai-website-optimizer'); ?>
                        </label>
                        <button type="submit" class="ai-optimizer-btn bulk-action-apply">
                            <?php _e('Apply Selected', 'ai-website-optimizer'); ?>
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="ai-optimizer-text-center ai-optimizer-text-muted">
                    <i class="fas fa-lightbulb" style="font-size: 48px; opacity: 0.3; margin-bottom: 20px;"></i>
                    <p><?php _e('No SEO suggestions available. Run an analysis to get AI-powered recommendations.', 'ai-website-optimizer'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SEO Analysis Results -->
    <div class="ai-optimizer-grid ai-optimizer-grid-2">
        
        <!-- Recent Analysis -->
        <div class="ai-optimizer-card">
            <div class="ai-optimizer-card-title"><?php _e('Recent Analysis Results', 'ai-website-optimizer'); ?></div>
            <div class="analysis-results">
                <?php if (!empty($analysis)): ?>
                    <div class="analysis-table-container">
                        <table class="ai-optimizer-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Page', 'ai-website-optimizer'); ?></th>
                                    <th><?php _e('Score', 'ai-website-optimizer'); ?></th>
                                    <th><?php _e('Issues', 'ai-website-optimizer'); ?></th>
                                    <th><?php _e('Date', 'ai-website-optimizer'); ?></th>
                                    <th><?php _e('Actions', 'ai-website-optimizer'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($analysis, 0, 10) as $result): 
                                    $data = json_decode($result['analysis_data'], true);
                                    $page_score = $this->calculate_page_score($data);
                                    $issues_count = $this->count_page_issues($data);
                                ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url($data['url']); ?>" target="_blank">
                                            <?php echo esc_html(get_the_title($result['page_id'])); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="page-score <?php echo $page_score >= 80 ? 'good' : ($page_score >= 60 ? 'medium' : 'poor'); ?>">
                                            <?php echo $page_score; ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="ai-optimizer-badge ai-optimizer-badge-<?php echo $issues_count > 5 ? 'error' : ($issues_count > 2 ? 'warning' : 'success'); ?>">
                                            <?php echo $issues_count; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($result['created_at'])); ?></td>
                                    <td>
                                        <button class="ai-optimizer-btn ai-optimizer-btn-secondary view-analysis" data-page-id="<?php echo $result['page_id']; ?>">
                                            <?php _e('View', 'ai-website-optimizer'); ?>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="ai-optimizer-text-center ai-optimizer-text-muted">
                        <i class="fas fa-chart-bar" style="font-size: 48px; opacity: 0.3; margin-bottom: 20px;"></i>
                        <p><?php _e('No analysis results yet. Run your first SEO analysis to see detailed insights.', 'ai-website-optimizer'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SEO Metrics -->
        <div class="ai-optimizer-card">
            <div class="ai-optimizer-card-title"><?php _e('SEO Metrics Overview', 'ai-website-optimizer'); ?></div>
            <div class="seo-metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-heading"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label"><?php _e('Title Tags', 'ai-website-optimizer'); ?></div>
                        <div class="metric-value">
                            <span class="good">85%</span> <?php _e('Optimized', 'ai-website-optimizer'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label"><?php _e('Meta Descriptions', 'ai-website-optimizer'); ?></div>
                        <div class="metric-value">
                            <span class="medium">72%</span> <?php _e('Present', 'ai-website-optimizer'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-image"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label"><?php _e('Image Alt Tags', 'ai-website-optimizer'); ?></div>
                        <div class="metric-value">
                            <span class="poor">58%</span> <?php _e('Complete', 'ai-website-optimizer'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label"><?php _e('Internal Links', 'ai-website-optimizer'); ?></div>
                        <div class="metric-value">
                            <span class="good">91%</span> <?php _e('Optimized', 'ai-website-optimizer'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label"><?php _e('Sitemap', 'ai-website-optimizer'); ?></div>
                        <div class="metric-value">
                            <?php
                            $sitemap_exists = wp_remote_retrieve_response_code(wp_remote_head(home_url('/sitemap.xml'))) === 200;
                            echo $sitemap_exists ? '<span class="good">' . __('Active', 'ai-website-optimizer') . '</span>' : '<span class="poor">' . __('Missing', 'ai-website-optimizer') . '</span>';
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label"><?php _e('Robots.txt', 'ai-website-optimizer'); ?></div>
                        <div class="metric-value">
                            <?php
                            $robots_exists = wp_remote_retrieve_response_code(wp_remote_head(home_url('/robots.txt'))) === 200;
                            echo $robots_exists ? '<span class="good">' . __('Present', 'ai-website-optimizer') . '</span>' : '<span class="medium">' . __('Missing', 'ai-website-optimizer') . '</span>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- SEO Tools -->
    <div class="ai-optimizer-card">
        <div class="ai-optimizer-card-title"><?php _e('SEO Tools', 'ai-website-optimizer'); ?></div>
        
        <div class="ai-optimizer-tabs">
            <div class="ai-optimizer-tab-nav">
                <button class="ai-optimizer-tab-button active" data-target="keyword-analyzer">
                    <?php _e('Keyword Analyzer', 'ai-website-optimizer'); ?>
                </button>
                <button class="ai-optimizer-tab-button" data-target="content-optimizer">
                    <?php _e('Content Optimizer', 'ai-website-optimizer'); ?>
                </button>
                <button class="ai-optimizer-tab-button" data-target="competitor-analysis">
                    <?php _e('Competitor Analysis', 'ai-website-optimizer'); ?>
                </button>
            </div>
            
            <div id="keyword-analyzer" class="ai-optimizer-tab-content active">
                <div class="tool-content">
                    <form class="keyword-analysis-form">
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Target Keywords (one per line)', 'ai-website-optimizer'); ?></label>
                            <textarea class="ai-optimizer-textarea" name="keywords" rows="5" placeholder="<?php _e('Enter keywords to analyze...', 'ai-website-optimizer'); ?>"></textarea>
                        </div>
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Target URL (optional)', 'ai-website-optimizer'); ?></label>
                            <input type="url" class="ai-optimizer-input" name="target_url" placeholder="<?php echo home_url(); ?>">
                        </div>
                        <button type="button" class="ai-optimizer-btn analyze-keywords">
                            <i class="fas fa-search"></i> <?php _e('Analyze Keywords', 'ai-website-optimizer'); ?>
                        </button>
                    </form>
                    <div id="keyword-results" class="tool-results"></div>
                </div>
            </div>
            
            <div id="content-optimizer" class="ai-optimizer-tab-content">
                <div class="tool-content">
                    <form class="content-optimization-form">
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Content to Optimize', 'ai-website-optimizer'); ?></label>
                            <textarea class="ai-optimizer-textarea" name="content" rows="8" placeholder="<?php _e('Paste your content here for AI optimization...', 'ai-website-optimizer'); ?>"></textarea>
                        </div>
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Target Keywords', 'ai-website-optimizer'); ?></label>
                            <input type="text" class="ai-optimizer-input" name="target_keywords" placeholder="<?php _e('keyword1, keyword2, keyword3', 'ai-website-optimizer'); ?>">
                        </div>
                        <button type="button" class="ai-optimizer-btn optimize-content">
                            <i class="fas fa-magic"></i> <?php _e('Optimize Content', 'ai-website-optimizer'); ?>
                        </button>
                    </form>
                    <div id="content-results" class="tool-results"></div>
                </div>
            </div>
            
            <div id="competitor-analysis" class="ai-optimizer-tab-content">
                <div class="tool-content">
                    <form class="competitor-analysis-form">
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Competitor URLs (one per line)', 'ai-website-optimizer'); ?></label>
                            <textarea class="ai-optimizer-textarea" name="competitor_urls" rows="5" placeholder="<?php _e('https://competitor1.com\nhttps://competitor2.com', 'ai-website-optimizer'); ?>"></textarea>
                        </div>
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Analysis Focus', 'ai-website-optimizer'); ?></label>
                            <select class="ai-optimizer-select" name="analysis_focus">
                                <option value="keywords"><?php _e('Keywords', 'ai-website-optimizer'); ?></option>
                                <option value="content"><?php _e('Content Strategy', 'ai-website-optimizer'); ?></option>
                                <option value="technical"><?php _e('Technical SEO', 'ai-website-optimizer'); ?></option>
                                <option value="all"><?php _e('Comprehensive', 'ai-website-optimizer'); ?></option>
                            </select>
                        </div>
                        <button type="button" class="ai-optimizer-btn analyze-competitors">
                            <i class="fas fa-users"></i> <?php _e('Analyze Competitors', 'ai-website-optimizer'); ?>
                        </button>
                    </form>
                    <div id="competitor-results" class="tool-results"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.suggestions-list {
    max-height: 600px;
    overflow-y: auto;
}

.suggestion-item {
    display: flex;
    align-items: center;
    padding: 20px;
    border: 1px solid var(--ai-border);
    border-radius: 8px;
    margin-bottom: 15px;
    background: rgba(255, 255, 255, 0.02);
    transition: all 0.3s ease;
}

.suggestion-item:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: var(--ai-secondary);
}

.suggestion-checkbox {
    margin-right: 15px;
}

.suggestion-content {
    flex: 1;
}

.suggestion-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.suggestion-title label {
    font-weight: 600;
    cursor: pointer;
    margin: 0;
}

.suggestion-description {
    color: var(--ai-text-muted);
    margin-bottom: 8px;
    line-height: 1.5;
}

.suggestion-meta {
    font-size: 0.85em;
    color: var(--ai-text-muted);
}

.suggestion-meta span {
    margin-right: 15px;
}

.suggestion-actions {
    display: flex;
    gap: 10px;
}

.bulk-actions-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--ai-border);
}

.page-score.good {
    color: var(--ai-success);
}

.page-score.medium {
    color: var(--ai-warning);
}

.page-score.poor {
    color: var(--ai-error);
}

.seo-metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.metric-card {
    display: flex;
    align-items: center;
    padding: 15px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
    border: 1px solid var(--ai-border);
}

.metric-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--ai-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: white;
}

.metric-content {
    flex: 1;
}

.metric-label {
    font-size: 0.85em;
    color: var(--ai-text-muted);
    margin-bottom: 5px;
}

.metric-value {
    font-weight: 600;
}

.metric-value .good {
    color: var(--ai-success);
}

.metric-value .medium {
    color: var(--ai-warning);
}

.metric-value .poor {
    color: var(--ai-error);
}

.tool-content {
    padding: 20px 0;
}

.tool-results {
    margin-top: 20px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
    border: 1px solid var(--ai-border);
    min-height: 200px;
}

.suggestions-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}

@media (max-width: 768px) {
    .suggestion-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .suggestion-actions {
        width: 100%;
        margin-top: 15px;
    }
    
    .suggestions-controls {
        float: none !important;
        margin-top: 15px;
    }
    
    .seo-metrics-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Run SEO analysis
    $('#run-seo-analysis').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        
        button.prop('disabled', true).html('<span class="ai-optimizer-loading"></span> Analyzing...');
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'run_seo_analysis',
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Analysis failed: ' + response.data);
            }
        })
        .fail(function() {
            alert('Network error occurred');
        })
        .always(function() {
            button.prop('disabled', false).text(originalText);
        });
    });
    
    // Select all suggestions
    $('#select-all-suggestions').on('change', function() {
        $('input[name="bulk_items[]"]').prop('checked', $(this).is(':checked'));
    });
    
    // Filter suggestions
    $('#suggestions-filter').on('change', function() {
        const priority = $(this).val();
        $('.suggestion-item').each(function() {
            if (priority === 'all' || $(this).data('priority') === priority) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Tool handlers
    $('.analyze-keywords').on('click', function() {
        const keywords = $('textarea[name="keywords"]').val();
        const targetUrl = $('input[name="target_url"]').val();
        
        if (!keywords.trim()) {
            alert('Please enter keywords to analyze');
            return;
        }
        
        const button = $(this);
        button.prop('disabled', true).html('<span class="ai-optimizer-loading"></span> Analyzing...');
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'analyze_keywords',
            keywords: keywords,
            target_url: targetUrl,
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                $('#keyword-results').html(response.data);
            } else {
                $('#keyword-results').html('<div class="ai-optimizer-alert ai-optimizer-alert-error">' + response.data + '</div>');
            }
        })
        .always(function() {
            button.prop('disabled', false).html('<i class="fas fa-search"></i> Analyze Keywords');
        });
    });
    
    $('.optimize-content').on('click', function() {
        const content = $('textarea[name="content"]').val();
        const keywords = $('input[name="target_keywords"]').val();
        
        if (!content.trim()) {
            alert('Please enter content to optimize');
            return;
        }
        
        const button = $(this);
        button.prop('disabled', true).html('<span class="ai-optimizer-loading"></span> Optimizing...');
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'optimize_content',
            content: content,
            keywords: keywords,
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                $('#content-results').html(response.data);
            } else {
                $('#content-results').html('<div class="ai-optimizer-alert ai-optimizer-alert-error">' + response.data + '</div>');
            }
        })
        .always(function() {
            button.prop('disabled', false).html('<i class="fas fa-magic"></i> Optimize Content');
        });
    });
    
    $('.analyze-competitors').on('click', function() {
        const urls = $('textarea[name="competitor_urls"]').val();
        const focus = $('select[name="analysis_focus"]').val();
        
        if (!urls.trim()) {
            alert('Please enter competitor URLs');
            return;
        }
        
        const button = $(this);
        button.prop('disabled', true).html('<span class="ai-optimizer-loading"></span> Analyzing...');
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'analyze_competitors',
            urls: urls,
            focus: focus,
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                $('#competitor-results').html(response.data);
            } else {
                $('#competitor-results').html('<div class="ai-optimizer-alert ai-optimizer-alert-error">' + response.data + '</div>');
            }
        })
        .always(function() {
            button.prop('disabled', false).html('<i class="fas fa-users"></i> Analyze Competitors');
        });
    });
});
</script>

<?php
AI_Optimizer_Admin::render_footer();

// Helper methods
function calculate_page_score($data) {
    $score = 100;
    
    if (!empty($data['title']['issues'])) {
        $score -= count($data['title']['issues']) * 10;
    }
    
    if (!empty($data['meta_description']['issues'])) {
        $score -= count($data['meta_description']['issues']) * 10;
    }
    
    if (!empty($data['headings']['issues'])) {
        $score -= count($data['headings']['issues']) * 5;
    }
    
    if ($data['images']['without_alt'] > 0) {
        $score -= $data['images']['without_alt'] * 3;
    }
    
    return max(0, $score);
}

function count_page_issues($data) {
    $issues = 0;
    
    $issues += count($data['title']['issues'] ?? array());
    $issues += count($data['meta_description']['issues'] ?? array());
    $issues += count($data['headings']['issues'] ?? array());
    $issues += $data['images']['without_alt'] ?? 0;
    
    return $issues;
}
?>

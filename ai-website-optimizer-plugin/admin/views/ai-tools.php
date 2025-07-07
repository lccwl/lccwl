<?php
/**
 * AI Tools view
 */

if (!defined('ABSPATH')) {
    exit;
}

AI_Optimizer_Admin::render_header(
    __('AI Tools & Content Generation', 'ai-website-optimizer'),
    __('Generate and optimize content using advanced AI models', 'ai-website-optimizer')
);

$ai_tools = new AI_Optimizer_AI_Tools();
$recent_generations = $ai_tools->get_recent_generations();
$video_requests = $ai_tools->get_video_requests();
?>

<div class="ai-optimizer-wrap">
    
    <!-- AI Generation Tools -->
    <div class="ai-optimizer-card">
        <div class="ai-optimizer-card-title"><?php _e('AI Content Generation', 'ai-website-optimizer'); ?></div>
        
        <div class="ai-optimizer-tabs">
            <div class="ai-optimizer-tab-nav">
                <button class="ai-optimizer-tab-button active" data-target="text-generation">
                    <i class="fas fa-file-alt"></i> <?php _e('Text', 'ai-website-optimizer'); ?>
                </button>
                <button class="ai-optimizer-tab-button" data-target="image-generation">
                    <i class="fas fa-image"></i> <?php _e('Images', 'ai-website-optimizer'); ?>
                </button>
                <button class="ai-optimizer-tab-button" data-target="video-generation">
                    <i class="fas fa-video"></i> <?php _e('Videos', 'ai-website-optimizer'); ?>
                </button>
                <button class="ai-optimizer-tab-button" data-target="audio-generation">
                    <i class="fas fa-music"></i> <?php _e('Audio', 'ai-website-optimizer'); ?>
                </button>
                <button class="ai-optimizer-tab-button" data-target="code-generation">
                    <i class="fas fa-code"></i> <?php _e('Code', 'ai-website-optimizer'); ?>
                </button>
            </div>
            
            <!-- Text Generation -->
            <div id="text-generation" class="ai-optimizer-tab-content active">
                <form class="generation-form" data-type="text">
                    <div class="ai-optimizer-grid ai-optimizer-grid-2">
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Content Type', 'ai-website-optimizer'); ?></label>
                            <select class="ai-optimizer-select" name="text_type">
                                <option value="article"><?php _e('Blog Article', 'ai-website-optimizer'); ?></option>
                                <option value="product_description"><?php _e('Product Description', 'ai-website-optimizer'); ?></option>
                                <option value="social_media"><?php _e('Social Media Post', 'ai-website-optimizer'); ?></option>
                                <option value="email"><?php _e('Email Content', 'ai-website-optimizer'); ?></option>
                                <option value="seo_content"><?php _e('SEO Optimized Content', 'ai-website-optimizer'); ?></option>
                            </select>
                        </div>
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Word Count', 'ai-website-optimizer'); ?></label>
                            <select class="ai-optimizer-select" name="word_count">
                                <option value="500">500 <?php _e('words', 'ai-website-optimizer'); ?></option>
                                <option value="1000">1000 <?php _e('words', 'ai-website-optimizer'); ?></option>
                                <option value="1500">1500 <?php _e('words', 'ai-website-optimizer'); ?></option>
                                <option value="2000">2000 <?php _e('words', 'ai-website-optimizer'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label"><?php _e('Topic or Prompt', 'ai-website-optimizer'); ?></label>
                        <textarea class="ai-optimizer-textarea" name="prompt" rows="4" placeholder="<?php _e('Describe what you want to create...', 'ai-website-optimizer'); ?>" required></textarea>
                    </div>
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label"><?php _e('Target Keywords (optional)', 'ai-website-optimizer'); ?></label>
                        <input type="text" class="ai-optimizer-input" name="keywords" placeholder="<?php _e('keyword1, keyword2, keyword3', 'ai-website-optimizer'); ?>">
                    </div>
                    <button type="submit" class="ai-optimizer-btn ai-optimizer-btn-pulse generate-content">
                        <i class="fas fa-magic"></i> <?php _e('Generate Text', 'ai-website-optimizer'); ?>
                    </button>
                </form>
            </div>
            
            <!-- Image Generation -->
            <div id="image-generation" class="ai-optimizer-tab-content">
                <form class="generation-form" data-type="image">
                    <div class="ai-optimizer-grid ai-optimizer-grid-2">
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Image Style', 'ai-website-optimizer'); ?></label>
                            <select class="ai-optimizer-select" name="style">
                                <option value="realistic"><?php _e('Realistic', 'ai-website-optimizer'); ?></option>
                                <option value="artistic"><?php _e('Artistic', 'ai-website-optimizer'); ?></option>
                                <option value="cartoon"><?php _e('Cartoon', 'ai-website-optimizer'); ?></option>
                                <option value="minimalist"><?php _e('Minimalist', 'ai-website-optimizer'); ?></option>
                                <option value="vintage"><?php _e('Vintage', 'ai-website-optimizer'); ?></option>
                            </select>
                        </div>
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Image Size', 'ai-website-optimizer'); ?></label>
                            <select class="ai-optimizer-select" name="size">
                                <option value="512x512">512×512</option>
                                <option value="1024x1024" selected>1024×1024</option>
                                <option value="1024x768">1024×768</option>
                                <option value="768x1024">768×1024</option>
                            </select>
                        </div>
                    </div>
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label"><?php _e('Image Description', 'ai-website-optimizer'); ?></label>
                        <textarea class="ai-optimizer-textarea" name="prompt" rows="4" placeholder="<?php _e('Describe the image you want to create...', 'ai-website-optimizer'); ?>" required></textarea>
                    </div>
                    <button type="submit" class="ai-optimizer-btn ai-optimizer-btn-pulse generate-content">
                        <i class="fas fa-image"></i> <?php _e('Generate Image', 'ai-website-optimizer'); ?>
                    </button>
                </form>
            </div>
            
            <!-- Video Generation -->
            <div id="video-generation" class="ai-optimizer-tab-content">
                <form class="generation-form" data-type="video">
                    <div class="ai-optimizer-grid ai-optimizer-grid-2">
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Video Duration', 'ai-website-optimizer'); ?></label>
                            <select class="ai-optimizer-select" name="duration">
                                <option value="5">5 <?php _e('seconds', 'ai-website-optimizer'); ?></option>
                                <option value="10" selected>10 <?php _e('seconds', 'ai-website-optimizer'); ?></option>
                                <option value="15">15 <?php _e('seconds', 'ai-website-optimizer'); ?></option>
                                <option value="30">30 <?php _e('seconds', 'ai-website-optimizer'); ?></option>
                            </select>
                        </div>
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Video Quality', 'ai-website-optimizer'); ?></label>
                            <select class="ai-optimizer-select" name="quality">
                                <option value="standard"><?php _e('Standard', 'ai-website-optimizer'); ?></option>
                                <option value="hd" selected><?php _e('HD', 'ai-website-optimizer'); ?></option>
                                <option value="4k"><?php _e('4K', 'ai-website-optimizer'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label"><?php _e('Video Description', 'ai-website-optimizer'); ?></label>
                        <textarea class="ai-optimizer-textarea" name="prompt" rows="4" placeholder="<?php _e('Describe the video scene you want to create...', 'ai-website-optimizer'); ?>" required></textarea>
                    </div>
                    <button type="submit" class="ai-optimizer-btn ai-optimizer-btn-pulse generate-content">
                        <i class="fas fa-video"></i> <?php _e('Generate Video', 'ai-website-optimizer'); ?>
                    </button>
                </form>
            </div>
            
            <!-- Audio Generation -->
            <div id="audio-generation" class="ai-optimizer-tab-content">
                <form class="generation-form" data-type="audio">
                    <div class="ai-optimizer-grid ai-optimizer-grid-2">
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Voice Type', 'ai-website-optimizer'); ?></label>
                            <select class="ai-optimizer-select" name="voice">
                                <option value="male"><?php _e('Male Voice', 'ai-website-optimizer'); ?></option>
                                <option value="female" selected><?php _e('Female Voice', 'ai-website-optimizer'); ?></option>
                                <option value="child"><?php _e('Child Voice', 'ai-website-optimizer'); ?></option>
                                <option value="elderly"><?php _e('Elderly Voice', 'ai-website-optimizer'); ?></option>
                            </select>
                        </div>
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Speaking Speed', 'ai-website-optimizer'); ?></label>
                            <select class="ai-optimizer-select" name="speed">
                                <option value="0.5"><?php _e('Slow', 'ai-website-optimizer'); ?></option>
                                <option value="1.0" selected><?php _e('Normal', 'ai-website-optimizer'); ?></option>
                                <option value="1.5"><?php _e('Fast', 'ai-website-optimizer'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label"><?php _e('Text to Convert', 'ai-website-optimizer'); ?></label>
                        <textarea class="ai-optimizer-textarea" name="prompt" rows="6" placeholder="<?php _e('Enter the text you want to convert to speech...', 'ai-website-optimizer'); ?>" required></textarea>
                    </div>
                    <button type="submit" class="ai-optimizer-btn ai-optimizer-btn-pulse generate-content">
                        <i class="fas fa-music"></i> <?php _e('Generate Audio', 'ai-website-optimizer'); ?>
                    </button>
                </form>
            </div>
            
            <!-- Code Generation -->
            <div id="code-generation" class="ai-optimizer-tab-content">
                <form class="generation-form" data-type="code">
                    <div class="ai-optimizer-grid ai-optimizer-grid-2">
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Programming Language', 'ai-website-optimizer'); ?></label>
                            <select class="ai-optimizer-select" name="language">
                                <option value="php" selected>PHP</option>
                                <option value="javascript">JavaScript</option>
                                <option value="css">CSS</option>
                                <option value="html">HTML</option>
                                <option value="python">Python</option>
                            </select>
                        </div>
                        <div class="ai-optimizer-form-group">
                            <label class="ai-optimizer-label"><?php _e('Framework/Context', 'ai-website-optimizer'); ?></label>
                            <select class="ai-optimizer-select" name="framework">
                                <option value="wordpress" selected>WordPress</option>
                                <option value="vanilla"><?php _e('Vanilla/Pure', 'ai-website-optimizer'); ?></option>
                                <option value="react">React</option>
                                <option value="vue">Vue.js</option>
                                <option value="jquery">jQuery</option>
                            </select>
                        </div>
                    </div>
                    <div class="ai-optimizer-form-group">
                        <label class="ai-optimizer-label"><?php _e('Code Requirements', 'ai-website-optimizer'); ?></label>
                        <textarea class="ai-optimizer-textarea" name="prompt" rows="6" placeholder="<?php _e('Describe the functionality you want to implement...', 'ai-website-optimizer'); ?>" required></textarea>
                    </div>
                    <button type="submit" class="ai-optimizer-btn ai-optimizer-btn-pulse generate-content">
                        <i class="fas fa-code"></i> <?php _e('Generate Code', 'ai-website-optimizer'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Generation Results -->
    <div class="ai-optimizer-card" id="generation-results" style="display: none;">
        <div class="ai-optimizer-card-title"><?php _e('Generation Results', 'ai-website-optimizer'); ?></div>
        <div id="generated-content-container"></div>
    </div>

    <!-- Recent Generations -->
    <div class="ai-optimizer-grid ai-optimizer-grid-2">
        
        <!-- Recent Text/Image/Audio Generations -->
        <div class="ai-optimizer-card">
            <div class="ai-optimizer-card-title"><?php _e('Recent Generations', 'ai-website-optimizer'); ?></div>
            <div class="generations-list">
                <?php if (!empty($recent_generations)): ?>
                    <?php foreach (array_slice($recent_generations, 0, 10) as $generation): ?>
                    <div class="generation-item">
                        <div class="generation-icon">
                            <i class="fas fa-<?php echo $this->get_generation_icon($generation['type']); ?>"></i>
                        </div>
                        <div class="generation-content">
                            <div class="generation-type"><?php echo esc_html(ucfirst($generation['type'])); ?></div>
                            <div class="generation-prompt"><?php echo esc_html(wp_trim_words($generation['prompt'], 10)); ?></div>
                            <div class="generation-time"><?php echo human_time_diff(strtotime($generation['created_at'])); ?> ago</div>
                        </div>
                        <div class="generation-actions">
                            <?php if ($generation['type'] === 'text'): ?>
                                <button class="ai-optimizer-btn ai-optimizer-btn-secondary view-generation" data-id="<?php echo $generation['id']; ?>">
                                    <?php _e('View', 'ai-website-optimizer'); ?>
                                </button>
                            <?php elseif (in_array($generation['type'], ['image', 'audio'])): ?>
                                <a href="<?php echo esc_url($generation['result']); ?>" class="ai-optimizer-btn ai-optimizer-btn-secondary" target="_blank">
                                    <?php _e('Download', 'ai-website-optimizer'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ai-optimizer-text-center ai-optimizer-text-muted">
                        <i class="fas fa-magic" style="font-size: 48px; opacity: 0.3; margin-bottom: 20px;"></i>
                        <p><?php _e('No generations yet. Start creating amazing content!', 'ai-website-optimizer'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Video Generation Status -->
        <div class="ai-optimizer-card">
            <div class="ai-optimizer-card-title"><?php _e('Video Generation Status', 'ai-website-optimizer'); ?></div>
            <div class="video-requests-list">
                <?php if (!empty($video_requests)): ?>
                    <?php foreach (array_slice($video_requests, 0, 10) as $request): ?>
                    <div class="video-request">
                        <div class="request-info">
                            <div class="request-prompt"><?php echo esc_html(wp_trim_words($request['prompt'], 8)); ?></div>
                            <div class="request-time"><?php echo human_time_diff(strtotime($request['created_at'])); ?> ago</div>
                        </div>
                        <div class="request-status">
                            <span class="ai-optimizer-badge ai-optimizer-badge-<?php echo $request['status'] === 'completed' ? 'success' : ($request['status'] === 'failed' ? 'error' : 'info'); ?>">
                                <?php echo esc_html($request['status']); ?>
                            </span>
                        </div>
                        <div class="request-actions">
                            <?php if ($request['status'] === 'completed' && !empty($request['result_url'])): ?>
                                <a href="<?php echo esc_url($request['result_url']); ?>" class="ai-optimizer-btn ai-optimizer-btn-success" target="_blank">
                                    <?php _e('Download', 'ai-website-optimizer'); ?>
                                </a>
                            <?php elseif ($request['status'] === 'processing'): ?>
                                <button class="ai-optimizer-btn ai-optimizer-btn-secondary check-video-status" data-request-id="<?php echo esc_attr($request['request_id']); ?>">
                                    <?php _e('Check Status', 'ai-website-optimizer'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ai-optimizer-text-center ai-optimizer-text-muted">
                        <i class="fas fa-video" style="font-size: 48px; opacity: 0.3; margin-bottom: 20px;"></i>
                        <p><?php _e('No video generation requests yet.', 'ai-website-optimizer'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Bulk Content Generation -->
    <div class="ai-optimizer-card">
        <div class="ai-optimizer-card-title"><?php _e('Bulk Content Generation', 'ai-website-optimizer'); ?></div>
        <form class="bulk-generation-form">
            <div class="ai-optimizer-grid ai-optimizer-grid-2">
                <div class="ai-optimizer-form-group">
                    <label class="ai-optimizer-label"><?php _e('Content Type', 'ai-website-optimizer'); ?></label>
                    <select class="ai-optimizer-select" name="bulk_type">
                        <option value="text"><?php _e('Text Content', 'ai-website-optimizer'); ?></option>
                        <option value="image"><?php _e('Images', 'ai-website-optimizer'); ?></option>
                        <option value="product_descriptions"><?php _e('Product Descriptions', 'ai-website-optimizer'); ?></option>
                    </select>
                </div>
                <div class="ai-optimizer-form-group">
                    <label class="ai-optimizer-label"><?php _e('Quantity', 'ai-website-optimizer'); ?></label>
                    <select class="ai-optimizer-select" name="bulk_quantity">
                        <option value="5">5 <?php _e('items', 'ai-website-optimizer'); ?></option>
                        <option value="10" selected>10 <?php _e('items', 'ai-website-optimizer'); ?></option>
                        <option value="20">20 <?php _e('items', 'ai-website-optimizer'); ?></option>
                        <option value="50">50 <?php _e('items', 'ai-website-optimizer'); ?></option>
                    </select>
                </div>
            </div>
            <div class="ai-optimizer-form-group">
                <label class="ai-optimizer-label"><?php _e('Prompts (one per line)', 'ai-website-optimizer'); ?></label>
                <textarea class="ai-optimizer-textarea" name="bulk_prompts" rows="6" placeholder="<?php _e('Enter prompts for bulk generation, one per line...', 'ai-website-optimizer'); ?>" required></textarea>
            </div>
            <button type="submit" class="ai-optimizer-btn ai-optimizer-btn-pulse" id="bulk-generate">
                <i class="fas fa-layer-group"></i> <?php _e('Start Bulk Generation', 'ai-website-optimizer'); ?>
            </button>
        </form>
        <div id="bulk-progress" style="display: none;">
            <div class="ai-optimizer-progress">
                <div class="ai-optimizer-progress-bar" id="bulk-progress-bar"></div>
            </div>
            <div class="bulk-status" id="bulk-status"></div>
        </div>
    </div>

</div>

<style>
.generation-form {
    padding: 20px 0;
}

.generations-list, .video-requests-list {
    max-height: 500px;
    overflow-y: auto;
}

.generation-item, .video-request {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid var(--ai-border);
}

.generation-item:last-child, .video-request:last-child {
    border-bottom: none;
}

.generation-icon {
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

.generation-content, .request-info {
    flex: 1;
}

.generation-type {
    font-weight: 600;
    text-transform: capitalize;
    margin-bottom: 5px;
}

.generation-prompt, .request-prompt {
    color: var(--ai-text-muted);
    font-size: 0.9em;
    margin-bottom: 5px;
}

.generation-time, .request-time {
    font-size: 0.8em;
    color: var(--ai-text-muted);
}

.generation-actions, .request-actions {
    margin-left: 15px;
}

.request-status {
    margin-left: 15px;
}

.bulk-generation-form {
    margin-bottom: 20px;
}

.bulk-status {
    text-align: center;
    margin-top: 10px;
    font-weight: 600;
}

#generated-content-container {
    padding: 20px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
    min-height: 200px;
}

.generated-text {
    line-height: 1.6;
    color: var(--ai-text);
}

.generated-image img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: var(--ai-shadow);
}

.generated-audio audio {
    width: 100%;
    margin: 20px 0;
}

.generated-code pre {
    background: rgba(0, 0, 0, 0.3);
    padding: 20px;
    border-radius: 8px;
    overflow-x: auto;
    border-left: 4px solid var(--ai-secondary);
}

.generated-code code {
    color: var(--ai-secondary);
    font-family: 'Courier New', monospace;
}

@media (max-width: 768px) {
    .generation-item, .video-request {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .generation-actions, .request-actions, .request-status {
        margin-left: 0;
        margin-top: 10px;
        width: 100%;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Handle content generation
    $('.generation-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const type = form.data('type');
        const formData = form.serialize();
        const button = form.find('.generate-content');
        const originalText = button.html();
        
        // Get form values
        const prompt = form.find('[name="prompt"]').val();
        if (!prompt.trim()) {
            alert('Please enter a prompt');
            return;
        }
        
        button.prop('disabled', true).html('<span class="ai-optimizer-loading"></span> Generating...');
        
        // Prepare options based on type
        let options = {};
        form.find('select, input').each(function() {
            if ($(this).attr('name') !== 'prompt') {
                options[$(this).attr('name')] = $(this).val();
            }
        });
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'generate_content',
            content_type: type,
            prompt: prompt,
            options: JSON.stringify(options),
            nonce: aiOptimizer.nonce
        })
        .done(function(response) {
            if (response.success) {
                displayGeneratedContent(response.data, type);
                $('#generation-results').show();
                
                // Scroll to results
                $('html, body').animate({
                    scrollTop: $('#generation-results').offset().top - 100
                }, 500);
                
                // Clear form
                form.find('[name="prompt"]').val('');
            } else {
                alert('Generation failed: ' + response.data);
            }
        })
        .fail(function() {
            alert('Network error occurred');
        })
        .always(function() {
            button.prop('disabled', false).html(originalText);
        });
    });
    
    // Handle bulk generation
    $('.bulk-generation-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const type = form.find('[name="bulk_type"]').val();
        const quantity = parseInt(form.find('[name="bulk_quantity"]').val());
        const prompts = form.find('[name="bulk_prompts"]').val().split('\n').filter(p => p.trim());
        
        if (prompts.length === 0) {
            alert('Please enter at least one prompt');
            return;
        }
        
        $('#bulk-progress').show();
        $('#bulk-progress-bar').css('width', '0%');
        $('#bulk-status').text('Starting bulk generation...');
        
        // Process prompts in batches
        processBulkGeneration(prompts, type, 0);
    });
    
    function processBulkGeneration(prompts, type, index) {
        if (index >= prompts.length) {
            $('#bulk-status').text('Bulk generation completed!');
            setTimeout(() => {
                $('#bulk-progress').hide();
                location.reload();
            }, 2000);
            return;
        }
        
        const progress = ((index + 1) / prompts.length) * 100;
        $('#bulk-progress-bar').css('width', progress + '%');
        $('#bulk-status').text(`Processing ${index + 1} of ${prompts.length}...`);
        
        $.post(aiOptimizer.ajaxUrl, {
            action: 'ai_optimizer_action',
            action_type: 'generate_content',
            content_type: type,
            prompt: prompts[index],
            options: '{}',
            nonce: aiOptimizer.nonce
        })
        .always(function() {
            // Add delay and continue
            setTimeout(() => {
                processBulkGeneration(prompts, type, index + 1);
            }, 2000);
        });
    }
    
    function displayGeneratedContent(data, type) {
        const container = $('#generated-content-container');
        let html = '';
        
        switch (type) {
            case 'text':
                html = `<div class="generated-text">
                    <h4>Generated Content:</h4>
                    <div>${data.content ? data.content.replace(/\n/g, '<br>') : 'Content generated successfully'}</div>
                </div>`;
                break;
                
            case 'image':
                html = `<div class="generated-image">
                    <h4>Generated Image:</h4>
                    <img src="${data.url}" alt="Generated Image">
                    <p><a href="${data.url}" target="_blank" class="ai-optimizer-btn ai-optimizer-btn-secondary">Download Image</a></p>
                </div>`;
                break;
                
            case 'video':
                html = `<div class="generated-video">
                    <h4>Video Generation Started</h4>
                    <p>${data.message}</p>
                    <p><strong>Request ID:</strong> ${data.request_id}</p>
                    <p>Check back in a few minutes for your video.</p>
                </div>`;
                break;
                
            case 'audio':
                html = `<div class="generated-audio">
                    <h4>Generated Audio:</h4>
                    <audio controls>
                        <source src="${data.url}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                    <p><a href="${data.url}" target="_blank" class="ai-optimizer-btn ai-optimizer-btn-secondary">Download Audio</a></p>
                </div>`;
                break;
                
            case 'code':
                html = `<div class="generated-code">
                    <h4>Generated Code:</h4>
                    <pre><code>${escapeHtml(data.code || 'Code generated successfully')}</code></pre>
                    <button class="ai-optimizer-btn ai-optimizer-btn-secondary copy-code">Copy Code</button>
                </div>`;
                break;
        }
        
        container.html(html);
    }
    
    // Copy code functionality
    $(document).on('click', '.copy-code', function() {
        const code = $(this).prev('pre').find('code').text();
        navigator.clipboard.writeText(code).then(() => {
            $(this).text('Copied!').addClass('ai-optimizer-btn-success');
            setTimeout(() => {
                $(this).text('Copy Code').removeClass('ai-optimizer-btn-success');
            }, 2000);
        });
    });
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>

<?php
AI_Optimizer_Admin::render_footer();

// Helper function for generation icons
function get_generation_icon($type) {
    $icons = array(
        'text' => 'file-alt',
        'image' => 'image',
        'video' => 'video',
        'audio' => 'music',
        'code' => 'code',
    );
    
    return isset($icons[$type]) ? $icons[$type] : 'magic';
}
?>

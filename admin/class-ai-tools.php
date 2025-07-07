<?php
/**
 * AI Tools class
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_AI_Tools {
    
    private $api_handler;
    
    public function __construct() {
        $this->api_handler = new AI_Optimizer_API_Handler();
    }
    
    /**
     * Render AI tools page
     */
    public static function render() {
        $tools = new self();
        $recent_generations = $tools->get_recent_generations();
        
        include AI_OPTIMIZER_PLUGIN_PATH . 'admin/views/ai-tools.php';
    }
    
    /**
     * Generate content
     */
    public function generate_content($type, $prompt, $options = array()) {
        switch ($type) {
            case 'text':
                return $this->generate_text($prompt, $options);
            case 'image':
                return $this->generate_image($prompt, $options);
            case 'video':
                return $this->generate_video($prompt, $options);
            case 'audio':
                return $this->generate_audio($prompt, $options);
            case 'code':
                return $this->generate_code($prompt, $options);
            default:
                return array('error' => 'Invalid content type');
        }
    }
    
    /**
     * Generate text content
     */
    private function generate_text($prompt, $options) {
        $model = $options['model'] ?? 'Qwen/QwQ-32B';
        $max_tokens = $options['max_tokens'] ?? 1000;
        
        $messages = array(
            array(
                'role' => 'system',
                'content' => 'You are a professional content writer. Create high-quality, engaging content that is SEO-optimized and suitable for WordPress websites.'
            ),
            array(
                'role' => 'user',
                'content' => $prompt
            )
        );
        
        $response = $this->api_handler->chat_completion_with_options($messages, $model, array(
            'max_tokens' => $max_tokens,
            'temperature' => 0.7,
        ));
        
        if ($response) {
            $this->store_generation('text', $prompt, $response, $options);
            return array('success' => true, 'content' => $response);
        }
        
        return array('error' => 'Failed to generate text content');
    }
    
    /**
     * Generate image
     */
    private function generate_image($prompt, $options) {
        $model = $options['model'] ?? 'stabilityai/stable-diffusion-xl-base-1.0';
        $size = $options['size'] ?? '1024x1024';
        
        $result = $this->api_handler->generate_image($prompt, $model, array(
            'size' => $size,
            'quality' => $options['quality'] ?? 'standard',
        ));
        
        if ($result && isset($result['url'])) {
            // Download and save image to WordPress media library
            $image_data = $this->download_and_save_image($result['url'], $prompt);
            
            if ($image_data) {
                $this->store_generation('image', $prompt, $image_data['url'], $options);
                return array(
                    'success' => true,
                    'url' => $image_data['url'],
                    'attachment_id' => $image_data['attachment_id']
                );
            }
        }
        
        return array('error' => 'Failed to generate image');
    }
    
    /**
     * Generate video
     */
    private function generate_video($prompt, $options) {
        $model = $options['model'] ?? 'Lightricks/LTX-Video';
        $duration = $options['duration'] ?? 10;
        
        // Submit video generation request
        $request_id = $this->api_handler->submit_video_generation($prompt, $model, array(
            'duration' => $duration,
        ));
        
        if ($request_id) {
            // Store the request for later processing
            $this->store_video_request($request_id, $prompt, $options);
            
            return array(
                'success' => true,
                'request_id' => $request_id,
                'status' => 'processing',
                'message' => 'Video generation started. Check back in a few minutes.'
            );
        }
        
        return array('error' => 'Failed to start video generation');
    }
    
    /**
     * Generate audio
     */
    private function generate_audio($prompt, $options) {
        $model = $options['model'] ?? 'fishaudio/fish-speech-1.5';
        $voice = $options['voice'] ?? 'default';
        
        $result = $this->api_handler->text_to_speech($prompt, $model, array(
            'voice' => $voice,
            'speed' => $options['speed'] ?? 1.0,
        ));
        
        if ($result && isset($result['url'])) {
            // Download and save audio file
            $audio_data = $this->download_and_save_audio($result['url'], $prompt);
            
            if ($audio_data) {
                $this->store_generation('audio', $prompt, $audio_data['url'], $options);
                return array(
                    'success' => true,
                    'url' => $audio_data['url'],
                    'attachment_id' => $audio_data['attachment_id']
                );
            }
        }
        
        return array('error' => 'Failed to generate audio');
    }
    
    /**
     * Generate code
     */
    private function generate_code($prompt, $options) {
        $language = $options['language'] ?? 'php';
        $framework = $options['framework'] ?? 'wordpress';
        
        $system_prompt = "You are an expert {$language} developer specializing in {$framework}. Generate clean, secure, and well-documented code that follows best practices.";
        
        $messages = array(
            array(
                'role' => 'system',
                'content' => $system_prompt
            ),
            array(
                'role' => 'user',
                'content' => $prompt
            )
        );
        
        $response = $this->api_handler->chat_completion_with_options($messages, 'Qwen/QwQ-32B', array(
            'max_tokens' => 2000,
            'temperature' => 0.3,
        ));
        
        if ($response) {
            $this->store_generation('code', $prompt, $response, $options);
            return array('success' => true, 'code' => $response);
        }
        
        return array('error' => 'Failed to generate code');
    }
    
    /**
     * Check video generation status
     */
    public function check_video_status($request_id) {
        $result = $this->api_handler->get_video_status($request_id);
        
        if ($result && isset($result['status'])) {
            if ($result['status'] === 'completed' && isset($result['url'])) {
                // Download and save video
                $video_data = $this->download_and_save_video($result['url'], $request_id);
                
                if ($video_data) {
                    $this->update_video_request($request_id, 'completed', $video_data['url']);
                    return array(
                        'success' => true,
                        'status' => 'completed',
                        'url' => $video_data['url'],
                        'attachment_id' => $video_data['attachment_id']
                    );
                }
            }
            
            return array(
                'success' => true,
                'status' => $result['status'],
                'progress' => $result['progress'] ?? 0
            );
        }
        
        return array('error' => 'Failed to check video status');
    }
    
    /**
     * Store generation record
     */
    private function store_generation($type, $prompt, $result, $options) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_generations';
        
        $wpdb->insert(
            $table_name,
            array(
                'type' => $type,
                'prompt' => $prompt,
                'result' => is_array($result) ? json_encode($result) : $result,
                'options' => json_encode($options),
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s', '%d', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Store video request
     */
    private function store_video_request($request_id, $prompt, $options) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_video_requests';
        
        $wpdb->insert(
            $table_name,
            array(
                'request_id' => $request_id,
                'prompt' => $prompt,
                'options' => json_encode($options),
                'status' => 'processing',
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s', '%d', '%s')
        );
    }
    
    /**
     * Update video request
     */
    private function update_video_request($request_id, $status, $result_url = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_video_requests';
        
        $update_data = array(
            'status' => $status,
            'updated_at' => current_time('mysql'),
        );
        
        if ($result_url) {
            $update_data['result_url'] = $result_url;
        }
        
        $wpdb->update(
            $table_name,
            $update_data,
            array('request_id' => $request_id),
            array('%s', '%s', '%s'),
            array('%s')
        );
    }
    
    /**
     * Download and save image to media library
     */
    private function download_and_save_image($url, $description) {
        $temp_file = download_url($url);
        
        if (is_wp_error($temp_file)) {
            return false;
        }
        
        $file_array = array(
            'name' => 'ai-generated-' . time() . '.jpg',
            'tmp_name' => $temp_file,
        );
        
        $attachment_id = media_handle_sideload($file_array, 0, $description);
        
        if (is_wp_error($attachment_id)) {
            @unlink($temp_file);
            return false;
        }
        
        return array(
            'attachment_id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
        );
    }
    
    /**
     * Download and save audio to media library
     */
    private function download_and_save_audio($url, $description) {
        $temp_file = download_url($url);
        
        if (is_wp_error($temp_file)) {
            return false;
        }
        
        $file_array = array(
            'name' => 'ai-generated-audio-' . time() . '.mp3',
            'tmp_name' => $temp_file,
        );
        
        $attachment_id = media_handle_sideload($file_array, 0, $description);
        
        if (is_wp_error($attachment_id)) {
            @unlink($temp_file);
            return false;
        }
        
        return array(
            'attachment_id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
        );
    }
    
    /**
     * Download and save video to media library
     */
    private function download_and_save_video($url, $request_id) {
        $temp_file = download_url($url);
        
        if (is_wp_error($temp_file)) {
            return false;
        }
        
        $file_array = array(
            'name' => 'ai-generated-video-' . $request_id . '.mp4',
            'tmp_name' => $temp_file,
        );
        
        $attachment_id = media_handle_sideload($file_array, 0, 'AI Generated Video');
        
        if (is_wp_error($attachment_id)) {
            @unlink($temp_file);
            return false;
        }
        
        return array(
            'attachment_id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
        );
    }
    
    /**
     * Get recent generations
     */
    public function get_recent_generations() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_generations';
        
        return $wpdb->get_results(
            "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 20",
            ARRAY_A
        );
    }
    
    /**
     * Get video requests
     */
    public function get_video_requests() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_video_requests';
        
        return $wpdb->get_results(
            "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 20",
            ARRAY_A
        );
    }
    
    /**
     * Bulk content generation
     */
    public function bulk_generate_content($type, $prompts, $options = array()) {
        $results = array();
        
        foreach ($prompts as $prompt) {
            $result = $this->generate_content($type, $prompt, $options);
            $results[] = $result;
            
            // Add delay to respect rate limits
            sleep(1);
        }
        
        return $results;
    }
    
    /**
     * Content optimization
     */
    public function optimize_existing_content($content, $optimization_type) {
        $prompts = array(
            'seo' => "Optimize this content for SEO while maintaining its original meaning and quality:\n\n{$content}",
            'readability' => "Improve the readability of this content without changing its core message:\n\n{$content}",
            'engagement' => "Make this content more engaging and compelling while keeping the facts accurate:\n\n{$content}",
            'length' => "Expand this content to make it more comprehensive and detailed:\n\n{$content}",
        );
        
        if (!isset($prompts[$optimization_type])) {
            return array('error' => 'Invalid optimization type');
        }
        
        return $this->generate_text($prompts[$optimization_type], array());
    }
}

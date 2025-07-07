<?php
/**
 * Video Generator class for AI-powered video creation
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_Video_Generator {
    
    private $api_handler;
    private $upload_dir;
    
    public function __construct() {
        $this->api_handler = new AI_Optimizer_API_Handler();
        $this->upload_dir = wp_upload_dir();
    }
    
    /**
     * Generate video content
     */
    public function generate_video($prompt, $options = array()) {
        $model = $options['model'] ?? 'Lightricks/LTX-Video';
        $duration = intval($options['duration'] ?? 10);
        $quality = $options['quality'] ?? 'hd';
        
        AI_Optimizer_Utils::log('Starting video generation', 'info', array(
            'prompt' => wp_trim_words($prompt, 10),
            'model' => $model,
            'duration' => $duration
        ));
        
        // Submit video generation request
        $request_id = $this->api_handler->submit_video_generation($prompt, $model, array(
            'duration' => $duration,
            'quality' => $quality
        ));
        
        if (!$request_id) {
            AI_Optimizer_Utils::log('Video generation submission failed', 'error');
            return array('error' => 'Failed to submit video generation request');
        }
        
        // Store request in database
        $this->store_video_request($request_id, $prompt, $options);
        
        return array(
            'success' => true,
            'request_id' => $request_id,
            'status' => 'processing',
            'message' => __('Video generation started. Check back in a few minutes.', 'ai-website-optimizer')
        );
    }
    
    /**
     * Check video generation status
     */
    public function check_video_status($request_id) {
        $result = $this->api_handler->get_video_status($request_id);
        
        if (!$result) {
            return array('error' => 'Failed to check video status');
        }
        
        // Update database record
        $this->update_video_request($request_id, $result['status'], $result['url'] ?? null);
        
        if ($result['status'] === 'completed' && isset($result['url'])) {
            // Download and save video
            $video_data = $this->download_and_save_video($result['url'], $request_id);
            
            if ($video_data) {
                $this->update_video_request($request_id, 'completed', $video_data['url']);
                
                AI_Optimizer_Utils::log('Video generation completed', 'info', array(
                    'request_id' => $request_id,
                    'local_url' => $video_data['url']
                ));
                
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
    
    /**
     * Generate long video by combining segments
     */
    public function generate_long_video($script, $total_duration = 7200) {
        $segments = $this->split_script_into_segments($script, $total_duration);
        $generated_segments = array();
        
        AI_Optimizer_Utils::log('Starting long video generation', 'info', array(
            'total_duration' => $total_duration,
            'segments_count' => count($segments)
        ));
        
        foreach ($segments as $index => $segment) {
            $result = $this->generate_video($segment['prompt'], array(
                'duration' => $segment['duration']
            ));
            
            if (isset($result['request_id'])) {
                $generated_segments[] = array(
                    'request_id' => $result['request_id'],
                    'prompt' => $segment['prompt'],
                    'duration' => $segment['duration'],
                    'order' => $index
                );
                
                // Wait between requests to respect rate limits
                sleep(2);
            }
        }
        
        // Store long video project
        $project_id = $this->store_long_video_project($generated_segments, $total_duration);
        
        return array(
            'success' => true,
            'project_id' => $project_id,
            'segments' => $generated_segments,
            'message' => __('Long video generation started. Segments will be combined when ready.', 'ai-website-optimizer')
        );
    }
    
    /**
     * Combine video segments
     */
    public function combine_video_segments($project_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_video_projects';
        
        $project = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $project_id),
            ARRAY_A
        );
        
        if (!$project) {
            return array('error' => 'Project not found');
        }
        
        $segments_data = json_decode($project['segments_data'], true);
        $completed_segments = array();
        
        // Check if all segments are completed
        foreach ($segments_data as $segment) {
            $status_result = $this->check_video_status($segment['request_id']);
            
            if ($status_result['status'] === 'completed') {
                $completed_segments[] = array(
                    'url' => $status_result['url'],
                    'order' => $segment['order']
                );
            }
        }
        
        if (count($completed_segments) !== count($segments_data)) {
            return array(
                'success' => false,
                'message' => 'Not all segments are ready yet',
                'completed' => count($completed_segments),
                'total' => count($segments_data)
            );
        }
        
        // Sort segments by order
        usort($completed_segments, function($a, $b) {
            return $a['order'] - $b['order'];
        });
        
        // Combine videos using FFmpeg (if available) or simple concatenation
        $combined_video = $this->concatenate_videos($completed_segments, $project_id);
        
        if ($combined_video) {
            $wpdb->update(
                $table_name,
                array(
                    'status' => 'completed',
                    'final_video_url' => $combined_video['url'],
                    'completed_at' => current_time('mysql')
                ),
                array('id' => $project_id),
                array('%s', '%s', '%s'),
                array('%d')
            );
            
            return array(
                'success' => true,
                'url' => $combined_video['url'],
                'attachment_id' => $combined_video['attachment_id']
            );
        }
        
        return array('error' => 'Failed to combine video segments');
    }
    
    /**
     * Generate video with AI-enhanced script
     */
    public function generate_enhanced_video($topic, $target_audience = 'general', $style = 'educational') {
        // Generate script using AI
        $script_prompt = "Create a compelling video script about '{$topic}' for {$target_audience} audience in {$style} style. Include scene descriptions and visual cues.";
        
        $script = $this->api_handler->chat_completion(
            $script_prompt,
            'Qwen/QwQ-32B',
            'You are a professional video script writer. Create engaging, well-structured video scripts with clear scene descriptions.'
        );
        
        if (!$script) {
            return array('error' => 'Failed to generate script');
        }
        
        // Extract scenes from script
        $scenes = $this->extract_scenes_from_script($script);
        $video_results = array();
        
        foreach ($scenes as $scene) {
            $result = $this->generate_video($scene['description'], array(
                'duration' => $scene['duration'] ?? 10
            ));
            
            if (isset($result['request_id'])) {
                $video_results[] = $result;
                sleep(1); // Rate limiting
            }
        }
        
        return array(
            'success' => true,
            'script' => $script,
            'scenes' => $scenes,
            'video_requests' => $video_results
        );
    }
    
    /**
     * Auto-generate video content from website data
     */
    public function auto_generate_from_content($post_id) {
        $post = get_post($post_id);
        
        if (!$post) {
            return array('error' => 'Post not found');
        }
        
        // Analyze post content
        $content_analysis = $this->analyze_post_content($post);
        
        // Generate video script based on content
        $script_prompt = "Create a video script based on this article:\n\n";
        $script_prompt .= "Title: {$post->post_title}\n";
        $script_prompt .= "Content: " . wp_trim_words($post->post_content, 200) . "\n\n";
        $script_prompt .= "Create an engaging video script that summarizes the key points with visual descriptions.";
        
        $script = $this->api_handler->chat_completion(
            $script_prompt,
            'Qwen/QwQ-32B',
            'You are a content creator specializing in turning written content into engaging video scripts.'
        );
        
        if (!$script) {
            return array('error' => 'Failed to generate script from content');
        }
        
        // Generate video based on script
        $video_prompt = "Create a video based on this script: " . wp_trim_words($script, 100);
        
        return $this->generate_video($video_prompt, array(
            'duration' => 30,
            'quality' => 'hd'
        ));
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
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%d', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update video request
     */
    private function update_video_request($request_id, $status, $result_url = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_video_requests';
        
        $update_data = array(
            'status' => $status,
            'updated_at' => current_time('mysql')
        );
        
        if ($result_url) {
            $update_data['result_url'] = $result_url;
        }
        
        if ($status === 'completed') {
            $update_data['completed_at'] = current_time('mysql');
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
     * Download and save video to media library
     */
    private function download_and_save_video($url, $request_id) {
        $temp_file = download_url($url);
        
        if (is_wp_error($temp_file)) {
            AI_Optimizer_Utils::log('Failed to download video', 'error', array(
                'url' => $url,
                'error' => $temp_file->get_error_message()
            ));
            return false;
        }
        
        $file_array = array(
            'name' => 'ai-generated-video-' . $request_id . '.mp4',
            'tmp_name' => $temp_file,
        );
        
        $attachment_id = media_handle_sideload($file_array, 0, 'AI Generated Video');
        
        if (is_wp_error($attachment_id)) {
            @unlink($temp_file);
            AI_Optimizer_Utils::log('Failed to save video to media library', 'error', array(
                'error' => $attachment_id->get_error_message()
            ));
            return false;
        }
        
        return array(
            'attachment_id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id)
        );
    }
    
    /**
     * Split script into segments for long video
     */
    private function split_script_into_segments($script, $total_duration) {
        $sentences = preg_split('/[.!?]+/', $script);
        $sentences = array_filter($sentences, 'trim');
        
        $segment_duration = min(30, $total_duration / count($sentences));
        $segments = array();
        
        foreach ($sentences as $index => $sentence) {
            if (trim($sentence)) {
                $segments[] = array(
                    'prompt' => trim($sentence),
                    'duration' => $segment_duration,
                    'order' => $index
                );
            }
        }
        
        return $segments;
    }
    
    /**
     * Store long video project
     */
    private function store_long_video_project($segments, $total_duration) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_video_projects';
        
        $wpdb->insert(
            $table_name,
            array(
                'segments_data' => json_encode($segments),
                'total_duration' => $total_duration,
                'status' => 'processing',
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql')
            ),
            array('%s', '%d', '%s', '%d', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Concatenate videos
     */
    private function concatenate_videos($segments, $project_id) {
        // For now, we'll use a simple approach - return the first segment
        // In production, you'd want to use FFmpeg for proper concatenation
        
        if (empty($segments)) {
            return false;
        }
        
        // Create a playlist file for the segments
        $playlist_content = '';
        foreach ($segments as $segment) {
            $playlist_content .= "file '" . $segment['url'] . "'\n";
        }
        
        // Save playlist file
        $upload_dir = wp_upload_dir();
        $playlist_file = $upload_dir['path'] . '/video_playlist_' . $project_id . '.txt';
        file_put_contents($playlist_file, $playlist_content);
        
        // For demonstration, return the first video
        // In production, implement FFmpeg concatenation here
        return array(
            'url' => $segments[0]['url'],
            'attachment_id' => null // Would be set after actual concatenation
        );
    }
    
    /**
     * Extract scenes from script
     */
    private function extract_scenes_from_script($script) {
        $scenes = array();
        
        // Simple scene extraction - split by paragraphs or scene markers
        $paragraphs = explode("\n\n", $script);
        
        foreach ($paragraphs as $index => $paragraph) {
            if (trim($paragraph)) {
                $scenes[] = array(
                    'description' => trim($paragraph),
                    'duration' => 10, // Default duration per scene
                    'order' => $index
                );
            }
        }
        
        return $scenes;
    }
    
    /**
     * Analyze post content for video generation
     */
    private function analyze_post_content($post) {
        $analysis = array(
            'word_count' => str_word_count($post->post_content),
            'key_topics' => array(),
            'tone' => 'neutral',
            'complexity' => 'medium'
        );
        
        // Extract key topics using simple keyword analysis
        $words = str_word_count(strtolower($post->post_content), 1);
        $word_freq = array_count_values($words);
        arsort($word_freq);
        
        // Get top keywords (excluding common words)
        $stop_words = array('the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should');
        
        $key_topics = array();
        foreach ($word_freq as $word => $freq) {
            if (strlen($word) > 3 && !in_array($word, $stop_words) && count($key_topics) < 10) {
                $key_topics[] = $word;
            }
        }
        
        $analysis['key_topics'] = $key_topics;
        
        return $analysis;
    }
    
    /**
     * Get video generation statistics
     */
    public function get_generation_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_video_requests';
        
        $stats = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_requests,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_requests,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_requests,
                AVG(CASE WHEN completed_at IS NOT NULL AND created_at IS NOT NULL 
                    THEN TIMESTAMPDIFF(MINUTE, created_at, completed_at) END) as avg_processing_time
            FROM {$table_name} 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            ARRAY_A
        );
        
        return $stats ?: array(
            'total_requests' => 0,
            'completed_requests' => 0,
            'processing_requests' => 0,
            'failed_requests' => 0,
            'avg_processing_time' => 0
        );
    }
    
    /**
     * Clean up old video files
     */
    public function cleanup_old_videos($days_old = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_video_requests';
        
        // Get old completed requests
        $old_requests = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} 
                WHERE status = 'completed' 
                AND completed_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days_old
            ),
            ARRAY_A
        );
        
        $cleaned_count = 0;
        
        foreach ($old_requests as $request) {
            if ($request['result_url']) {
                // Get attachment ID from URL
                $attachment_id = attachment_url_to_postid($request['result_url']);
                
                if ($attachment_id) {
                    wp_delete_attachment($attachment_id, true);
                    $cleaned_count++;
                }
            }
        }
        
        // Remove database records
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table_name} 
                WHERE status = 'completed' 
                AND completed_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days_old
            )
        );
        
        AI_Optimizer_Utils::log('Cleaned up old videos', 'info', array(
            'files_removed' => $cleaned_count,
            'days_old' => $days_old
        ));
        
        return $cleaned_count;
    }
}

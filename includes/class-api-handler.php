<?php
/**
 * API Handler for Siliconflow integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_API_Handler {
    
    private $api_key;
    private $base_url = 'https://api.siliconflow.cn';
    private $rate_limiter;
    
    public function __construct() {
        $this->api_key = AI_Optimizer_Settings::get_api_key();
        $this->rate_limiter = new AI_Optimizer_Rate_Limiter();
    }
    
    /**
     * Get user account information
     */
    public function get_user_info() {
        if (empty($this->api_key)) {
            return false;
        }
        
        $response = $this->make_request('GET', '/v1/user/info');
        
        if ($response && isset($response['id'])) {
            return $response;
        }
        
        return false;
    }
    
    /**
     * Get available models
     */
    public function get_models($type = null, $sub_type = null) {
        $params = array();
        
        if ($type) {
            $params['type'] = $type;
        }
        
        if ($sub_type) {
            $params['sub_type'] = $sub_type;
        }
        
        $endpoint = '/v1/models';
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }
        
        $response = $this->make_request('GET', $endpoint);
        
        if ($response && isset($response['data'])) {
            return $response['data'];
        }
        
        return array();
    }
    
    /**
     * Chat completion
     */
    public function chat_completion($prompt, $model = 'Qwen/QwQ-32B', $system_prompt = null) {
        $messages = array();
        
        if ($system_prompt) {
            $messages[] = array(
                'role' => 'system',
                'content' => $system_prompt
            );
        }
        
        $messages[] = array(
            'role' => 'user',
            'content' => $prompt
        );
        
        return $this->chat_completion_with_options($messages, $model);
    }
    
    /**
     * Chat completion with full options
     */
    public function chat_completion_with_options($messages, $model = 'Qwen/QwQ-32B', $options = array()) {
        $data = array_merge(array(
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => 2000,
            'temperature' => 0.7,
            'stream' => false
        ), $options);
        
        $response = $this->make_request('POST', '/v1/chat/completions', $data);
        
        if ($response && isset($response['choices'][0]['message']['content'])) {
            AI_Optimizer_Utils::log('Chat completion successful', 'info', array(
                'model' => $model,
                'tokens' => $response['usage']['total_tokens'] ?? 0
            ));
            
            return $response['choices'][0]['message']['content'];
        }
        
        AI_Optimizer_Utils::log('Chat completion failed', 'error', array(
            'model' => $model,
            'response' => $response
        ));
        
        return false;
    }
    
    /**
     * Generate embeddings
     */
    public function generate_embeddings($input, $model = 'BAAI/bge-large-zh-v1.5') {
        $data = array(
            'model' => $model,
            'input' => is_array($input) ? $input : array($input)
        );
        
        $response = $this->make_request('POST', '/v1/embeddings', $data);
        
        if ($response && isset($response['data'])) {
            return $response['data'];
        }
        
        return false;
    }
    
    /**
     * Rerank documents
     */
    public function rerank($query, $documents, $model = 'BAAI/bge-reranker-large') {
        $data = array(
            'model' => $model,
            'query' => $query,
            'documents' => $documents
        );
        
        $response = $this->make_request('POST', '/v1/rerank', $data);
        
        if ($response && isset($response['results'])) {
            return $response['results'];
        }
        
        return false;
    }
    
    /**
     * Generate image
     */
    public function generate_image($prompt, $model = 'stabilityai/stable-diffusion-xl-base-1.0', $options = array()) {
        $data = array_merge(array(
            'model' => $model,
            'prompt' => $prompt,
            'size' => '1024x1024',
            'quality' => 'standard',
            'n' => 1
        ), $options);
        
        $response = $this->make_request('POST', '/v1/images/generations', $data);
        
        if ($response && isset($response['data'][0]['url'])) {
            AI_Optimizer_Utils::log('Image generation successful', 'info', array(
                'model' => $model,
                'prompt' => $prompt
            ));
            
            return array(
                'url' => $response['data'][0]['url'],
                'revised_prompt' => $response['data'][0]['revised_prompt'] ?? $prompt
            );
        }
        
        AI_Optimizer_Utils::log('Image generation failed', 'error', array(
            'model' => $model,
            'prompt' => $prompt,
            'response' => $response
        ));
        
        return false;
    }
    
    /**
     * Submit video generation
     */
    public function submit_video_generation($prompt, $model = 'Lightricks/LTX-Video', $options = array()) {
        $data = array_merge(array(
            'model' => $model,
            'prompt' => $prompt,
            'duration' => 10
        ), $options);
        
        $response = $this->make_request('POST', '/v1/video/submit', $data);
        
        if ($response && isset($response['request_id'])) {
            AI_Optimizer_Utils::log('Video generation submitted', 'info', array(
                'model' => $model,
                'request_id' => $response['request_id']
            ));
            
            return $response['request_id'];
        }
        
        return false;
    }
    
    /**
     * Get video generation status
     */
    public function get_video_status($request_id) {
        $data = array(
            'request_id' => $request_id
        );
        
        $response = $this->make_request('POST', '/v1/video/status', $data);
        
        if ($response) {
            return $response;
        }
        
        return false;
    }
    
    /**
     * Text to speech
     */
    public function text_to_speech($text, $model = 'fishaudio/fish-speech-1.5', $options = array()) {
        $data = array_merge(array(
            'model' => $model,
            'input' => $text,
            'voice' => 'default',
            'speed' => 1.0
        ), $options);
        
        $response = $this->make_request('POST', '/v1/audio/speech', $data);
        
        if ($response && isset($response['url'])) {
            return array(
                'url' => $response['url']
            );
        }
        
        return false;
    }
    
    /**
     * Speech to text
     */
    public function speech_to_text($audio_file, $model = 'FunAudioLLM/SenseVoiceSmall') {
        $data = array(
            'model' => $model,
            'file' => $audio_file
        );
        
        $response = $this->make_request('POST', '/v1/audio/transcriptions', $data);
        
        if ($response && isset($response['text'])) {
            return $response['text'];
        }
        
        return false;
    }
    
    /**
     * Create batch job
     */
    public function create_batch($input_file_id, $endpoint, $completion_window = '24h') {
        $data = array(
            'input_file_id' => $input_file_id,
            'endpoint' => $endpoint,
            'completion_window' => $completion_window
        );
        
        $response = $this->make_request('POST', '/v1/batches', $data);
        
        if ($response && isset($response['id'])) {
            return $response;
        }
        
        return false;
    }
    
    /**
     * Get batch status
     */
    public function get_batch_status($batch_id) {
        $response = $this->make_request('GET', '/v1/batches/' . $batch_id);
        
        if ($response) {
            return $response;
        }
        
        return false;
    }
    
    /**
     * Cancel batch job
     */
    public function cancel_batch($batch_id) {
        $response = $this->make_request('POST', '/v1/batches/' . $batch_id . '/cancel');
        
        if ($response) {
            return $response;
        }
        
        return false;
    }
    
    /**
     * Upload file
     */
    public function upload_file($file_path, $purpose = 'batch') {
        $data = array(
            'file' => new CURLFile($file_path),
            'purpose' => $purpose
        );
        
        $response = $this->make_request('POST', '/v1/files', $data, array(
            'Content-Type' => 'multipart/form-data'
        ));
        
        if ($response && isset($response['id'])) {
            return $response;
        }
        
        return false;
    }
    
    /**
     * Make HTTP request to API
     */
    private function make_request($method, $endpoint, $data = null, $additional_headers = array()) {
        if (empty($this->api_key)) {
            AI_Optimizer_Utils::log('API key not configured', 'error');
            return false;
        }
        
        // Check rate limiting
        if (!$this->rate_limiter->check()) {
            AI_Optimizer_Utils::log('Rate limit exceeded', 'warning');
            return false;
        }
        
        $url = $this->base_url . $endpoint;
        
        $headers = array_merge(array(
            'Authorization' => 'Bearer ' . $this->api_key,
            'Content-Type' => 'application/json',
            'User-Agent' => 'AI-Website-Optimizer/' . AI_OPTIMIZER_VERSION
        ), $additional_headers);
        
        $args = array(
            'method' => $method,
            'headers' => $headers,
            'timeout' => 60,
            'user-agent' => 'AI-Website-Optimizer/' . AI_OPTIMIZER_VERSION
        );
        
        if ($data && $method !== 'GET') {
            if (isset($headers['Content-Type']) && $headers['Content-Type'] === 'multipart/form-data') {
                unset($headers['Content-Type']); // Let WordPress handle multipart
                $args['body'] = $data;
            } else {
                $args['body'] = json_encode($data);
            }
        }
        
        AI_Optimizer_Utils::log('Making API request', 'debug', array(
            'method' => $method,
            'endpoint' => $endpoint,
            'data_size' => $data ? strlen(json_encode($data)) : 0
        ));
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            AI_Optimizer_Utils::log('API request error', 'error', array(
                'error' => $response->get_error_message(),
                'endpoint' => $endpoint
            ));
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code >= 200 && $status_code < 300) {
            $decoded = json_decode($body, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                AI_Optimizer_Utils::log('API request successful', 'debug', array(
                    'endpoint' => $endpoint,
                    'status_code' => $status_code
                ));
                return $decoded;
            } else {
                AI_Optimizer_Utils::log('API response JSON decode error', 'error', array(
                    'endpoint' => $endpoint,
                    'json_error' => json_last_error_msg()
                ));
                return false;
            }
        } else {
            AI_Optimizer_Utils::log('API request failed', 'error', array(
                'endpoint' => $endpoint,
                'status_code' => $status_code,
                'response' => $body
            ));
            return false;
        }
    }
    
    /**
     * Get API usage statistics
     */
    public function get_usage_stats() {
        // This would depend on if Siliconflow provides usage endpoints
        // For now, we'll track locally
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_api_usage';
        
        $stats = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_requests,
                SUM(CASE WHEN endpoint LIKE '%chat%' THEN 1 ELSE 0 END) as chat_requests,
                SUM(CASE WHEN endpoint LIKE '%image%' THEN 1 ELSE 0 END) as image_requests,
                SUM(CASE WHEN endpoint LIKE '%video%' THEN 1 ELSE 0 END) as video_requests
            FROM {$table_name} 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            ARRAY_A
        );
        
        return $stats ?: array(
            'total_requests' => 0,
            'successful_requests' => 0,
            'chat_requests' => 0,
            'image_requests' => 0,
            'video_requests' => 0
        );
    }
    
    /**
     * Log API usage
     */
    private function log_api_usage($endpoint, $status, $response_time = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ai_optimizer_api_usage';
        
        $wpdb->insert(
            $table_name,
            array(
                'endpoint' => $endpoint,
                'status' => $status,
                'response_time' => $response_time,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%f', '%s')
        );
    }
}

/**
 * Rate limiter class
 */
class AI_Optimizer_Rate_Limiter {
    
    private $cache_key = 'ai_optimizer_rate_limit';
    private $limit;
    private $window;
    
    public function __construct($limit = null, $window = 60) {
        $this->limit = $limit ?: AI_Optimizer_Settings::get('rate_limit', 60);
        $this->window = $window;
    }
    
    public function check() {
        $current_time = time();
        $window_start = $current_time - $this->window;
        
        $requests = get_transient($this->cache_key) ?: array();
        
        // Clean old requests
        $requests = array_filter($requests, function($timestamp) use ($window_start) {
            return $timestamp > $window_start;
        });
        
        if (count($requests) >= $this->limit) {
            return false;
        }
        
        // Add current request
        $requests[] = $current_time;
        set_transient($this->cache_key, $requests, $this->window);
        
        return true;
    }
    
    public function get_remaining() {
        $requests = get_transient($this->cache_key) ?: array();
        return max(0, $this->limit - count($requests));
    }
    
    public function reset() {
        delete_transient($this->cache_key);
    }
}

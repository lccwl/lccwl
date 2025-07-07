<?php
/**
 * API endpoints configuration for Siliconflow integration
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Siliconflow API endpoints configuration
 */
class AI_Optimizer_API_Endpoints {
    
    const BASE_URL = 'https://api.siliconflow.cn';
    
    /**
     * User and account management endpoints
     */
    const USER_INFO = '/v1/user/info';
    
    /**
     * Model management endpoints
     */
    const MODELS_LIST = '/v1/models';
    
    /**
     * Chat completion endpoints
     */
    const CHAT_COMPLETIONS = '/v1/chat/completions';
    
    /**
     * Text processing endpoints
     */
    const EMBEDDINGS = '/v1/embeddings';
    const RERANK = '/v1/rerank';
    
    /**
     * Image generation endpoints
     */
    const IMAGE_GENERATIONS = '/v1/images/generations';
    
    /**
     * Video generation endpoints
     */
    const VIDEO_SUBMIT = '/v1/video/submit';
    const VIDEO_STATUS = '/v1/video/status';
    
    /**
     * Audio processing endpoints
     */
    const AUDIO_SPEECH = '/v1/audio/speech';
    const AUDIO_TRANSCRIPTIONS = '/v1/audio/transcriptions';
    
    /**
     * Batch processing endpoints
     */
    const BATCHES = '/v1/batches';
    const BATCH_CANCEL = '/v1/batches/{batch_id}/cancel';
    const BATCH_STATUS = '/v1/batches/{batch_id}';
    
    /**
     * File management endpoints
     */
    const FILES = '/v1/files';
    const FILES_LIST = '/v1/files';
    
    /**
     * Available models configuration
     */
    public static function get_available_models() {
        return array(
            'chat' => array(
                'Qwen/QwQ-32B' => array(
                    'name' => 'Qwen QwQ 32B',
                    'description' => 'Advanced reasoning and problem-solving model',
                    'max_tokens' => 32768,
                    'use_cases' => array('analysis', 'problem_solving', 'code_review')
                ),
                'Qwen/Qwen2.5-72B-Instruct' => array(
                    'name' => 'Qwen 2.5 72B Instruct',
                    'description' => 'Large instruction-following model',
                    'max_tokens' => 8192,
                    'use_cases' => array('content_generation', 'analysis', 'qa')
                ),
                'meta-llama/Meta-Llama-3.1-8B-Instruct' => array(
                    'name' => 'Llama 3.1 8B Instruct',
                    'description' => 'Efficient instruction-following model',
                    'max_tokens' => 8192,
                    'use_cases' => array('content_generation', 'summarization')
                )
            ),
            'embedding' => array(
                'BAAI/bge-large-zh-v1.5' => array(
                    'name' => 'BGE Large Chinese',
                    'description' => 'Chinese text embedding model',
                    'dimensions' => 1024,
                    'use_cases' => array('semantic_search', 'similarity')
                ),
                'BAAI/bge-large-en-v1.5' => array(
                    'name' => 'BGE Large English',
                    'description' => 'English text embedding model',
                    'dimensions' => 1024,
                    'use_cases' => array('semantic_search', 'similarity')
                )
            ),
            'rerank' => array(
                'BAAI/bge-reranker-large' => array(
                    'name' => 'BGE Reranker Large',
                    'description' => 'Text reranking model',
                    'use_cases' => array('search_ranking', 'document_ranking')
                )
            ),
            'image' => array(
                'stabilityai/stable-diffusion-xl-base-1.0' => array(
                    'name' => 'Stable Diffusion XL',
                    'description' => 'High-quality image generation',
                    'sizes' => array('512x512', '1024x1024', '1024x768', '768x1024'),
                    'use_cases' => array('content_creation', 'marketing', 'illustration')
                ),
                'black-forest-labs/FLUX.1-schnell' => array(
                    'name' => 'FLUX.1 Schnell',
                    'description' => 'Fast image generation model',
                    'sizes' => array('512x512', '1024x1024'),
                    'use_cases' => array('rapid_prototyping', 'concept_art')
                )
            ),
            'video' => array(
                'Lightricks/LTX-Video' => array(
                    'name' => 'LTX Video',
                    'description' => 'Text-to-video generation',
                    'max_duration' => 30,
                    'resolutions' => array('512x512', '768x768'),
                    'use_cases' => array('content_creation', 'marketing_videos')
                )
            ),
            'audio' => array(
                'fishaudio/fish-speech-1.5' => array(
                    'name' => 'Fish Speech 1.5',
                    'description' => 'Text-to-speech synthesis',
                    'languages' => array('en', 'zh', 'ja', 'ko'),
                    'use_cases' => array('voiceover', 'accessibility', 'content_creation')
                ),
                'FunAudioLLM/SenseVoiceSmall' => array(
                    'name' => 'SenseVoice Small',
                    'description' => 'Speech-to-text transcription',
                    'languages' => array('en', 'zh', 'ja', 'ko'),
                    'use_cases' => array('transcription', 'subtitles')
                )
            )
        );
    }
    
    /**
     * Model recommendations for specific use cases
     */
    public static function get_recommended_models() {
        return array(
            'seo_analysis' => array(
                'primary' => 'Qwen/QwQ-32B',
                'fallback' => 'Qwen/Qwen2.5-72B-Instruct'
            ),
            'content_generation' => array(
                'primary' => 'Qwen/Qwen2.5-72B-Instruct',
                'fallback' => 'meta-llama/Meta-Llama-3.1-8B-Instruct'
            ),
            'code_analysis' => array(
                'primary' => 'Qwen/QwQ-32B',
                'fallback' => 'Qwen/Qwen2.5-72B-Instruct'
            ),
            'content_rewriting' => array(
                'primary' => 'Qwen/Qwen2.5-72B-Instruct',
                'fallback' => 'meta-llama/Meta-Llama-3.1-8B-Instruct'
            ),
            'image_generation' => array(
                'primary' => 'stabilityai/stable-diffusion-xl-base-1.0',
                'fallback' => 'black-forest-labs/FLUX.1-schnell'
            ),
            'video_generation' => array(
                'primary' => 'Lightricks/LTX-Video'
            ),
            'text_to_speech' => array(
                'primary' => 'fishaudio/fish-speech-1.5'
            ),
            'speech_to_text' => array(
                'primary' => 'FunAudioLLM/SenseVoiceSmall'
            ),
            'text_embedding' => array(
                'english' => 'BAAI/bge-large-en-v1.5',
                'chinese' => 'BAAI/bge-large-zh-v1.5'
            ),
            'text_reranking' => array(
                'primary' => 'BAAI/bge-reranker-large'
            )
        );
    }
    
    /**
     * API rate limits and constraints
     */
    public static function get_rate_limits() {
        return array(
            'default' => array(
                'requests_per_minute' => 60,
                'requests_per_hour' => 1000,
                'requests_per_day' => 10000
            ),
            'chat_completions' => array(
                'requests_per_minute' => 30,
                'tokens_per_minute' => 100000
            ),
            'image_generations' => array(
                'requests_per_minute' => 10,
                'requests_per_hour' => 100
            ),
            'video_generations' => array(
                'requests_per_minute' => 5,
                'requests_per_hour' => 20,
                'requests_per_day' => 50
            ),
            'audio_processing' => array(
                'requests_per_minute' => 20,
                'requests_per_hour' => 200
            ),
            'batch_processing' => array(
                'requests_per_minute' => 5,
                'max_file_size_mb' => 100
            )
        );
    }
    
    /**
     * Error codes and messages
     */
    public static function get_error_codes() {
        return array(
            400 => 'Bad Request - Invalid parameters',
            401 => 'Unauthorized - Invalid API key',
            402 => 'Payment Required - Insufficient credits',
            403 => 'Forbidden - Access denied',
            404 => 'Not Found - Endpoint or resource not found',
            422 => 'Unprocessable Entity - Invalid input data',
            429 => 'Too Many Requests - Rate limit exceeded',
            500 => 'Internal Server Error - API server error',
            502 => 'Bad Gateway - API server temporarily unavailable',
            503 => 'Service Unavailable - API server overloaded',
            504 => 'Gateway Timeout - API server timeout'
        );
    }
    
    /**
     * Request timeout configurations
     */
    public static function get_timeout_config() {
        return array(
            'default' => 30,
            'chat_completions' => 60,
            'image_generations' => 120,
            'video_generations' => 300,
            'audio_processing' => 90,
            'file_upload' => 180,
            'batch_processing' => 600
        );
    }
    
    /**
     * Content type mappings
     */
    public static function get_content_types() {
        return array(
            'json' => 'application/json',
            'multipart' => 'multipart/form-data',
            'text' => 'text/plain',
            'audio' => 'audio/mpeg',
            'image' => 'image/png',
            'video' => 'video/mp4'
        );
    }
    
    /**
     * Supported file formats
     */
    public static function get_supported_formats() {
        return array(
            'audio_input' => array('mp3', 'wav', 'flac', 'm4a', 'ogg'),
            'audio_output' => array('mp3', 'wav'),
            'image_output' => array('png', 'jpg', 'jpeg', 'webp'),
            'video_output' => array('mp4', 'webm'),
            'text_input' => array('txt', 'json', 'csv'),
            'batch_input' => array('jsonl')
        );
    }
    
    /**
     * Default parameters for different API calls
     */
    public static function get_default_parameters() {
        return array(
            'chat_completions' => array(
                'temperature' => 0.7,
                'max_tokens' => 2000,
                'top_p' => 0.9,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
                'stream' => false
            ),
            'image_generations' => array(
                'size' => '1024x1024',
                'quality' => 'standard',
                'n' => 1,
                'response_format' => 'url'
            ),
            'video_generations' => array(
                'duration' => 10,
                'fps' => 24,
                'resolution' => '512x512'
            ),
            'audio_speech' => array(
                'voice' => 'default',
                'speed' => 1.0,
                'response_format' => 'mp3'
            ),
            'audio_transcriptions' => array(
                'language' => 'auto',
                'response_format' => 'text'
            ),
            'embeddings' => array(
                'encoding_format' => 'float'
            )
        );
    }
    
    /**
     * Build full URL for endpoint
     */
    public static function build_url($endpoint, $params = array()) {
        $url = self::BASE_URL . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * Get headers for API request
     */
    public static function get_headers($api_key, $content_type = 'application/json') {
        return array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => $content_type,
            'User-Agent' => 'AI-Website-Optimizer/' . AI_OPTIMIZER_VERSION
        );
    }
    
    /**
     * Validate endpoint exists
     */
    public static function is_valid_endpoint($endpoint) {
        $valid_endpoints = array(
            self::USER_INFO,
            self::MODELS_LIST,
            self::CHAT_COMPLETIONS,
            self::EMBEDDINGS,
            self::RERANK,
            self::IMAGE_GENERATIONS,
            self::VIDEO_SUBMIT,
            self::VIDEO_STATUS,
            self::AUDIO_SPEECH,
            self::AUDIO_TRANSCRIPTIONS,
            self::BATCHES,
            self::FILES
        );
        
        return in_array($endpoint, $valid_endpoints);
    }
    
    /**
     * Get endpoint category
     */
    public static function get_endpoint_category($endpoint) {
        $categories = array(
            'user' => array(self::USER_INFO),
            'models' => array(self::MODELS_LIST),
            'chat' => array(self::CHAT_COMPLETIONS),
            'text' => array(self::EMBEDDINGS, self::RERANK),
            'image' => array(self::IMAGE_GENERATIONS),
            'video' => array(self::VIDEO_SUBMIT, self::VIDEO_STATUS),
            'audio' => array(self::AUDIO_SPEECH, self::AUDIO_TRANSCRIPTIONS),
            'batch' => array(self::BATCHES),
            'files' => array(self::FILES)
        );
        
        foreach ($categories as $category => $endpoints) {
            if (in_array($endpoint, $endpoints)) {
                return $category;
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Get cost estimates (placeholder values - replace with actual pricing)
     */
    public static function get_cost_estimates() {
        return array(
            'chat_completions' => array(
                'input_tokens' => 0.001,  // per 1K tokens
                'output_tokens' => 0.002  // per 1K tokens
            ),
            'image_generations' => array(
                'standard' => 0.04,  // per image
                'hd' => 0.08        // per image
            ),
            'video_generations' => array(
                'per_second' => 0.1  // per second of video
            ),
            'audio_speech' => array(
                'per_character' => 0.000015  // per character
            ),
            'audio_transcriptions' => array(
                'per_minute' => 0.006  // per minute
            ),
            'embeddings' => array(
                'per_1k_tokens' => 0.0001  // per 1K tokens
            )
        );
    }
}

/**
 * API endpoint utility functions
 */
class AI_Optimizer_API_Utils {
    
    /**
     * Calculate estimated cost for API call
     */
    public static function estimate_cost($endpoint, $params = array()) {
        $costs = AI_Optimizer_API_Endpoints::get_cost_estimates();
        $category = AI_Optimizer_API_Endpoints::get_endpoint_category($endpoint);
        
        switch ($category) {
            case 'chat':
                $tokens = self::estimate_tokens($params['messages'] ?? array());
                return ($tokens / 1000) * $costs['chat_completions']['input_tokens'];
                
            case 'image':
                $quality = $params['quality'] ?? 'standard';
                return $costs['image_generations'][$quality] ?? $costs['image_generations']['standard'];
                
            case 'video':
                $duration = $params['duration'] ?? 10;
                return $duration * $costs['video_generations']['per_second'];
                
            case 'audio':
                if ($endpoint === AI_Optimizer_API_Endpoints::AUDIO_SPEECH) {
                    $text_length = strlen($params['input'] ?? '');
                    return $text_length * $costs['audio_speech']['per_character'];
                }
                break;
                
            default:
                return 0;
        }
        
        return 0;
    }
    
    /**
     * Estimate token count for text
     */
    public static function estimate_tokens($messages) {
        $total_text = '';
        
        if (is_array($messages)) {
            foreach ($messages as $message) {
                $total_text .= $message['content'] ?? '';
            }
        } else {
            $total_text = (string) $messages;
        }
        
        // Rough estimation: 1 token ≈ 0.75 words
        $word_count = str_word_count($total_text);
        return ceil($word_count / 0.75);
    }
    
    /**
     * Format API error message
     */
    public static function format_error($error_code, $error_message = '') {
        $error_codes = AI_Optimizer_API_Endpoints::get_error_codes();
        $standard_message = $error_codes[$error_code] ?? 'Unknown error';
        
        if (!empty($error_message)) {
            return $standard_message . ': ' . $error_message;
        }
        
        return $standard_message;
    }
    
    /**
     * Check if model supports feature
     */
    public static function model_supports_feature($model, $feature) {
        $models = AI_Optimizer_API_Endpoints::get_available_models();
        
        foreach ($models as $category => $category_models) {
            if (isset($category_models[$model])) {
                $use_cases = $category_models[$model]['use_cases'] ?? array();
                return in_array($feature, $use_cases);
            }
        }
        
        return false;
    }
    
    /**
     * Get optimal model for use case
     */
    public static function get_optimal_model($use_case) {
        $recommendations = AI_Optimizer_API_Endpoints::get_recommended_models();
        
        if (isset($recommendations[$use_case])) {
            return $recommendations[$use_case]['primary'];
        }
        
        // Default fallback
        return 'Qwen/Qwen2.5-72B-Instruct';
    }
    
    /**
     * Validate request parameters
     */
    public static function validate_parameters($endpoint, $params) {
        $defaults = AI_Optimizer_API_Endpoints::get_default_parameters();
        $category = AI_Optimizer_API_Endpoints::get_endpoint_category($endpoint);
        
        $errors = array();
        
        switch ($category) {
            case 'chat':
                if (empty($params['messages'])) {
                    $errors[] = 'Messages parameter is required';
                }
                if (isset($params['max_tokens']) && ($params['max_tokens'] < 1 || $params['max_tokens'] > 32768)) {
                    $errors[] = 'Max tokens must be between 1 and 32768';
                }
                break;
                
            case 'image':
                if (empty($params['prompt'])) {
                    $errors[] = 'Prompt parameter is required';
                }
                if (isset($params['n']) && ($params['n'] < 1 || $params['n'] > 10)) {
                    $errors[] = 'Number of images must be between 1 and 10';
                }
                break;
                
            case 'video':
                if (empty($params['prompt'])) {
                    $errors[] = 'Prompt parameter is required';
                }
                if (isset($params['duration']) && ($params['duration'] < 1 || $params['duration'] > 30)) {
                    $errors[] = 'Duration must be between 1 and 30 seconds';
                }
                break;
        }
        
        return array(
            'valid' => empty($errors),
            'errors' => $errors
        );
    }
}

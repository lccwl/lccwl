<?php
/**
 * API端点配置
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Siliconflow API端点配置
 */
class AI_Optimizer_API_Endpoints {
    
    /**
     * API基础URL
     */
    const BASE_URL = 'https://api.siliconflow.cn';
    
    /**
     * 聊天完成端点
     */
    const CHAT_COMPLETIONS = '/v1/chat/completions';
    
    /**
     * 图片生成端点
     */
    const IMAGE_GENERATION = '/v1/images/generations';
    
    /**
     * 视频生成端点
     */
    const VIDEO_GENERATION = '/v1/video/submit';
    const VIDEO_STATUS = '/v1/video/status';
    
    /**
     * 音频处理端点
     */
    const AUDIO_TRANSCRIPTION = '/v1/audio/transcriptions';
    const AUDIO_SPEECH = '/v1/audio/speech';
    
    /**
     * 文本处理端点
     */
    const EMBEDDINGS = '/v1/embeddings';
    const RERANK = '/v1/rerank';
    
    /**
     * 可用模型配置
     */
    public static function get_chat_models() {
        return array(
            'Qwen/QwQ-32B-Preview' => array(
                'name' => 'Qwen QwQ 32B Preview',
                'description' => '具有强大推理能力的大型语言模型',
                'max_tokens' => 32768,
                'cost_per_1k_tokens' => 0.0007
            ),
            'Qwen/Qwen2.5-72B-Instruct' => array(
                'name' => 'Qwen 2.5 72B Instruct',
                'description' => '优化的指令遵循模型',
                'max_tokens' => 32768,
                'cost_per_1k_tokens' => 0.0007
            ),
            'meta-llama/Meta-Llama-3.1-8B-Instruct' => array(
                'name' => 'Llama 3.1 8B Instruct',
                'description' => 'Meta开发的高效指令模型',
                'max_tokens' => 8192,
                'cost_per_1k_tokens' => 0.0007
            ),
            'meta-llama/Meta-Llama-3.1-70B-Instruct' => array(
                'name' => 'Llama 3.1 70B Instruct',
                'description' => 'Meta开发的大型指令模型',
                'max_tokens' => 8192,
                'cost_per_1k_tokens' => 0.0007
            )
        );
    }
    
    /**
     * 图片生成模型配置
     */
    public static function get_image_models() {
        return array(
            'stabilityai/stable-diffusion-xl-base-1.0' => array(
                'name' => 'Stable Diffusion XL',
                'description' => '高质量图片生成模型',
                'max_resolution' => '1024x1024',
                'cost_per_image' => 0.02
            ),
            'black-forest-labs/FLUX.1-schnell' => array(
                'name' => 'FLUX.1 Schnell',
                'description' => '快速图片生成模型',
                'max_resolution' => '1024x1024',
                'cost_per_image' => 0.015
            )
        );
    }
    
    /**
     * 视频生成模型配置
     */
    public static function get_video_models() {
        return array(
            'ltx-video' => array(
                'name' => 'LTX Video',
                'description' => '文本到视频生成模型',
                'max_duration' => 15,
                'resolution' => '768x768',
                'cost_per_second' => 0.05
            )
        );
    }
    
    /**
     * 音频模型配置
     */
    public static function get_audio_models() {
        return array(
            'tts' => array(
                'name' => '文本转语音',
                'description' => '高质量语音合成',
                'languages' => array('zh', 'en', 'ja', 'ko'),
                'cost_per_minute' => 0.01
            ),
            'whisper' => array(
                'name' => '语音识别',
                'description' => '多语言语音转文本',
                'languages' => array('zh', 'en', 'ja', 'ko'),
                'cost_per_minute' => 0.006
            )
        );
    }
    
    /**
     * 获取完整端点URL
     */
    public static function get_endpoint_url($endpoint) {
        return self::BASE_URL . $endpoint;
    }
    
    /**
     * 获取默认请求头
     */
    public static function get_default_headers($api_key) {
        return array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
            'User-Agent' => 'AI-Website-Optimizer/' . AI_OPTIMIZER_VERSION
        );
    }
    
    /**
     * 获取API速率限制
     */
    public static function get_rate_limits() {
        return array(
            'requests_per_minute' => 60,
            'requests_per_day' => 1000,
            'tokens_per_minute' => 100000,
            'images_per_minute' => 10,
            'videos_per_hour' => 5
        );
    }
    
    /**
     * 验证模型可用性
     */
    public static function validate_model($model, $type = 'chat') {
        switch ($type) {
            case 'chat':
                return array_key_exists($model, self::get_chat_models());
            case 'image':
                return array_key_exists($model, self::get_image_models());
            case 'video':
                return array_key_exists($model, self::get_video_models());
            case 'audio':
                return array_key_exists($model, self::get_audio_models());
            default:
                return false;
        }
    }
    
    /**
     * 获取模型信息
     */
    public static function get_model_info($model, $type = 'chat') {
        switch ($type) {
            case 'chat':
                $models = self::get_chat_models();
                break;
            case 'image':
                $models = self::get_image_models();
                break;
            case 'video':
                $models = self::get_video_models();
                break;
            case 'audio':
                $models = self::get_audio_models();
                break;
            default:
                return null;
        }
        
        return isset($models[$model]) ? $models[$model] : null;
    }
    
    /**
     * 估算API调用成本
     */
    public static function estimate_cost($model, $type, $usage) {
        $model_info = self::get_model_info($model, $type);
        
        if (!$model_info) {
            return 0;
        }
        
        switch ($type) {
            case 'chat':
                $tokens = isset($usage['tokens']) ? $usage['tokens'] : 1000;
                return ($tokens / 1000) * $model_info['cost_per_1k_tokens'];
                
            case 'image':
                $images = isset($usage['images']) ? $usage['images'] : 1;
                return $images * $model_info['cost_per_image'];
                
            case 'video':
                $duration = isset($usage['duration']) ? $usage['duration'] : 5;
                return $duration * $model_info['cost_per_second'];
                
            case 'audio':
                $minutes = isset($usage['minutes']) ? $usage['minutes'] : 1;
                return $minutes * $model_info['cost_per_minute'];
                
            default:
                return 0;
        }
    }
    
    /**
     * 构建聊天完成请求参数
     */
    public static function build_chat_request($messages, $options = array()) {
        $defaults = array(
            'model' => 'Qwen/Qwen2.5-72B-Instruct',
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'top_p' => 0.9,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'stream' => false
        );
        
        $params = array_merge($defaults, $options);
        $params['messages'] = $messages;
        
        return $params;
    }
    
    /**
     * 构建图片生成请求参数
     */
    public static function build_image_request($prompt, $options = array()) {
        $defaults = array(
            'model' => 'stabilityai/stable-diffusion-xl-base-1.0',
            'prompt' => $prompt,
            'num_images' => 1,
            'size' => '1024x1024',
            'quality' => 'standard',
            'style' => 'vivid'
        );
        
        return array_merge($defaults, $options);
    }
    
    /**
     * 构建视频生成请求参数
     */
    public static function build_video_request($prompt, $options = array()) {
        $defaults = array(
            'model' => 'ltx-video',
            'prompt' => $prompt,
            'num_frames' => 121,
            'height' => 768,
            'width' => 768,
            'num_inference_steps' => 50
        );
        
        return array_merge($defaults, $options);
    }
    
    /**
     * 构建音频请求参数
     */
    public static function build_audio_request($input, $type = 'tts', $options = array()) {
        if ($type === 'tts') {
            $defaults = array(
                'model' => 'tts',
                'input' => $input,
                'voice' => 'default',
                'speed' => 1.0
            );
        } else {
            $defaults = array(
                'model' => 'whisper',
                'file' => $input,
                'language' => 'zh'
            );
        }
        
        return array_merge($defaults, $options);
    }
}
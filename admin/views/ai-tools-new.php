<?php
/**
 * 新版AI工具页面
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap ai-optimizer-wrap">
    <h1>🤖 AI智能内容生成工具</h1>
    
    <!-- API配置检查 -->
    <div class="ai-optimizer-card">
        <h2>🔧 API配置状态</h2>
        <div class="api-status-grid">
            <div class="api-status-item">
                <div class="api-icon">🔑</div>
                <div class="api-content">
                    <div class="api-label">Siliconflow API</div>
                    <div class="api-status" id="siliconflow-status">检查中...</div>
                </div>
                <button type="button" id="test-siliconflow" class="button button-small">测试连接</button>
            </div>
            <div class="api-status-item">
                <div class="api-icon">🌐</div>
                <div class="api-content">
                    <div class="api-label">自定义API</div>
                    <div class="api-status" id="custom-api-status">未配置</div>
                </div>
                <button type="button" id="configure-custom-api" class="button button-small">配置</button>
            </div>
        </div>
        
        <!-- 自定义API配置面板 -->
        <div id="custom-api-panel" class="custom-api-panel" style="display: none;">
            <h3>自定义API配置</h3>
            <table class="form-table">
                <tr>
                    <th><label for="custom_api_name">API名称</label></th>
                    <td><input type="text" id="custom_api_name" placeholder="OpenAI API" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="custom_api_endpoint">API端点</label></th>
                    <td><input type="url" id="custom_api_endpoint" placeholder="https://api.openai.com/v1/chat/completions" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="custom_api_key">API密钥</label></th>
                    <td><input type="password" id="custom_api_key" placeholder="sk-..." class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="custom_api_model">默认模型</label></th>
                    <td><input type="text" id="custom_api_model" placeholder="gpt-3.5-turbo" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="custom_api_type">API类型</label></th>
                    <td>
                        <select id="custom_api_type">
                            <option value="openai">OpenAI兼容</option>
                            <option value="claude">Claude API</option>
                            <option value="huggingface">HuggingFace</option>
                            <option value="custom">自定义格式</option>
                        </select>
                    </td>
                </tr>
            </table>
            <div class="custom-api-actions">
                <button type="button" id="save-custom-api" class="button button-primary">保存配置</button>
                <button type="button" id="test-custom-api" class="button button-secondary">测试连接</button>
                <button type="button" id="cancel-custom-api" class="button">取消</button>
            </div>
        </div>
    </div>
    
    <!-- 内容生成工具 -->
    <div class="ai-optimizer-card">
        <h2>📝 内容生成工具</h2>
        
        <!-- 生成类型选择 -->
        <div class="generation-type-tabs">
            <button class="type-tab active" data-type="text">
                <span class="tab-icon">📝</span>
                <span class="tab-label">文本生成</span>
            </button>
            <button class="type-tab" data-type="image">
                <span class="tab-icon">🖼️</span>
                <span class="tab-label">图片生成</span>
            </button>
            <button class="type-tab" data-type="video">
                <span class="tab-icon">🎬</span>
                <span class="tab-label">视频生成</span>
            </button>
            <button class="type-tab" data-type="audio">
                <span class="tab-icon">🎵</span>
                <span class="tab-label">音频生成</span>
            </button>
            <button class="type-tab" data-type="code">
                <span class="tab-icon">💻</span>
                <span class="tab-label">代码生成</span>
            </button>
        </div>
        
        <!-- 文本生成面板 -->
        <div id="text-generation" class="generation-panel active">
            <form class="generation-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="text-model">AI模型选择</label>
                        <select id="text-model" name="model">
                            <option value="Qwen/QwQ-32B-Preview">Qwen/QwQ-32B (推荐)</option>
                            <option value="Qwen/Qwen2.5-7B-Instruct">Qwen2.5-7B (快速)</option>
                            <option value="meta-llama/Meta-Llama-3.1-8B-Instruct">Meta-Llama-3.1-8B</option>
                            <option value="deepseek-ai/DeepSeek-V2.5">DeepSeek-V2.5</option>
                            <option value="custom">自定义模型</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="text-type">内容类型</label>
                        <select id="text-type" name="content_type">
                            <option value="article">博客文章</option>
                            <option value="product">产品描述</option>
                            <option value="social">社交媒体</option>
                            <option value="email">邮件内容</option>
                            <option value="seo">SEO内容</option>
                            <option value="technical">技术文档</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="text-length">内容长度</label>
                        <select id="text-length" name="length">
                            <option value="short">短文本 (100-300字)</option>
                            <option value="medium">中等长度 (300-800字)</option>
                            <option value="long">长文本 (800-1500字)</option>
                            <option value="very_long">超长文本 (1500+字)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="text-language">语言</label>
                        <select id="text-language" name="language">
                            <option value="zh">中文</option>
                            <option value="en">English</option>
                            <option value="ja">日本語</option>
                            <option value="ko">한국어</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="text-prompt">内容描述</label>
                    <textarea id="text-prompt" name="prompt" rows="4" placeholder="请详细描述您想要生成的内容..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="text-keywords">关键词 (可选)</label>
                    <input type="text" id="text-keywords" name="keywords" placeholder="关键词1, 关键词2, 关键词3">
                </div>
                
                <div class="form-group">
                    <label for="text-tone">语调风格</label>
                    <select id="text-tone" name="tone">
                        <option value="professional">专业</option>
                        <option value="friendly">友好</option>
                        <option value="casual">随意</option>
                        <option value="formal">正式</option>
                        <option value="humorous">幽默</option>
                        <option value="persuasive">说服性</option>
                    </select>
                </div>
                
                <div class="generation-actions">
                    <button type="submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-admin-post"></span>
                        生成文本内容
                    </button>
                    <button type="button" class="button button-secondary save-template">
                        <span class="dashicons dashicons-saved"></span>
                        保存为模板
                    </button>
                </div>
            </form>
        </div>
        
        <!-- 图片生成面板 -->
        <div id="image-generation" class="generation-panel">
            <form class="generation-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="image-model">AI模型选择</label>
                        <select id="image-model" name="model">
                            <option value="stabilityai/stable-diffusion-xl-base-1.0">Stable Diffusion XL (推荐)</option>
                            <option value="stabilityai/stable-diffusion-2-1">Stable Diffusion 2.1</option>
                            <option value="runwayml/stable-diffusion-v1-5">Stable Diffusion 1.5</option>
                            <option value="custom">自定义模型</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image-size">图片尺寸</label>
                        <select id="image-size" name="size">
                            <option value="512x512">512×512 (正方形)</option>
                            <option value="1024x1024">1024×1024 (高清正方形)</option>
                            <option value="1024x768">1024×768 (横向)</option>
                            <option value="768x1024">768×1024 (竖向)</option>
                            <option value="1536x1024">1536×1024 (宽屏)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image-style">艺术风格</label>
                        <select id="image-style" name="style">
                            <option value="realistic">写实风格</option>
                            <option value="digital_art">数字艺术</option>
                            <option value="oil_painting">油画风格</option>
                            <option value="watercolor">水彩画</option>
                            <option value="cartoon">卡通风格</option>
                            <option value="anime">动漫风格</option>
                            <option value="minimalist">极简风格</option>
                            <option value="vintage">复古风格</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image-quality">图片质量</label>
                        <select id="image-quality" name="quality">
                            <option value="standard">标准质量</option>
                            <option value="high">高质量</option>
                            <option value="ultra">超高质量</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image-prompt">图片描述</label>
                    <textarea id="image-prompt" name="prompt" rows="4" placeholder="请详细描述您想要生成的图片..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="negative-prompt">负面提示词 (可选)</label>
                    <textarea id="negative-prompt" name="negative_prompt" rows="2" placeholder="描述您不想在图片中出现的内容..."></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="image-steps">生成步数</label>
                        <select id="image-steps" name="steps">
                            <option value="20">20步 (快速)</option>
                            <option value="50" selected>50步 (推荐)</option>
                            <option value="100">100步 (精细)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image-seed">随机种子 (可选)</label>
                        <input type="number" id="image-seed" name="seed" placeholder="留空随机生成">
                    </div>
                </div>
                
                <div class="generation-actions">
                    <button type="submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-format-image"></span>
                        生成图片
                    </button>
                    <button type="button" class="button button-secondary save-template">
                        <span class="dashicons dashicons-saved"></span>
                        保存为模板
                    </button>
                </div>
            </form>
        </div>
        
        <!-- 视频生成面板 -->
        <div id="video-generation" class="generation-panel">
            <form class="generation-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="video-model">AI模型选择</label>
                        <select id="video-model" name="model">
                            <option value="Lightricks/LTX-Video">LTX-Video (推荐)</option>
                            <option value="ali-vilab/i2vgen-xl">I2VGen-XL</option>
                            <option value="damo-vilab/text-to-video-ms-1.7b">Text-to-Video MS</option>
                            <option value="custom">自定义模型</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="video-duration">视频时长</label>
                        <select id="video-duration" name="duration">
                            <option value="3">3秒</option>
                            <option value="5" selected>5秒</option>
                            <option value="10">10秒</option>
                            <option value="15">15秒</option>
                            <option value="30">30秒</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="video-fps">帧率</label>
                        <select id="video-fps" name="fps">
                            <option value="24">24 FPS</option>
                            <option value="30" selected>30 FPS</option>
                            <option value="60">60 FPS</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="video-resolution">分辨率</label>
                        <select id="video-resolution" name="resolution">
                            <option value="512x512">512×512</option>
                            <option value="720x480">720×480 (SD)</option>
                            <option value="1280x720">1280×720 (HD)</option>
                            <option value="1920x1080">1920×1080 (FHD)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="video-prompt">视频描述</label>
                    <textarea id="video-prompt" name="prompt" rows="4" placeholder="请详细描述您想要生成的视频场景..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="reference-image">参考图片 (可选)</label>
                    <input type="file" id="reference-image" name="reference_image" accept="image/*">
                    <p class="description">上传参考图片可以提高视频生成质量</p>
                </div>
                
                <div class="form-group">
                    <label for="video-style">视频风格</label>
                    <select id="video-style" name="style">
                        <option value="realistic">写实风格</option>
                        <option value="animation">动画风格</option>
                        <option value="cinematic">电影风格</option>
                        <option value="documentary">纪录片风格</option>
                        <option value="artistic">艺术风格</option>
                    </select>
                </div>
                
                <div class="generation-actions">
                    <button type="submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-video-alt3"></span>
                        生成视频
                    </button>
                    <button type="button" class="button button-secondary save-template">
                        <span class="dashicons dashicons-saved"></span>
                        保存为模板
                    </button>
                </div>
            </form>
        </div>
        
        <!-- 音频生成面板 -->
        <div id="audio-generation" class="generation-panel">
            <form class="generation-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="audio-model">AI模型选择</label>
                        <select id="audio-model" name="model">
                            <option value="fishaudio/fish-speech-1.5">Fish Speech 1.5 (推荐)</option>
                            <option value="microsoft/speecht5_tts">SpeechT5 TTS</option>
                            <option value="espnet/kan-bayashi_ljspeech_vits">VITS</option>
                            <option value="custom">自定义模型</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="audio-voice">语音类型</label>
                        <select id="audio-voice" name="voice">
                            <option value="female_young">年轻女声</option>
                            <option value="female_mature">成熟女声</option>
                            <option value="male_young">年轻男声</option>
                            <option value="male_mature">成熟男声</option>
                            <option value="child">儿童声</option>
                            <option value="elderly">老年声</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="audio-language">语言</label>
                        <select id="audio-language" name="language">
                            <option value="zh">中文</option>
                            <option value="en">English</option>
                            <option value="ja">日本語</option>
                            <option value="ko">한국어</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="audio-speed">语速</label>
                        <select id="audio-speed" name="speed">
                            <option value="0.5">慢速</option>
                            <option value="1.0" selected>正常</option>
                            <option value="1.5">快速</option>
                            <option value="2.0">超快</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="audio-text">文本内容</label>
                    <textarea id="audio-text" name="text" rows="6" placeholder="请输入要转换为语音的文本内容..." required></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="audio-pitch">音调</label>
                        <select id="audio-pitch" name="pitch">
                            <option value="low">低音调</option>
                            <option value="normal" selected>正常</option>
                            <option value="high">高音调</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="audio-emotion">情感</label>
                        <select id="audio-emotion" name="emotion">
                            <option value="neutral" selected>中性</option>
                            <option value="happy">快乐</option>
                            <option value="sad">悲伤</option>
                            <option value="angry">愤怒</option>
                            <option value="excited">兴奋</option>
                            <option value="calm">平静</option>
                        </select>
                    </div>
                </div>
                
                <div class="generation-actions">
                    <button type="submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-media-audio"></span>
                        生成语音
                    </button>
                    <button type="button" class="button button-secondary save-template">
                        <span class="dashicons dashicons-saved"></span>
                        保存为模板
                    </button>
                </div>
            </form>
        </div>
        
        <!-- 代码生成面板 -->
        <div id="code-generation" class="generation-panel">
            <form class="generation-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="code-model">AI模型选择</label>
                        <select id="code-model" name="model">
                            <option value="Qwen/QwQ-32B-Preview">Qwen/QwQ-32B (推荐)</option>
                            <option value="deepseek-ai/DeepSeek-Coder-V2-Instruct">DeepSeek-Coder-V2</option>
                            <option value="meta-llama/CodeLlama-34b-Instruct-hf">CodeLlama-34B</option>
                            <option value="custom">自定义模型</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="code-language">编程语言</label>
                        <select id="code-language" name="language">
                            <option value="php">PHP</option>
                            <option value="javascript">JavaScript</option>
                            <option value="python">Python</option>
                            <option value="java">Java</option>
                            <option value="cpp">C++</option>
                            <option value="css">CSS</option>
                            <option value="html">HTML</option>
                            <option value="sql">SQL</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="code-framework">框架/库</label>
                        <select id="code-framework" name="framework">
                            <option value="wordpress">WordPress</option>
                            <option value="laravel">Laravel</option>
                            <option value="react">React</option>
                            <option value="vue">Vue.js</option>
                            <option value="jquery">jQuery</option>
                            <option value="bootstrap">Bootstrap</option>
                            <option value="vanilla">原生代码</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="code-complexity">复杂度</label>
                        <select id="code-complexity" name="complexity">
                            <option value="simple">简单</option>
                            <option value="medium" selected>中等</option>
                            <option value="complex">复杂</option>
                            <option value="advanced">高级</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="code-description">功能描述</label>
                    <textarea id="code-description" name="description" rows="4" placeholder="请详细描述您想要实现的功能..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="code-requirements">具体要求 (可选)</label>
                    <textarea id="code-requirements" name="requirements" rows="3" placeholder="例如：需要错误处理、数据验证、安全性考虑等..."></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="code-comments">注释风格</label>
                        <select id="code-comments" name="comments">
                            <option value="minimal">最少注释</option>
                            <option value="standard" selected>标准注释</option>
                            <option value="detailed">详细注释</option>
                            <option value="documentation">文档化注释</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="code-style">代码风格</label>
                        <select id="code-style" name="style">
                            <option value="clean">简洁风格</option>
                            <option value="readable" selected>可读性优先</option>
                            <option value="performance">性能优先</option>
                            <option value="secure">安全性优先</option>
                        </select>
                    </div>
                </div>
                
                <div class="generation-actions">
                    <button type="submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-editor-code"></span>
                        生成代码
                    </button>
                    <button type="button" class="button button-secondary save-template">
                        <span class="dashicons dashicons-saved"></span>
                        保存为模板
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- 生成结果显示 -->
    <div id="generation-result" class="ai-optimizer-card" style="display: none;">
        <h2>📄 生成结果</h2>
        <div class="result-header">
            <div class="result-info">
                <span id="result-type">文本</span>
                <span id="result-model">Qwen/QwQ-32B-Preview</span>
                <span id="result-time">--</span>
            </div>
            <div class="result-actions">
                <button type="button" id="copy-result" class="button button-small">
                    <span class="dashicons dashicons-admin-page"></span>
                    复制
                </button>
                <button type="button" id="download-result" class="button button-small">
                    <span class="dashicons dashicons-download"></span>
                    下载
                </button>
                <button type="button" id="publish-result" class="button button-small">
                    <span class="dashicons dashicons-admin-post"></span>
                    发布
                </button>
            </div>
        </div>
        <div id="result-content" class="result-content">
            <!-- 生成的内容将显示在这里 -->
        </div>
    </div>
    
    <!-- 生成历史 -->
    <div class="ai-optimizer-card">
        <h2>📚 生成历史</h2>
        <div class="history-filters">
            <select id="history-type-filter">
                <option value="all">所有类型</option>
                <option value="text">文本</option>
                <option value="image">图片</option>
                <option value="video">视频</option>
                <option value="audio">音频</option>
                <option value="code">代码</option>
            </select>
            <select id="history-date-filter">
                <option value="today">今天</option>
                <option value="week">本周</option>
                <option value="month">本月</option>
                <option value="all">全部</option>
            </select>
            <button type="button" id="clear-history" class="button button-secondary">清空历史</button>
        </div>
        <div id="generation-history" class="generation-history">
            <div class="no-history">暂无生成历史</div>
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

.api-status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.api-status-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e1e5e9;
}

.api-icon {
    font-size: 24px;
    width: 40px;
    text-align: center;
}

.api-content {
    flex: 1;
}

.api-label {
    font-weight: bold;
    color: #333;
}

.api-status {
    font-size: 14px;
    color: #666;
}

.api-status.connected {
    color: #2ED573;
}

.api-status.error {
    color: #FF4757;
}

.custom-api-panel {
    border-top: 1px solid #e1e5e9;
    padding-top: 20px;
    margin-top: 20px;
}

.custom-api-actions {
    text-align: right;
    margin-top: 15px;
}

.generation-type-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 1px solid #e1e5e9;
    padding-bottom: 10px;
}

.type-tab {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    border: 1px solid #e1e5e9;
    background: #f8f9fa;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.type-tab:hover {
    background: #e9ecef;
}

.type-tab.active {
    background: #165DFF;
    color: white;
    border-color: #165DFF;
}

.tab-icon {
    font-size: 18px;
}

.tab-label {
    font-weight: 500;
}

.generation-panel {
    display: none;
}

.generation-panel.active {
    display: block;
}

.generation-form {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
    font-family: inherit;
}

.form-group .description {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.generation-actions {
    text-align: center;
    margin-top: 20px;
}

.generation-actions .button {
    margin: 0 5px;
}

.result-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e1e5e9;
}

.result-info {
    display: flex;
    gap: 15px;
}

.result-info span {
    padding: 4px 8px;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 12px;
    color: #666;
}

.result-actions {
    display: flex;
    gap: 10px;
}

.result-content {
    min-height: 200px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e1e5e9;
}

.result-content.text {
    font-family: inherit;
    line-height: 1.6;
    white-space: pre-wrap;
}

.result-content.code {
    font-family: 'Courier New', monospace;
    background: #1a1a1a;
    color: #00F5D4;
    white-space: pre-wrap;
}

.result-content.image {
    text-align: center;
}

.result-content.image img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

.result-content.video {
    text-align: center;
}

.result-content.video video {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

.result-content.audio {
    text-align: center;
}

.history-filters {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.generation-history {
    max-height: 400px;
    overflow-y: auto;
}

.no-history {
    text-align: center;
    color: #666;
    font-style: italic;
    padding: 40px;
}

.history-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    margin-bottom: 10px;
    background: #f8f9fa;
}

.history-icon {
    font-size: 24px;
    width: 40px;
    text-align: center;
}

.history-content {
    flex: 1;
}

.history-title {
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.history-meta {
    font-size: 12px;
    color: #666;
}

.history-actions {
    display: flex;
    gap: 5px;
}

.progress-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.progress-content {
    background: white;
    padding: 40px;
    border-radius: 8px;
    text-align: center;
    max-width: 400px;
    width: 90%;
}

.progress-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #165DFF;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.progress-text {
    font-size: 16px;
    color: #333;
    margin-bottom: 10px;
}

.progress-subtext {
    font-size: 14px;
    color: #666;
}

@media (max-width: 768px) {
    .generation-type-tabs {
        flex-wrap: wrap;
    }
    
    .type-tab {
        flex: 1;
        min-width: 120px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .result-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .history-filters {
        flex-direction: column;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    let currentGenerationType = 'text';
    let isGenerating = false;
    
    // 初始化
    initializeAITools();
    
    function initializeAITools() {
        // 检查API状态
        checkAPIStatus();
        
        // 加载生成历史
        loadGenerationHistory();
        
        // 绑定事件
        bindEvents();
    }
    
    // 检查API状态
    function checkAPIStatus() {
        $.post(ajaxurl, {
            action: 'ai_optimizer_check_api_status',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>'
        })
        .done(function(response) {
            if (response.success) {
                $('#siliconflow-status').text('连接正常').addClass('connected');
            } else {
                $('#siliconflow-status').text('连接失败: ' + response.data).addClass('error');
            }
        })
        .fail(function() {
            $('#siliconflow-status').text('检查失败').addClass('error');
        });
    }
    
    // 绑定事件
    function bindEvents() {
        // 类型标签切换
        $('.type-tab').on('click', function() {
            const type = $(this).data('type');
            switchGenerationType(type);
        });
        
        // 生成表单提交
        $('.generation-form').on('submit', function(e) {
            e.preventDefault();
            
            if (isGenerating) {
                return;
            }
            
            const formData = new FormData(this);
            const type = currentGenerationType;
            
            startGeneration(type, formData);
        });
        
        // API配置
        $('#configure-custom-api').on('click', function() {
            $('#custom-api-panel').toggle();
        });
        
        $('#save-custom-api').on('click', function() {
            saveCustomAPIConfig();
        });
        
        $('#cancel-custom-api').on('click', function() {
            $('#custom-api-panel').hide();
        });
        
        // 测试API连接
        $('#test-siliconflow').on('click', function() {
            testAPIConnection('siliconflow');
        });
        
        $('#test-custom-api').on('click', function() {
            testAPIConnection('custom');
        });
        
        // 结果操作
        $('#copy-result').on('click', function() {
            copyResult();
        });
        
        $('#download-result').on('click', function() {
            downloadResult();
        });
        
        $('#publish-result').on('click', function() {
            publishResult();
        });
        
        // 历史筛选
        $('#history-type-filter, #history-date-filter').on('change', function() {
            loadGenerationHistory();
        });
        
        $('#clear-history').on('click', function() {
            clearGenerationHistory();
        });
        
        // 保存模板
        $('.save-template').on('click', function() {
            saveTemplate();
        });
    }
    
    // 切换生成类型
    function switchGenerationType(type) {
        currentGenerationType = type;
        
        // 更新标签状态
        $('.type-tab').removeClass('active');
        $(`.type-tab[data-type="${type}"]`).addClass('active');
        
        // 切换面板
        $('.generation-panel').removeClass('active');
        $(`#${type}-generation`).addClass('active');
    }
    
    // 开始生成
    function startGeneration(type, formData) {
        isGenerating = true;
        
        // 显示进度覆盖
        showProgressOverlay('正在生成' + getTypeLabel(type) + '...');
        
        // 构建请求数据
        const requestData = {
            action: 'ai_optimizer_generate_content',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>',
            type: type,
            form_data: formData
        };
        
        // 发送AJAX请求
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: requestData,
            processData: false,
            contentType: false,
            timeout: 300000, // 5分钟超时
            success: function(response) {
                if (response.success) {
                    displayGenerationResult(type, response.data);
                    addToHistory(type, response.data);
                } else {
                    showError('生成失败: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = '生成失败';
                
                if (status === 'timeout') {
                    errorMessage = '请求超时，请稍后重试';
                } else if (xhr.responseJSON && xhr.responseJSON.data) {
                    errorMessage = '生成失败: ' + xhr.responseJSON.data;
                } else {
                    errorMessage = '网络错误: ' + error;
                }
                
                showError(errorMessage);
            },
            complete: function() {
                isGenerating = false;
                hideProgressOverlay();
            }
        });
    }
    
    // 显示生成结果
    function displayGenerationResult(type, data) {
        $('#generation-result').show();
        $('#result-type').text(getTypeLabel(type));
        $('#result-model').text(data.model || '--');
        $('#result-time').text(new Date().toLocaleString());
        
        const resultContent = $('#result-content');
        resultContent.removeClass().addClass('result-content ' + type);
        
        switch (type) {
            case 'text':
            case 'code':
                resultContent.text(data.content || data.text || '');
                break;
                
            case 'image':
                if (data.image_url) {
                    resultContent.html(`<img src="${data.image_url}" alt="生成的图片">`);
                } else {
                    resultContent.text('图片生成失败');
                }
                break;
                
            case 'video':
                if (data.video_url) {
                    resultContent.html(`<video controls><source src="${data.video_url}" type="video/mp4">您的浏览器不支持视频播放</video>`);
                } else {
                    resultContent.text('视频生成失败');
                }
                break;
                
            case 'audio':
                if (data.audio_url) {
                    resultContent.html(`<audio controls><source src="${data.audio_url}" type="audio/mpeg">您的浏览器不支持音频播放</audio>`);
                } else {
                    resultContent.text('音频生成失败');
                }
                break;
        }
    }
    
    // 显示进度覆盖
    function showProgressOverlay(message) {
        const overlay = $(`
            <div class="progress-overlay">
                <div class="progress-content">
                    <div class="progress-spinner"></div>
                    <div class="progress-text">${message}</div>
                    <div class="progress-subtext">请耐心等待，生成过程可能需要几分钟</div>
                </div>
            </div>
        `);
        
        $('body').append(overlay);
    }
    
    // 隐藏进度覆盖
    function hideProgressOverlay() {
        $('.progress-overlay').remove();
    }
    
    // 显示错误
    function showError(message) {
        alert(message);
    }
    
    // 获取类型标签
    function getTypeLabel(type) {
        const labels = {
            'text': '文本',
            'image': '图片',
            'video': '视频',
            'audio': '音频',
            'code': '代码'
        };
        return labels[type] || type;
    }
    
    // 测试API连接
    function testAPIConnection(apiType) {
        const button = $(`#test-${apiType}`);
        const originalText = button.text();
        
        button.prop('disabled', true).text('测试中...');
        
        $.post(ajaxurl, {
            action: 'ai_optimizer_test_api_connection',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>',
            api_type: apiType
        })
        .done(function(response) {
            if (response.success) {
                alert('API连接测试成功！');
                if (apiType === 'siliconflow') {
                    $('#siliconflow-status').text('连接正常').removeClass('error').addClass('connected');
                } else {
                    $('#custom-api-status').text('连接正常').removeClass('error').addClass('connected');
                }
            } else {
                alert('API连接测试失败: ' + response.data);
            }
        })
        .fail(function() {
            alert('测试过程中发生网络错误');
        })
        .always(function() {
            button.prop('disabled', false).text(originalText);
        });
    }
    
    // 保存自定义API配置
    function saveCustomAPIConfig() {
        const config = {
            name: $('#custom_api_name').val(),
            endpoint: $('#custom_api_endpoint').val(),
            key: $('#custom_api_key').val(),
            model: $('#custom_api_model').val(),
            type: $('#custom_api_type').val()
        };
        
        if (!config.name || !config.endpoint || !config.key) {
            alert('请填写完整的API配置信息');
            return;
        }
        
        $.post(ajaxurl, {
            action: 'ai_optimizer_save_custom_api',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>',
            config: JSON.stringify(config)
        })
        .done(function(response) {
            if (response.success) {
                alert('自定义API配置已保存');
                $('#custom-api-panel').hide();
                $('#custom-api-status').text('已配置');
            } else {
                alert('保存失败: ' + response.data);
            }
        })
        .fail(function() {
            alert('保存配置时发生网络错误');
        });
    }
    
    // 复制结果
    function copyResult() {
        const content = $('#result-content').text();
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(content).then(function() {
                alert('内容已复制到剪贴板');
            });
        } else {
            // 兼容性处理
            const textarea = document.createElement('textarea');
            textarea.value = content;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('内容已复制到剪贴板');
        }
    }
    
    // 下载结果
    function downloadResult() {
        const content = $('#result-content').text();
        const type = $('#result-type').text();
        const timestamp = new Date().toISOString().substr(0, 10);
        
        const blob = new Blob([content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${type}-${timestamp}.txt`;
        a.click();
        URL.revokeObjectURL(url);
    }
    
    // 发布结果
    function publishResult() {
        const content = $('#result-content').text();
        const type = $('#result-type').text();
        
        if (!content) {
            alert('没有可发布的内容');
            return;
        }
        
        const title = prompt('请输入文章标题:', type + '内容 - ' + new Date().toLocaleDateString());
        if (!title) {
            return;
        }
        
        $.post(ajaxurl, {
            action: 'ai_optimizer_publish_content',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>',
            title: title,
            content: content,
            type: type
        })
        .done(function(response) {
            if (response.success) {
                alert('内容已发布为新文章');
                if (response.data.edit_url) {
                    window.open(response.data.edit_url, '_blank');
                }
            } else {
                alert('发布失败: ' + response.data);
            }
        })
        .fail(function() {
            alert('发布时发生网络错误');
        });
    }
    
    // 加载生成历史
    function loadGenerationHistory() {
        const typeFilter = $('#history-type-filter').val();
        const dateFilter = $('#history-date-filter').val();
        
        $.post(ajaxurl, {
            action: 'ai_optimizer_get_generation_history',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>',
            type_filter: typeFilter,
            date_filter: dateFilter
        })
        .done(function(response) {
            if (response.success) {
                displayGenerationHistory(response.data);
            }
        })
        .fail(function() {
            console.error('加载生成历史失败');
        });
    }
    
    // 显示生成历史
    function displayGenerationHistory(history) {
        const container = $('#generation-history');
        
        if (history.length === 0) {
            container.html('<div class="no-history">暂无生成历史</div>');
            return;
        }
        
        let html = '';
        history.forEach(function(item) {
            const icon = getTypeIcon(item.type);
            html += `
                <div class="history-item">
                    <div class="history-icon">${icon}</div>
                    <div class="history-content">
                        <div class="history-title">${item.title || item.prompt}</div>
                        <div class="history-meta">
                            ${getTypeLabel(item.type)} · ${item.model} · ${item.created_at}
                        </div>
                    </div>
                    <div class="history-actions">
                        <button class="button button-small view-history" data-id="${item.id}">查看</button>
                        <button class="button button-small reuse-history" data-id="${item.id}">重用</button>
                        <button class="button button-small delete-history" data-id="${item.id}">删除</button>
                    </div>
                </div>
            `;
        });
        
        container.html(html);
    }
    
    // 获取类型图标
    function getTypeIcon(type) {
        const icons = {
            'text': '📝',
            'image': '🖼️',
            'video': '🎬',
            'audio': '🎵',
            'code': '💻'
        };
        return icons[type] || '📄';
    }
    
    // 添加到历史
    function addToHistory(type, data) {
        // 重新加载历史记录
        loadGenerationHistory();
    }
    
    // 清空历史
    function clearGenerationHistory() {
        if (!confirm('确定要清空所有生成历史吗？此操作无法撤销。')) {
            return;
        }
        
        $.post(ajaxurl, {
            action: 'ai_optimizer_clear_generation_history',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>'
        })
        .done(function(response) {
            if (response.success) {
                loadGenerationHistory();
                alert('历史记录已清空');
            } else {
                alert('清空失败: ' + response.data);
            }
        })
        .fail(function() {
            alert('清空历史时发生网络错误');
        });
    }
    
    // 保存模板
    function saveTemplate() {
        const formData = $(`.generation-panel.active .generation-form`).serialize();
        const type = currentGenerationType;
        
        const templateName = prompt('请输入模板名称:');
        if (!templateName) {
            return;
        }
        
        $.post(ajaxurl, {
            action: 'ai_optimizer_save_template',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>',
            type: type,
            name: templateName,
            form_data: formData
        })
        .done(function(response) {
            if (response.success) {
                alert('模板已保存');
            } else {
                alert('保存模板失败: ' + response.data);
            }
        })
        .fail(function() {
            alert('保存模板时发生网络错误');
        });
    }
    
    // 历史操作
    $(document).on('click', '.view-history', function() {
        const id = $(this).data('id');
        // 查看历史记录详情
        alert('查看历史记录 #' + id + ' 的详情（功能待实现）');
    });
    
    $(document).on('click', '.reuse-history', function() {
        const id = $(this).data('id');
        // 重用历史记录
        alert('重用历史记录 #' + id + ' 的配置（功能待实现）');
    });
    
    $(document).on('click', '.delete-history', function() {
        const id = $(this).data('id');
        
        if (!confirm('确定要删除这条历史记录吗？')) {
            return;
        }
        
        $.post(ajaxurl, {
            action: 'ai_optimizer_delete_history_item',
            nonce: '<?php echo wp_create_nonce("ai-opt-nonce"); ?>',
            id: id
        })
        .done(function(response) {
            if (response.success) {
                loadGenerationHistory();
            } else {
                alert('删除失败: ' + response.data);
            }
        })
        .fail(function() {
            alert('删除时发生网络错误');
        });
    });
});
</script>
<?php
/**
 * AI工具页面类
 */

if (!defined('ABSPATH')) {
    exit;
}

class AI_Optimizer_AI_Tools {
    
    /**
     * 渲染AI工具页面
     */
    public static function render() {
        AI_Optimizer_Admin::verify_admin_access();
        ?>
        <div class="wrap ai-optimizer-ai-tools">
            <h1>AI工具</h1>
            <p>使用AI生成文本、图片、视频、音频和代码内容。</p>
            
            <div class="ai-optimizer-card">
                <h3>内容生成</h3>
                <form id="contentGenerationForm">
                    <label for="contentType">内容类型:</label>
                    <select id="contentType">
                        <option value="text">文本</option>
                        <option value="image">图片</option>
                        <option value="video">视频</option>
                        <option value="audio">音频</option>
                        <option value="code">代码</option>
                    </select>
                    
                    <label for="contentPrompt">描述:</label>
                    <textarea id="contentPrompt" placeholder="描述您想要生成的内容..."></textarea>
                    
                    <button type="submit" class="ai-optimizer-btn">生成内容</button>
                </form>
                
                <div id="generationResults" style="margin-top: 20px;"></div>
            </div>
        </div>
        
        <script>
        document.getElementById('contentGenerationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const type = document.getElementById('contentType').value;
            const prompt = document.getElementById('contentPrompt').value;
            const resultsDiv = document.getElementById('generationResults');
            
            resultsDiv.innerHTML = '<p>正在生成...</p>';
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=ai_optimizer_generate_content&type=' + type + '&prompt=' + encodeURIComponent(prompt) + '&nonce=<?php echo wp_create_nonce('ai_optimizer_nonce'); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultsDiv.innerHTML = '<h4>生成结果</h4><p>' + data.data.result + '</p>';
                } else {
                    resultsDiv.innerHTML = '<p>生成失败: ' + data.data + '</p>';
                }
            });
        });
        </script>
        <?php
    }
}
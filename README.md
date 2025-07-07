# AI Website Optimizer

✅ **插件已完全修复并优化，可正常使用！**

A comprehensive WordPress plugin that integrates with Siliconflow API to provide AI-powered website monitoring, optimization, and content generation capabilities.

## Features

### 🔍 Real-time Monitoring
- **Performance Tracking**: Monitor website load times, memory usage, and database queries
- **Error Detection**: Automatic detection of PHP errors, JavaScript errors, and resource failures
- **SEO Monitoring**: Track SEO metrics, sitemap status, and search engine optimization
- **Security Monitoring**: Monitor for security vulnerabilities and suspicious activities

### 🚀 AI-Powered Optimization
- **SEO Optimization**: AI-driven SEO analysis and automatic optimization suggestions
- **Code Analysis**: Intelligent code review with security vulnerability detection
- **Performance Enhancement**: Automated performance optimization recommendations
- **Content Optimization**: AI-enhanced content for better readability and engagement

### 🎨 Content Generation
- **Text Generation**: Create high-quality articles, product descriptions, and marketing copy
- **Image Generation**: Generate custom images using advanced AI models
- **Video Creation**: Produce engaging videos from text prompts
- **Audio Synthesis**: Convert text to natural-sounding speech
- **Code Generation**: Generate PHP, JavaScript, CSS, and HTML code snippets

### 📊 Advanced Analytics
- **Interactive Dashboard**: Real-time charts and visualizations using Chart.js
- **Performance Metrics**: Detailed performance analytics and trends
- **SEO Insights**: Comprehensive SEO analysis and improvement tracking
- **Error Reporting**: Advanced error tracking and resolution suggestions

### 🤖 Intelligent Automation
- **Content Collection**: Automatically collect and rewrite content from RSS feeds and websites
- **Auto-Publishing**: AI-powered content creation and automatic publishing
- **Smart Categorization**: Intelligent content categorization and tagging
- **Bulk Operations**: Process multiple items with AI assistance

## Installation

1. **Download**: Download the plugin files from the repository
2. **Upload**: Upload the `ai-website-optimizer` folder to your `/wp-content/plugins/` directory
3. **Activate**: Activate the plugin through the 'Plugins' menu in WordPress
4. **Configure**: Go to 'AI Optimizer' → 'Settings' to configure your Siliconflow API key

## Configuration

### API Setup
1. Sign up for a [Siliconflow account](https://cloud.siliconflow.cn/)
2. Generate your API key from the console
3. Navigate to **AI Optimizer** → **Settings** → **API Settings**
4. Enter your API key and test the connection

### Monitoring Configuration
- **Enable Real-time Monitoring**: Track performance metrics automatically
- **Set Monitoring Interval**: Choose between hourly, twice daily, or daily monitoring
- **Frontend Tracking**: Enable client-side performance tracking

### SEO Settings
- **Auto-optimization**: Enable automatic SEO improvements
- **Target Keywords**: Set your primary keywords for optimization
- **Backup Settings**: Configure backup options before making changes

## Usage

### Dashboard Overview
The main dashboard provides:
- **Performance Metrics**: Real-time charts showing load times and memory usage
- **SEO Score**: Current SEO optimization level
- **Recent Activities**: Latest optimization actions and results
- **Active Alerts**: Important notifications requiring attention

### Content Generation
1. Navigate to **AI Optimizer** → **AI Tools**
2. Choose your content type (Text, Image, Video, Audio, Code)
3. Enter your prompt and configure options
4. Click "Generate" and wait for AI processing
5. Review and use the generated content

### SEO Optimization
1. Go to **AI Optimizer** → **SEO Optimization**
2. Click "Run SEO Analysis" to analyze your site
3. Review AI-generated suggestions
4. Apply optimizations individually or in bulk
5. Monitor improvements over time

### Code Analysis
1. Visit **AI Optimizer** → **Monitor**
2. Run a comprehensive code analysis
3. Review security and performance issues
4. Apply AI-suggested fixes where appropriate
5. Track code health improvements

## API Integration

This plugin integrates with the Siliconflow API to provide:

### Supported Models
- **Chat Models**: Qwen/QwQ-32B, Qwen/Qwen2.5-72B-Instruct, Meta-Llama-3.1-8B-Instruct
- **Image Models**: Stable Diffusion XL, FLUX.1-schnell
- **Video Models**: Lightricks/LTX-Video
- **Audio Models**: Fish Speech 1.5, SenseVoice Small
- **Embedding Models**: BGE-Large-EN/ZH-v1.5

### API Features
- **Chat Completions**: Advanced text generation and analysis
- **Image Generation**: High-quality image creation
- **Video Generation**: Text-to-video synthesis
- **Audio Processing**: Text-to-speech and speech-to-text
- **Embeddings**: Semantic text analysis and similarity

## Security Features

### Data Protection
- **Encryption**: All sensitive data is encrypted using AES-256
- **Secure Storage**: API keys and sensitive information are securely stored
- **Access Control**: Role-based permissions for different features
- **Audit Logging**: Comprehensive logging of all activities

### Security Monitoring
- **Vulnerability Scanning**: Automatic detection of security issues
- **Access Monitoring**: Track login attempts and suspicious activities
- **Rate Limiting**: Protection against abuse and excessive API usage
- **Input Validation**: Comprehensive sanitization of all user inputs

## Performance Optimization

### Caching
- **Smart Caching**: Intelligent caching of API responses and analysis results
- **Browser Caching**: Optimized browser caching headers
- **Database Optimization**: Efficient database queries and indexing

### Resource Optimization
- **Lazy Loading**: Automatic lazy loading for images and content
- **Script Optimization**: Async/defer loading for JavaScript files
- **CSS Optimization**: Optimized CSS delivery and minification

## Requirements

### System Requirements
- **PHP**: 7.4 or higher
- **WordPress**: 5.0 or higher
- **MySQL**: 5.6 or higher
- **Memory**: 128MB minimum (256MB recommended)

### PHP Extensions
- **cURL**: Required for API communication
- **JSON**: Required for data processing
- **OpenSSL**: Required for encryption
- **GD**: Recommended for image processing

### API Requirements
- **Siliconflow API Key**: Required for AI features
- **Internet Connection**: Required for API communication
- **SSL/HTTPS**: Recommended for security

## Database Tables

The plugin creates the following database tables:

- `wp_ai_optimizer_monitoring` - Performance monitoring data
- `wp_ai_optimizer_analysis` - AI analysis results
- `wp_ai_optimizer_seo_analysis` - SEO analysis data
- `wp_ai_optimizer_seo_suggestions` - SEO optimization suggestions
- `wp_ai_optimizer_code_issues` - Code analysis issues
- `wp_ai_optimizer_generations` - AI content generations
- `wp_ai_optimizer_video_requests` - Video generation requests
- `wp_ai_optimizer_api_usage` - API usage tracking
- `wp_ai_optimizer_logs` - System logs
- `wp_ai_optimizer_collected_content` - Content collection data

## Troubleshooting

### Common Issues

**API Connection Failed**
- Verify your API key is correct
- Check your internet connection
- Ensure SSL certificates are valid

**Monitoring Not Working**
- Check WordPress cron is functioning
- Verify monitoring is enabled in settings
- Review error logs for issues

**Performance Issues**
- Increase PHP memory limit
- Optimize database tables
- Clear plugin cache

### Debug Mode
Enable debug mode by adding to wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// AI Website Optimizer - Main JavaScript

// Global configuration
window.AIOptimizer = {
    baseUrl: window.location.origin,
    apiUrl: window.location.origin + '/api',
    currentUser: null,
    isDebug: true
};

// Utility functions
const Utils = {
    // Show loading state
    showLoading: function(element, text = 'Loading...') {
        const originalHtml = element.innerHTML;
        element.setAttribute('data-original-html', originalHtml);
        element.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${text}`;
        element.disabled = true;
    },
    
    // Hide loading state
    hideLoading: function(element) {
        const originalHtml = element.getAttribute('data-original-html');
        if (originalHtml) {
            element.innerHTML = originalHtml;
            element.removeAttribute('data-original-html');
        }
        element.disabled = false;
    },
    
    // Show notification
    showNotification: function(message, type = 'info', duration = 5000) {
        const alertClass = type === 'error' ? 'danger' : type;
        const icon = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        }[type] || 'info-circle';
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${alertClass} alert-dismissible fade show position-fixed`;
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            <i class="fas fa-${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        if (duration > 0) {
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, duration);
        }
    },
    
    // Format bytes
    formatBytes: function(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    },
    
    // Debounce function
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Copy to clipboard
    copyToClipboard: function(text) {
        navigator.clipboard.writeText(text).then(() => {
            Utils.showNotification('Copied to clipboard!', 'success');
        }).catch(err => {
            Utils.showNotification('Failed to copy to clipboard', 'error');
        });
    }
};

// API Client
const APIClient = {
    // Generic API request
    request: async function(endpoint, options = {}) {
        const url = `${window.AIOptimizer.apiUrl}${endpoint}`;
        const config = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };
        
        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }
            
            return data;
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    },
    
    // Test API connection
    testConnection: async function() {
        return await this.request('/test');
    },
    
    // Generate content
    generateContent: async function(type, prompt, options = {}) {
        return await this.request('/generate', {
            method: 'POST',
            body: JSON.stringify({
                type: type,
                prompt: prompt,
                ...options
            })
        });
    },
    
    // Run SEO analysis
    analyzeSEO: async function(url, options = {}) {
        return await this.request('/seo/analyze', {
            method: 'POST',
            body: JSON.stringify({
                url: url,
                ...options
            })
        });
    },
    
    // Get performance metrics
    getPerformanceMetrics: async function(timeframe = '24h') {
        return await this.request(`/metrics?timeframe=${timeframe}`);
    }
};

// Dashboard functionality
const Dashboard = {
    charts: {},
    
    init: function() {
        this.bindEvents();
        this.loadDashboardData();
    },
    
    bindEvents: function() {
        // Auto-refresh dashboard every 5 minutes
        setInterval(() => {
            this.loadDashboardData();
        }, 300000);
    },
    
    loadDashboardData: async function() {
        try {
            const metrics = await APIClient.getPerformanceMetrics();
            this.updateMetrics(metrics);
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
            Utils.showNotification('Failed to load dashboard data', 'error');
        }
    },
    
    updateMetrics: function(data) {
        // Update metric cards
        const metricCards = document.querySelectorAll('[data-metric]');
        metricCards.forEach(card => {
            const metric = card.getAttribute('data-metric');
            if (data[metric]) {
                const valueElement = card.querySelector('.metric-value');
                if (valueElement) {
                    valueElement.textContent = data[metric];
                }
            }
        });
        
        // Update charts if they exist
        if (this.charts.performance) {
            this.updatePerformanceChart(data.performance);
        }
    },
    
    updatePerformanceChart: function(data) {
        if (!this.charts.performance) return;
        
        this.charts.performance.data.datasets[0].data = data.loadTimes;
        this.charts.performance.data.datasets[1].data = data.seoScores;
        this.charts.performance.update();
    }
};

// Content Generator
const ContentGenerator = {
    currentContent: null,
    
    init: function() {
        this.bindEvents();
    },
    
    bindEvents: function() {
        // Auto-save drafts
        const textareas = document.querySelectorAll('textarea[data-autosave]');
        textareas.forEach(textarea => {
            textarea.addEventListener('input', Utils.debounce(() => {
                this.saveDraft(textarea.value);
            }, 1000));
        });
    },
    
    generate: async function(type, prompt, options = {}) {
        const button = document.querySelector(`[data-generate="${type}"]`);
        
        if (button) {
            Utils.showLoading(button, 'Generating...');
        }
        
        try {
            const result = await APIClient.generateContent(type, prompt, options);
            this.currentContent = result;
            this.displayContent(result);
            Utils.showNotification('Content generated successfully!', 'success');
        } catch (error) {
            console.error('Content generation failed:', error);
            Utils.showNotification('Failed to generate content: ' + error.message, 'error');
        } finally {
            if (button) {
                Utils.hideLoading(button);
            }
        }
    },
    
    displayContent: function(content) {
        const container = document.getElementById('content-display');
        if (!container) return;
        
        let html = '';
        
        switch (content.type) {
            case 'text':
                html = `<div class="generated-text">${content.content}</div>`;
                break;
            case 'image':
                html = `<img src="${content.url}" alt="Generated image" class="img-fluid rounded">`;
                break;
            case 'video':
                html = `<video controls class="w-100 rounded">
                    <source src="${content.url}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>`;
                break;
            case 'audio':
                html = `<audio controls class="w-100">
                    <source src="${content.url}" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>`;
                break;
        }
        
        container.innerHTML = html;
    },
    
    saveDraft: function(content) {
        const draft = {
            content: content,
            timestamp: Date.now(),
            type: 'draft'
        };
        
        localStorage.setItem('aiOptimizer_draft', JSON.stringify(draft));
    },
    
    loadDraft: function() {
        const draft = localStorage.getItem('aiOptimizer_draft');
        if (draft) {
            try {
                return JSON.parse(draft);
            } catch (error) {
                console.error('Failed to load draft:', error);
            }
        }
        return null;
    }
};

// SEO Analyzer
const SEOAnalyzer = {
    init: function() {
        this.bindEvents();
    },
    
    bindEvents: function() {
        // Real-time URL validation
        const urlInputs = document.querySelectorAll('input[type="url"]');
        urlInputs.forEach(input => {
            input.addEventListener('input', this.validateUrl.bind(this));
        });
    },
    
    validateUrl: function(event) {
        const input = event.target;
        const url = input.value;
        
        if (url && !this.isValidUrl(url)) {
            input.setCustomValidity('Please enter a valid URL');
            input.classList.add('is-invalid');
        } else {
            input.setCustomValidity('');
            input.classList.remove('is-invalid');
        }
    },
    
    isValidUrl: function(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    },
    
    analyze: async function(url, options = {}) {
        const button = document.querySelector('[data-analyze-seo]');
        
        if (button) {
            Utils.showLoading(button, 'Analyzing...');
        }
        
        try {
            const result = await APIClient.analyzeSEO(url, options);
            this.displayResults(result);
            Utils.showNotification('SEO analysis completed!', 'success');
        } catch (error) {
            console.error('SEO analysis failed:', error);
            Utils.showNotification('Failed to analyze SEO: ' + error.message, 'error');
        } finally {
            if (button) {
                Utils.hideLoading(button);
            }
        }
    },
    
    displayResults: function(results) {
        const container = document.getElementById('seo-results');
        if (!container) return;
        
        const html = `
            <div class="seo-results">
                <div class="row">
                    <div class="col-md-4">
                        <div class="metric-card">
                            <h3 class="metric-value text-success">${results.score}</h3>
                            <p class="metric-label">SEO Score</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-card">
                            <h3 class="metric-value text-warning">${results.issues.length}</h3>
                            <p class="metric-label">Issues Found</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-card">
                            <h3 class="metric-value text-info">${results.recommendations.length}</h3>
                            <p class="metric-label">Recommendations</p>
                        </div>
                    </div>
                </div>
                
                ${results.issues.length > 0 ? `
                    <div class="mt-4">
                        <h5>Issues Found:</h5>
                        <ul class="list-group">
                            ${results.issues.map(issue => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${issue.description}
                                    <span class="badge bg-${issue.severity === 'high' ? 'danger' : issue.severity === 'medium' ? 'warning' : 'secondary'} rounded-pill">
                                        ${issue.severity}
                                    </span>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                ` : ''}
                
                ${results.recommendations.length > 0 ? `
                    <div class="mt-4">
                        <h5>Recommendations:</h5>
                        <ul class="list-group">
                            ${results.recommendations.map(rec => `
                                <li class="list-group-item">
                                    <strong>${rec.title}</strong><br>
                                    <small class="text-muted">${rec.description}</small>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                ` : ''}
            </div>
        `;
        
        container.innerHTML = html;
    }
};

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modules
    Dashboard.init();
    ContentGenerator.init();
    SEOAnalyzer.init();
    
    // Load saved drafts
    const draft = ContentGenerator.loadDraft();
    if (draft) {
        const textarea = document.querySelector('textarea[data-autosave]');
        if (textarea) {
            textarea.value = draft.content;
            Utils.showNotification('Draft loaded', 'info', 3000);
        }
    }
    
    // Global error handler
    window.addEventListener('error', function(event) {
        console.error('Global error:', event.error);
        Utils.showNotification('An unexpected error occurred', 'error');
    });
    
    // Service worker registration (if available)
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').then(function(registration) {
            console.log('ServiceWorker registration successful');
        }).catch(function(error) {
            console.log('ServiceWorker registration failed');
        });
    }
});

// Export for global access
window.AIOptimizer.Utils = Utils;
window.AIOptimizer.APIClient = APIClient;
window.AIOptimizer.Dashboard = Dashboard;
window.AIOptimizer.ContentGenerator = ContentGenerator;
window.AIOptimizer.SEOAnalyzer = SEOAnalyzer;
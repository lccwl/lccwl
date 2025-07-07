/**
 * AI网站优化器 - 前端脚本
 */

(function($) {
    'use strict';
    
    // 前端监控类
    const AIOptimizerFrontend = {
        
        init: function() {
            this.bindEvents();
            this.startPerformanceTracking();
            this.startErrorTracking();
        },
        
        bindEvents: function() {
            // 页面加载完成后开始监控
            $(window).on('load', this.onPageLoad.bind(this));
            
            // 页面卸载前发送数据
            $(window).on('beforeunload', this.onPageUnload.bind(this));
        },
        
        onPageLoad: function() {
            // 延迟收集性能数据，确保所有资源加载完成
            setTimeout(() => {
                this.collectPerformanceData();
            }, 2000);
        },
        
        onPageUnload: function() {
            // 发送剩余的分析数据
            this.sendPendingData();
        },
        
        collectPerformanceData: function() {
            if (!window.performance || !window.performance.timing) {
                return;
            }
            
            const timing = window.performance.timing;
            const navigation = window.performance.navigation;
            
            const performanceData = {
                url: window.location.href,
                pageLoadTime: timing.loadEventEnd - timing.navigationStart,
                domContentLoaded: timing.domContentLoadedEventEnd - timing.navigationStart,
                firstContentfulPaint: 0,
                largestContentfulPaint: 0,
                cumulativeLayoutShift: 0,
                firstInputDelay: 0,
                viewportWidth: window.innerWidth,
                viewportHeight: window.innerHeight,
                connectionType: this.getConnectionType(),
                deviceType: this.getDeviceType(),
                browserInfo: this.getBrowserInfo()
            };
            
            // 获取Paint Timing API数据
            if (window.PerformanceObserver) {
                this.collectPaintMetrics(performanceData);
                this.collectLayoutShiftMetrics(performanceData);
                this.collectInputDelayMetrics(performanceData);
            }
            
            // 发送性能数据
            this.sendData('performance', performanceData);
        },
        
        collectPaintMetrics: function(data) {
            try {
                const observer = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        if (entry.name === 'first-contentful-paint') {
                            data.firstContentfulPaint = entry.startTime;
                        }
                    }
                });
                observer.observe({entryTypes: ['paint']});
                
                // LCP
                const lcpObserver = new PerformanceObserver((list) => {
                    const entries = list.getEntries();
                    const lastEntry = entries[entries.length - 1];
                    data.largestContentfulPaint = lastEntry.startTime;
                });
                lcpObserver.observe({entryTypes: ['largest-contentful-paint']});
                
            } catch (e) {
                console.warn('Paint metrics collection failed:', e);
            }
        },
        
        collectLayoutShiftMetrics: function(data) {
            try {
                let clsValue = 0;
                const observer = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        if (!entry.hadRecentInput) {
                            clsValue += entry.value;
                            data.cumulativeLayoutShift = clsValue;
                        }
                    }
                });
                observer.observe({entryTypes: ['layout-shift']});
            } catch (e) {
                console.warn('Layout shift metrics collection failed:', e);
            }
        },
        
        collectInputDelayMetrics: function(data) {
            try {
                const observer = new PerformanceObserver((list) => {
                    for (const entry of list.getEntries()) {
                        data.firstInputDelay = entry.processingStart - entry.startTime;
                        break; // 只记录第一次输入延迟
                    }
                });
                observer.observe({entryTypes: ['first-input']});
            } catch (e) {
                console.warn('Input delay metrics collection failed:', e);
            }
        },
        
        startPerformanceTracking: function() {
            // 定期收集性能指标
            setInterval(() => {
                this.collectRuntimeMetrics();
            }, 30000); // 每30秒收集一次
        },
        
        collectRuntimeMetrics: function() {
            const data = {
                url: window.location.href,
                timestamp: Date.now(),
                memoryUsage: this.getMemoryUsage(),
                connectionType: this.getConnectionType(),
                scrollDepth: this.getScrollDepth(),
                timeOnPage: this.getTimeOnPage()
            };
            
            this.sendData('runtime', data);
        },
        
        startErrorTracking: function() {
            // JavaScript错误监控
            window.addEventListener('error', (event) => {
                this.handleError({
                    type: 'javascript',
                    message: event.message,
                    filename: event.filename,
                    lineno: event.lineno,
                    colno: event.colno,
                    stack: event.error ? event.error.stack : '',
                    url: window.location.href,
                    userAgent: navigator.userAgent,
                    timestamp: Date.now()
                });
            });
            
            // Promise rejection错误
            window.addEventListener('unhandledrejection', (event) => {
                this.handleError({
                    type: 'promise_rejection',
                    message: 'Unhandled Promise Rejection: ' + event.reason,
                    stack: event.reason && event.reason.stack ? event.reason.stack : '',
                    url: window.location.href,
                    userAgent: navigator.userAgent,
                    timestamp: Date.now()
                });
            });
            
            // 资源加载错误
            window.addEventListener('error', (event) => {
                if (event.target !== window) {
                    this.handleError({
                        type: 'resource',
                        message: 'Resource loading error',
                        resource: event.target.src || event.target.href,
                        tagName: event.target.tagName,
                        url: window.location.href,
                        timestamp: Date.now()
                    });
                }
            }, true);
        },
        
        handleError: function(errorData) {
            // 过滤一些常见的非关键错误
            if (this.shouldIgnoreError(errorData)) {
                return;
            }
            
            // 添加浏览器信息
            errorData.browserInfo = this.getBrowserInfo();
            
            // 发送错误数据
            this.sendData('error', errorData);
        },
        
        shouldIgnoreError: function(errorData) {
            const ignoredMessages = [
                'Script error',
                'Non-Error promise rejection captured',
                'ResizeObserver loop limit exceeded'
            ];
            
            return ignoredMessages.some(msg => 
                errorData.message && errorData.message.includes(msg)
            );
        },
        
        sendData: function(type, data) {
            if (!window.aiOptimizerPublic || !window.aiOptimizerPublic.trackingEnabled) {
                return;
            }
            
            // 使用fetch API发送数据，如果不支持则降级为XHR
            if (window.fetch) {
                this.sendDataFetch(type, data);
            } else {
                this.sendDataXHR(type, data);
            }
        },
        
        sendDataFetch: function(type, data) {
            const formData = new FormData();
            formData.append('action', 'ai_optimizer_track');
            formData.append('type', type);
            formData.append('data', JSON.stringify(data));
            formData.append('nonce', window.aiOptimizerPublic.nonce);
            
            fetch(window.aiOptimizerPublic.ajaxUrl, {
                method: 'POST',
                body: formData
            }).catch(error => {
                console.warn('Failed to send tracking data:', error);
            });
        },
        
        sendDataXHR: function(type, data) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', window.aiOptimizerPublic.ajaxUrl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            const params = [
                'action=ai_optimizer_track',
                'type=' + encodeURIComponent(type),
                'data=' + encodeURIComponent(JSON.stringify(data)),
                'nonce=' + encodeURIComponent(window.aiOptimizerPublic.nonce)
            ].join('&');
            
            xhr.send(params);
        },
        
        sendPendingData: function() {
            // 发送会话结束数据
            const sessionData = {
                url: window.location.href,
                sessionDuration: this.getTimeOnPage(),
                scrollDepth: this.getScrollDepth(),
                interactions: this.getInteractionCount(),
                timestamp: Date.now()
            };
            
            // 使用sendBeacon API确保数据能够发送
            if (navigator.sendBeacon && window.aiOptimizerPublic) {
                const formData = new FormData();
                formData.append('action', 'ai_optimizer_track');
                formData.append('type', 'session');
                formData.append('data', JSON.stringify(sessionData));
                formData.append('nonce', window.aiOptimizerPublic.nonce);
                
                navigator.sendBeacon(window.aiOptimizerPublic.ajaxUrl, formData);
            }
        },
        
        // 工具方法
        getConnectionType: function() {
            if (navigator.connection) {
                return navigator.connection.effectiveType || 'unknown';
            }
            return 'unknown';
        },
        
        getDeviceType: function() {
            const width = window.innerWidth;
            if (width < 768) return 'mobile';
            if (width < 1024) return 'tablet';
            return 'desktop';
        },
        
        getBrowserInfo: function() {
            return {
                userAgent: navigator.userAgent,
                language: navigator.language,
                platform: navigator.platform,
                cookieEnabled: navigator.cookieEnabled,
                onLine: navigator.onLine,
                doNotTrack: navigator.doNotTrack
            };
        },
        
        getMemoryUsage: function() {
            if (window.performance && window.performance.memory) {
                return {
                    used: window.performance.memory.usedJSHeapSize,
                    total: window.performance.memory.totalJSHeapSize,
                    limit: window.performance.memory.jsHeapSizeLimit
                };
            }
            return null;
        },
        
        getScrollDepth: function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            
            return Math.round((scrollTop + windowHeight) / documentHeight * 100);
        },
        
        getTimeOnPage: function() {
            if (!this.pageStartTime) {
                this.pageStartTime = Date.now();
            }
            return Date.now() - this.pageStartTime;
        },
        
        getInteractionCount: function() {
            return this.interactionCount || 0;
        }
    };
    
    // 交互事件监控
    let interactionCount = 0;
    const trackInteraction = function() {
        interactionCount++;
        AIOptimizerFrontend.interactionCount = interactionCount;
    };
    
    // 监听用户交互
    ['click', 'scroll', 'keydown', 'mousemove', 'touchstart'].forEach(eventType => {
        document.addEventListener(eventType, trackInteraction, { passive: true });
    });
    
    // 页面可见性变化监控
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            AIOptimizerFrontend.sendPendingData();
        } else {
            AIOptimizerFrontend.pageStartTime = Date.now();
        }
    });
    
    // 初始化
    $(document).ready(function() {
        AIOptimizerFrontend.init();
    });
    
    // 导出到全局作用域
    window.AIOptimizerFrontend = AIOptimizerFrontend;
    
})(jQuery);
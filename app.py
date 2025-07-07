import os
import logging
from flask import Flask, render_template, request, jsonify, redirect, url_for, flash
from flask_sqlalchemy import SQLAlchemy
from sqlalchemy.orm import DeclarativeBase
from werkzeug.middleware.proxy_fix import ProxyFix
from datetime import datetime
import json
import random

# Configure logging
logging.basicConfig(level=logging.DEBUG)

class Base(DeclarativeBase):
    pass

db = SQLAlchemy(model_class=Base)

# Create the app
app = Flask(__name__)
app.secret_key = os.environ.get("SESSION_SECRET", "ai-optimizer-demo-key")
app.wsgi_app = ProxyFix(app.wsgi_app, x_proto=1, x_host=1)

# Configure the database
app.config["SQLALCHEMY_DATABASE_URI"] = os.environ.get("DATABASE_URL", "sqlite:///ai_optimizer_demo.db")
app.config["SQLALCHEMY_ENGINE_OPTIONS"] = {
    "pool_recycle": 300,
    "pool_pre_ping": True,
}

# Initialize the app with the extension
db.init_app(app)

# Database Models
class MonitoringData(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    url = db.Column(db.String(500), nullable=False)
    load_time = db.Column(db.Float, nullable=False)
    memory_usage = db.Column(db.Float, nullable=False)
    timestamp = db.Column(db.DateTime, default=datetime.utcnow)

class SEOAnalysis(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    url = db.Column(db.String(500), nullable=False)
    seo_score = db.Column(db.Integer, nullable=False)
    title_optimized = db.Column(db.Boolean, default=False)
    meta_description = db.Column(db.Text)
    suggestions = db.Column(db.Text)
    timestamp = db.Column(db.DateTime, default=datetime.utcnow)

class AIGeneration(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    type = db.Column(db.String(50), nullable=False)  # text, image, video, audio, code
    prompt = db.Column(db.Text, nullable=False)
    result = db.Column(db.Text)
    status = db.Column(db.String(20), default='pending')  # pending, completed, failed
    timestamp = db.Column(db.DateTime, default=datetime.utcnow)

# Create tables
with app.app_context():
    db.create_all()

# Helper functions for demo data
def generate_demo_monitoring_data():
    """Generate realistic demo monitoring data"""
    urls = [
        'https://example.com',
        'https://example.com/blog',
        'https://example.com/products',
        'https://example.com/contact',
        'https://example.com/about'
    ]
    
    for _ in range(50):
        data = MonitoringData(
            url=random.choice(urls),
            load_time=random.uniform(0.5, 3.5),
            memory_usage=random.uniform(20, 150),
            timestamp=datetime.utcnow()
        )
        db.session.add(data)
    
    db.session.commit()

def generate_demo_seo_data():
    """Generate demo SEO analysis data"""
    urls = [
        'https://example.com',
        'https://example.com/blog',
        'https://example.com/products'
    ]
    
    suggestions = [
        "Optimize meta description length",
        "Add alt text to images",
        "Improve internal linking structure",
        "Enhance page loading speed",
        "Add structured data markup"
    ]
    
    for url in urls:
        analysis = SEOAnalysis(
            url=url,
            seo_score=random.randint(65, 95),
            title_optimized=random.choice([True, False]),
            meta_description="AI-optimized meta description for better search visibility",
            suggestions=json.dumps(random.sample(suggestions, 3))
        )
        db.session.add(analysis)
    
    db.session.commit()

# Routes
@app.route('/')
def dashboard():
    """Main dashboard showing plugin overview"""
    # Get recent monitoring data
    recent_monitoring = MonitoringData.query.order_by(MonitoringData.timestamp.desc()).limit(10).all()
    
    # Get SEO analysis data
    seo_analyses = SEOAnalysis.query.order_by(SEOAnalysis.timestamp.desc()).limit(5).all()
    
    # Get recent AI generations
    recent_generations = AIGeneration.query.order_by(AIGeneration.timestamp.desc()).limit(5).all()
    
    # Calculate some stats
    avg_load_time = db.session.query(db.func.avg(MonitoringData.load_time)).scalar() or 0
    avg_seo_score = db.session.query(db.func.avg(SEOAnalysis.seo_score)).scalar() or 0
    total_generations = AIGeneration.query.count()
    
    return render_template('dashboard.html', 
                         recent_monitoring=recent_monitoring,
                         seo_analyses=seo_analyses,
                         recent_generations=recent_generations,
                         avg_load_time=round(avg_load_time, 2),
                         avg_seo_score=round(avg_seo_score),
                         total_generations=total_generations)

@app.route('/monitoring')
def monitoring():
    """Performance monitoring page"""
    monitoring_data = MonitoringData.query.order_by(MonitoringData.timestamp.desc()).limit(100).all()
    return render_template('monitoring.html', monitoring_data=monitoring_data)

@app.route('/seo-optimization')
def seo_optimization():
    """SEO optimization page"""
    seo_data = SEOAnalysis.query.order_by(SEOAnalysis.timestamp.desc()).all()
    return render_template('seo_optimization.html', seo_data=seo_data)

@app.route('/ai-tools')
def ai_tools():
    """AI content generation tools"""
    generations = AIGeneration.query.order_by(AIGeneration.timestamp.desc()).limit(20).all()
    return render_template('ai_tools.html', generations=generations)

@app.route('/settings')
def settings():
    """Plugin settings page"""
    return render_template('settings.html')

# API Routes
@app.route('/api/run-analysis', methods=['POST'])
def run_analysis():
    """Run SEO analysis on a URL"""
    data = request.get_json()
    url = data.get('url', 'https://example.com')
    
    # Simulate AI analysis
    analysis = SEOAnalysis(
        url=url,
        seo_score=random.randint(70, 95),
        title_optimized=random.choice([True, False]),
        meta_description="AI-generated meta description optimized for search engines",
        suggestions=json.dumps([
            "Optimize page title for target keywords",
            "Improve meta description length",
            "Add structured data markup",
            "Enhance internal linking"
        ])
    )
    
    db.session.add(analysis)
    db.session.commit()
    
    return jsonify({
        'success': True,
        'analysis': {
            'url': analysis.url,
            'seo_score': analysis.seo_score,
            'suggestions': json.loads(analysis.suggestions)
        }
    })

@app.route('/api/generate-content', methods=['POST'])
def generate_content():
    """Generate AI content"""
    data = request.get_json()
    content_type = data.get('type', 'text')
    prompt = data.get('prompt', 'Generate content')
    
    # Simulate AI generation
    if content_type == 'text':
        result = f"AI-generated content based on: '{prompt}'. This is a comprehensive article about {prompt.lower()} that covers all important aspects..."
    elif content_type == 'image':
        result = f"https://via.placeholder.com/800x600/165DFF/FFFFFF?text=AI+Generated+Image"
    elif content_type == 'video':
        result = f"Video generation initiated for: {prompt}"
    elif content_type == 'audio':
        result = f"Audio synthesis completed for: {prompt}"
    elif content_type == 'code':
        result = f"// AI-generated code for: {prompt}\nfunction optimizeWebsite() {{\n    // Implementation here\n    return 'optimized';\n}}"
    else:
        result = f"Generated content for: {prompt}"
    
    generation = AIGeneration(
        type=content_type,
        prompt=prompt,
        result=result,
        status='completed'
    )
    
    db.session.add(generation)
    db.session.commit()
    
    return jsonify({
        'success': True,
        'generation': {
            'type': content_type,
            'prompt': prompt,
            'result': result,
            'id': generation.id
        }
    })

@app.route('/api/test-api', methods=['POST'])
def test_api():
    """Test API connection"""
    # Simulate API test
    return jsonify({
        'success': True,
        'message': 'API connection successful',
        'status': 'connected',
        'models_available': ['Qwen/QwQ-32B', 'Stable Diffusion XL', 'LTX-Video']
    })

@app.route('/api/monitoring-data')
def get_monitoring_data():
    """Get monitoring data for charts"""
    data = MonitoringData.query.order_by(MonitoringData.timestamp.desc()).limit(50).all()
    
    chart_data = {
        'labels': [d.timestamp.strftime('%H:%M') for d in reversed(data)],
        'load_times': [d.load_time for d in reversed(data)],
        'memory_usage': [d.memory_usage for d in reversed(data)]
    }
    
    return jsonify(chart_data)

@app.route('/initialize-demo-data')
def initialize_demo_data():
    """Initialize demo data for testing"""
    try:
        # Clear existing data
        db.session.query(MonitoringData).delete()
        db.session.query(SEOAnalysis).delete()
        db.session.query(AIGeneration).delete()
        
        # Generate new demo data
        generate_demo_monitoring_data()
        generate_demo_seo_data()
        
        # Add some demo AI generations
        demo_generations = [
            AIGeneration(type='text', prompt='Write SEO-optimized blog post about AI', 
                        result='Comprehensive blog post about AI and its applications...', status='completed'),
            AIGeneration(type='image', prompt='Professional website header image', 
                        result='https://via.placeholder.com/1200x400/165DFF/FFFFFF?text=AI+Header', status='completed'),
            AIGeneration(type='code', prompt='WordPress optimization function', 
                        result='function optimize_wp_performance() { /* code */ }', status='completed')
        ]
        
        for gen in demo_generations:
            db.session.add(gen)
        
        db.session.commit()
        flash('Demo data initialized successfully!', 'success')
        
    except Exception as e:
        db.session.rollback()
        flash(f'Error initializing demo data: {str(e)}', 'error')
    
    return redirect(url_for('dashboard'))

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
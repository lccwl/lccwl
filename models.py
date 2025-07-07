from app import db
from datetime import datetime
from sqlalchemy import Column, Integer, String, Text, DateTime, Float, Enum, Boolean

class User(db.Model):
    id = Column(Integer, primary_key=True)
    username = Column(String(64), unique=True, nullable=False)
    email = Column(String(120), unique=True, nullable=False)
    password_hash = Column(String(256))
    created_at = Column(DateTime, default=datetime.utcnow)

class MonitoringData(db.Model):
    __tablename__ = 'monitoring_data'
    
    id = Column(Integer, primary_key=True)
    url = Column(String(500), nullable=False)
    response_time = Column(Float, nullable=False)
    status_code = Column(Integer, nullable=False)
    error_message = Column(Text)
    created_at = Column(DateTime, default=datetime.utcnow)

class SEOAnalysis(db.Model):
    __tablename__ = 'seo_analysis'
    
    id = Column(Integer, primary_key=True)
    url = Column(String(500), nullable=False)
    title = Column(String(255))
    description = Column(Text)
    keywords = Column(Text)
    score = Column(Integer, default=0)
    issues = Column(Text)
    recommendations = Column(Text)
    created_at = Column(DateTime, default=datetime.utcnow)

class AIGeneratedContent(db.Model):
    __tablename__ = 'ai_generated_content'
    
    id = Column(Integer, primary_key=True)
    content_type = Column(Enum('text', 'image', 'video', 'audio', 'code', name='content_types'), nullable=False)
    prompt = Column(Text, nullable=False)
    model = Column(String(100), nullable=False)
    content = Column(Text)
    url = Column(String(500))
    status = Column(Enum('pending', 'processing', 'completed', 'failed', name='status_types'), default='pending')
    error_message = Column(Text)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

class APIUsage(db.Model):
    __tablename__ = 'api_usage'
    
    id = Column(Integer, primary_key=True)
    endpoint = Column(String(100), nullable=False)
    model = Column(String(100))
    tokens_used = Column(Integer, default=0)
    cost = Column(Float, default=0.0)
    response_time = Column(Float, default=0.0)
    status = Column(Enum('success', 'error', name='api_status_types'), default='success')
    error_message = Column(Text)
    created_at = Column(DateTime, default=datetime.utcnow)
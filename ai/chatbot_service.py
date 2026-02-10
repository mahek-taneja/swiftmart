import os
from typing import Optional, Dict, Any

import requests

from forecast_service import generate_forecast


OLLAMA_HOST = os.getenv("OLLAMA_HOST", "http://127.0.0.1:11434")
OLLAMA_MODEL = os.getenv("OLLAMA_MODEL", "llama3")


def check_ollama_availability() -> Dict[str, Any]:
    """Check if Ollama service is available and the model is installed."""
    try:
        # First check if Ollama API is reachable
        resp = requests.get(f"{OLLAMA_HOST}/api/tags", timeout=5)
        resp.raise_for_status()
        models = resp.json().get("models", [])
        model_names = [m.get("name", "") for m in models]
        
        # Check if the configured model is available
        model_available = any(OLLAMA_MODEL in name for name in model_names)
        
        return {
            "available": True,
            "host": OLLAMA_HOST,
            "model_installed": model_available,
            "configured_model": OLLAMA_MODEL,
            "installed_models": model_names
        }
    except requests.exceptions.ConnectionError:
        return {
            "available": False,
            "host": OLLAMA_HOST,
            "error": "Cannot connect to Ollama. Please ensure Ollama is running.",
            "suggestion": "Run 'ollama serve' to start the Ollama service."
        }
    except Exception as e:
        return {
            "available": False,
            "host": OLLAMA_HOST,
            "error": str(e)
        }


def _build_system_context() -> str:
    try:
        kpis, _ = generate_forecast(horizon_days=30)
        recent_avg = kpis.get('recent_avg', 0)
        recent_total = kpis.get('recent_total', 0)
        pred_30_total = kpis.get('pred_30_total', 0)
        trend_pct = kpis.get('trend_pct', 0)
        
        context_lines = [
            "You are an analytics assistant for an e-commerce store.",
            "Be concise (<= 80 words), factual, and reference metrics when helpful.",
            "CRITICAL: Always format ALL monetary amounts in Indian Rupees (₹) with comma separators.",
            "Example: ₹12,345.67 not $12345.67 or just 12345.67",
            f"Recent avg daily sales: ₹{recent_avg:,.2f}",
            f"Last 30d total: ₹{recent_total:,.2f}",
            f"Forecast next 30d total: ₹{pred_30_total:,.2f}",
            f"Short-term trend change: {trend_pct:+.1f}%",
        ]
        return "\n".join(context_lines)
    except Exception:
        # If forecast fails, return basic context
        return "You are an analytics assistant for an e-commerce store. Be concise (<= 80 words) and factual. Always format monetary amounts in Indian Rupees (₹) with proper formatting like ₹12,345.67."


def _generate_fallback_response(message: str, include_forecast_context: bool = True) -> str:
    """Generate a fallback response when Ollama is not available."""
    message_lower = message.lower()
    try:
        kpis, _ = generate_forecast(horizon_days=30)
    except Exception as e:
        # If forecast fails, return basic message
        return f"I can help with sales analytics questions. However, I'm currently in fallback mode. For AI-powered responses, please install and start Ollama (https://ollama.ai/download)."
    
    if include_forecast_context:
        recent_avg = kpis.get('recent_avg', 0)
        recent_total = kpis.get('recent_total', 0)
        pred_30_total = kpis.get('pred_30_total', 0)
        trend_pct = kpis.get('trend_pct', 0)
        
        if any(word in message_lower for word in ['sales', 'revenue']):
            trend_desc = "increasing" if trend_pct > 0 else "decreasing" if trend_pct < 0 else "stable"
            return f"Recent sales average: ₹{recent_avg:,.2f} per day. Last 30 days total: ₹{recent_total:,.2f}. Forecast for next 30 days: ₹{pred_30_total:,.2f}. Trend is {trend_desc} ({trend_pct:+.1f}%)."
        
        if any(word in message_lower for word in ['forecast', 'prediction', 'future']):
            return f"Sales forecast for next 30 days: ₹{pred_30_total:,.2f} total, averaging ₹{pred_30_total/30:,.2f} per day. Current trend: {trend_pct:+.1f}% change."
        
        if any(word in message_lower for word in ['trend', 'performance', 'how']):
            return f"Sales performance shows {abs(trend_pct):.1f}% {'growth' if trend_pct > 0 else 'decline'}. Recent daily average: ₹{recent_avg:,.2f}. Forecast suggests continuation of this trend."
    else:
        return "I can help with sales analytics. Please include forecast context for detailed answers, or install and start Ollama for AI-powered responses."
    
    return f"Based on current data: Average daily sales ₹{kpis.get('recent_avg', 0):,.2f}, 30-day total ₹{kpis.get('recent_total', 0):,.2f}. Forecast: ₹{kpis.get('pred_30_total', 0):,.2f} for next 30 days."


def ask_chatbot(message: str, include_forecast_context: bool = True) -> Dict[str, Any]:
    if not message:
        return {"answer": "Please provide a question.", "model": OLLAMA_MODEL}

    system_ctx = _build_system_context(
    ) if include_forecast_context else "Be concise and factual."

    payload = {
        "model": OLLAMA_MODEL,
        "messages": [
            {"role": "system", "content": system_ctx},
            {"role": "user", "content": message}
        ],
        "stream": False
    }

    # Try to connect to Ollama, but gracefully fallback if unavailable
    # CATCH ALL POSSIBLE ERRORS - no exceptions should leak through
    ollama_available = False
    try:
        # Quick check if Ollama is reachable before attempting chat
        check_resp = requests.get(f"{OLLAMA_HOST}/api/tags", timeout=2)
        ollama_available = (check_resp.status_code == 200)
    except Exception:
        ollama_available = False
    
    if ollama_available:
        try:
            resp = requests.post(f"{OLLAMA_HOST}/api/chat",
                                 json=payload, timeout=5)
            resp.raise_for_status()
            data = resp.json()
            answer = (data.get("message", {}) or {}).get("content", "")
            if answer and answer.strip():
                # Validate answer doesn't contain error messages
                answer_lower = answer.lower()
                if any(err_word in answer_lower for err_word in ["failed", "error", "exception", "traceback", "connection", "httpconnectionpool", "max retries"]):
                    raise Exception("Answer contains error")
                return {"answer": answer.strip(), "model": OLLAMA_MODEL}
        except Exception as e:
            # Explicitly catch and ignore - never return error messages
            pass  # Fall through to fallback
    
    # ALWAYS use fallback if Ollama is not available or failed
    try:
        fallback_answer = _generate_fallback_response(message, include_forecast_context)
        # Validate fallback answer is clean
        if fallback_answer and isinstance(fallback_answer, str):
            return {
                "answer": fallback_answer,
                "model": "fallback",
                "warning": "Using forecast data - Ollama not available"
            }
    except Exception:
        pass
    
    # Ultimate fallback - guaranteed clean response
    return {
        "answer": "I can help with sales analytics. Please include forecast context for detailed answers, or install and start Ollama for AI-powered responses (https://ollama.ai/download).",
        "model": "fallback",
        "warning": "Using forecast data mode"
    }

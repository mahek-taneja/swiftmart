import os
import json
import logging
from flask import Flask, request, jsonify
from dotenv import load_dotenv

from forecast_service import generate_forecast
from chatbot_service import ask_chatbot, check_ollama_availability

# Suppress urllib3 connection warnings when Ollama is not available
logging.getLogger("urllib3").setLevel(logging.ERROR)
logging.getLogger("requests").setLevel(logging.ERROR)


load_dotenv(dotenv_path=os.path.join(os.path.dirname(__file__), "..", ".env"))

app = Flask(__name__)


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"})


@app.route("/health/ollama", methods=["GET"])
def health_ollama():
    """Check Ollama service availability and model status."""
    status = check_ollama_availability()
    if status.get("available"):
        return jsonify(status), 200
    else:
        return jsonify(status), 503


@app.route("/forecast", methods=["GET"])
def forecast():
    try:
        horizon = int(request.args.get("horizon", "90"))
        if horizon not in (30, 60, 90):
            horizon = 90
        vendor_id = request.args.get("vendor_id")
        vid = int(vendor_id) if vendor_id is not None and str(vendor_id).isdigit() else None
        kpis, chart_json = generate_forecast(horizon, vendor_id=vid)
        return jsonify({"kpis": kpis, "chart": json.loads(chart_json)})
    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route("/chat", methods=["POST"])
def chat():
    try:
        payload = request.get_json(force=True) or {}
        message = (payload.get("message") or "").strip()
        include_ctx = bool(payload.get("include_context", True))
        
        # Call chatbot - it will ALWAYS return a valid dict with 'answer' field
        answer = ask_chatbot(message, include_forecast_context=include_ctx)
        
        # Double-check: ensure answer field exists and doesn't contain error messages
        if not isinstance(answer, dict):
            answer = {"answer": "I'm currently using forecast data mode.", "model": "fallback"}
        
        if "answer" not in answer:
            answer["answer"] = "I can help with sales analytics questions."
            answer["model"] = answer.get("model", "fallback")
        
        # Sanitize: remove any error messages that might have leaked through
        answer_text = str(answer.get("answer", "")).lower()
        # Check for ALL possible error indicators
        error_indicators = [
            "failed", "error", "exception", "traceback", "connectionerror", 
            "connection", "refused", "timeout", "max retries", "httpconnectionpool",
            "newconnectionerror", "10061", "urllib3", "requests.exceptions"
        ]
        if any(err_word in answer_text for err_word in error_indicators):
            # Force fallback response
            try:
                from chatbot_service import _generate_fallback_response
                fallback = _generate_fallback_response(message, include_ctx)
                answer["answer"] = fallback
            except Exception:
                answer["answer"] = "I'm currently using forecast data mode. For AI-powered responses, please install and start Ollama."
            answer["warning"] = "Using forecast data - Ollama unavailable"
            answer["model"] = "fallback"
        
        # Ensure all amounts are in rupees format (convert $ to ₹)
        import re
        answer_str = answer.get("answer", "")
        # Replace dollar signs with rupee symbol
        answer_str = re.sub(r'\$\s*(\d+(?:,\d{3})*(?:\.\d{2})?)', r'₹\1', answer_str)
        # Replace "dollars" or "USD" with rupees
        answer_str = re.sub(r'(\d+(?:,\d{3})*(?:\.\d{2})?)\s*(?:dollars?|USD)', r'₹\1', answer_str, flags=re.IGNORECASE)
        answer["answer"] = answer_str
        
        return jsonify(answer)
    except Exception:
        # Ultimate fallback - return a safe response
        return jsonify({
            "answer": "I can help with sales analytics. Please try asking about sales trends, forecasts, or revenue.",
            "model": "fallback",
            "warning": "Using forecast data mode"
        })


def create_app():
    return app


if __name__ == "__main__":
    port = int(os.getenv("FLASK_PORT", "5055"))
    app.run(host="0.0.0.0", port=port, debug=False)

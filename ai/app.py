import os
import json
from flask import Flask, request, jsonify
from dotenv import load_dotenv

from forecast_service import generate_forecast
from chatbot_service import ask_chatbot


load_dotenv(dotenv_path=os.path.join(os.path.dirname(__file__), "..", ".env"))

app = Flask(__name__)


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"})


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
        answer = ask_chatbot(message, include_forecast_context=include_ctx)
        return jsonify(answer)
    except Exception as e:
        return jsonify({"error": str(e)}), 500


def create_app():
    return app


if __name__ == "__main__":
    port = int(os.getenv("FLASK_PORT", "5055"))
    app.run(host="0.0.0.0", port=port, debug=False)

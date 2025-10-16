import os
from typing import Optional, Dict, Any

import requests

from forecast_service import generate_forecast


OLLAMA_HOST = os.getenv("OLLAMA_HOST", "http://127.0.0.1:11434")
OLLAMA_MODEL = os.getenv("OLLAMA_MODEL", "llama3")


def _build_system_context() -> str:
    kpis, _ = generate_forecast(horizon_days=30)
    context_lines = [
        "You are an analytics assistant for an e-commerce store.",
        "Be concise (<= 80 words), factual, and reference metrics when helpful.",
        f"Recent avg daily sales: {kpis.get('recent_avg', 0)}",
        f"Last 30d total: {kpis.get('recent_total', 0)}",
        f"Forecast next 30d total: {kpis.get('pred_30_total', 0)}",
        f"Short-term trend change: {kpis.get('trend_pct', 0)}%",
    ]
    return "\n".join(context_lines)


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

    try:
        resp = requests.post(f"{OLLAMA_HOST}/api/chat",
                             json=payload, timeout=60)
        resp.raise_for_status()
        data = resp.json()
        answer = (data.get("message", {}) or {}).get("content", "")
        return {"answer": answer.strip(), "model": OLLAMA_MODEL}
    except requests.RequestException as e:
        return {"answer": f"Ollama request failed: {e}", "model": OLLAMA_MODEL}

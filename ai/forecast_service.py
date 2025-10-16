import os
import json
from typing import Dict, Any, Tuple, Optional

import pandas as pd
import numpy as np
import mysql.connector
from prophet import Prophet
import plotly.graph_objs as go
import plotly


def _get_db_connection():
    return mysql.connector.connect(
        host=os.getenv("DB_HOST", "127.0.0.1"),
        user=os.getenv("DB_USER", "root"),
        password=os.getenv("DB_PASS", ""),
        database=os.getenv("DB_NAME", "swiftmart"),
        port=int(os.getenv("DB_PORT", "3307"))
    )


def _fetch_sales_timeseries(vendor_id: Optional[int] = None) -> pd.DataFrame:
    # Note: Current orders table doesn't have vendor_id column
    # For now, return all orders regardless of vendor_id
    query = """
        SELECT DATE(created_at) AS ds, SUM(total_amount) AS y
        FROM orders
        GROUP BY DATE(created_at) 
        ORDER BY ds ASC
    """
    conn = _get_db_connection()
    try:
        cursor = conn.cursor()
        cursor.execute(query)
        rows = cursor.fetchall()
        columns = [desc[0] for desc in cursor.description]
        df = pd.DataFrame(rows, columns=columns)
    finally:
        conn.close()

    # Ensure correct dtypes
    df["ds"] = pd.to_datetime(df["ds"])  # Prophet expects 'ds'
    df["y"] = pd.to_numeric(df["y"], errors="coerce").fillna(0.0)
    return df


def _compute_kpis(history_df: pd.DataFrame, forecast_df: pd.DataFrame) -> Dict[str, Any]:
    if history_df.empty:
        return {
            "days": 0,
            "recent_avg": 0.0,
            "recent_total": 0.0,
            "pred_30_total": 0.0,
            "pred_60_total": 0.0,
            "pred_90_total": 0.0,
            "trend_pct": 0.0
        }

    history_df = history_df.sort_values("ds")
    last_30 = history_df[history_df["ds"] >= (
        history_df["ds"].max() - pd.Timedelta(days=29))]
    recent_avg = float(last_30["y"].mean()) if not last_30.empty else float(
        history_df["y"].mean())
    recent_total = float(last_30["y"].sum()) if not last_30.empty else float(
        history_df["y"].sum())

    f30 = forecast_df[forecast_df["ds"] <=
                      forecast_df["ds"].min() + pd.Timedelta(days=29)]
    f60 = forecast_df[forecast_df["ds"] <=
                      forecast_df["ds"].min() + pd.Timedelta(days=59)]
    f90 = forecast_df[forecast_df["ds"] <=
                      forecast_df["ds"].min() + pd.Timedelta(days=89)]

    pred_30_total = float(f30["yhat"].sum()) if not f30.empty else 0.0
    pred_60_total = float(f60["yhat"].sum()) if not f60.empty else 0.0
    pred_90_total = float(f90["yhat"].sum()) if not f90.empty else 0.0

    # Simple trend: compare last 7d avg vs previous 7d avg
    last_7 = history_df[history_df["ds"] >
                        history_df["ds"].max() - pd.Timedelta(days=7)]["y"].mean()
    prev_7 = history_df[(history_df["ds"] <= history_df["ds"].max() - pd.Timedelta(days=7))
                        & (history_df["ds"] > history_df["ds"].max() - pd.Timedelta(days=14))]["y"].mean()
    if np.isfinite(prev_7) and prev_7 != 0 and np.isfinite(last_7):
        trend_pct = float((last_7 - prev_7) / abs(prev_7) * 100.0)
    else:
        trend_pct = 0.0

    return {
        "days": int(history_df.shape[0]),
        "recent_avg": round(recent_avg, 2),
        "recent_total": round(recent_total, 2),
        "pred_30_total": round(pred_30_total, 2),
        "pred_60_total": round(pred_60_total, 2),
        "pred_90_total": round(pred_90_total, 2),
        "trend_pct": round(trend_pct, 2)
    }


def _build_plot(history_df: pd.DataFrame, forecast_df: pd.DataFrame) -> str:
    fig = go.Figure()
    if not history_df.empty:
        fig.add_trace(go.Scatter(
            x=history_df["ds"], y=history_df["y"], mode="lines+markers", name="Actual"))
    fig.add_trace(go.Scatter(
        x=forecast_df["ds"], y=forecast_df["yhat"], mode="lines", name="Forecast"))
    fig.add_trace(go.Scatter(x=forecast_df["ds"], y=forecast_df["yhat_lower"], mode="lines", line=dict(
        width=0), showlegend=False))
    fig.add_trace(go.Scatter(x=forecast_df["ds"], y=forecast_df["yhat_upper"],
                  mode="lines", fill="tonexty", line=dict(width=0), name="Confidence"))
    fig.update_layout(
        title="Daily Sales Forecast",
        xaxis_title="Date",
        yaxis_title="Sales",
        template="plotly_white",
        height=400,
        margin=dict(l=40, r=20, t=40, b=40)
    )
    return json.dumps(fig, cls=plotly.utils.PlotlyJSONEncoder)


def generate_forecast(horizon_days: int = 90, vendor_id: Optional[int] = None) -> Tuple[Dict[str, Any], str]:
    history = _fetch_sales_timeseries(vendor_id=vendor_id)
    model = Prophet(daily_seasonality=True,
                    weekly_seasonality=True, yearly_seasonality=True)
    if not history.empty:
        model.fit(history)
    future = model.make_future_dataframe(periods=horizon_days)
    forecast = model.predict(future)

    # Only future portion for plotting forecast band
    future_forecast = forecast[forecast["ds"] > history["ds"].max(
    )] if not history.empty else forecast.tail(horizon_days)
    kpis = _compute_kpis(history, future_forecast)

    # Build full plot: actual + future forecast
    plot_df = future_forecast.copy()
    chart_json = _build_plot(history, plot_df)

    return kpis, chart_json

# PowerShell script to start Ollama service in the background
# This script starts Ollama and keeps it running

Write-Host "Starting Ollama service..." -ForegroundColor Cyan

# Check if Ollama is installed
try {
    $null = ollama --version 2>&1
} catch {
    Write-Host "Error: Ollama is not installed!" -ForegroundColor Red
    Write-Host "Please install Ollama from https://ollama.ai/download" -ForegroundColor Yellow
    exit 1
}

# Start Ollama serve in background
Write-Host "Starting Ollama on http://127.0.0.1:11434..." -ForegroundColor Yellow
Start-Process -NoNewWindow ollama -ArgumentList "serve"

# Wait a moment for it to start
Start-Sleep -Seconds 3

# Check if it's running
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:11434/api/tags" -TimeoutSec 3 -ErrorAction Stop
    Write-Host "✓ Ollama service is running successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Ollama is now available at http://127.0.0.1:11434" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Note: This window must remain open to keep Ollama running." -ForegroundColor Yellow
    Write-Host "Press Ctrl+C to stop the service." -ForegroundColor Yellow
} catch {
    Write-Host "⚠ Warning: Could not verify Ollama is running" -ForegroundColor Yellow
    Write-Host "  Error: $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "You may need to manually run: ollama serve" -ForegroundColor Yellow
}

# Keep the script running
Write-Host ""
Write-Host "Press Ctrl+C to stop Ollama..." -ForegroundColor Gray
try {
    while ($true) {
        Start-Sleep -Seconds 10
    }
} catch {
    Write-Host ""
    Write-Host "Stopping Ollama..." -ForegroundColor Yellow
}


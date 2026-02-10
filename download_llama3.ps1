# Download llama3 model for Ollama
Write-Host "Downloading llama3 model..." -ForegroundColor Cyan
Write-Host "This will take 2-5 minutes (~4.7GB)" -ForegroundColor Yellow
Write-Host ""

# Check if Ollama is running
try {
    $test = Invoke-WebRequest -Uri "http://127.0.0.1:11434/api/tags" -TimeoutSec 3 -UseBasicParsing
    Write-Host "Ollama is running, downloading llama3..." -ForegroundColor Green
} catch {
    Write-Host "ERROR: Ollama is not running. Please start Ollama GUI or run 'ollama serve'" -ForegroundColor Red
    exit 1
}

# Download via API (streaming)
Write-Host "Downloading model..." -ForegroundColor Yellow
Write-Host "Progress will be shown below..." -ForegroundColor Gray
Write-Host ""

try {
    $body = @{name="llama3"} | ConvertTo-Json
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:11434/api/pull" -Method Post -Body $body -ContentType "application/json" -TimeoutSec 0
    
    # Parse response (might be streamed)
    Write-Host "Download initiated. Check Ollama GUI for progress." -ForegroundColor Green
    Write-Host ""
    Write-Host "Alternatively, you can use the Ollama GUI:" -ForegroundColor Yellow
    Write-Host "1. Click the model dropdown" -ForegroundColor White
    Write-Host "2. Search for 'llama3'" -ForegroundColor White
    Write-Host "3. Click to download" -ForegroundColor White
} catch {
    Write-Host "API download failed. Use one of these methods:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Method 1: In Ollama GUI" -ForegroundColor Cyan
    Write-Host "  - Click model dropdown" -ForegroundColor White
    Write-Host "  - Search 'llama3' and download" -ForegroundColor White
    Write-Host ""
    Write-Host "Method 2: In terminal (if ollama command works)" -ForegroundColor Cyan
    Write-Host "  ollama pull llama3" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Once llama3 is downloaded, your chatbot will work!" -ForegroundColor Green


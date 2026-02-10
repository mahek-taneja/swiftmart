# Finish Ollama Setup Script
# Run this AFTER you've installed Ollama (double-clicked OllamaSetup.exe)

Write-Host "Finishing Ollama Setup..." -ForegroundColor Cyan
Write-Host ""

# Step 1: Check if Ollama is available
Write-Host "[1/3] Checking Ollama..." -ForegroundColor Yellow
try {
    $version = ollama --version 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Ollama found: $version" -ForegroundColor Green
    } else {
        Write-Host "ERROR: Ollama not found. Please install it first, then close and reopen this terminal." -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "ERROR: Ollama not found. Please install it first, then close and reopen this terminal." -ForegroundColor Red
    exit 1
}

# Step 2: Start Ollama service
Write-Host ""
Write-Host "[2/3] Starting Ollama service..." -ForegroundColor Yellow
Start-Process -WindowStyle Hidden ollama -ArgumentList "serve"
Start-Sleep -Seconds 3

# Wait for service to be ready
$ready = $false
for ($i = 0; $i -lt 10; $i++) {
    try {
        $test = Invoke-WebRequest -Uri "http://127.0.0.1:11434/api/tags" -TimeoutSec 2 -UseBasicParsing -ErrorAction Stop
        $ready = $true
        break
    } catch {
        Start-Sleep -Seconds 1
    }
}

if ($ready) {
    Write-Host "Ollama service is running" -ForegroundColor Green
} else {
    Write-Host "WARNING: Could not verify Ollama is running. It may need more time." -ForegroundColor Yellow
}

# Step 3: Check and download model
Write-Host ""
Write-Host "[3/3] Checking llama3 model..." -ForegroundColor Yellow
try {
    $models = Invoke-RestMethod -Uri "http://127.0.0.1:11434/api/tags" -TimeoutSec 3
    $hasModel = $models.models | Where-Object { $_.name -like "*llama3*" }
    if ($hasModel) {
        Write-Host "llama3 model is already installed!" -ForegroundColor Green
    } else {
        Write-Host "Downloading llama3 model (this takes 2-5 minutes)..." -ForegroundColor Yellow
        ollama pull llama3
        if ($LASTEXITCODE -eq 0) {
            Write-Host "llama3 model downloaded!" -ForegroundColor Green
        } else {
            Write-Host "WARNING: Model download had issues. Try running: ollama pull llama3" -ForegroundColor Yellow
        }
    }
} catch {
    Write-Host "Waiting for Ollama to start, then downloading model..." -ForegroundColor Yellow
    Start-Sleep -Seconds 3
    ollama pull llama3
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Ollama is ready! Your chatbot should work now." -ForegroundColor Green
Write-Host ""


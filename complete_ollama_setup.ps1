# Complete Ollama Setup Script
# Run this AFTER restarting your terminal after Ollama installation

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Completing Ollama Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if Ollama is available
Write-Host "Checking Ollama installation..." -ForegroundColor Yellow
try {
    $version = ollama --version 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Ollama is installed: $version" -ForegroundColor Green
    } else {
        throw "Ollama not in PATH"
    }
} catch {
    Write-Host "✗ Ollama is not accessible. Please restart your terminal." -ForegroundColor Red
    Write-Host "  After restart, Ollama should be available in PATH." -ForegroundColor Yellow
    exit 1
}

# Check if Ollama service is running
Write-Host ""
Write-Host "Checking if Ollama service is running..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:11434/api/tags" -TimeoutSec 3 -ErrorAction Stop
    Write-Host "✓ Ollama service is already running" -ForegroundColor Green
    $isRunning = $true
} catch {
    Write-Host "⚠ Ollama service is not running" -ForegroundColor Yellow
    Write-Host "  Starting Ollama service in background..." -ForegroundColor Yellow
    Start-Process -WindowStyle Hidden ollama -ArgumentList "serve"
    Start-Sleep -Seconds 3
    
    # Verify it started
    try {
        $response = Invoke-WebRequest -Uri "http://127.0.0.1:11434/api/tags" -TimeoutSec 3 -ErrorAction Stop
        Write-Host "✓ Ollama service started successfully" -ForegroundColor Green
        $isRunning = $true
    } catch {
        Write-Host "✗ Could not start Ollama service automatically" -ForegroundColor Red
        Write-Host "  Please run 'ollama serve' in a separate terminal manually." -ForegroundColor Yellow
        $isRunning = $false
    }
}

# Check if llama3 model is installed
Write-Host ""
Write-Host "Checking if llama3 model is installed..." -ForegroundColor Yellow
if ($isRunning) {
    try {
        $models = (Invoke-RestMethod -Uri "http://127.0.0.1:11434/api/tags").models
        $modelNames = $models | ForEach-Object { $_.name }
        $hasLlama3 = $modelNames | Where-Object { $_ -like "*llama3*" }
        
        if ($hasLlama3) {
            Write-Host "✓ llama3 model is installed" -ForegroundColor Green
            Write-Host "  Installed models: $($modelNames -join ', ')" -ForegroundColor Gray
        } else {
            Write-Host "✗ llama3 model is not installed" -ForegroundColor Red
            Write-Host ""
            Write-Host "Downloading llama3 model (this may take several minutes, ~4.7GB)..." -ForegroundColor Yellow
            Write-Host "Please wait..." -ForegroundColor Gray
            ollama pull llama3
            if ($LASTEXITCODE -eq 0) {
                Write-Host "✓ llama3 model downloaded successfully" -ForegroundColor Green
            } else {
                Write-Host "✗ Failed to download llama3 model" -ForegroundColor Red
                Write-Host "  Please run manually: ollama pull llama3" -ForegroundColor Yellow
                exit 1
            }
        }
    } catch {
        Write-Host "✗ Could not check installed models: $_" -ForegroundColor Red
    }
} else {
    Write-Host "⚠ Cannot check models - Ollama service is not running" -ForegroundColor Yellow
    Write-Host "  Please start Ollama with: ollama serve" -ForegroundColor Yellow
    Write-Host "  Then run: ollama pull llama3" -ForegroundColor Yellow
}

# Final verification
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Verifying Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

if ($isRunning) {
    try {
        $status = Invoke-RestMethod -Uri "http://127.0.0.1:11434/api/tags"
        Write-Host "✓ Ollama is running and accessible" -ForegroundColor Green
        Write-Host "  Host: http://127.0.0.1:11434" -ForegroundColor Gray
        Write-Host "  Models: $($status.models.Count)" -ForegroundColor Gray
        
        # Check if llama3 is available
        $hasLlama3 = $status.models | Where-Object { $_.name -like "*llama3*" }
        if ($hasLlama3) {
            Write-Host "✓ llama3 model is ready" -ForegroundColor Green
            Write-Host ""
            Write-Host "========================================" -ForegroundColor Cyan
            Write-Host "Setup Complete! ✓" -ForegroundColor Green
            Write-Host "========================================" -ForegroundColor Cyan
            Write-Host ""
            Write-Host "Ollama is ready to use with SwiftMart chatbot." -ForegroundColor Green
            Write-Host ""
            Write-Host "Next steps:" -ForegroundColor Yellow
            Write-Host "  1. Start your Flask service:" -ForegroundColor White
            Write-Host "     cd ai" -ForegroundColor Gray
            Write-Host "     python app.py" -ForegroundColor Gray
            Write-Host ""
            Write-Host "  2. Test the chatbot:" -ForegroundColor White
            Write-Host '     GET http://127.0.0.1:5055/health/ollama' -ForegroundColor Gray
            Write-Host ""
        } else {
            Write-Host "⚠ llama3 model not found. Please run: ollama pull llama3" -ForegroundColor Yellow
        }
    } catch {
        Write-Host "✗ Could not verify Ollama setup: $_" -ForegroundColor Red
    }
} else {
    Write-Host "⚠ Setup incomplete - Ollama service must be running" -ForegroundColor Yellow
}

Write-Host ""


# PowerShell script to set up and run Ollama for SwiftMart
# Run this script from the project root directory

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "SwiftMart Ollama Setup Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if .env file exists
if (-not (Test-Path ".env")) {
    Write-Host "Creating .env file from .env.example..." -ForegroundColor Yellow
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Write-Host "✓ .env file created" -ForegroundColor Green
    } else {
        Write-Host "⚠ .env.example not found. Please create .env manually." -ForegroundColor Yellow
    }
}

# Check if Ollama is installed
Write-Host "Checking if Ollama is installed..." -ForegroundColor Yellow
try {
    $ollamaVersion = ollama --version 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Ollama is installed: $ollamaVersion" -ForegroundColor Green
    } else {
        throw "Ollama not found"
    }
} catch {
    Write-Host "✗ Ollama is not installed" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install Ollama using one of these methods:" -ForegroundColor Yellow
    Write-Host "  1. Download from: https://ollama.ai/download" -ForegroundColor White
    Write-Host "  2. Or use chocolatey: choco install ollama" -ForegroundColor White
    Write-Host ""
    Write-Host "After installation, run this script again." -ForegroundColor Yellow
    exit 1
}

# Check if Ollama service is running
Write-Host ""
Write-Host "Checking if Ollama service is running..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:11434/api/tags" -TimeoutSec 3 -ErrorAction Stop
    Write-Host "✓ Ollama service is running" -ForegroundColor Green
    $isRunning = $true
} catch {
    Write-Host "✗ Ollama service is not running" -ForegroundColor Red
    Write-Host ""
    Write-Host "Starting Ollama service..." -ForegroundColor Yellow
    Write-Host "Note: You may need to run 'ollama serve' manually in a separate terminal." -ForegroundColor Yellow
    $isRunning = $false
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
            Write-Host "Downloading llama3 model (this may take several minutes)..." -ForegroundColor Yellow
            ollama pull llama3
            if ($LASTEXITCODE -eq 0) {
                Write-Host "✓ llama3 model downloaded successfully" -ForegroundColor Green
            } else {
                Write-Host "✗ Failed to download llama3 model" -ForegroundColor Red
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

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "  1. If Ollama service is not running, start it in a separate terminal:" -ForegroundColor White
Write-Host "     ollama serve" -ForegroundColor Gray
Write-Host ""
Write-Host "  2. Start the Flask service:" -ForegroundColor White
Write-Host "     cd ai" -ForegroundColor Gray
Write-Host "     python app.py" -ForegroundColor Gray
Write-Host ""
Write-Host "  3. Test the chatbot endpoint:" -ForegroundColor White
Write-Host '     GET http://127.0.0.1:5055/health/ollama' -ForegroundColor Gray
Write-Host ""


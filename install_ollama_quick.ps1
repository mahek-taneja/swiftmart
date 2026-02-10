# Quick Ollama Installation Script
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Quick Ollama Installation" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Download Ollama
Write-Host "[1/4] Downloading Ollama installer..." -ForegroundColor Yellow
$installerUrl = "https://ollama.com/download/OllamaSetup.exe"
$installerPath = "$env:TEMP\OllamaSetup.exe"

try {
    Invoke-WebRequest -Uri $installerUrl -OutFile $installerPath -UseBasicParsing -ErrorAction Stop
    Write-Host "Downloaded successfully" -ForegroundColor Green
} catch {
    Write-Host "Download failed. Trying alternative URL..." -ForegroundColor Red
    try {
        $installerUrl = "https://github.com/ollama/ollama/releases/latest/download/OllamaSetup.exe"
        Invoke-WebRequest -Uri $installerUrl -OutFile $installerPath -UseBasicParsing -ErrorAction Stop
        Write-Host "Downloaded from GitHub" -ForegroundColor Green
    } catch {
        Write-Host "Both download attempts failed. Please download manually from https://ollama.ai/download" -ForegroundColor Red
        exit 1
    }
}

# Step 2: Install Ollama
Write-Host ""
Write-Host "[2/4] Installing Ollama (silent install)..." -ForegroundColor Yellow
try {
    Start-Process -FilePath $installerPath -ArgumentList "/S" -Wait -NoNewWindow
    Write-Host "Installation completed" -ForegroundColor Green
    Start-Sleep -Seconds 2
} catch {
    Write-Host "Installation failed: $_" -ForegroundColor Red
    exit 1
}

# Step 3: Refresh PATH
Write-Host ""
Write-Host "[3/4] Refreshing environment..." -ForegroundColor Yellow
try {
    $machinePath = [System.Environment]::GetEnvironmentVariable("Path", "Machine")
    $userPath = [System.Environment]::GetEnvironmentVariable("Path", "User")
    $env:Path = "$machinePath;$userPath"
    Start-Sleep -Seconds 2
} catch {
    Write-Host "Could not refresh PATH automatically" -ForegroundColor Yellow
}

# Verify installation
Write-Host "Checking installation..." -ForegroundColor Gray
try {
    $version = & ollama --version 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Ollama installed: $version" -ForegroundColor Green
    } else {
        Write-Host "Ollama installed but not in PATH yet. Restart terminal after this." -ForegroundColor Yellow
    }
} catch {
    Write-Host "Ollama installed but PATH not updated yet." -ForegroundColor Yellow
    Write-Host "You may need to restart your terminal." -ForegroundColor Yellow
}

# Step 4: Start Ollama and pull model
Write-Host ""
Write-Host "[4/4] Starting Ollama and downloading llama3 model..." -ForegroundColor Yellow
Write-Host "(This may take a few minutes to download ~4.7GB)" -ForegroundColor Gray

try {
    Start-Process -WindowStyle Hidden ollama -ArgumentList "serve"
    Start-Sleep -Seconds 3
    
    # Wait for Ollama to be ready
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
        Write-Host "Ollama service started" -ForegroundColor Green
        
        # Pull llama3 model
        Write-Host "Downloading llama3 model..." -ForegroundColor Gray
        & ollama pull llama3
        if ($LASTEXITCODE -eq 0) {
            Write-Host "llama3 model downloaded" -ForegroundColor Green
        } else {
            Write-Host "Model download had issues. Run manually: ollama pull llama3" -ForegroundColor Yellow
        }
    } else {
        Write-Host "Ollama started but not ready yet. Please run manually:" -ForegroundColor Yellow
        Write-Host "  ollama serve" -ForegroundColor Gray
        Write-Host "  ollama pull llama3" -ForegroundColor Gray
    }
} catch {
    Write-Host "Could not start Ollama automatically. Please run:" -ForegroundColor Yellow
    Write-Host "  ollama serve" -ForegroundColor Gray
    Write-Host "  ollama pull llama3" -ForegroundColor Gray
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Installation Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Ollama should now be running. Test your chatbot!" -ForegroundColor Green
Write-Host ""

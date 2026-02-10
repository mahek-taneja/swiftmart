@echo off
REM Batch script to start Ollama service for SwiftMart
REM This script checks if Ollama is installed and starts the service

echo ========================================
echo SwiftMart Ollama Startup
echo ========================================
echo.

REM Check if Ollama is installed
where ollama >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Ollama is not installed!
    echo Please install Ollama from https://ollama.ai/download
    echo.
    pause
    exit /b 1
)

echo Starting Ollama service on http://127.0.0.1:11434...
echo.

REM Start Ollama serve
start "Ollama Service" ollama serve

REM Wait a moment
timeout /t 3 /nobreak >nul

REM Check if it's running
echo Checking if Ollama is running...
curl -s http://127.0.0.1:11434/api/tags >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [SUCCESS] Ollama service is running!
    echo.
    echo Ollama is now available at http://127.0.0.1:11434
    echo The service is running in a separate window.
    echo Close that window to stop Ollama.
) else (
    echo [WARNING] Could not verify Ollama is running
    echo You may need to check if Ollama started correctly.
)

echo.
pause


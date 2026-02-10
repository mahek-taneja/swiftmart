@echo off
REM Batch script to complete Ollama setup after installation
REM This opens in a new PowerShell window with fresh environment

echo ========================================
echo Completing Ollama Setup
echo ========================================
echo.

REM Open PowerShell in new window with the completion script
start powershell -NoExit -Command "cd '%~dp0'; .\complete_ollama_setup.ps1"

echo.
echo A new PowerShell window will open to complete the setup.
echo Please wait for it to finish downloading the llama3 model.
pause


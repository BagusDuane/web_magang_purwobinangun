@echo off
setlocal
cd /d "%~dp0"
title Setup Otomatis Proyek Kalurahan Purwobinangun

echo ========================================================
echo  SETUP OTOMATISASI PROYEK KALURAHAN PURWOBINANGUN
echo ========================================================
echo.

where powershell >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] PowerShell tidak ditemukan pada sistem ini.
    pause
    exit /b 1
)

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0setup.ps1"

echo.
pause

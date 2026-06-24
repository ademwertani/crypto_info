@echo off
title Crypto Info — Dev Server
color 0B

echo.
echo  ===================================================
echo   Crypto Info — Starting Development Environment
echo  ===================================================
echo.

echo  [1/4] Checking dependencies...
where node >nul 2>&1 || (echo  ERROR: Node.js not found. Install from nodejs.org && pause && exit /b 1)
where php  >nul 2>&1 || (echo  ERROR: PHP not found. Check your PATH && pause && exit /b 1)

echo  [2/4] Running database migrations...
php artisan migrate --force
if %ERRORLEVEL% neq 0 (
    echo  ERROR: Migrations failed. Check your DB_* settings in .env
    pause
    exit /b 1
)

echo  [3/4] Fetching latest crypto data from CoinGecko...
php artisan app:fetch-crypto-data

echo  [4/4] Starting servers...
echo.
echo   Web app  : http://127.0.0.1:8000
echo   Press Ctrl+C to stop all servers.
echo.

REM Start Vite dev server in a separate window (assets hot-reload)
start "Vite Dev Server" /min cmd /c "npm run dev & pause"

REM Wait briefly for Vite to boot before serving PHP
timeout /t 3 /nobreak >nul

REM Start PHP scheduler in a separate window
start "Laravel Scheduler" /min cmd /c "php artisan schedule:work & pause"

REM Run PHP dev server in this window (blocking — Ctrl+C stops everything)
php artisan serve

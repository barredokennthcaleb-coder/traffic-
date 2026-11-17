@echo off
REM ---------------------------------------------------------------------------
REM Installs the database watchdog (runs hidden, starts with Windows).
REM Run once. Safe to run again.
REM ---------------------------------------------------------------------------
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0install-watchdog.ps1"
echo.
pause

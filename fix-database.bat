@echo off
REM ---------------------------------------------------------------------------
REM One-click repair for the Traffic System database login/privileges.
REM Safe to run any time. Reads the account details from .env.
REM ---------------------------------------------------------------------------
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0fix-database.ps1"
echo.
pause

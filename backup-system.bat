@echo off
REM ---------------------------------------------------------------------------
REM Local backup of the Traffic System (database + files).
REM Double-click this file to create a backup in writable\backups\system_backup\
REM ---------------------------------------------------------------------------

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0backup-system.ps1"

echo.
pause

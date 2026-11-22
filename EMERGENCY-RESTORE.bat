@echo off
REM ---------------------------------------------------------------------------
REM EMERGENCY RESTORE - imports the newest verified database backup.
REM
REM This OVERWRITES the live traffic_live database. Use it only when the live
REM database is damaged and the owner has approved the restore.
REM
REM Preview without changing anything:
REM   powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0restore-latest-backup.ps1" -Preview
REM ---------------------------------------------------------------------------
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0restore-latest-backup.ps1" %*
echo.
pause

@echo off
REM ---------------------------------------------------------------------------
REM Cleanly stops XAMPP's MySQL for the Traffic System.
REM Run this before shutting the PC down so MySQL is never cut off
REM mid-write (that is what corrupts tables over time).
REM ---------------------------------------------------------------------------
REM Pause the watchdog for a while so it does not restart MySQL
REM while you are intentionally stopping it (e.g. before PC shutdown).
>"%TEMP%\traffic_mysql_stop" echo stopped

echo Stopping MySQL cleanly...
"C:\xampp\mysql\bin\mysqladmin.exe" -u root -h 127.0.0.1 shutdown
if errorlevel 1 (
    echo MySQL does not appear to be running.
) else (
    echo MySQL stopped cleanly.
)
timeout /t 4 >nul

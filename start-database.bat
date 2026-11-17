@echo off
REM ---------------------------------------------------------------------------
REM Starts XAMPP's MySQL for the Traffic System.
REM Double-click this file before using the app if MySQL is not already running.
REM ---------------------------------------------------------------------------

REM NOTE: we run a renamed copy of the server (traffic_mysqld.exe).
REM Something on this PC silently kills processes named "mysqld.exe",
REM so the app's server runs under a different name. See DATABASE-RECOVERY-NOTES.md.
set MYSQLD=C:\xampp\mysql\bin\traffic_mysqld.exe
set MYINI=C:\xampp\mysql\bin\my.ini

REM Resume the watchdog: it may start MySQL again whenever needed.
del "%TEMP%\traffic_mysql_stop" >nul 2>&1

netstat -ano | findstr /R /C:"127.0.0.1:3306 .*LISTENING" >nul
if not errorlevel 1 (
    echo MySQL is already running on port 3306.
    timeout /t 3 >nul
    exit /b 0
)

echo Starting MySQL...
start "Traffic MySQL" /min "%MYSQLD%" --defaults-file="%MYINI%" --console
timeout /t 5 >nul
netstat -ano | findstr /R /C:"127.0.0.1:3306 .*LISTENING" >nul
if errorlevel 1 (
    echo MySQL did not start. Check C:\xampp\mysql\data\mysql_error.log
) else (
    echo MySQL is up on port 3306.
)
timeout /t 5 >nul

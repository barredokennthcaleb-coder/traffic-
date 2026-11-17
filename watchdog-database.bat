@echo off
REM ---------------------------------------------------------------------------
REM Database watchdog for the Traffic System.
REM
REM Checks every ~30 seconds: if MySQL is not running, it starts it again.
REM Leave it running in the background (it starts hidden at logon).
REM
REM - Pause it by running stop-database.bat (creates a stop marker).
REM - Resume it by running start-database.bat (clears the stop marker).
REM - Activity log: %TEMP%\traffic_mysql_watchdog.log
REM ---------------------------------------------------------------------------

REM Runs a renamed copy of the server: something on this PC kills
REM processes named "mysqld.exe" silently. See DATABASE-RECOVERY-NOTES.md.
set MYSQLD="C:\xampp\mysql\bin\traffic_mysqld.exe"
set MYINI="C:\xampp\mysql\bin\my.ini"
set STOPMARK=%TEMP%\traffic_mysql_stop
set LOG=%TEMP%\traffic_mysql_watchdog.log
set HEALTH_TICKS=0

:loop
if exist "%STOPMARK%" goto wait_long

netstat -ano | findstr /R /C:"127.0.0.1:3306 .*LISTENING" >nul
if not errorlevel 1 goto wait_short

echo [%date% %time%] MySQL is down - starting it now.>> "%LOG%"
start "Traffic MySQL" /min %MYSQLD% --defaults-file=%MYINI%
call :sleep 25
netstat -ano | findstr /R /C:"127.0.0.1:3306 .*LISTENING" >nul
if errorlevel 1 (
    echo [%date% %time%] MySQL did not come up - backing off 2 minutes before the next attempt. Check C:\xampp\mysql\data\mysql_error.log>> "%LOG%"
    call :sleep 120
    goto loop
)
REM A crash can roll the grant tables back, so restore the app login as soon
REM as the server answers again instead of waiting for the next health tick.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0fix-database.ps1" -OnlyIfBroken -Quiet >> "%LOG%" 2>&1
goto loop

:wait_long
call :sleep 120
goto loop

:wait_short
REM Every ~2 minutes, prove the app account can still log in. A hard MySQL
REM crash can roll the grant tables back to an older state, which reaches the
REM site as "Access denied for user 'traffic_app'@'localhost'". That repair
REM only runs when the login actually fails, so a healthy database is never
REM touched. The app's own 503 page reloads itself once access is restored.
set /a HEALTH_TICKS+=1
if %HEALTH_TICKS% LSS 2 goto wait_short_done
set HEALTH_TICKS=0
echo [%date% %time%] Checking the app database login.>> "%LOG%"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0fix-database.ps1" -OnlyIfBroken -Quiet >> "%LOG%" 2>&1
:wait_short_done
call :sleep 30
goto loop

REM `timeout` fails at once when this script runs hidden (its stdin is
REM redirected), which turned the loop into a busy spin that checked MySQL
REM every few seconds. ping always honours the wait, hidden or not.
:sleep
set /a SLEEP_PINGS=%~1+1
ping -n %SLEEP_PINGS% 127.0.0.1 >nul 2>&1
exit /b 0

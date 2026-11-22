@echo off
REM ---------------------------------------------------------------------------
REM One-time setup: lets phones on the SAME WiFi open the Traffic System.
REM Double-clicking this file will ask for administrator rights automatically.
REM (Firewall changes need admin; only needs to be done once per PC.)
REM ---------------------------------------------------------------------------

NET SESSION >nul 2>&1
if %errorlevel% neq 0 (
    echo Requesting administrator rights...
    powershell -NoProfile -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b
)

echo Allowing port 80 (Apache - recommended for phones) ...
netsh advfirewall firewall delete rule name="Traffic System HTTP (80)" >nul 2>&1
netsh advfirewall firewall add rule name="Traffic System HTTP (80)" dir=in action=allow protocol=TCP localport=80 profile=any

echo Allowing port 8080 (PHP dev server - optional) ...
netsh advfirewall firewall delete rule name="Traffic System Dev (8080)" >nul 2>&1
netsh advfirewall firewall add rule name="Traffic System Dev (8080)" dir=in action=allow protocol=TCP localport=8080 profile=any

echo.
echo This PC's LAN address (open this on the phone):
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /R /C:"IPv4 Address"') do for /f "tokens=* delims= " %%b in ("%%a") do echo    http://%%b/
echo.
echo Make sure the phone and this PC are on the SAME WiFi network.
echo If it still fails, the WiFi may have "AP/client isolation" enabled,
echo which blocks devices from talking to each other - check router settings.
echo.
pause

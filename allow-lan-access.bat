@echo off
REM ---------------------------------------------------------------------------
REM One-time setup: lets phones on your WiFi open the Traffic System.
REM
REM HOW TO USE:  RIGHT-CLICK this file  ->  "Run as administrator"  ->  Yes
REM (only needs to be done once per PC)
REM ---------------------------------------------------------------------------

echo Allowing port 80 (Apache - recommended for phones) ...
netsh advfirewall firewall delete rule name="Traffic System HTTP (80)" >nul 2>&1
netsh advfirewall firewall add rule name="Traffic System HTTP (80)" dir=in action=allow protocol=TCP localport=80 profile=any

echo Allowing port 8080 (PHP dev server - optional) ...
netsh advfirewall firewall delete rule name="Traffic System Dev (8080)" >nul 2>&1
netsh advfirewall firewall add rule name="Traffic System Dev (8080)" dir=in action=allow protocol=TCP localport=8080 profile=any

echo.
echo Done. Phones on the same WiFi can now reach this PC.
pause

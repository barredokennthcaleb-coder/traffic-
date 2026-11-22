# ---------------------------------------------------------------------------
# Self-healing database setup for the Traffic System.
#
# Usage:  double-click fix-database.bat  (or run this file with PowerShell)
#
# What it does, safely and repeatably:
#   1. Waits for MySQL; if it is not running, starts it via start-database.bat.
#   2. Reads the database settings from .env (single source of truth).
#   3. Makes sure the app database user exists with the right password and
#      has full rights on the app database - retrying each step a few times.
#   4. Verifies the login end-to-end and reports the result.
#
# You can run it any time. If the site ever shows a database error again,
# this is the one-click repair.
# ---------------------------------------------------------------------------

param(
    # With -OnlyIfBroken the script first proves the app account can log in and
    # exits without touching anything when it can. The watchdog uses this so a
    # healthy database is never rewritten every few minutes.
    [switch]$OnlyIfBroken,
    [switch]$Quiet
)

$ErrorActionPreference = 'Continue'

function Say([string]$message) {
    if (-not $Quiet) { Write-Host $message }
}

$root    = Split-Path -Parent $MyInvocation.MyCommand.Path
$envFile = Join-Path $root '.env'
$mysql   = 'C:\xampp\mysql\bin\mysql.exe'

if (-not (Test-Path $envFile)) {
    throw ".env not found at $envFile"
}

# --- 1. Read credentials from .env -------------------------------------------
function Get-EnvValue([string]$key) {
    $match = Select-String -Path $envFile -Pattern ("^\s*" + [regex]::Escape($key) + "\s*=\s*(.+?)\s*$") |
        Select-Object -First 1
    if (-not $match) { return $null }
    $value = $match.Matches[0].Groups[1].Value.Trim()
    return $value.Trim("'", '"')
}

$dbUser = Get-EnvValue 'database.default.username'
$dbPass = Get-EnvValue 'database.default.password'
$dbName = Get-EnvValue 'database.default.database'
$dbHost = Get-EnvValue 'database.default.hostname'
$dbPort = Get-EnvValue 'database.default.port'

if (-not $dbUser -or -not $dbName) {
    throw "database.default.username / database.default.database are missing in .env"
}
if (-not $dbHost) { $dbHost = '127.0.0.1' }
if (-not $dbPort) { $dbPort = '3306' }

Say "App database : $dbName @ $dbHost`:$dbPort"
Say "App user     : $dbUser"
Say ''

# --- 2. Make sure MySQL is up -------------------------------------------------
function Test-MySql {
    & $mysql -u root -h $dbHost -P $dbPort --connect-timeout=4 -e "SELECT 1" 2>$null | Out-Null
    return ($LASTEXITCODE -eq 0)
}

if (-not (Test-MySql)) {
    Say 'MySQL is not responding. Trying to start it ...'
    $startBat = Join-Path $root 'start-database.bat'
    if (Test-Path $startBat) { & cmd /c "`"$startBat`"" | Out-Null }

    $up = $false
    for ($i = 0; $i -lt 45; $i++) {
        Start-Sleep -Seconds 1
        if (Test-MySql) { $up = $true; break }
    }
    if (-not $up) { throw 'MySQL could not be started. Check C:\xampp\mysql\data\mysql_error.log' }
}

Say 'MySQL is up.'
Say ''

# Login test for the app account.
#
# The password goes through a temporary option file instead of "-p$dbPass":
# a CLI argument lets cmd/PowerShell split or expand characters such as &, !
# or % inside the password, which made a perfectly working login report as
# "Access denied" and had the watchdog re-grant the account every minute.
function Test-AppLogin {
    $cnf = Join-Path $env:TEMP 'traffic_login_test.cnf'

    @"
[client]
host=$dbHost
port=$dbPort
user=$dbUser
password=$dbPass
"@ | Set-Content -Path $cnf -Encoding ASCII

    & $mysql "--defaults-extra-file=$cnf" --connect-timeout=4 -e "USE ``$dbName``; SELECT 1" 2>$null | Out-Null
    $ok = ($LASTEXITCODE -eq 0)

    Remove-Item $cnf -ErrorAction SilentlyContinue

    return $ok
}

# -OnlyIfBroken stops here when the account already works, so the watchdog can
# call this repeatedly and only a real failure rewrites grants.
$appLoginOk = Test-AppLogin

if ($appLoginOk) {
    Say 'App login OK.'
    if ($OnlyIfBroken) { exit 0 }
} elseif ($OnlyIfBroken) {
    Say 'App login FAILED - re-creating the account and its privileges.'
}

# --- 3. Ensure user + grants (retry each statement) ---------------------------
$sqlFile = Join-Path $env:TEMP 'traffic_fix_database.sql'

# Escape single quotes so unusual passwords cannot break the SQL.
$u = $dbUser.Replace("'", "''")
$p = $dbPass.Replace("'", "''")
$d = $dbName.Replace("'", "''")

@"
CREATE USER IF NOT EXISTS '$u'@'localhost' IDENTIFIED BY '$p';
ALTER USER '$u'@'localhost' IDENTIFIED BY '$p';
GRANT ALL PRIVILEGES ON ``$d``.* TO '$u'@'localhost';
CREATE USER IF NOT EXISTS '$u'@'127.0.0.1' IDENTIFIED BY '$p';
ALTER USER '$u'@'127.0.0.1' IDENTIFIED BY '$p';
GRANT ALL PRIVILEGES ON ``$d``.* TO '$u'@'127.0.0.1';
CREATE USER IF NOT EXISTS '$u'@'::1' IDENTIFIED BY '$p';
ALTER USER '$u'@'::1' IDENTIFIED BY '$p';
GRANT ALL PRIVILEGES ON ``$d``.* TO '$u'@'::1';
FLUSH PRIVILEGES;
FLUSH TABLES;
"@ | Set-Content -Path $sqlFile -Encoding ASCII

Say 'Applying account + privileges (with retries) ...'
$applied = $false
for ($attempt = 1; $attempt -le 3; $attempt++) {
    Get-Content $sqlFile | & $mysql -u root -h $dbHost -P $dbPort 2>&1 | Out-Host

    $verified = $false
    if (Test-MySql) {
        $verified = Test-AppLogin
    }
    if ($verified) {
        $applied = $true
        break
    }
    Say "Attempt $attempt was not fully applied - retrying ..."
    Start-Sleep -Seconds 2
}

if (-not $applied) {
    throw "Could not confirm the app account on $dbName. Run this again, or check fix output above."
}

Say ''
Say 'OK - the application account and privileges are in place.'
Say ("Verified: {0} can open {1}." -f $dbUser, $dbName)
Say ''
Say 'The site should work now. If it still shows an error, restart MySQL and try again.'

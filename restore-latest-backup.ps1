#requires -Version 5.1
<#
.SYNOPSIS
Emergency restore: imports the newest verified db_*.sql backup into the live database.

.DESCRIPTION
Finds the newest verified backup across writable\backups\auto and
D:\traffic-backups\auto, rejects incomplete dumps, asks for confirmation, stops
the PHP development server on port 8080 if needed, and restores into the live
database from .env.

This is a database-changing command. Only run it when the live database is
actually damaged and the owner has approved the restore.

.PARAMETER RootPassword
MySQL root password, if this XAMPP install has one. If omitted, the script tries
an empty password and prompts once if access is denied.

.PARAMETER Preview
Show which backup would be restored, then exit without changing anything.

.EXAMPLE
.\restore-latest-backup.ps1 -Preview

.EXAMPLE
.\restore-latest-backup.ps1

.NOTES
For a normal, safe test restore into a scratch database use restore-backup.ps1
without -ConfirmLiveRestore.
#>
[CmdletBinding()]
param(
    [string]$RootPassword,
    [switch]$Preview
)

$ErrorActionPreference = 'Stop'

$Root          = Split-Path -Parent $MyInvocation.MyCommand.Path
$Dest          = Join-Path $Root 'writable\backups\auto'
$Mirror        = 'D:\traffic-backups\auto'
$EnvFile       = Join-Path $Root '.env'
$MySql         = 'C:\xampp\mysql\bin\mysql.exe'
$RestoreScript = Join-Path $Root 'restore-backup.ps1'
$LogFile       = Join-Path $Dest 'restore.log'

New-Item -ItemType Directory -Force -Path $Dest | Out-Null

function Write-RestoreLog {
    param([string]$Message, [string]$Level = 'INFO')
    $line = '{0} [{1}] {2}' -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $Level, $Message
    Write-Host $line
    Add-Content -LiteralPath $LogFile -Value $line -Encoding UTF8
}

function Get-DotEnv {
    $map = @{}
    if (-not (Test-Path -LiteralPath $EnvFile)) { throw "Missing .env: $EnvFile" }
    foreach ($line in (Get-Content -LiteralPath $EnvFile)) {
        if ($line -match '^\s*([A-Za-z0-9_.]+)\s*=\s*(.*?)\s*$') {
            $key = $Matches[1]
            $val = $Matches[2]
            if ($val -match "^'(.*)'$")     { $val = $Matches[1] }
            elseif ($val -match '^"(.*)"$') { $val = $Matches[1] }
            $map[$key] = $val
        }
    }
    return $map
}

function Find-LatestVerifiedDump {
    param([string[]]$Folders)

    $files = @()
    foreach ($folder in $Folders) {
        if (Test-Path -LiteralPath $folder) {
            $files += Get-ChildItem -LiteralPath $folder -Filter 'db_*.sql' -File -ErrorAction SilentlyContinue
        }
    }
    if (-not $files -or $files.Count -eq 0) {
        throw "No db_*.sql backups found in: $($Folders -join ', ')"
    }

    foreach ($file in ($files | Sort-Object LastWriteTime -Descending)) {
        if ($file.Length -lt 1024) {
            Write-RestoreLog ("Skipping too-small dump: {0}" -f $file.FullName) 'WARN'
            continue
        }
        $tail = Get-Content -LiteralPath $file.FullName -Tail 8
        if ($tail -match 'Dump completed') {
            return $file
        }
        Write-RestoreLog ("Skipping incomplete dump (no completion marker): {0}" -f $file.FullName) 'WARN'
    }

    throw 'No complete, verified db_*.sql backup was found. Refusing to restore.'
}

function Test-MySqlRoot {
    param([string]$Password)

    $defaults = Join-Path $env:TEMP ('traffic_restore_check_{0}.ini' -f [guid]::NewGuid().ToString('N'))
    try {
        $lines = @('[client]', 'user=root')
        if ($Password) { $lines += "password=$Password" }
        $lines += "host=$DbHost"
        $lines += "port=$DbPort"
        $lines += 'default-character-set=utf8mb4'
        Set-Content -LiteralPath $defaults -Value $lines -Encoding ASCII

        $out = & $MySql "--defaults-extra-file=$defaults" --connect-timeout=5 --batch --skip-column-names -e 'SELECT 1' 2>&1
        return [pscustomobject]@{
            Ok     = ($LASTEXITCODE -eq 0 -and ("$out").Trim() -eq '1')
            Output = ("$out").Trim()
        }
    }
    finally {
        Remove-Item -LiteralPath $defaults -Force -ErrorAction SilentlyContinue
    }
}

function Get-PlainText {
    param([securestring]$Secure)
    $bstr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($Secure)
    try   { return [Runtime.InteropServices.Marshal]::PtrToStringBSTR($bstr) }
    finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr) }
}

function Stop-WebAppIfRunning {
    $listener = Get-NetTCPConnection -LocalPort 8080 -State Listen -ErrorAction SilentlyContinue |
                Select-Object -First 1
    if (-not $listener) { return $false }

    $proc = Get-Process -Id $listener.OwningProcess -ErrorAction SilentlyContinue
    $name = if ($proc) { $proc.ProcessName } else { 'unknown' }

    Write-Host ''
    Write-Warning ("The web app is listening on http://localhost:8080 (PID {0}, {1})." -f $listener.OwningProcess, $name)
    Write-Host 'Restoring while the app is running can cause errors and lose requests.'
    $answer = Read-Host 'Stop the web app now? [y/N]'
    if ($answer -notmatch '^(?i:y|yes)$') {
        throw 'Restore cancelled. Stop the web app first, then run this again.'
    }

    if (-not $proc -or $proc.Path -notlike 'C:\xampp\php\*') {
        throw ("Port 8080 is held by '{0}' (PID {1}); stop it manually first." -f $name, $listener.OwningProcess)
    }

    Stop-Process -Id $proc.Id -Force
    Start-Sleep -Seconds 2
    Write-RestoreLog ("Stopped web app PID {0} ({1})." -f $proc.Id, $proc.Path)
    return $true
}

# --- Resolve configuration and the newest valid backup -----------------------
$dotenv = Get-DotEnv
$DbHost = $dotenv['database.default.hostname']
$DbPort = $dotenv['database.default.port']
$liveDb = $dotenv['database.default.database']
if (-not $DbHost -or -not $DbPort -or -not $liveDb) {
    throw 'database.default.hostname/port/database are missing from .env'
}

$dump    = Find-LatestVerifiedDump -Folders @($Dest, $Mirror)
$ageDays = ((Get-Date) - $dump.LastWriteTime).TotalDays

Write-Host ''
Write-Host ("Newest verified backup: {0}" -f $dump.FullName)
Write-Host ("Created               : {0}" -f $dump.LastWriteTime)
Write-Host ("Size                  : {0:N0} KB" -f ($dump.Length / 1KB))
Write-Host ("Age                   : {0:N1} day(s)" -f $ageDays)
Write-Host ("Live database         : {0}" -f $liveDb)
if ($ageDays -gt 7) {
    Write-Warning ("This backup is {0:N1} days old; newer records will be lost." -f $ageDays)
}

if ($Preview) {
    Write-RestoreLog ("Preview only: would restore {0} into {1}." -f $dump.FullName, $liveDb)
    Write-Host 'Preview only - nothing was changed.'
    exit 0
}

Write-Host ''
Write-Warning ("This will OVERWRITE the live database '{0}' with the backup above." -f $liveDb)
Write-Host 'Make sure the web app is stopped (php spark serve or Apache) before continuing.'
$answer = Read-Host 'Type RESTORE to continue'
if ($answer -cne 'RESTORE') {
    Write-RestoreLog 'Restore cancelled at the confirmation prompt.'
    Write-Host 'Cancelled. Nothing was changed.'
    exit 1
}

# --- Verify MySQL is up and root can log in ---------------------------------
$check = Test-MySqlRoot -Password $RootPassword
if (-not $check.Ok -and $check.Output -match 'Access denied') {
    Write-Host ''
    $secure = Read-Host 'MySQL root password (leave blank for no password)' -AsSecureString
    $RootPassword = Get-PlainText -Secure $secure
    $check = Test-MySqlRoot -Password $RootPassword
}
if (-not $check.Ok) {
    throw ("MySQL is not reachable as root. If MySQL is down, run start-database.bat and retry. " +
           "If it will not start because of corruption, repair the data folder first " +
           "(see DATABASE-RECOVERY-NOTES.md). Details: {0}" -f $check.Output)
}

# --- Stop the web app, then import ------------------------------------------
$webWasStopped = Stop-WebAppIfRunning

try {
    Write-RestoreLog ("Restoring {0} into {1} ..." -f $dump.FullName, $liveDb)
    $restoreArgs = @{ Dump = $dump.FullName; ConfirmLiveRestore = $true }
    if ($RootPassword) { $restoreArgs['RootPassword'] = $RootPassword }

    & $RestoreScript @restoreArgs
    Write-RestoreLog ("Restore OK: {0}" -f $dump.FullName)

    if ($webWasStopped) {
        Write-Host ''
        Write-Host 'The web app was stopped. Restart it with: php spark serve'
    }
    exit 0
}
catch {
    Write-RestoreLog ("Restore FAILED: {0}" -f $_.Exception.Message) 'ERROR'
    throw
}

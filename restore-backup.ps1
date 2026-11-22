#requires -Version 5.1
<#
.SYNOPSIS
Restores a db_*.sql backup. By default restores into a NEW scratch database so the
live system is never put at risk. Overwriting the live database requires
-ConfirmLiveRestore.

Examples:
  .\restore-backup.ps1 db_20261119_130000.sql
  .\restore-backup.ps1 D:\traffic-backups\auto\db_20261119_130000.sql -TargetDatabase traffic_restore_test
  .\restore-backup.ps1 db_20261119_130000.sql -ConfirmLiveRestore

Database-changing command - only run with the owner's approval.
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)][string]$Dump,
    [string]$TargetDatabase,
    [switch]$ConfirmLiveRestore,
    [string]$RootPassword
)

$ErrorActionPreference = 'Stop'

$Root   = Split-Path -Parent $MyInvocation.MyCommand.Path
$EnvFile = Join-Path $Root '.env'
$Dest   = Join-Path $Root 'writable\backups\auto'
$Mirror = 'D:\traffic-backups\auto'
$MySql  = 'C:\xampp\mysql\bin\mysql.exe'

function Get-DotEnv {
    $map = @{}
    foreach ($line in (Get-Content -Path $EnvFile)) {
        if ($line -match '^\s*([A-Za-z0-9_.]+)\s*=\s*(.*?)\s*$') {
            $key = $Matches[1]; $val = $Matches[2]
            if ($val -match "^'(.*)'$")     { $val = $Matches[1] }
            elseif ($val -match '^"(.*)"$') { $val = $Matches[1] }
            $map[$key] = $val
        }
    }
    return $map
}

$dotenv = Get-DotEnv
$liveDb = $dotenv['database.default.database']
$dbHost = $dotenv['database.default.hostname']
$port   = $dotenv['database.default.port']

# --- Resolve and validate the dump -------------------------------------------
if (-not (Test-Path $Dump)) {
    $candidate = Join-Path $Dest $Dump
    if (-not (Test-Path $candidate)) { $candidate = Join-Path $Mirror $Dump }
    if (-not (Test-Path $candidate)) { throw "Dump not found: $Dump" }
    $Dump = $candidate
}
$item = Get-Item -Path $Dump
if ($item.Length -lt 1024) { throw "Dump is too small ($($item.Length) bytes); refusing to restore." }
$tail = Get-Content -Path $Dump -Tail 8
if (-not ($tail -match 'Dump completed')) {
    throw 'Dump has no completion marker and may be incomplete; refusing to restore.'
}
Write-Host ("Using dump : {0} ({1:N0} KB)" -f $Dump, ($item.Length / 1KB))

if (-not $TargetDatabase) {
    $TargetDatabase = 'traffic_restore_' + (Get-Date -Format 'yyyyMMdd_HHmmss')
}
if ($TargetDatabase -eq $liveDb -and -not $ConfirmLiveRestore) {
    throw "Refusing to overwrite live database '$liveDb' without -ConfirmLiveRestore."
}
Write-Host "Target DB  : $TargetDatabase"
if ($TargetDatabase -eq $liveDb) {
    Write-Warning 'This will overwrite the LIVE database. The app should be stopped first.'
}

# --- Connect as root (schema creation / import needs elevated rights) --------
$defaults = Join-Path $env:TEMP ('traffic_restore_root_{0}.ini' -f [guid]::NewGuid().ToString('N'))
$lines = @('[client]', 'user=root')
if ($RootPassword) { $lines += "password=$RootPassword" }
$lines += "host=$dbHost"
$lines += "port=$port"
Set-Content -Path $defaults -Value $lines -Encoding ASCII

try {
    $create = 'CREATE DATABASE IF NOT EXISTS `{0}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;' -f $TargetDatabase
    & $MySql "--defaults-extra-file=$defaults" -e $create
    if ($LASTEXITCODE -ne 0) { throw "Could not create target database (exit $LASTEXITCODE)" }

    # Strip USE / CREATE DATABASE into a temporary UTF-8 file, then feed the raw
    # bytes to mysql. This avoids PowerShell's text-pipeline encoding altering
    # names or other non-ASCII data during import.
    $clean = Join-Path $env:TEMP ('traffic_restore_{0}.sql' -f [guid]::NewGuid().ToString('N'))
    try {
        $utf8 = New-Object System.Text.UTF8Encoding($false)
        $sqlLines = [System.IO.File]::ReadLines($Dump, [System.Text.Encoding]::UTF8) |
                    Where-Object { $_ -notmatch '^\s*(USE|CREATE DATABASE)\s' }
        [System.IO.File]::WriteAllLines($clean, $sqlLines, $utf8)

        $psi = New-Object System.Diagnostics.ProcessStartInfo
        $psi.FileName = $MySql
        $psi.Arguments = '--defaults-extra-file="{0}" "{1}"' -f $defaults, $TargetDatabase
        $psi.UseShellExecute = $false
        $psi.RedirectStandardInput = $true
        $psi.CreateNoWindow = $true
        $proc = [System.Diagnostics.Process]::Start($psi)
        $stream = [System.IO.File]::OpenRead($clean)
        try { $stream.CopyTo($proc.StandardInput.BaseStream) }
        finally { $stream.Dispose() }
        $proc.StandardInput.Close()
        $proc.WaitForExit()
        if ($proc.ExitCode -ne 0) { throw "Import failed (exit $($proc.ExitCode))" }
    }
    finally {
        Remove-Item -LiteralPath $clean -Force -ErrorAction SilentlyContinue
    }

    $verify = 'SELECT (SELECT COUNT(*) FROM violations), (SELECT COUNT(*) FROM violators), (SELECT COUNT(*) FROM users), (SELECT COUNT(*) FROM violation_types);'
    $counts = & $MySql "--defaults-extra-file=$defaults" --batch --skip-column-names $TargetDatabase -e $verify
    if ($LASTEXITCODE -eq 0) {
        Write-Host ("Verification (violations / violators / users / types): {0}" -f ("$counts").Trim())
    }
    Write-Host "Restore complete: $TargetDatabase"
}
finally {
    Remove-Item -Force -Path $defaults -ErrorAction SilentlyContinue
}

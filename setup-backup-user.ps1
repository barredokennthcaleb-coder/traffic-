#requires -Version 5.1
<#
.SYNOPSIS
One-time setup of the least-privilege account used by auto-backup.ps1.

Without -Apply : generates a strong random password, writes credentials file and
                 the SQL to review, but does NOT touch the database.
With -Apply    : creates/updates the account in MySQL. Database change - only run
                 with the owner's approval.

Usage:
  .\setup-backup-user.ps1                 # generate files only (safe)
  .\setup-backup-user.ps1 -Apply          # create the account (needs approval)
  .\setup-backup-user.ps1 -Apply -RootPassword '...'   # if MySQL root has a password
#>
[CmdletBinding()]
param(
    [switch]$Apply,
    [string]$RootPassword
)

$ErrorActionPreference = 'Stop'

$Root      = Split-Path -Parent $MyInvocation.MyCommand.Path
$EnvFile   = Join-Path $Root '.env'
$CredFile  = Join-Path $Root 'writable\backups\.backup-credentials.ini'
$SqlFile   = Join-Path $Root 'writable\backups\create-backup-user.sql'
$MySql     = 'C:\xampp\mysql\bin\mysql.exe'

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
$db     = $dotenv['database.default.database']
$dbHost = $dotenv['database.default.hostname']
$port   = $dotenv['database.default.port']
if (-not $db -or -not $dbHost -or -not $port) {
    throw 'database.default.database/hostname/port must be set in .env'
}
# -Apply reuses files that were already generated and reviewed.
$reuse = $Apply -and (Test-Path $CredFile) -and (Test-Path $SqlFile)
if (-not $reuse -and (Test-Path $CredFile)) {
    throw "Credentials file already exists: $CredFile (delete it first to rotate the password)"
}

if (-not $reuse) {
    $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789-_'
    $rng   = [System.security.cryptography.RandomNumberGenerator]::Create()
    $bytes = New-Object byte[] 24
    $rng.GetBytes($bytes)
    $password = -join ($bytes | ForEach-Object { $chars[$_ % $chars.Length] })

    New-Item -ItemType Directory -Force -Path (Split-Path $CredFile -Parent) | Out-Null
    Set-Content -Path $CredFile -Value @('[client]', 'user=traffic_backup', "password=$password") -Encoding ASCII
    try {
        $aclUser = "$env:USERDOMAIN\$env:USERNAME"
        & icacls $CredFile /inheritance:r /grant ("{0}:F" -f $aclUser) | Out-Null
    }
    catch {
        Write-Warning "Could not restrict ACL on credentials file: $($_.Exception.Message)"
    }

    $sql = @(
        '-- Least-privilege account used only by auto-backup.ps1',
        "CREATE USER IF NOT EXISTS 'traffic_backup'@'localhost' IDENTIFIED BY '$password';",
        ('GRANT SELECT, SHOW VIEW, TRIGGER, EVENT ON `{0}`.* TO ''traffic_backup''@''localhost'';' -f $db),
        'FLUSH PRIVILEGES;'
    )
    Set-Content -Path $SqlFile -Value $sql -Encoding UTF8

    Write-Host 'Generated (no database changes made):'
    Write-Host "  Credentials : $CredFile"
    Write-Host "  SQL         : $SqlFile"
    Write-Host 'Review the SQL, then run with -Apply to create the account.'
}
else {
    Write-Host 'Reusing generated credentials and SQL.'
}

if (-not $Apply) { exit 0 }

$defaults = Join-Path $env:TEMP ('traffic_setup_root_{0}.ini' -f [guid]::NewGuid().ToString('N'))
$lines = @('[client]', 'user=root')
if ($RootPassword) { $lines += "password=$RootPassword" }
$lines += "host=$dbHost"
$lines += "port=$port"
Set-Content -Path $defaults -Value $lines -Encoding ASCII
try {
    Get-Content -Path $SqlFile | & $MySql "--defaults-extra-file=$defaults"
    if ($LASTEXITCODE -ne 0) { throw "mysql exited with code $LASTEXITCODE" }
    Write-Host 'Backup account created/updated successfully.'
}
finally {
    Remove-Item -Force -Path $defaults -ErrorAction SilentlyContinue
}

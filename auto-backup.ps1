#requires -Version 5.1
<#
.SYNOPSIS
Automated, verified backups for the CTRAMO traffic system.

.DESCRIPTION
  -Scope Database : full consistent logical dump of traffic_live (used by the weekly task)
  -Scope Files    : compact archive of uploads, config and code (used by the daily task)

  Both scopes write to writable\backups\auto and mirror to D:\traffic-backups\auto.
  A run that fails validation produces NO backup file, and the failure is recorded in
  backup.log and LAST-RESULT.txt on both locations.

.NOTES
  Read-only database access only. Credentials come from
  writable\backups\.backup-credentials.ini (created by setup-backup-user.ps1).
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [ValidateSet('Database', 'Files')]
    [string]$Scope,

    [switch]$NoMirror,   # skip D: copy (useful for a dry run)
    [switch]$NoPrune     # skip retention cleanup
)

$ErrorActionPreference = 'Stop'

$Root       = Split-Path -Parent $MyInvocation.MyCommand.Path
$Dest       = Join-Path $Root 'writable\backups\auto'
$Mirror     = 'D:\traffic-backups\auto'
$CredFile   = Join-Path $Root 'writable\backups\.backup-credentials.ini'
$EnvFile    = Join-Path $Root '.env'
$LogFile    = Join-Path $Dest 'backup.log'
$ResultFile = Join-Path $Dest 'LAST-RESULT.txt'
$MySqlDump  = 'C:\xampp\mysql\bin\mysqldump.exe'
$MySql      = 'C:\xampp\mysql\bin\mysql.exe'

function Get-DotEnv {
    $map = @{}
    if (-not (Test-Path $EnvFile)) { throw "Missing .env: $EnvFile" }
    foreach ($line in (Get-Content -Path $EnvFile)) {
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

function Write-BackupLog {
    param([string]$Message, [string]$Level = 'INFO')
    $line = '{0} [{1}] {2}' -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $Level, $Message
    Write-Host $line
    Add-Content -Path $LogFile -Value $line -Encoding UTF8
    $item = Get-Item -Path $LogFile -ErrorAction SilentlyContinue
    if ($item -and $item.Length -gt 1MB) {
        Move-Item -Force -Path $LogFile -Destination "$LogFile.1"
    }
}

function Set-Result {
    param([string]$Status, [string]$Detail)
    $text = @(
        "Last run : $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')",
        "Status   : $Status",
        "Scope    : $Scope",
        "Detail   : $Detail"
    ) -join [Environment]::NewLine
    Set-Content -Path $ResultFile -Value $text -Encoding UTF8
    if (-not $NoMirror -and (Test-Path $Mirror)) {
        Copy-Item -Force -Path $ResultFile -Destination (Join-Path $Mirror 'LAST-RESULT.txt')
    }
}

function New-DefaultsFile {
    if (-not (Test-Path $CredFile)) {
        throw "Missing credentials file '$CredFile'. Run setup-backup-user.ps1 -Apply first."
    }
    $creds = @{}
    foreach ($line in (Get-Content -Path $CredFile)) {
        if ($line -match '^\s*([A-Za-z0-9_]+)\s*=\s*(.*?)\s*$') { $creds[$Matches[1]] = $Matches[2] }
    }
    if (-not $creds['user'] -or -not $creds['password']) {
        throw "Credentials file is missing user/password: $CredFile"
    }
    $dotenv = Get-DotEnv
    $tmp = Join-Path $env:TEMP ('traffic_backup_defaults_{0}.ini' -f [guid]::NewGuid().ToString('N'))
    $lines = @(
        '[client]',
        "user=$($creds['user'])",
        "password=$($creds['password'])",
        "host=$($dotenv['database.default.hostname'])",
        "port=$($dotenv['database.default.port'])",
        'default-character-set=utf8mb4'
    )
    Set-Content -Path $tmp -Value $lines -Encoding ASCII
    return $tmp
}

function Test-DatabaseReady {
    param([string]$Defaults)
    # A hung MariaDB can still hold port 3306, so verify it actually answers a query.
    for ($i = 1; $i -le 2; $i++) {
        $out = & $MySql "--defaults-extra-file=$Defaults" --connect-timeout=5 --batch --skip-column-names -e 'SELECT 1' 2>&1
        if ($LASTEXITCODE -eq 0 -and ("$out").Trim() -eq '1') { return $true }
        if ($i -lt 2) { Start-Sleep -Seconds 10 }
    }
    return $false
}

function Backup-Database {
    New-Item -ItemType Directory -Force -Path $Dest | Out-Null
    $dotenv = Get-DotEnv
    $db = $dotenv['database.default.database']
    if (-not $db) { throw 'database.default.database missing from .env' }

    $defaults = $null
    $tmpSql   = $null
    try {
        $defaults = New-DefaultsFile
        $stamp    = Get-Date -Format 'yyyyMMdd_HHmmss'
        $tmpSql   = Join-Path $env:TEMP ("traffic_db_{0}.sql" -f $stamp)
        $finalSql = Join-Path $Dest ("db_{0}.sql" -f $stamp)

        if (-not (Test-DatabaseReady -Defaults $defaults)) {
            throw 'Database did not answer SELECT 1 (down or hung). No backup was saved.'
        }

        # --single-transaction gives a consistent InnoDB snapshot without table locks.
        # No --routines/--events/--triggers: this app has none, and dumping them would
        # require extra privileges on mysql.* system tables.
        & $MySqlDump "--defaults-extra-file=$defaults" --single-transaction --hex-blob --default-character-set=utf8mb4 --result-file=$tmpSql $db
        if ($LASTEXITCODE -ne 0) { throw "mysqldump exited with code $LASTEXITCODE" }

        $item = Get-Item -Path $tmpSql
        if ($item.Length -lt 1024) { throw "Dump is too small ($($item.Length) bytes)" }
        $tail = Get-Content -Path $tmpSql -Tail 8
        if (-not ($tail -match 'Dump completed')) {
            throw 'Dump has no completion marker - it is incomplete and was discarded.'
        }

        Move-Item -Force -Path $tmpSql -Destination $finalSql
        Write-BackupLog ("Database OK: {0} ({1:N0} KB)" -f (Split-Path -Leaf $finalSql), ($item.Length / 1KB))
        return @($finalSql)
    }
    finally {
        if ($defaults) { Remove-Item -Force -Path $defaults -ErrorAction SilentlyContinue }
        if ($tmpSql)   { Remove-Item -Force -Path $tmpSql   -ErrorAction SilentlyContinue }
    }
}

function Backup-Files {
    New-Item -ItemType Directory -Force -Path $Dest | Out-Null
    $stamp   = Get-Date -Format 'yyyyMMdd_HHmmss'
    $staging = Join-Path $env:TEMP ("traffic_files_{0}" -f $stamp)
    $zip     = Join-Path $Dest ("files_{0}.zip" -f $stamp)
    try {
        New-Item -ItemType Directory -Force -Path $staging | Out-Null

        $excluded = @('vendor', '.git', 'node_modules', 'build', 'mockups',
                      'vibe_images', '.cloud-attachments', '.freebuff') |
                    ForEach-Object { Join-Path $Root $_ }
        $excluded += @('cache', 'logs', 'session', 'debugbar', 'backups') |
                     ForEach-Object { Join-Path $Root (Join-Path 'writable' $_) }

        $rcArgs = @($Root, $staging, '/E', '/NFL', '/NDL', '/NJH', '/NJS', '/NP', '/R:1', '/W:1')
        foreach ($dir in $excluded) { $rcArgs += @('/XD', $dir) }
        & robocopy.exe @rcArgs | Out-Null
        if ($LASTEXITCODE -ge 8) { throw "robocopy exited with code $LASTEXITCODE" }

        $tar = Join-Path $env:SystemRoot 'System32\tar.exe'
        Push-Location $staging
        try {
            if (Test-Path $tar) {
                & $tar -a -c -f $zip .
                $code = $LASTEXITCODE
            } else {
                Compress-Archive -Path (Join-Path $staging '*') -DestinationPath $zip -CompressionLevel Optimal -Force
                $code = $LASTEXITCODE
            }
        }
        finally { Pop-Location }
        if ($code -ne 0) { throw "Archive creation failed (exit $code)" }

        $item = Get-Item -Path $zip
        if ($item.Length -lt 10KB) { throw "Archive is too small ($($item.Length) bytes)" }
        Write-BackupLog ("Files OK: {0} ({1:N0} KB)" -f (Split-Path -Leaf $zip), ($item.Length / 1KB))
        return @($zip)
    }
    finally {
        Remove-Item -Recurse -Force -Path $staging -ErrorAction SilentlyContinue
    }
}

function Copy-ToMirror {
    param([string[]]$Paths)
    if ($NoMirror) { return }
    if (-not (Test-Path $Mirror)) { New-Item -ItemType Directory -Force -Path $Mirror | Out-Null }
    foreach ($p in $Paths) {
        Copy-Item -Force -Path $p -Destination (Join-Path $Mirror (Split-Path -Leaf $p))
    }
    Write-BackupLog ("Mirrored {0} file(s) to {1}" -f $Paths.Count, $Mirror)
}

function Invoke-Prune {
    param([string]$Folder, [string]$Prefix, [switch]$KeepRecent, [int]$RetainWeeks = 4)
    if (-not (Test-Path $Folder)) { return }
    $files = Get-ChildItem -Path $Folder -Filter "$Prefix*" -File |
             Where-Object { $_.Extension -in @('.sql', '.zip') } |
             Sort-Object LastWriteTime -Descending
    if (-not $files) { return }

    $now       = Get-Date
    $recentCut = $now.AddHours(-24)
    $dailyCut  = $now.AddDays(-7)
    $weeklyCut = $now.AddDays(-7 * $RetainWeeks)
    $seenDay   = @{}
    $seenWeek  = @{}
    $deleted   = 0

    foreach ($f in $files) {
        $keep = $false
        if ($KeepRecent -and $f.LastWriteTime -ge $recentCut) {
            $keep = $true
        } elseif ($f.LastWriteTime -ge $dailyCut) {
            $key = $f.LastWriteTime.ToString('yyyy-MM-dd')
            if (-not $seenDay.ContainsKey($key)) { $seenDay[$key] = $true; $keep = $true }
        } elseif ($f.LastWriteTime -ge $weeklyCut) {
            $key = $f.LastWriteTime.AddDays(-[int]$f.LastWriteTime.DayOfWeek).ToString('yyyy-MM-dd')
            if (-not $seenWeek.ContainsKey($key)) { $seenWeek[$key] = $true; $keep = $true }
        }
        if (-not $keep) {
            Remove-Item -Force -Path $f.FullName
            $deleted++
        }
    }
    if ($deleted -gt 0) {
        Write-BackupLog ("Pruned {0} old {1} file(s) from {2}" -f $deleted, $Prefix, $Folder)
    }
}

try {
    New-Item -ItemType Directory -Force -Path $Dest | Out-Null
    $created = @()
    if ($Scope -eq 'Database') { $created = Backup-Database }
    else                       { $created = Backup-Files }

    Copy-ToMirror -Paths $created

    if (-not $NoPrune) {
        Invoke-Prune -Folder $Dest -Prefix 'db_' -KeepRecent -RetainWeeks 8
        Invoke-Prune -Folder $Dest -Prefix 'files_'
        if (-not $NoMirror -and (Test-Path $Mirror)) {
            Invoke-Prune -Folder $Mirror -Prefix 'db_' -KeepRecent -RetainWeeks 8
            Invoke-Prune -Folder $Mirror -Prefix 'files_'
        }
    }

    $names = ($created | ForEach-Object { Split-Path -Leaf $_ }) -join ', '
    Set-Result -Status 'OK' -Detail "Created: $names"
    Write-BackupLog 'Run complete.'
    exit 0
}
catch {
    $msg = $_.Exception.Message
    Write-BackupLog -Message $msg -Level 'ERROR'
    Set-Result -Status 'FAILED' -Detail $msg
    exit 1
}

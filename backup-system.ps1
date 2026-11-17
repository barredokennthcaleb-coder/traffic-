# ---------------------------------------------------------------------------
# Local backup of the Traffic System: database dump + application files.
#
# Usage:  double-click backup-system.bat  (or run this file with PowerShell)
#
# Produces, inside  writable\backups\system_backup\ :
#   db_<timestamp>.sql      full database export (traffic_live)
#   system_<timestamp>.zip  app code, public assets, config, writable uploads
#
# Safe to run while the site is in use: the dump uses a consistent snapshot
# and the files are read only.
# ---------------------------------------------------------------------------

$ErrorActionPreference = 'Stop'

$root    = Split-Path -Parent $MyInvocation.MyCommand.Path
$dest    = Join-Path $root 'writable\backups\system_backup'
$stamp   = Get-Date -Format 'yyyyMMdd_HHmm'
$mysqld  = 'C:\xampp\mysql\bin\mysqldump.exe'
$dbName  = 'traffic_live'

New-Item -ItemType Directory -Force -Path $dest | Out-Null

# --- 1. Database ------------------------------------------------------------
$sqlFile = Join-Path $dest "db_$stamp.sql"
Write-Host "Dumping database $dbName ..."

# --result-file lets mysqldump write the file itself. Redirecting with '>'
# would encode the dump as UTF-16, which mysql cannot read back.
& $mysqld -u root --single-transaction --routines --default-character-set=utf8mb4 --result-file=$sqlFile $dbName
if ($LASTEXITCODE -ne 0) { throw "mysqldump failed with exit code $LASTEXITCODE" }

if (-not (Test-Path $sqlFile) -or (Get-Item $sqlFile).Length -lt 1024) {
    throw "Database dump failed or is empty: $sqlFile"
}
Write-Host ("  database: {0:N0} KB" -f ((Get-Item $sqlFile).Length / 1KB))

# --- 2. Application files ---------------------------------------------------
# Everything needed to rebuild the site, minus runtime junk and old backups.
$staging = Join-Path $env:TEMP "traffic_backup_$stamp"
$exclude = @('session', 'cache', 'logs', 'debugbar', 'backups')

Write-Host 'Copying application files ...'
$rcArgs = @($root, $staging, '/E', '/NFL', '/NDL', '/NJH', '/NJS', '/R:1', '/W:1')
foreach ($dir in $exclude) {
    $rcArgs += '/XD'
    $rcArgs += (Join-Path $root "writable\$dir")
}
# The .git folder (version history) can be hundreds of MB and is not needed
# to rebuild or run the system - backup stays small this way.
$rcArgs += '/XD'
$rcArgs += (Join-Path $root '.git')
& robocopy.exe @rcArgs | Out-Null
# robocopy returns 0-7 on success (8+ means failure)
if ($LASTEXITCODE -ge 8) { throw "robocopy failed with exit code $LASTEXITCODE" }

# Old backup archives must never end up inside a new archive.
Remove-Item -Recurse -Force (Join-Path $staging 'writable\backups') -ErrorAction SilentlyContinue

$zipFile = Join-Path $dest "system_$stamp.zip"
Write-Host 'Compressing ...'

# Windows' built-in tar (bsdtar) writes a real .zip and is far faster than
# Compress-Archive on a project with thousands of vendor files.
$tarExe = Join-Path $env:SystemRoot 'System32\tar.exe'
if (Test-Path $tarExe) {
    Push-Location $staging
    & $tarExe -a -c -f $zipFile .
    $tarCode = $LASTEXITCODE
    Pop-Location
    if ($tarCode -ne 0) { throw "Archive creation failed (tar exit code $tarCode)" }
} else {
    Compress-Archive -Path (Join-Path $staging '*') -DestinationPath $zipFile -CompressionLevel Optimal -Force
}

Remove-Item -Recurse -Force $staging -ErrorAction SilentlyContinue

Write-Host ("  files:    {0:N0} KB" -f ((Get-Item $zipFile).Length / 1KB))

# --- 3. Keep the newest 10 of each, so the folder cannot grow forever -------
$keep = 10
foreach ($pattern in @('db_*.sql', 'system_*.zip')) {
    Get-ChildItem -Path $dest -Filter $pattern |
        Sort-Object LastWriteTime -Descending |
        Select-Object -Skip $keep |
        Remove-Item -Force
}

Write-Host ''
Write-Host "Backup complete: $dest"
Write-Host "  db_$stamp.sql"
Write-Host "  system_$stamp.zip"

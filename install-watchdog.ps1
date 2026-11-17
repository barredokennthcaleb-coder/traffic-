# ---------------------------------------------------------------------------
# Installs the Traffic System database watchdog:
#   - starts it right now (hidden, no window),
#   - adds it to the per-user Startup folder so it runs at every logon.
#
# The watchdog keeps MySQL running: if it ever stops or crashes, it is
# started again within ~30 seconds. stop-database.bat pauses the watchdog;
# start-database.bat resumes it.
# ---------------------------------------------------------------------------

$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$vbs  = Join-Path $root 'watchdog-database-hidden.vbs'

if (-not (Test-Path $vbs)) {
    throw "Missing $vbs"
}

# 1. Start it now (hidden).
Start-Process -FilePath 'wscript.exe' -ArgumentList ('"' + $vbs + '"')
Write-Host 'Watchdog started (hidden).'

# 2. Add it to Startup so it runs at every logon.
$startup = [Environment]::GetFolderPath('Startup')
$lnk     = Join-Path $startup 'Traffic Database Watchdog.lnk'

$shell    = New-Object -ComObject WScript.Shell
$shortcut = $shell.CreateShortcut($lnk)
$shortcut.TargetPath       = 'wscript.exe'
$shortcut.Arguments        = '"' + $vbs + '"'
$shortcut.WorkingDirectory = $root
$shortcut.WindowStyle      = 7
$shortcut.Description      = 'Keeps MySQL running for the Traffic System'
$shortcut.Save()

Write-Host "Installed for every logon: $lnk"
Write-Host ''
Write-Host 'Done. If MySQL ever goes down, it will be started again automatically.'

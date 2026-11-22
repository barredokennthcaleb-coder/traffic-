#requires -Version 5.1
<#
.SYNOPSIS
Registers the two Windows Scheduled Tasks for automated backups.

By default tasks are registered DISABLED so nothing touches the database until
the owner approves the final enable step. Run with -Enable to activate them.
#>
[CmdletBinding()]
param([switch]$Enable)

$ErrorActionPreference = 'Stop'
$Root   = Split-Path -Parent $MyInvocation.MyCommand.Path
$Script = Join-Path $Root 'auto-backup.ps1'
if (-not (Test-Path $Script)) { throw "Missing $Script" }
$user = "$env:USERDOMAIN\$env:USERNAME"

function Register-BackupTask {
    param([string]$Name, [string]$Argument, $Trigger, [string]$Description)
    $action = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument $Argument -WorkingDirectory $Root
    $settings = New-ScheduledTaskSettingsSet `
        -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable `
        -ExecutionTimeLimit (New-TimeSpan -Minutes 30) -MultipleInstances IgnoreNew
    try {
        $principal = New-ScheduledTaskPrincipal -UserId $user -LogonType S4U -RunLevel Limited
        Register-ScheduledTask -TaskName $Name -Action $action -Trigger $Trigger -Settings $settings `
            -Principal $principal -Description $Description -Force | Out-Null
    }
    catch {
        Write-Warning "S4U registration failed ($($_.Exception.Message)); using Interactive fallback."
        $principal = New-ScheduledTaskPrincipal -UserId $user -LogonType Interactive -RunLevel Limited
        Register-ScheduledTask -TaskName $Name -Action $action -Trigger $Trigger -Settings $settings `
            -Principal $principal -Description $Description -Force | Out-Null
    }
    if ($Enable) { Enable-ScheduledTask -TaskName $Name | Out-Null }
    else         { Disable-ScheduledTask -TaskName $Name | Out-Null }
    Write-Host ("{0}: {1}" -f $Name, (Get-ScheduledTask -TaskName $Name).State)
}

# Daily safety dump at 03:00 plus a weekly full dump on Saturday at 23:00.
# Every run dumps every record, so each backup contains all old and newly added data.
$dailyDbTrigger    = New-ScheduledTaskTrigger -Daily -At '03:00'
$weeklyDbTrigger   = New-ScheduledTaskTrigger -Weekly -DaysOfWeek Saturday -At '23:00'
$dailyFilesTrigger = New-ScheduledTaskTrigger -Daily -At '03:00'

$argDb    = '-NoProfile -ExecutionPolicy Bypass -NonInteractive -File "{0}" -Scope Database' -f $Script
$argFiles = '-NoProfile -ExecutionPolicy Bypass -NonInteractive -File "{0}" -Scope Files'    -f $Script

Register-BackupTask -Name 'Traffic Auto Backup - Database' -Argument $argDb -Trigger @($dailyDbTrigger, $weeklyDbTrigger) `
    -Description 'Daily full verified dump at 03:00 plus a weekly full dump every Saturday at 23:00 to writable\backups\auto and D:\traffic-backups\auto'
Register-BackupTask -Name 'Traffic Auto Backup - Files' -Argument $argFiles -Trigger $dailyFilesTrigger `
    -Description 'Daily compact archive of uploads, config and code'

Write-Host ''
if ($Enable) {
    Write-Host 'Tasks are active. Monitor them with: Get-ScheduledTask "Traffic Auto Backup*" | Get-ScheduledTaskInfo'
}
else {
    Write-Host 'Enable them once setup and verification are complete: .\install-auto-backup.ps1 -Enable'
}

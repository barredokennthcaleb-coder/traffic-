# Automated backups

Daily and weekly full database dumps plus a daily compact file archive, with a
second copy on the D: drive. Verified, logged, and self-pruning.

## Files

| File | Purpose |
| --- | --- |
| `auto-backup.ps1` | The backup worker. `-Scope Database` or `-Scope Files`. |
| `setup-backup-user.ps1` | One-time creation of the least-privilege `traffic_backup` account. |
| `install-auto-backup.ps1` | Registers the two Windows Scheduled Tasks. |
| `restore-backup.ps1` | Restores a dump into a scratch DB (or live with explicit flag). |
| `restore-latest-backup.ps1` | One-click emergency restore of the newest verified dump. |
| `EMERGENCY-RESTORE.bat` | Double-click wrapper for the emergency restore. |
| `writable\backups\auto\` | Primary backup location (SQL, ZIP, log, LAST-RESULT.txt). |
| `D:\traffic-backups\auto\` | Mirror on the second drive. |
| `writable\backups\.backup-credentials.ini` | DB credentials for the backup account (git-ignored, ACL-restricted). |

Everything under `writable\backups\` is git-ignored, so no database content or
credentials are ever committed.

## How a run works

1. Confirm MySQL genuinely answers `SELECT 1` (the old watchdog only checked the
   port, so a hung server could pass as healthy). Retries once after 10 seconds.
2. `mysqldump --single-transaction --hex-blob` writes to a temp file via
   `--result-file` (avoids UTF-16 corruption). The consistent snapshot needs no
   table locks; routine/event dumping is omitted because the app has none and it
   would require extra privileges on `mysql.*`.
3. The dump is rejected unless it is >1 KB **and** ends with the
   `Dump completed` marker — no partial dumps are ever stored.
4. The good file is moved into `writable\backups\auto\`, copied to D:, then old
   files are pruned.
5. Every run writes to `backup.log` and `LAST-RESULT.txt` in both locations.

File scope copies only code, `public\uploads\`, `.env`, config and migrations.
`vendor`, `.git`, caches, logs and old backups are excluded — the archive stays
small and the app can be rebuilt with `composer install`.

## Retention

- Database: keep any dump from the last **24 hours**, the newest of each day for
  **7 days**, then the newest of each week for **8 weeks**. Every run is a full
  dump, so each new backup contains all old and newly added records.
- Files: the newest of each day for **7 days**, then the newest of each week for
  **4 weeks**.
- Applied to both C: and D: copies.

## Schedule

| Task | Runs |
| --- | --- |
| `Traffic Auto Backup - Database` | Daily at 03:00, plus a weekly full dump Saturday at 23:00 |
| `Traffic Auto Backup - Files` | Daily at 03:00 |

Tasks are registered **disabled** until the owner approves enabling them.

```powershell
.\install-auto-backup.ps1            # register/update, disabled
.\install-auto-backup.ps1 -Enable    # activate
```

On this PC the installer could not use S4U (it needs elevation) and fell back to
**Interactive**, so tasks run only while the user is logged on. To make them run
while logged out, right-click PowerShell → **Run as administrator** and re-run
`.\install-auto-backup.ps1 -Enable` so S4U registration succeeds.

Check status / history in `taskschd.msc` (Task Scheduler → Task Scheduler Library),
or:

```powershell
Get-ScheduledTask 'Traffic Auto Backup*' | Get-ScheduledTaskInfo |
    Select-Object TaskName, LastRunTime, LastTaskResult, NextRunTime
```

## Setup / rotation

```powershell
.\setup-backup-user.ps1            # generates credentials + SQL only, no DB access
.\setup-backup-user.ps1 -Apply     # creates the account (requires approval)
```

To rotate the password, delete `writable\backups\.backup-credentials.ini` and
re-run with `-Apply`.

## Restore

### Emergency (newest backup, one click)

Use only when the live database is damaged and the owner has approved the restore.

1. Stop the web app if it is still serving (`php spark serve` / Apache). If the
   PHP dev server on port 8080 is running, the script offers to stop it.
2. Double-click `EMERGENCY-RESTORE.bat`.
3. Check the dump path, age and size it prints, then type `RESTORE`.
4. The script imports into the live database, verifies the main table counts, and
   writes `writable\backups\auto\restore.log`.
5. Restart the app afterwards.

Preview without changing anything:

```powershell
.\restore-latest-backup.ps1 -Preview
```

If MySQL's root account has a password:

```powershell
.\restore-latest-backup.ps1 -RootPassword 'your-root-password'
```

The script refuses to run if no complete dump is found or MySQL is not answering.
If MySQL cannot start because its data files are corrupted, repair the data
folder first — a logical SQL dump cannot be imported into a server that will not
start. See `DATABASE-RECOVERY-NOTES.md`.

### Manual restore

```powershell
# Safe: restores into a new scratch database, leaves live data untouched
.\restore-backup.ps1 db_20261119_130000.sql

# Only with explicit approval: overwrite the live database
# (stop the web app first; MySQL must stay running)
.\restore-backup.ps1 db_20261119_130000.sql -ConfirmLiveRestore
```

The restore script strips `USE` / `CREATE DATABASE` statements from the dump so
data cannot accidentally land in the wrong database, verifies the dump's
completion marker, and imports as UTF-8 without PowerShell altering non-ASCII
data.

## Troubleshooting

- **LAST-RESULT.txt says FAILED** — read `backup.log` for the reason. A hung-but-
  listening MySQL produces "Database did not answer SELECT 1"; follow the Aria
  repair procedure in `DATABASE-RECOVERY-NOTES.md`.
- **No D: copy** — confirm `D:\traffic-backups\auto` exists and is writable.
  The task still logs a successful C: backup; the mirror step is best-effort at
  the file level but logs any failure as a run failure.
- **Dry run without touching the database:**
  `powershell -File .\auto-backup.ps1 -Scope Files -NoMirror -NoPrune`
- **Tasks run only when logged on?** The installer tries S4U (runs whether logged
  in or not, no stored password) and falls back to Interactive if Windows refuses.
  Check the task's "Log on as" in Task Scheduler if backups stop after logoff.

## Standing rule

Creating the backup account, running a dump, and restoring are database-changing
commands. They require explicit owner approval per `CLAUDE.md`; this document and
the scripts were prepared without performing them.

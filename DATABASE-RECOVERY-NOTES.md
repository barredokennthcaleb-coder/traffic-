# Database Recovery Notes (2026-11-16)

What happened, what was done, and how to keep the database healthy.

## THE FINAL ROOT CAUSE (important!)

Something on this PC **silently terminates any process named `mysqld.exe`**
(no crash log, no Windows error report — a deliberate external kill; likely an
antivirus/security/"optimizer" tool protecting against database servers, or a
stray cleaner). Proven by experiment: the exact same server binary renamed to
`traffic_mysqld.exe` runs for hours, while `mysqld.exe` dies within seconds.

**Therefore the app's MySQL now runs as `C:\xampp\mysql\bin\traffic_mysqld.exe`**
(a renamed copy of the same server). All launchers (start-database.bat,
watchdog, startup shortcut) were updated to use it. Keep that file — if MySQL
is ever upgraded, copy the new mysqld.exe to this name again.

During the outage windows, the repeated kills had physically damaged the
`traffic_live` InnoDB files; the database was rebuilt fresh from the clean
backup dump (`traffic_live.sql`) with all data verified intact
(54 violations, 21 violators, 2 users, 15 violation types).

## What broke

1. **Corrupted system table `mysql.db`** (Aria engine, "wrong checksum" + "marked as
   crashed"). MySQL could no longer read the privilege table, so logins failed with
   confusing errors like `Access denied for user ''@'localhost' to database 'traffic_live'`.
2. **Corrupted InnoDB page in `traffic_live.violation_types`**, caused by MySQL being
   repeatedly killed / crashing over the past days (unclean shutdowns).
3. This XAMPP MariaDB build has an intermittent **Aria redo-log write bug**
   (`Error writing file 'aria_log.00000001' (Errcode: 22 "Invalid argument")`).
   It only affects rare admin operations (GRANT / user changes), not the app's
   normal InnoDB activity.

## What was done

- Full raw copy of the data directory was taken before any repair.
- `mysql.db` was repaired offline with `aria_chk` and then repaired online.
- The good, pre-corruption copy of the data folder was **promoted to be the live
  data directory** (`C:\xampp\mysql\data`), after verifying every `traffic_live`
  table with `CHECK TABLE` (all OK).
- The app account `traffic_app` was re-created with the password from `.env`
  and given full rights on `traffic_live`.
- A graceful error handler was added so that if MySQL is ever unavailable again,
  users see a friendly "Temporarily unavailable" page instead of a stack trace
  (`app/Exceptions/DatabaseExceptionHandler.php`, wired in `app/Config/Exceptions.php`).
  That page **reloads itself every 8 seconds** and returns to the page the user
  was on as soon as the database is reachable again — no button mashing needed.
- The DB connect timeout was reduced from 10s to 4s
  (`system/Database/MySQLi/Connection.php`, marked with a "Local tweak" comment)
  so requests fail fast instead of hanging during short outages.
- Because MySQL on this PC is being killed periodically by something outside the
  app, grants are now applied together with `FLUSH TABLES` so they are written
  to disk immediately and survive a later kill (see `fix-database.bat`).

## Backups (do not delete)

- `C:\xampp\mysql_data_backup_20261116_1012\`
  - `data_raw\` — full copy of the MySQL data folder (pre-corruption, raw)
  - `traffic_live.sql` — clean logical dump of the app database
  - `alldbs_*.sql` — logical dump of all readable databases
- `C:\xampp\mysql\data_broken_20261116_1242\` — the replaced (damaged) data folder,
  kept for forensics.
- `writable\backups\system_backup\` — rolling backups made by `backup-system.bat`.

## Helper scripts (project root)

| Script                   | Purpose                                                        |
| ------------------------ | -------------------------------------------------------------- |
| `start-database.bat`     | Start MySQL if it is not already running (resumes the watchdog) |
| `stop-database.bat`      | **Cleanly** stop MySQL before shutting the PC down (pauses the watchdog) |
| `fix-database.bat`       | One-click repair: reads `.env`, re-creates user + rights       |
| `install-watchdog.bat`   | Install/refresh the database watchdog (already installed)      |
| `watchdog-database.bat`  | The watchdog itself: restarts MySQL within ~30s if it goes down |
| `backup-system.bat`      | Database dump + app files archive                              |

### Watchdog

The watchdog (`watchdog-database-hidden.vbs` → `watchdog-database.bat`) runs
hidden, starts at every logon, and checks every ~30 seconds whether MySQL is
answering on 127.0.0.1:3306. If not — for any reason — it starts it again and
writes a line to `%TEMP%\traffic_mysql_watchdog.log`.

- `stop-database.bat` creates `%TEMP%\traffic_mysql_stop` which pauses the
  watchdog, so an intentional shutdown is not fought.
- `start-database.bat` deletes that file, resuming the watchdog.

## How to avoid this happening again

1. **Before shutting down or restarting the PC, run `stop-database.bat`**
   (or stop MySQL from the XAMPP Control Panel). Cutting power / forcing the PC
   off while MySQL is writing is what corrupts tables.
2. If the site ever shows a database error, run `fix-database.bat` first.
3. **Suspected antivirus interference (may need Administrator):**
   During the repair session, mysqld sometimes died silently — no crash log, no
   Windows error report — which is the signature of a process being *terminated*
   by something else. At the same moments, Windows logged Defender configuration
   changes (events 1006/5007):
   - 2026-11-16 12:02 and 12:46 — matching the silent deaths.
   Ask an administrator to:
   - Open Windows Security → **Protection history** and look for `mysqld.exe`.
   - Add exclusions for `C:\xampp\mysql\bin\mysqld.exe` and `C:\xampp\mysql\data`.
   - Review the events in Event Viewer → `Microsoft-Windows-Windows Defender/Operational`
     (event 5007 = configuration changed — find out what is changing it).
   Also check for any third-party "system optimizer" / antivirus that may
   terminate or clean up running processes.
4. Install MySQL as a Windows service if possible (XAMPP Control Panel → MySQL →
   Service): it then stops cleanly with Windows shutdown. No admin? The watchdog
   already covers availability.
5. Keep running `backup-system.bat` regularly.

## Addendum: the PHP dev server

The site at `localhost:8080` is served by PHP's built-in dev server, which
handles **one request at a time**. If a request ever hangs while the database
is down, the whole site appears frozen until it times out. If that happens,
restart that server (stop the `php.exe` process and start it again) or simply
reload after a few seconds.

## Housekeeping done (2026-11-16)

- `writable\` shrunk from ~672 MB to ~86 MB: debug-toolbar dumps, cache,
  stale sessions and old log files cleared; two stale 282 MB system backup
  zips removed (newer backups kept).
- UI preview artifacts that were loose in `writable\` (plus `preview_report.html`
  and `database.sql` from the project root) were **archived, not deleted**, at
  `writable\backups\_dev_previews_archive\`.
- Removed local tool leftovers: `.appdata\`, `.freebuff\`, `.cloud-attachments\`.
- `.git` garbage objects pruned (`git gc --prune=now`).
- Every `traffic_live` table and every `mysql` system table re-checked:
  all report **OK** (system tables were repaired).
- **Never touched:** `public\uploads\` (violator/officer photos),
  `writable\backups\`, recovery snapshots, `mockups\`.

## Deploying to web hosting — checklist

1. Upload the project **without**: `.git`, `writable\backups`, `writable\cache`,
   `writable\logs`, `writable\session`, `writable\debugbar`, `mockups`, `build`,
   and the local helper scripts (`*.bat`, `fix-database.ps1`, `watchdog-*`).
   The `.dockerignore` file already lists these.
2. Point the hosting **document root to the `public\` folder**.
3. On the server create `.env` (see `.env.example`) with:
   - `CI_ENVIRONMENT = production`
   - hosting database credentials (`database.default.*`)
   - `app.baseURL = https://your-domain`
   - a **new** `auth.masterRecoveryKey` and **changed** default account
     passwords (`auth.defaultAdminUsername/Password`, `auth.defaultUserPassword`)
4. Import the database from a recent dump (`writable\backups\system_backup\db_*.sql`
   or `C:\xampp\mysql_data_backup_20261116_1012\traffic_live.sql`).
5. Make sure `writable\` (outside the document root) is writable by the server.
6. In production mode the debug toolbar is off automatically; the friendly
   "Temporarily unavailable" database page also works on the host with no
   extra setup.

## Known leftovers (not part of the app)

- Databases `mark`, `order_system`, `orders_db` (and parts of `traffic_main`,
  `traffic_scratch`) reference tables that the engine cannot open
  ("doesn't exist in engine"). They were already unreadable before this repair.
  Their files are preserved in the backups above if recovery is ever needed.
- `phpMyAdmin` works; its optional "pmadb" control-user grants were not restored.

## Addendum: 2026-11-16 evening — Aria recovery crash-loop (root cause found)

**Symptom:** every page showed the friendly "Temporarily unavailable" sheet; the
app log filled with `Access denied for user 'traffic_app'@'localhost'`, then
`MySQL server has gone away`, and finally the server was not listening at all.
The watchdog kept announcing "MySQL is down - starting it now" every ~25 s.

**Cause:** MariaDB itself could not start. `mysql_error.log` showed:

```
Cannot find checkpoint record at LSN (1,0x5556)
[ERROR] mysqld.exe: Aria recovery failed. Please run aria_chk -r on all Aria
        tables and delete all aria_log.######## files
[ERROR] Plugin 'Aria' registration as a STORAGE ENGINE failed.
[ERROR] Could not open mysql.plugin table. Some plugins may be not loaded
[ERROR] Failed to initialize plugins.
[ERROR] Aborting
```

The Aria redo log (`aria_log.00000001`) had lost its checkpoint, so the Aria
engine — which holds **every `mysql.*` grant/system table** — refused to load.
That is why the app account kept "disappearing": the server rolled back to an
older grant state, and each repair was lost again at the next crash.

**Repair performed 2026-11-16 ~18:34:**

1. Paused the watchdog (stop marker) so it could not start the server mid-repair.
2. Backed up the mysql system tables and aria logs to
   `C:\xampp\mysql\aria_backup_20261116_1834\`.
3. Ran `aria_chk -r` on `data\mysql\*.MAI` (only `columns_priv` needed `-f`;
   it verifies clean now).
4. Deleted the stale `aria_log.00000001` (backed up) so Aria creates a fresh one.
5. Started MariaDB — it logged `Aria engine: starting recovery ... recovery done`
   and `ready for connections`.
6. Re-ran `fix-database.ps1`; verified `traffic_app` can open `traffic_live`.
7. Clean-restart test: shutdown → start → app login still works, so the grants
   now survive a normal restart. Data intact (21 violators, 54 violations).

**New safety net:** `watchdog-database.bat` now also guards the app login. Right
after it starts MySQL, and every ~2 minutes, it runs
`fix-database.ps1 -OnlyIfBroken -Quiet`. That exits without touching anything
while the login works, and re-creates the account from `.env` only when it fails
— so a rolled-back grant table heals itself and the site's 503 page reloads back
into the app.

**Backoff:** if a start attempt does not bring port 3306 up, the watchdog now
waits two minutes before the next attempt instead of retrying every 25 seconds,
so a damaged data directory is not hammered.

**Still needs an administrator:** stop whatever kills `mysqld` (Defender
exclusions for `C:\xampp\mysql\bin\mysqld.exe` and `C:\xampp\mysql\data`, and
install MySQL as a Windows service). Unclean shutdowns are what corrupt Aria in
the first place; everything above only makes recovery automatic and fast.

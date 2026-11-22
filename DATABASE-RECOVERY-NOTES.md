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
| `EMERGENCY-RESTORE.bat`  | One-click restore of the newest verified database dump         |

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

## Addendum: 2026-11-17 — grant rollback traced to a split-brain data directory

**Symptom:** the login page kept returning "Temporarily unavailable" after every
restart, with `Access denied for user 'traffic_app'@'localhost'`. Re-running
`fix-database.ps1` restored access, but the next restart lost it again, and
`FLUSH PRIVILEGES` intermittently failed with
`error 176 "Read page with wrong checksum" from storage engine Aria`.

**Root cause:** `C:\xampp\mysql\bin\my.ini` had `datadir` pointing at one folder
while `innodb_data_home_dir` and `innodb_log_group_home_dir` still pointed at the
old, damaged `data_recovered_20261117_121105` directory. Every start therefore
mixed a clean schema/lock state with stale InnoDB pages and old grant tables, so
password and privilege writes were effectively rolled back. `mysql.db` was also
reported as *crashed* and its pages failed checksum verification.

**Repair performed:**

1. Backed up the Aria logs and the `mysql.*` system tables before touching them.
2. Ran `aria_chk -r -f -n` (and `-o -f` where the sort buffer was too small) over
   the Aria tables, then deleted the stale `aria_log.*` files so Aria rebuilt its
   log from a clean checkpoint.
3. Created a brand-new data directory `C:\xampp\mysql\data_live`, initialised it
   with `mysql_install_db.exe`, and pointed **all three** directory settings in
   `my.ini` (`datadir`, `innodb_data_home_dir`, `innodb_log_group_home_dir`) at it.
4. Imported `writable\backups\system_backup\db_20261116_2108.sql`
   (54 violations / 21 violators / 2 users / 15 violation types) and re-created
   the `traffic_app` account with `fix-database.ps1`.
5. Installed MariaDB as a Windows service `TrafficMySQL` (auto-start) so it no
   longer depends on a chat/terminal session. It points at the same `my.ini`.

**Verification:** after a full shutdown and a fresh start,
`php tools\db-login-check.php` printed `LOGIN OK, violations rows: 54`, the site
returned HTTP 200 on `/login`, and the row counts above were unchanged.

**Fix in `fix-database.ps1`:** the login check used `mysql -p<password>` on the
command line, which let cmd/PowerShell expand characters inside the password and
report a working account as "Access denied" (making the watchdog re-grant every
minute). It now uses a temporary `--defaults-extra-file`, so the check is quiet
and accurate.

**Keep, do not delete yet:** `data_corrupt_20261117_1155`,
`data_recovered_20261117_121105`, `data_1`, `db_table_backup_20261117_1131`,
`log_rebuild_backup_20261117_1111/1115`, `aria_repair_*`.

**Reminder:** never start two MySQL instances against the same data directory,
and stop MySQL cleanly (`stop-database.bat`) before shutting the PC down. Hard
kills are what corrupt the Aria tables that hold every account and privilege.

## Addendum: 2026-11-17 evening — login 503 while MySQL was running

**Symptom:** the login page showed "Temporarily unavailable" even though
`TrafficMySQL` was running and port 3306 was open. `php tools\db-login-check.php`
reported `Access denied for user 'traffic_app'@'localhost'`; `mysql_error.log`
repeated `Table '.\mysql\columns_priv' is marked as crashed and last
(automatic?) repair failed`.

**Cause:** two Aria privilege tables were damaged again:
`mysql.columns_priv` (crashed, error 144) and `mysql.db` (wrong page checksum,
error 176). Because the server could not read them, it denied every login, and
the app's exception handler presented the outage page.

**Repair performed:**

1. Created the watchdog stop marker and stopped MySQL cleanly with `mysqladmin shutdown`.
2. Backed up `columns_priv.*` and the whole `mysql` schema folder to
   `C:\xampp\mysql\db_table_backup_columns_priv_20261117_190616\` and
   `C:\xampp\mysql\db_table_backup_mysqlschema_20261117_190749\`.
3. Repaired offline with `aria_chk -o -f` (safe recover with keycache; the
   normal sorted recover still fails with the 16 KB sort buffer in this build).
   Safe-recovering `mysql.db` dropped its 5 rows; `columns_priv` had none.
4. Started MySQL, ran `REPAIR TABLE mysql.columns_priv` online (it retries with
   keycache and reports OK), then `FLUSH PRIVILEGES`.
5. Re-ran `fix-database.ps1` to re-create the `traffic_app` grants on
   `localhost`, `127.0.0.1` and `::1`.
6. Fixed a typo in `fix-database.ps1`: its login check built
   ``USE `traffic_live; SELECT 1`` (missing closing backtick), so the script
   reported "App login FAILED" even on a healthy database. It now wraps the
   database name with two backticks on each side.

**Verification:** `CHECK TABLE mysql.db, mysql.columns_priv, mysql.user` all OK;
`php tools\db-login-check.php` prints `LOGIN OK, violations rows: 54`;
`/traffic-/public/login` returns HTTP 200; 54 violations / 21 violators intact.

**Note:** after the repair MySQL was started with `start-database.bat`, so the
`TrafficMySQL` Windows service shows *Stopped* until the next reboot (the
service is Automatic and will start normally then). The watchdog keeps the
current process running in the meantime.

## Addendum: 2026-11-17 ~21:15 — Aria checkpoint lost again (XAMPP "MySQL shutdown unexpectedly")

**Symptom:** XAMPP showed `Error: MySQL shutdown unexpectedly`; nothing was
listening on 3306 and the watchdog was retrying every 2 minutes. Every attempt
in `data_live\mysql_error.log` ended with:

```
Cannot find checkpoint record at LSN (1,0x289f)
[ERROR] traffic_mysqld.exe: Aria recovery failed. Please run aria_chk -r on all
        Aria tables and delete all aria_log.######## files
[ERROR] Plugin 'Aria' registration as a STORAGE ENGINE failed.
[ERROR] Could not open mysql.plugin table. Some plugins may be not loaded
[ERROR] Aborting
```

`traffic_mysqld.dmp` (17:41) shows the server itself crashed again earlier that
afternoon, which left the Aria redo log without a checkpoint.

**Repair performed (same procedure as 2026-11-16):**

1. Paused the watchdog (`%TEMP%\traffic_mysql_stop`).
2. Backed up `aria_log.00000001`, `aria_log_control`, `mysql\` and
   `traffic_live\` to `C:\xampp\mysql\aria_repair_20261117_211440\`.
3. `aria_chk` found four damaged Aria tables; `columns_priv` and
   `global_priv` had `create_trd` > max transaction id, `event` and
   `proxies_priv` had a wrong data-file size. All four were safe-recovered
   with `aria_chk -o -f` (run from `data_live`, so the control file is found).
4. Deleted the stale `aria_log.00000001` (backup kept). All 24 Aria tables then
   verified clean.
5. Started with `start-database.bat`, re-ran `fix-database.ps1`.

**Verification:** `php tools\db-login-check.php` → `LOGIN OK, violations rows:
54`; violations/violators/users/types = 54/21/2/15; `CHECK TABLE` OK;
`http://localhost:8080/traffic-/public/login` → HTTP 200.

**Note:** while started by `start-database.bat`, the XAMPP Control Panel and the
`TrafficMySQL` service both show MySQL as stopped — the server is the separate
`traffic_mysqld.exe` process. Do not press Start in the panel while it is
running (the port is taken). Starting the service needs an administrator.

## Addendum: 2026-11-19 ~13:00 — MySQL "up" but hung, site 503

**Symptom:** `/traffic-/public/login` showed "Temporarily unavailable" and the
XAMPP panel said `Error: MySQL shutdown unexpectedly`. In fact a
`traffic_mysqld.exe` **was** listening on 3306, but the error log stopped at
`Server socket created on IP: '127.0.0.1'` — there was no
`ready for connections` line, and a client got
`ERROR 2013 ... handshake: reading initial communication packet`.
The watchdog only checks whether port 3306 is listening, so it considered this
hung server healthy and did nothing.

**Root cause:** two Aria system tables were damaged again — `mysql.db`
(`Index is corrupted` / "doesn't have a correct index definition", its `.MAI`
had ballooned from 24 KB to 1.3 MB) and `mysql.proxies_priv` (wrong page info,
error 176). Aria could not finish plugin/grant initialisation after opening the
socket, so the server hung forever instead of accepting connections.

**Repair performed:**

1. Created `%TEMP%\traffic_mysql_stop` to pause the watchdog, and stopped the
   watchdog's in-flight `fix-database.ps1` (it was trying to start a *second*
   server while the first was hung).
2. The hung server ignored `mysqladmin shutdown` (no handshake), so it was
   force-stopped. Full copy of `data_live` saved to
   `C:\xampp\mysql\data_live_backup_20261119_131939\`.
3. `aria_chk -o -f mysql/proxies_priv` repaired that table (4 records, OK).
4. `mysql/db` could not be repaired by `aria_chk` at all; a clean trio
   (`db.frm`, `db.MAD`, `db.MAI`) was restored from
   `C:\xampp\mysql\aria_repair_20261117_211440\mysql_schema\`.
5. Deleted stale `aria_log.00000003`. After a crash-cycle this left
   `aria_log_control` pointing at a missing log
   (`File 'aria_log.00000003' not found / Plugin 'Aria' registration failed`);
   deleting `aria_log_control` as well let Aria rebuild from scratch.
6. Started `traffic_mysqld.exe` directly with `--defaults-file=...\my.ini`,
   ran `REPAIR TABLE mysql.db` as root (it reported the known Aria redo-log
   error but `CHECK TABLE mysql.db` was OK afterwards), then `fix-database.ps1`.

**Verification:** `php tools\db-login-check.php` → `LOGIN OK, violations rows:
61`; `/traffic-/public/login` → HTTP 200, no outage page.

**Watchdog caveat:** its health check is only `netstat` on port 3306. If a
server is hung after opening the socket, the watchdog will not restart it and
will not log anything. If the site shows the outage page while port 3306 is
listening, test with `mysql -u root --connect-timeout=5 -e "SELECT 1"`; if that
fails, stop the hung process manually and repeat the Aria repair above.

**InnoDB damage — resolved the same day:** `traffic_live.inspections` had a
corrupt page 0 and `traffic_live.orcr_sequences.ibd` reported `Expected
tablespace id 7 but found 4294967294`. Both `SELECT` and `CHECK TABLE` on
`inspections` crashed the server. Repair procedure that worked:

1. Cleanly stopped the live server, copied `data_live` to
   `C:\xampp\mysql\data_salvage_20261119\`, and restarted the live app server.
2. Started a **second, isolated** server from `bin\my_salvage.ini` (port 3307,
   separate datadir, `innodb_force_recovery=1`). With force recovery the
   damaged `inspections` table was readable (2 rows) and was dumped with
   `mysqldump --skip-lock-tables --no-create-info`. `orcr_sequences` was
   unreadable ("doesn't exist in engine") and was not recoverable.
3. **Do not try `DROP TABLE` on a corrupt table with normal recovery** — that
   crashes the server. Instead start the live server with
   `--innodb-force-recovery=3`, `DROP TABLE` both tables (succeeds), shut down,
   then restart normally and recreate them from the migration definitions.
4. Re-imported the 2 inspection rows and seeded `orcr_sequences` from the
   highest paid `violations.receipt_number` (`last_number = 234567`, width 6).
   `CHECK TABLE` reports **OK** for both; they no longer crash the server.

**After the crash-cycle the Aria grant tables were damaged again** (`mysql.db`
wrong checksum, `mysql.proxies_priv` "from another system"). Final fix:
`aria_chk -o -f` / restore clean `db.*` from `aria_repair_20261117_211440`,
delete all `aria_log.*` including `aria_log_control`, start, then run
`REPAIR TABLE mysql.db, mysql.proxies_priv` online and `fix-database.ps1`.
Everything was then verified after a full clean shutdown/restart: `php
tools\db-login-check.php` → `LOGIN OK, violations rows: 61`, login page HTTP
200, watchdog marker cleared. Keep `data_salvage_20261119\` and
`mysql_schema_backup_20261119_1335\` as backups.

# Traffic Citation and Enforcement System

A CodeIgniter 4 application for traffic citation records, violator histories, payments, inspections, and administrative reporting.

## Requirements

- PHP 8.2 or newer with `intl`, `mbstring`, and `mysqli`
- Composer
- MySQL or MariaDB for the application database
- XAMPP, or another PHP-compatible web server

## Local setup (XAMPP)

1. Start Apache and MySQL from the XAMPP Control Panel.
2. Create an empty MySQL/MariaDB database for the app.
3. Install PHP dependencies:

   ```powershell
   composer install
   ```

4. Configure the application in `.env`. Set `app.baseURL` and the `database.default.*` values for the database you created. Do not commit credentials.
5. Apply the schema and seed the violation catalog:

   ```powershell
   php spark migrate
   php spark db:seed ViolationTypesSeeder
   ```

   Run the seeder only on a fresh setup: it replaces the contents of the violation-types table.

6. Point Apache's document root at the project's `public` folder, or use CodeIgniter's development server:

   ```powershell
   php spark serve
   ```

7. Open the configured base URL. Registration creates driver accounts only. For an empty installation, provision the first administrator through a controlled database setup using a password hash (never a plaintext password); create later elevated accounts through the admin user-management screen.

The legacy `database.sql` dump is provided for existing installations. For a new installation, use migrations rather than importing the dump and then running migrations against the same database.

## Accessing from a phone / another device on the same network

Apache listens on all interfaces and the app builds its links from the request
host, so no code changes are needed for LAN access. Two things can still block it:

1. **Windows Firewall.** By default inbound port 80 is blocked. Run
   `allow-lan-access.bat` (double-click it, or right-click → **Run as
   administrator**) and approve the UAC prompt. This opens ports 80 (Apache)
   and 8080 (`php spark serve`). It only needs to be done once.
2. **Same network.** The phone and the PC must be on the same WiFi. If the WiFi
   has "AP isolation / client isolation" enabled, devices cannot reach each
   other — disable it on the router.

Then find the PC's LAN address (`ipconfig`, the `IPv4 Address` of the active
Wi‑Fi adapter) and open it on the phone, for example `http://199.1.14.19/`.
Private ranges (`10.x`, `172.16-31.x`, `192.168.x`) and the PC's own IP are
trusted automatically, so DHCP address changes keep working. Fixed hostnames
can be added to `app.allowedHostnames` in `.env`.

Do not use `localhost` on the phone — that points at the phone itself.

## Main workflows

- **Officer/enforcer:** set duty status, issue multi-infraction citations with TCT numbers, review violator history, and manage pending citations.
- **Driver:** review citations and payment history, and view payment receipts.
- **Administrator:** manage users and violation types, review violators, record payments, and produce reports.
- **Inspection:** create, update, review, and print inspection reports.
- **Public lookup:** search citation records through the public lookup page.

## Tests

Run the PHPUnit suite from the repository root:

```powershell
vendor\bin\phpunit
```

Tests use the in-memory SQLite test connection configured in `app/Config/Database.php`; they do not need the application MySQL database. Enable both `sqlite3` and `pdo_sqlite` in PHP to run the database tests. In XAMPP, uncomment their extension entries in `C:\xampp\php\php.ini` and restart PHP/Apache.

## Deployment notes

- Serve only the `public` directory; do not expose the repository root.
- Set `CI_ENVIRONMENT = production` and use a strong encryption key in the deployment environment.
- Configure and verify the database backup process before entering operational records.
- Never commit `.env`, passwords, or analytics credentials.

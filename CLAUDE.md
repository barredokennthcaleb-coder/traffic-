# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

CodeIgniter 4 application for the CTRAMO Kabankalan traffic citation and enforcement system: citation issuance, violator registry, payments, inspections, and administrative reporting. PHP 8.2+, MySQL/MariaDB, served from the `public/` directory (`app.baseURL` defaults to `http://localhost/traffic-/public/`).

## Commands

```powershell
composer install                                   # install PHP deps
php spark migrate                                  # apply schema
php spark db:seed ViolationTypesSeeder             # seed offense catalog (replaces table contents; fresh setups only)
php spark serve                                    # dev server (or let XAMPP/Apache serve public/)

vendor\bin\phpunit                                 # full test suite
vendor\bin\phpunit tests/unit/TrafficWorkflowHelpersTest.php    # one test file
vendor\bin\phpunit --filter testNormalizesSerialOnlyTicketNumbersUsingTheSelectedYear   # one test method
vendor\bin\phpunit tests/unit                      # one test directory
```

Tests run against the in-memory SQLite connection (`Database::$tests` in `app/Config/Database.php`; `ENVIRONMENT === 'testing'` forces that group), so they do not touch the MySQL app database. Both `sqlite3` and `pdo_sqlite` must be enabled in `php.ini`. There is no linter/formatter script wired up (php-cs-fixer is a dev dependency but no config file is committed).

Docker: `Dockerfile` builds `php:8.2-apache` with the document root at `/public` and runs `composer install --no-dev`.

## Architecture

### Role-based routing and access control

`app/Config/Routes.php` groups routes by area, each guarded by a filter alias (defined in `app/Config/Filters.php`):

- Public: `/`, `/login`, `/register`, `/forgot-password`, `/lookup` (public citation lookup).
- `officer/*` → `App\Filters\OfficerAuth` (allows `enforcer` and `admin`).
- `user/*` → `App\Filters\Auth` (any logged-in user).
- `admin` area (empty prefix, e.g. `/dashboard`, `/users`, `/penalties`, `/reports`) and `/violators` → `App\Filters\AdminAuth` (requires `role === 'admin'`).
- `/inspections/*` → `OfficerAuth`.

Session keys used throughout: `isLoggedIn`, `id`, `role`, `username`, `email`, `profile_image`. Roles are `admin`, `enforcer`, `driver` (both `driver` and the legacy `user` value redirect to `/user/dashboard` — see `AuthController::redirectByRole`). Registration only ever creates `driver` accounts; elevated accounts come from the admin user-management screen.

The global `compress` after-filter gzips text responses (the `php -S` dev server has no `mod_deflate`), and the debug `toolbar` is deliberately disabled in `$required['after']`.

### Data model: ticket → violations → violator

This is the central concept and spans several files:

- **A ticket** is identified by `ticket_id` in the TCT format `YYYY-XXXXXXX`. A single ticket can carry **multiple infraction rows**, one per selected offense. Each row in the `violations` table is one infraction, and all rows sharing a `ticket_id` are the same citation.
- **A violator** is a person (`violators` table). `violations.violator_id` links every infraction back to one person across many tickets.
- **Statuses** (`violations.status`): `Pending`, `Paid`, `Cancelled`. A payment settles the *entire* ticket (all non-cancelled infractions on that `ticket_id`), but cancelled infractions stay cancelled.

Money semantics, applied consistently in dashboards and reports: a `Cancelled` citation is void and adds no money anywhere; `total_amount`/assessed value = Paid + Pending; `paid_amount` = collected revenue only. Keep `assessed = collected + pending` true when touching any aggregation.

**Model naming trap** — read the `$table` property, not the class name:
- `ViolatorRecord` → table `violations` (the primary violations model: TCT generation, payment/cancel, and all reporting/aggregation queries).
- `ViolatorModel` → table `violators` (person registry: `resolveForTicket`, `updateSnapshot`, `syncTicket`, `pruneIfOrphan`, paginated directory).
- `ViolationRecord`, `ViolationModel`, `AnalyticModel`, `PaymentModel` are **dead leftovers** — nothing instantiates them, and the two `_Old` classes (`class ViolationModel_Old`, `class ViolationRecord_Old`) do not even match their file names, so PSR-4 can never load them. Their table mappings (`violations`, `payments`) are notes, not live code; `ViolatorRecord` supersedes both. Safe to delete.

`ViolatorIdentity` (`app/Libraries/ViolatorIdentity.php`) computes a stable `identity_key` used to match citations to one person, in priority order: driver's license number (`dl:`), normalized name + real plate (`np:`), then a ticket-only fallback (`tk:`) for legacy anonymous rows. `ViolatorModel::resolveForTicket` is the single entry point that matches-or-creates a violator; an explicit `violator_id` from a profile always wins. Violators are pruned when their last ticket is deleted (`pruneIfOrphan`), so the registry count tracks live citations.

### TCT numbering

Format is `YYYY-XXXXXXX` (7-digit serial). `app/Helpers/tct_helper.php` (autoloaded as the `tct` helper) provides `normalize_tct_number`, `parse_tct_number`, `format_tct_number_display`, `tct_split_name`, `sanction_label`, and `kabankalan_areas` (the Barangay coverage list for Kabankalan stations). Allocation lives in `ViolatorRecord::generateNextTctNumber`, which locks the `tct_sequences` row `FOR UPDATE` and cross-checks the max serial actually present in `violations` (so it recovers even if the sequence table drifts). `peekNextTctNumber` previews without reserving; `syncSequenceIfHigher` keeps the sequence aligned when an officer enters a manual booklet number.

### Controller / view layout

- `app/Controllers/Officer/OfficerController.php` is the largest controller (citation issuance in `store`, ticket editing, violator profiles, duty status). `store` issues the whole ticket inside one transaction and guards against double-submits with a one-time `submission_token` stored in the session.
- `app/Controllers/Admin/AdminController.php` holds the dashboard, reports, CSV exports, and the `api/nature-summary` JSON endpoint.
- `app/Controllers/Admin/PenaltyController.php` handles viewing/paying/cancelling/reversing penalties.
- Views are grouped by role under `app/Views/{admin,officer,user,inspection,lookup,auth}`. Shared chrome is `app/Views/layouts/admin.php` and `app/Views/theme/sidebar.php`; pagination uses `app/Views/pagers/bootstrap_pagination.php`.

### Migrations

Schema is built by migrations in `app/Database/Migrations/` (the legacy `database.sql` dump is for existing installs only). Migrations are defensive — they check `tableExists`/`fieldExists` before altering, and the violator-linking migration backfills historical rows. New schema changes should follow that idempotent style.

### Analytics (PostHog)

`app/Views/theme/posthog.php` emits session-replay config only when `posthog.apiKey` is set in `.env` (see `.env.example`); with no key it prints nothing. The SDK version is pinned in `package.json` and resolved via an import map. Controllers queue journey events through the `posthog_journey` session key (e.g. `AuthController::loginPost` sets `['signed_in']`).

## Conventions observed in this codebase

- Controllers live in role sub-namespaces (`App\Controllers\Admin\…`, `Officer\…`, `User\…`) except the shared `AuthController`, `LookupController`, `InspectionController`.
- Models instantiate dependencies in the controller constructor rather than via `model()`.
- `UserModel` hashes passwords in `beforeInsert`/`beforeUpdate` callbacks; never write a plaintext password.
- Odd/legacy demo values are intentional in places (e.g. short-password validation in `UserModel`, password-reset-to-default in `AuthController::forgotPasswordPost`); check surrounding comments before "fixing" them.

## Database safety (standing rule from the project owner)

Never run a command that touches the database — schema or data changes, user/password/privilege changes, imports, exports, backups, migrations, or restarts of the database service — without asking the owner for permission first and explaining why the command is needed. Wait for explicit approval, then run only the approved scope. Read-only database inspections are announced before running. The application must connect with a dedicated least-privilege account, never `root`.

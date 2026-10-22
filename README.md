# Afro-Asian brokerage portal

A Laravel portal for cedants and broker administrators to view covers and statements, submit claims and premium adjustments, upload private documents, and exchange feedback.

## Local setup

Requires PHP 8.2+, Composer, Node.js with npm, and SQL Server with the PHP SQLSRV/PDO_SQLSRV drivers.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm ci
```

Configure your database connection and COMPANY_NAME in `.env`. Review and run migrations against the intended database:

```powershell
php artisan migrate --pretend
php artisan migrate
npm run build
php artisan serve
```

## Demo data

Use a separate local database with APP_ENV=local:

```powershell
php artisan db:seed --class=DemoSeeder
```

New demo accounts use password `password`: `admin@example.com`, `superadmin@example.com`, `client@example.com`, and `client2@example.com`. Existing passwords are preserved. Demo seeding is blocked outside local/testing environments. See [demo and seeding details](docs/demo-seeding.md).

## Production seeding

```powershell
php artisan db:seed --class=ProductionRolesSeeder --force
```

The default DatabaseSeeder creates roles, permissions, one General department and initial topic routing, without accounts or business examples. RolesSeeder defines permissions; DepartmentsSeeder supplies the default department. Create the first real superadmin with `php artisan portal:superadmin` using hidden password prompts. See [access and department setup](docs/portal-access-and-departments.md).

For deployment, configure APP_ENV=production, APP_DEBUG=false, HTTPS, real database/mail settings, and private document storage. Install PHP dependencies with `composer install --no-dev --optimize-autoloader`, build frontend assets with `npm ci` and `npm run build`, and apply reviewed migrations with a database backup. Keep `.env`, private documents and credentials out of Git.

## Mirrored source tables

| RBS source | Portal table |
| --- | --- |
| CSCompanies | companies |
| RECoverHeader | covers |
| RECoverReinsurers | cover_reinsurers |
| REClaims | claims |
| ARDebtorsPremiums | cedant_premiums |
| ARDebtorsPremiumsAGED | aged_cedant_premiums |
| APCreditorsClaimsAGED | aged_cedant_claim_payables |
| APCreditorsClaims | cedant_claim_payables |
| APCreditorsPremiums | reinsurer_premium_payables |
| REPremiumAdjustment | premium_adjustments |
| RERskCedHeader | profit_commissions |
| REPCRiskNotesPYears | profit_commission_details |

Company codes, cover numbers and claim numbers retain their source meaning. Mirror tables allow independent sync order. Portal submissions and private documents are separate from mirrored records.

## Current scope

Cedant records are restricted to the linked company. Claims are facultative, require supporting documents and use OrigClaimNo as the submitted/generated reference. Premium adjustments use CoverNo. Both sides have paginated feedback conversations with department routing and optional statement/document context. Premium adjustment documents are required; details are optional. Official claim status comes from linked mirrored claims; portal status controls remain deferred. Profit commissions remain disabled.

The RBS synchronisation process remains separate. Mirrored claims link to portal submissions by OrigClaimNo and company, without writing cached identifiers. Public registration requires email verification and broker approval before company access; each company defaults to five approved or live pending users. Only superadmin manages broker staff, departments and company limits. Emails use queued delivery. See [current migrations and setup](docs/portal-access-and-departments.md).

The previous test suite and obsolete preview files were archived outside the repository during cleanup. Test files were removed at the project owner's request. Standard development dependencies remain available; production installs exclude them using `--no-dev`.

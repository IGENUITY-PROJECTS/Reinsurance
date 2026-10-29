# Production And Demo Guide

Run commands from the reinsurance project directory. Production and demo are separate seeders; there is no combined production/demo seeder. These instructions do not run any database operations for you.

## Production Setup

1. Back up an existing database and private uploads. Confirm that the configured database is the intended production database.
2. Configure the production environment: APP_ENV=production, APP_DEBUG=false, APP_URL using the real HTTPS address, database credentials, SMTP settings, and a persistent cache/queue backend. Serve only the public directory. Keep .env and storage/app/private outside public access.
3. Retain the existing APP_KEY. For a new installation with no key only, generate it once with php artisan key:generate. Do not regenerate it during routine deployments.
4. Install dependencies and build assets, or deploy assets built by your build pipeline:

```sh
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --class=ProductionRolesSeeder --force
php artisan optimize
```

ProductionRolesSeeder calls RolesSeeder and DepartmentsSeeder. It creates the super-admin, admin and client roles; permissions and default assignments; one active General department; and initial feedback-topic routes. It creates no users, passwords, companies, claims or financial examples. Existing topic mappings are preserved. Existing feedback with no department is assigned to General. The default DatabaseSeeder calls this same production seeder, so php artisan db:seed --force is an alternative, not an extra required step.

Never run migrate:fresh, migrate:refresh or DemoSeeder against production. Production seeding does not remove existing demo data; use a clean production database rather than treating production seeding as cleanup.

## Create The First Superadmin

After production roles exist:

```sh
php artisan portal:superadmin
```

Enter a name, real email, password and password confirmation. Password prompts are hidden. Use at least 12 characters with uppercase, lowercase, a number and a symbol. The email must be new: the command does not promote or overwrite an existing account. The account is approved but must verify its email before entering the portal. No company code or department is required for superadmin.

Configure SMTP and process queued jobs so verification and feedback mail can be delivered. For a foreground worker:

```sh
php artisan queue:work --tries=3
```

In production, run the worker under the host's process manager so it survives terminal closure and restarts on failure. For an existing worker after deploying changed code:

```sh
php artisan queue:restart
```

Verify the superadmin email, then create real broker departments and staff under Departments and Users. Assign broker staff to active departments and route feedback topics. Staff must be approved and email-verified to receive departmental notifications. Check failed jobs with php artisan queue:failed before retrying a specific reviewed job.

For subsequent deployments, back up, deploy reviewed code/assets, apply pending migrations, refresh caches and restart workers. Creating a superadmin and running production seeders are not required on every deployment. If environment settings change, rebuild configuration caches with php artisan optimize.

## Demo Setup

Use a separate local/testing database. DemoSeeder deliberately refuses APP_ENV=production. Do not change a real production application's environment to bypass this protection.

```sh
php artisan migrate
php artisan db:seed --class=DemoSeeder
```

DemoSeeder includes roles/permissions, General/Demo Claims/Demo Finance departments, topic routing, company and cover examples, cover reinsurers, claims and submissions, private sample documents, premium adjustment submissions/records, premium/claim statements, aged mirrors, reinsurer premium payables, feedback conversations/replies, and verification/approval examples.

It currently does not seed profit_commissions or profit_commission_details. A premium adjustment's linked InvoiceAmount therefore needs a corresponding mirrored header to display; absence is shown as Not Available.

| Email | Purpose |
| --- | --- |
| superadmin@example.com | Superadmin |
| admin@example.com | General broker admin |
| claims.admin@example.com | Claims department broker |
| finance.admin@example.com | Finance department broker |
| client@example.com | Cedant DEMO-C001 |
| client2@example.com | Cedant DEMO-C002 |
| colleague@example.com | Another user for DEMO-C001 |
| pending@example.com | Verified, awaiting approval |
| unverified@example.com | Awaiting verification and approval |

New demo accounts use password as their password. Existing passwords are preserved. This intentionally weak sample password is only for demo accounts; real registration/creation uses the strict password rules. Rerunning DemoSeeder restores reserved demo account access/verification states and updates reserved fixtures. It is not a reset or cleanup operation. It writes private sample files and does not send verification emails.

## Login Rate Limiting

Configured in app/Providers/FortifyServiceProvider.php and enabled by config/fortify.php. The key is normalised email plus IP address. Up to five login POST requests are allowed per approximately 60-second window. All submissions count, including correct passwords; merely opening the login page does not count. A sixth submission during the same window receives HTTP 429 and must wait for the window to expire. The account is not deleted, suspended or permanently locked. Changing browsers on the same IP does not reset the same email/IP counter. Other email/IP combinations have separate counters, so this is one defence rather than complete protection against distributed attacks.

Counters depend on the cache. Use a persistent shared cache for production, particularly with multiple application servers. Configure trusted proxies correctly so the request IP reflects the real client. Do not clear the application's whole cache as a routine response to a login lockout.

Registration separately limits requests to three per minute and ten per hour per IP. Company capacity defaults to five approved or live pending accounts and is managed by superadmin under Users -> Company Access Limits. Reaching company capacity rejects the additional registration; it does not revoke existing users.

To test login throttling on staging, submit the same email from the same connection five times within a minute, then make a sixth attempt. Inspect HTTP 429 and Retry-After in browser Network tools. Wait for the indicated delay and try again. Test access approval/verification separately from throttling.

## Shared Broker Feedback — 29 October 2026

All approved, email-verified admin and super-admin users receive new feedback and follow-up/reply notifications regardless of department, including staff without a department or with an inactive department. The author is excluded from their own email. All broker staff can view/reply across the shared inbox; dashboard counts match. Broker replies also notify the conversation's original cedant. Other cedants cannot view or receive another user's conversation. Delivery-time checks revalidate approval, verification and broker role. Department/topic labels remain organisational and changing a label no longer resends the conversation. No migration is required; existing conversations use this policy immediately. Previously queued notifications are subject to the new eligibility checks but do not gain recipients that were never originally queued.

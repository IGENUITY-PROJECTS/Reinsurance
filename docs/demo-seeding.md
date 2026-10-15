# Demo and production seeding

Run the pending migrations before either seeder. Do not use `migrate:fresh` on a database whose records you need.

## Production

```powershell
php artisan migrate --force
php artisan db:seed --class=ProductionRolesSeeder --force
```

`DatabaseSeeder` uses the same production entry point. `RolesSeeder` defines the roles and permissions; `DepartmentsSeeder` creates one default General department and initial topic routing. No users, passwords or sample business records are created. Existing configured departments, routing and custom permission grants are preserved.

For the first real superadmin, use `php artisan portal:superadmin`. It prompts for a strong password without displaying it, creates an approved account and queues verification to the supplied real email.

## Local demo

```powershell
php artisan migrate
php artisan db:seed --class=DemoSeeder
```

Only use this against a local/testing demo database. The seeder creates sample companies, covers, reinsurers, claims, adjustment submissions and documents, statement records and feedback conversations. It also creates General, Demo Claims and Demo Finance teams, broker staff, a same-company colleague and pending/unverified account examples.

New demo passwords are `password`; existing passwords are preserved. Rerunning restores the reserved demonstration account states and refreshes example business data, while preserving existing user replies. Documents are harmless private text files under `demo/reinsurance`. Demo seeding does not send notifications.

See [Portal access, departments and feedback](portal-access-and-departments.md#demo) for the full account list, access states and current workflow. The demo company codes remain `DEMO-C001` and `DEMO-C002`; public registration still requires verification, broker approval and a free company user place. Reinsurer codes cannot be used for cedant registration.

System session/cache/job tables and future, unimplemented statement mirrors are not populated with fake operational data. Profit commission interfaces remain disabled.

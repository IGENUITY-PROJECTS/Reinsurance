# Demo and production seeding

Run migrations yourself before seeding. Do not use migrate:fresh against a database containing records you need.

## Production roles only

```powershell
php artisan db:seed --class=ProductionRolesSeeder
```

The default `php artisan db:seed` also runs only this roles/permissions seeder.
It creates no users, passwords, or business data. Existing custom role permissions are preserved.
Create production administrators through your authorised account-management process.

## Local demo

```powershell
php artisan migrate --pretend
php artisan migrate
php artisan db:seed --class=DemoSeeder
```

DemoSeeder runs only when APP_ENV is local or testing. Never point it at a real production database.
It uses reserved DEMO- codes and can be rerun without adding duplicate fixture records.
It refreshes its own example business data. Existing account passwords are preserved.

New example accounts all use password `password`:

| Email | Role | Company code |
| --- | --- | --- |
| superadmin@example.com | super-admin | none |
| admin@example.com | admin | none |
| client@example.com | client (cedant) | DEMO-C001 |
| client2@example.com | client (cedant) | DEMO-C002 |

The fixture includes 2 cedants, 3 reinsurers, 4 covers, 12 cover reinsurer rows,
6 portal claim submissions, 2 mirrored RBS claims, claim documents/status history,
3 premium adjustment submissions with documents, 13 feedback messages on claims and adjustments, 3 mirrored adjustment rows, 6 premium statement rows and
6 claim-payable statement rows. Source status codes marked DEMO are illustrative, not confirmed RBS codes.
Statement rows exercise unpaid, part-paid and paid balances in USD and ZMW.
Example exchange rates are fictional.

Documents are harmless text files under the private local disk at demo/reinsurance/.
System tables such as sessions, jobs, password resets and cache are not seeded.

## Registration examples

Use a new email and company code DEMO-C001 or DEMO-C002 to register.
Missing/unknown codes and reinsurer codes such as DEMO-R001 are rejected.
Public registration always assigns client; submitted role and company_id fields are ignored.
Users still use the existing company_id relationship, resolved server-side from the company code.
The meaning of MActive is not confirmed, so registration does not interpret that field.

A company-code check confirms the company exists, not that the person is its employee.
Broker approval/invitations and email verification are separate access controls not added by this change.

## Portal workflows

Both broker and cedant pages read database records. Cedants see only records belonging to their linked company, including private documents. Brokers can view submissions and documents. Claim tracking displays the linked mirrored RBS MStatusDesc, falling back to MStatusCode. Portal status labels, filters, history, review controls and the review route are commented out for now; stored columns and the review handler remain intact. No automatic RBS claim matching has been added. Cedants and brokers can exchange messages on claim and premium-adjustment submissions through Help & Feedback. Both sides can upload follow-up documents. Review status is separate from official RBS status. Draft submissions cannot be reviewed.

Premium adjustments use CoverNo, with no user-facing or generated adjustment reference. A private internal submission token still identifies files and conversations. Multiple submissions and mirrored item rows may share a cover. The mirrored table preserves all 34 supplied REPremiumAdjustment fields. Source identity must be verified before configuring a sync upsert; DocumentNo or CoverNo alone must not be assumed unique. Cedant access to mirrored adjustments is checked through the owning cover; records whose covers have not synced stay hidden from cedants.

Profit Commissions and static preview routes are disabled. Help & Feedback is available from both dashboards and menus. The broker page lists feedback in a paginated table; View opens the submission conversation for reading and replying. Messages are saved in submission_feedback and paginated on submission details and the feedback list. Cedant access follows company ownership; broker replies require the admin or super-admin role. DemoSeeder adds 13 sample feedback messages, with cedant questions and broker replies. Re-running it preserves real portal messages and avoids duplicate examples. DemoSeeder silently calls the shared RolesSeeder, so its console output focuses on demo data; ProductionRolesSeeder is a production-safe wrapper for the same role definitions and does not create business examples. The claim matcher remains removed. Linked demo claims are fixtures, not an automatic matching service.

## Applying the October changes

The original premium_adjustment_submissions migration now creates the table without adjustment_reference or its company/reference unique index. The separate column-removal migration has been deleted. The internal submission_reference remains for documents and conversations, while CoverNo identifies the cover.

For this test database, roll back the original table migration before applying the edited definition. After rollback, run `php artisan migrate --pretend`, review it, then `php artisan migrate`. The premium_adjustments migration creates the separate RBS mirror table. Re-run DemoSeeder only if you want local sample records again. Editing an already-applied migration does not alter an existing database table.

## Company branding

Set COMPANY_NAME and COMPANY_TAGLINE in .env to reuse the portal for another company. Current example: COMPANY_NAME="Afro-Asian". The public header, browser title and shared logos use this setting. Run `php artisan config:clear` after changing it.

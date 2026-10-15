# Portal access, departments and feedback

## Apply the changes

No migrations have been run against the real database by the assistant. Run the following from the project directory before opening the updated portal:

```powershell
php artisan migrate
```

New migrations, in order:

1. `2026_10_14_090000_create_departments_and_feedback_topic_routes.php`: departments and per-topic routing.
2. `2026_10_14_090001_add_portal_access_controls.php`: user approval/expiry/audit fields, department assignment and company user limits.
3. `2026_10_14_090002_add_feedback_department_and_statement_context.php`: conversation department, topic, assignment flag and statement context.

The existing email verification column and many-users-to-one-company association are reused. No existing company migration is rewritten. Account roles remain admin, client (cedant) and super-admin.

## Production

```powershell
php artisan migrate --force
php artisan db:seed --class=ProductionRolesSeeder --force
```

`DatabaseSeeder` defaults to `ProductionRolesSeeder`. This invokes `RolesSeeder` and `DepartmentsSeeder`: only roles, permission assignments, one General department and the four topic mappings to General. There are no seeded users, passwords or business examples. Rerunning production seeding preserves configured topic routes and departments. Existing feedback without a department is assigned to General during this upgrade.

Create the first superadmin separately, using hidden password prompts (no default account/password):

```powershell
php artisan portal:superadmin
```

The account is approved but must verify its real email. This command never promotes an existing account. It requires production roles to have been seeded. Existing superadmin/admin accounts retain approval during migration, but email verification is still required. Existing cedants become pending and must be verified and reviewed; if an existing company has more than five users, superadmin can raise its limit before approvals.

## Demo

```powershell
php artisan db:seed --class=DemoSeeder
```

DemoSeeder is limited to local/testing environments. It supplies roles, departments, broker staff, sample companies/records/documents, general and statement conversations, a same-company colleague and account approval examples. New sample accounts use `password`; existing passwords are preserved. Repeating the seeder intentionally restores the demonstration verification/access states for the reserved demo accounts. It does not send email.

| Account | State / purpose |
|---|---|
| superadmin@example.com | Verified, approved superadmin |
| admin@example.com | Verified, approved General broker |
| claims.admin@example.com | Verified, approved Demo Claims broker |
| finance.admin@example.com | Verified, approved Demo Finance broker |
| client@example.com | Verified, approved cedant, DEMO-C001 |
| client2@example.com | Verified, approved cedant, DEMO-C002 |
| colleague@example.com | Second verified, approved user for DEMO-C001 |
| pending@example.com | Email verified; awaiting broker approval |
| unverified@example.com | Awaiting email verification and approval |

Only use these accounts on a demo database. Profit commission interfaces and future statement tables are not enabled by this change.

## Verification and approval

Public registration requires an existing cedant company code, unique email and the configured strict password. Roles/company IDs/approval cannot be supplied by public input. New registrations reserve a place for seven days and receive a queued, signed email verification link. Resending is available before approval, rate-limited by Fortify. Public registration is throttled to three attempts per minute and ten per hour per IP. Email verification proves control of the mailbox, not company membership.

Unverified users are redirected to verification. Verified pending users see the waiting page, without access to data, exports, uploads or feedback. Brokers confirm membership with a trusted company contact, then approve/reject in User Management. Rejected and suspended users cannot enter the portal. Approval sends an account update email. Expired pending users can renew a reservation after verification if capacity is available. Broker approval checks capacity again, even for expired reservations.

Every company defaults to five approved or live pending accounts. Expired/rejected/suspended accounts release their place. Company row locks serialize registration, approval and admin-created accounts/moves; no cleanup scheduler is needed to release expired reservations. Pending places can still be occupied by unwanted registrations; brokers can reject them or superadmin can adjust the limit. User approval is the security boundary for company data.

Admin-created cedants are approved by the creating broker but must verify email. Only superadmin creates/edits/deletes broker staff or superadmins, assigns departments, manages roles/routing/limits or suspends/reactivates staff. Admins cannot see protected staff in user management or escalate permissions. Broker staff require a department. Changing any user's email clears verification. Superadmin cannot suspend their own account. Livewire requests persist verification, approval and role middleware checks.

## Departments and feedback

Superadmin opens **Departments** to create departments and assign feedback topics. **Users** has a searchable, paginated accounts table and an Add/Edit dialog for staff or cedants. **Users → Company access limits** manages company places separately. Broker admins see cedants only; superadmin identities, roles and actions remain hidden and protected server-side. General is the default and cannot be deactivated. Active departments are available for assignments. Existing staff without a department should be assigned by superadmin before handling feedback.

Topics: General enquiry, Claims / claims statement, Premiums / premium statement, Premium adjustments. Topic routing chooses the configured active department, falling back to General. Existing conversations keep their department until superadmin reassigns them. Department deactivation blocks ordinary staff from its conversations; superadmin retains access.

Cedants see only their own conversations, not colleagues' conversations. Broker staff see only their department's conversations. Superadmin sees all and can reassign. A statement question can include the selected document (validated against the cedant company and correct table), period, currency and search filters. Document numbers are stored as context snapshots; the accounting data is not duplicated. Statements have general and per-document **Ask a question** links. The document picker searches up to 30 results per request, including historical documents.

Emails notify verified, approved staff in the assigned active department. Cedant follow-ups go to that same team; broker replies go only to the verified, approved conversation creator, with a company check. When no eligible staff exist, the conversation is flagged for attention and eligible superadmins receive the notification. No conversation is dropped. Delivery checks authorization again when queued emails are processed, so department reassignments or revoked accounts suppress stale notifications. Email bodies contain a subject/topic and an authenticated conversation link rather than the full discussion or statement data.

## Mail and queue

The current local configuration selects SMTP and a database queue; this change does not alter credentials. Configure a working SMTP sender and the correct HTTPS APP_URL in production. Signed email verification links depend on APP_URL. A queue worker is required for verification, account updates and feedback notifications:

```powershell
php artisan queue:work --tries=3
```

Restart existing workers after deploying new code (`php artisan queue:restart`), and monitor failed jobs (`php artisan queue:failed`). Successful enqueueing is not proof of SMTP delivery. No live email has been sent during the assistant's fake-notification verification. No new packages are required.

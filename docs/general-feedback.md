# General Help & Feedback

This replaces the rolled-back submission_feedback migration and model. No general feedback field refers to a claim or premium-adjustment submission.

feedback_conversations stores the subject, creator and optional company. feedback_messages stores the original message and each reply with its author. Cedants can ask any question without selecting a submission, even if their account has no company assigned. Only the sender can view a cedant conversation; broker admins can view and reply. Author/ownership values are assigned server-side. Dashboard-only permission allows viewing but not broker replies.

Lists paginate 15 conversations; detail pages paginate 20 messages. Messages are escaped on display, limited to 5,000 characters and rate limited. Creation and replies use transactions. DemoSeeder adds 3 general conversations and 6 messages and preserves user replies on reruns. Production seeding remains roles only.

The new migration has not been applied to your real database. Review and run:

```powershell
php artisan migrate --pretend
php artisan migrate
```

Submission-specific feedback relationships, forms, routes and demo messages have been removed. Claim and premium-adjustment documents and RBS status displays remain unchanged.

## Email notifications

A new cedant conversation or follow-up queues an individual email to each admin-role broker account with a valid email. A broker/super-admin reply queues an email only to the conversation creator. Super-admin-only accounts do not receive every broker notification unless they also hold admin. Notification recipients never see each other's email addresses.

Emails identify the subject and provide a sign-in link; the actual message is read inside the portal. Recipient access is checked again when the queued email is sent. Emails are queued only after messages commit. Delivery failures leave messages intact. Jobs retry three times; failed jobs need monitoring and retry through the normal queue tools.

The current local mailer is log and does not deliver external email. Configure MAIL_MAILER=smtp, SMTP host/port/credentials and MAIL_FROM_ADDRESS in .env. APP_URL must be the correct portal URL for email links. Keep credentials out of Git. After changing configuration, run php artisan config:clear. Run a queue worker (or your existing composer dev worker):

```powershell
php artisan queue:work --tries=3
```

For deployment, run the worker under a process manager and restart it after releases. No mail was sent externally during verification.

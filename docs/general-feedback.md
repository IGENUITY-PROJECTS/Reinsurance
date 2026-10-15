# Help & Feedback

The current implementation supports general enquiries and optional statement context, routed to departments. Conversations and replies are stored in `feedback_conversations` and `feedback_messages`.

Cedants see only their own conversations for their current company. Verified, approved broker staff handle their own department; superadmin can view and reassign every conversation. Lists paginate 15 conversations and messages paginate 20 per page.

Emails are queued after a message commits and recipients are rechecked at delivery. No eligible department staff means the conversation is flagged and eligible superadmins are notified. Broker replies go to the conversation creator.

See [Portal access, departments and feedback](portal-access-and-departments.md) for migrations, seeders, account approval, department setup, statement questions and mail/queue instructions.

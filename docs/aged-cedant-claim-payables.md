# Aged cedant claim payables

Source: `APCreditorsClaimsAGED`. Portal: `aged_cedant_claim_payables`. Model: `AgedCedantClaimPayable`.

All 36 source columns retain their names and string lengths, including `PostingSeq`. Source fields are nullable pending source validation. Monetary values and rates use signed decimal(24,8); source datetimes remain datetimes. Document numbers remain strings to preserve leading zeroes.

Local `id`, timestamps and `last_synced_at` support portal storage. Company relationships use `CedantCode` to `CmpCode`, without a database foreign key so imports can precede companies.

DocumentNo and PostingSeq repeat in the supplied source sample. Lookup indexes are not unique constraints or an implemented synchronization key. Confirm a stable source key before implementing upserts. Do not collapse separate payment rows or interpret ClaimReference as ClaimNo without source confirmation.

DemoSeeder includes six reserved examples, including repeated document numbers, negative payments and a settled balance. Production seeders add no financial examples.

This migration does not add a report or merge aged rows with current statements. Confirm source accounting semantics before combining balances or calculating aging buckets.

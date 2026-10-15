# Reinsurer premium payables mirror

RBS source: `APCreditorsPremiums`. Portal table: `reinsurer_premium_payables`. Model: `ReinsurerPremiumPayable`. These are premiums the broker owes the reinsurer; from the reinsurer's perspective they are premium receivables.

All 30 supplied source fields retain their names and string lengths, including `ReinsureCode`. Signed `DECIMAL(24,8)` is used for amounts, rates and percentages to avoid introducing binary floating-point arithmetic in the portal; preserve source signs and do not recalculate balances. Null source values remain nullable. Datetime fields remain datetime. Portal metadata: `id`, `last_synced_at`, `created_at`, `updated_at`.

`reinsurer()` links `ReinsureCode` to `companies.CmpCode`; `cedant()` links `CedantCode` to that same company code. The Company model offers `premiumPayablesAsReinsurer()` and `premiumPayablesAsCedant()`. No foreign keys to mirror tables are imposed, so rows can sync before related companies. These relationships identify companies; they do not grant user access.

`DetailID`, `DocumentNo`, reinsurer/date, cedant/date, cedant document and risk reference have non-unique lookup indexes. The local `id` identifies a portal row only. A stable source sync key must be verified before configuring upserts. DocumentNo must not be assumed unique or used to collapse reinsurer/item rows.

Read-only source checks:

```sql
SELECT DetailID, COUNT(*) AS RecordCount
FROM APCreditorsPremiums
GROUP BY DetailID
HAVING COUNT(*) > 1;

SELECT DetailID, DocumentNo
FROM APCreditorsPremiums
WHERE DetailID IS NULL;

SELECT DetailID, DocumentNo
FROM APCreditorsPremiums
WHERE DocumentNo IS NULL OR LTRIM(RTRIM(DocumentNo)) = '';
```

Apply the new migration yourself with `php artisan migrate`. On a demo database only, rerun `php artisan db:seed --class=DemoSeeder` for six reserved demo rows covering unpaid, partly paid, fully settled and negative balances. Production seeding does not insert any mirror examples. Existing demo passwords remain preserved.

This change creates the mirror/model and demo fixtures only; it does not enable reinsurer registration, a new report screen or synchronisation. When this report is enabled, use the existing rules: exclude `PendingAmount = 0`, keep company ownership checks, group totals by currency, and hide reversal fields in client-facing exports. A risk reference is not a verified CoverNo link, and CedantDoc is not assumed to uniquely identify another mirror row.

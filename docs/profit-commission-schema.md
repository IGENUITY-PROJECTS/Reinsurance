# Profit commission mirror tables

Schema and models only. The portal module remains disabled. No forms, routes, demo fixtures or sync logic were added, and migrations have not been applied to your database.

| Source | Portal table | Model |
| --- | --- | --- |
| RERskCedHeader | profit_commissions | ProfitCommission |
| Detail schema supplied alongside REPCRiskNotesPYears (exact source name to confirm) | profit_commission_details | ProfitCommissionDetail |

All 108 header and 10 detail source columns retain their exact names and supplied string lengths. Dates supplied as VARCHAR remain strings; InstallmentDate remains an integer. Source flags remain integers. Source columns are nullable. Local id, last_synced_at and Laravel timestamps are added as metadata.

Financial amounts and rates use signed DECIMAL(24,8) and decimal:8 model casts. This supports negative credit values and avoids additional binary floating-point rounding. Original source FLOAT values still require an agreed rounding/range policy for sync. The models do not recalculate amounts.

Relationships use DocumentNo -> DocNo, CVNo -> CoverNo and company codes -> CmpCode. Detail CompanyCode can identify a cedant or reinsurer. CompanyOption is preserved without assuming permanent meanings from the sample. Mirror relationships have no database foreign keys, allowing any sync order.

DocNo, RecNo and DetailID have ordinary indexes. Verify source identity, duplicates and missing keys before introducing unique constraints or configuring sync upserts. Relationships by DocNo require it to identify one header reliably. Confirm whether header document numbers ever exceed the supplied detail DocumentNo length of 50.

RERskCedHeader contains multiple transaction types. The supplied CRN002700 has RskAcctTypeCode TYP68 / Profit Commission. CRN002699 is a minimum/deposit premium, so a profit-commission-only import should exclude it. Confirm the filter with RBS before implementing sync. No universal status mapping is assumed for Posted/Committed flags.

Review and apply the existing migrations yourself:

```powershell
php artisan migrate --pretend
php artisan migrate
```

Both models and migration files already exist; no make:model command is needed.

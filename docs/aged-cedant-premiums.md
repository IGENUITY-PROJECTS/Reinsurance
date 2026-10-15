# Aged cedant premium mirror

Source: `ARDebtorsPremiumsAGED`. Portal table: `aged_cedant_premiums`. Model: `AgedCedantPremium`.

All 33 supplied source columns retain their names and string lengths. Amounts, rates, percentages and both supplied FLOAT revaluation fields use signed DECIMAL(24,8). `Revalued2021` is not assumed to be a boolean. DetailID, document date number, reversal flag, PostingSEQ and Picked preserve integer values. Portal metadata adds id, last_synced_at and timestamps. Nullable columns and ordinary lookup indexes follow the current agreement; no source unique/upsert constraint is assumed.

The source samples include receipts, journals, negative balances, blank risk fields and account code `30010`. Preserve these rows and their source signs; do not convert missing company names into invented companies or enforce a company foreign key. The `company()` relationship uses CedantCode to companies.CmpCode; inverse Company.agedPremiums() uses the same code. A missing company may resolve after a later company sync. Company resolution is not user authorization.

Keep this aged mirror separate from `cedant_premiums`. Do not simply union their amounts into one statement: overlap and accounting meaning must first be confirmed against RBS. No ageing buckets or historical closing balances have been invented. Source identifiers, the role of PostingSEQ and snapshot/as-of semantics will be confirmed before implementing synchronisation or an aged report.

Run `php artisan migrate` yourself. DemoSeeder supplies six reserved historical receipt/journal rows across two cedants/currencies, with signed balances and a fully settled example. Production seeding creates no aged financial data. The existing statement screens do not read this mirror yet. When aged reports are implemented, keep agreed company access rules, exclude PendingAmount=0 and omit reversal fields from the presentation.

Only ARDebtorsPremiumsAGED was defined in this request. APCreditorsClaimsAGED will be created after its own columns/types are supplied; its schema is not assumed identical.

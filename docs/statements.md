# Statements

Premiums use `cedant_premiums`; claims receivable use `cedant_claim_payables`. No report migration or duplicated accounting table is required.

Clients are restricted to their associated company code server-side. Broker admins and superadmins can select any cedant or all cedants. Delegated accounts must have `view reports` as well as access to the admin area.

The preview has ten documents per page. Start/end dates filter `DocumentDate`, including the full final day. Both dates are required for downloads. Search, company, currency and date filters are shared by the preview, Excel and PDF. Downloads include all matching documents, never just the current page. Empty reports cannot be downloaded.

The Excel workbook contains Summary and Documents sheets, numeric amount/date cells, fixed text references, frozen headings and column filters. Source text is explicitly written as text to prevent formula execution. Excel query exports read documents in chunks. PDF uses landscape A4, repeated headings, company branding and page numbers. A configured local PNG/JPEG logo in the public directory is embedded; remote logos are not fetched.

Amounts, settled amounts and outstanding balances come from the mirrored fields. Aggregate totals are separate for each currency and include reversed records with their original source signs. Reversals are labelled; no unconfirmed reversal exclusion or sign adjustment is applied. Null amounts remain blank in document rows. Balances are current synced balances for the selected documents, not reconstructed historical closing balances. Opening balances and historical as-of reports require settlement history.

Synchronous PDF exports are limited to 5,000 documents (`config/reports.php`) to bound memory use. Larger requests return a message recommending a narrower period or Excel, without truncation. This threshold should be tuned to production resources. Export routes are rate-limited to ten requests per minute. No new migrations, production seeding or external document storage are required.

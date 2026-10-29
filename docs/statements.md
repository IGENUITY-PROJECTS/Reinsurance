# Statements

Premiums use `cedant_premiums`; claims receivable use `cedant_claim_payables`. No report migration or duplicated accounting table is required.

Clients are restricted to their associated company code server-side. Broker admins and superadmins can select any cedant or all cedants. Delegated accounts must have `view reports` as well as access to the admin area.

The preview has ten documents per page. Start/end dates filter `DocumentDate`, including the full final day. Both dates are required for downloads. Search, company, currency and date filters are shared by the preview, Excel and PDF. Downloads include all matching documents, never just the current page. Empty reports cannot be downloaded.

The Excel workbook contains Summary and Documents sheets, numeric amount/date cells, fixed text references, frozen headings and column filters. Source text is explicitly written as text to prevent formula execution. Excel query exports read documents in chunks. PDF uses landscape A4, repeated headings, company branding and page numbers. A configured local PNG/JPEG logo in the public directory is embedded; remote logos are not fetched.

Amounts, settled amounts and outstanding balances come from the mirrored fields. Aggregate totals are separate for each currency and include reversed records with their original source signs. Reversal fields are stored but hidden from the report; no unconfirmed reversal exclusion or sign adjustment is applied. Null amounts remain blank in document rows. Balances are current synced balances for the selected documents, not reconstructed historical closing balances. Opening balances and historical as-of reports require settlement history.

Synchronous PDF exports are limited to 5,000 documents (`config/reports.php`) to bound memory use. Larger requests return a message recommending a narrower period or Excel, without truncation. This threshold should be tuned to production resources. Export routes are rate-limited to ten requests per minute. No new migrations, production seeding or external document storage are required.

Fully settled documents (`PendingAmount = 0`) are excluded from previews, totals, Excel and PDF. Unpaid records (`SettledAmount = 0`) remain visible when their pending balance is nonzero. Negative pending balances remain visible. Null pending balances are treated as unknown and retained, not assumed fully settled.

## Current report presentation and reconciliation

Normal statements exclude PendingAmount = 0, retain negative balances and retain unknown/null balances. The screen, totals and exports use the same query. There is no document-number deduplication or cover/company join that drops statement documents. Date, search, currency and company filters remain effective; a missing DocumentDate cannot match a date range. The preview is paginated; downloads include all matching pages. A broker data-check panel reports source counts and missing dates/balances before date/search filters. These checks do not reconcile against the separate RBS database.

Outstanding Functional Amount is calculated as PendingAmount multiplied by ExchangeRate, using the direction in the supplied RBS sample. It is separate from the original FunctionalAmount; null inputs remain unknown. The assumption should be verified against finance's rate convention. Values are aggregated in the database at decimal precision.

The report name uses REPORT_COMPANY_NAME (falling back to COMPANY_NAME or Afro-Asian Reinsurance Brokerage). Broker company filters include companies with no report rows; clients remain company-scoped.

Brought-forward balances use current PendingAmount on previous-calendar-year documents. The balance summary separately shows those balances, outstanding on current-year documents from January 1 through the selected end date (capped at December 31), and their sum. These are current synced balances, not historical opening/closing balances. Summary balances ignore the document search, as labelled, but honor company and currency. A statement spanning years keeps its normal selected-period rows; the summary explicitly refers to the start year.

Ageing groups the same mirrors by company and currency as of the selected date, using DocumentDate and 0–30/31–60/61–90/91–180/181–365/over-365-day buckets. Future, undated, settled and unknown-balance records cannot contribute to buckets. No separate brought-forward amount is added to ageing, which already includes older documents.

## Row-count comparison update

Normal statements now include fully settled rows (PendingAmount = 0), per the latest request to compare the 81 visible rows with 126 source rows. Ageing remains outstanding-only. Date, company, currency and search filters still apply, and exports still include all matching pages. This supersedes earlier outstanding-only descriptions for normal statements.

## Risk-Note Rules — 29 October 2026

The latest rules supersede earlier all-document/settled-row settings. Both premium and claims payable statements, ageing, totals, brought-forward balances and current-year summaries require DocumentTypeCode exactly D0C1 (zero, not letter O), PendingAmount strictly greater than 0.05, Reversed exactly 0, and a non-null DocumentDate through the selected As At day. Null document types, balances or reversal flags do not qualify. Currency/company/search filters and normal-statement From/To filters remain available. Ageing ignores From/To and includes eligible older documents through As At. The full As At day is included even for timestamps.

Normal statements also show Age Days, Age Bucket and Running Balance. Calendar-day age uses DocumentDate. Running balances sum eligible displayed documents separately per CedantCode/CurrencyCode in DocumentDate, DocumentNo, local-id order; local id breaks ties without dropping duplicate document numbers. The window is evaluated before pagination and export chunking. Running balance does not separately add the brought-forward summary, avoiding double counting. Existing balances remain current synced values rather than reconstructed historical payment balances.

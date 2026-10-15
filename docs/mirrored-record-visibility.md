# Claims and premium adjustment visibility

Module lists include historical mirrored records and unmatched portal submissions, with no default date cutoff. Explicit date filters apply to the source registration date for official claims, the document date for adjustments, and the submission date for portal-only entries.

Official claims match portal submissions by nonblank, trimmed OrigClaimNo and CedCode/company_code. Cached ClaimNo is not required and no fields are written during reads. Each official claim remains visible. Its detail page includes documents from the matching portal submission. If multiple official claims share a reference, all remain visible; a portal detail does not guess one official claim or status.

Adjustment ownership uses the related cover's CusCode when a cover exists; otherwise CedantAccountNo is used. This rule is shared by list and detail authorization. A conflicting cedant account cannot override existing cover ownership. Every mirrored adjustment item is retained, alongside portal schedules, because cover alone does not identify a specific submitted schedule.

Dashboard counts use the same combined lists. Recent submissions remains the portal submission feed. Clients are always restricted to their associated company; broker lists include all companies. Pagination remains ten items per page. No migration or data rewrite is needed. Data must already be synchronised into the portal mirror tables to be displayed.

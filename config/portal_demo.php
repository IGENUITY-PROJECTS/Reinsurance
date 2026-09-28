<?php

// Fictional preview records only. No business tables or uploads are written.
return [
    'cedant' => 'Savanna Assurance',
    'submissions' => [
        ['id' => 'CLM-DEMO-001', 'company' => 'Savanna Assurance', 'type' => 'Claim', 'title' => 'Warehouse water damage', 'policy' => 'POL-DEMO-001', 'date' => '2026-09-18', 'status' => 'Under review', 'documents' => ['Claim form.pdf', 'Loss assessment.pdf'], 'history' => [['2026-09-18', 'Submitted', 'Cedant', 'Claim form and assessment submitted.'], ['2026-09-21', 'Under review', 'Broker', 'Documents received. The assessment is being reviewed.']]],
        ['id' => 'CLM-DEMO-002', 'company' => 'Savanna Assurance', 'type' => 'Claim', 'title' => 'Marine cargo damage', 'policy' => 'POL-DEMO-002', 'date' => '2026-09-20', 'status' => 'More information required', 'documents' => ['Marine claim form.pdf'], 'history' => [['2026-09-20', 'Submitted', 'Cedant', 'Cargo damage reported.'], ['2026-09-22', 'More information required', 'Broker', 'Please provide the survey report and bill of lading.']]],
        ['id' => 'CLM-DEMO-003', 'company' => 'Highland Insurance', 'type' => 'Claim', 'title' => 'Equipment breakdown', 'policy' => 'POL-DEMO-004', 'date' => '2026-09-23', 'status' => 'Submitted', 'documents' => ['Equipment claim.pdf'], 'history' => [['2026-09-23', 'Submitted', 'Cedant', 'Completed claim form uploaded.']]],
        ['id' => 'PAD-DEMO-001', 'company' => 'Savanna Assurance', 'type' => 'Premium adjustment', 'title' => 'Property premium adjustment', 'policy' => 'POL-DEMO-001', 'date' => '2026-09-19', 'status' => 'Under review', 'documents' => ['Premium adjustment.xlsx', 'Supporting schedule.pdf'], 'history' => [['2026-09-19', 'Submitted', 'Cedant', 'Revised premium schedule submitted.'], ['2026-09-21', 'Under review', 'Broker', 'Checking the revised exposure figures.']]],
        ['id' => 'PAD-DEMO-002', 'company' => 'Highland Insurance', 'type' => 'Premium adjustment', 'title' => 'Engineering adjustment', 'policy' => 'POL-DEMO-004', 'date' => '2026-09-22', 'status' => 'Submitted', 'documents' => ['Adjustment schedule.xlsx'], 'history' => [['2026-09-22', 'Submitted', 'Cedant', 'Adjustment schedule ready for review.']]],
        ['id' => 'PCM-DEMO-001', 'company' => 'Savanna Assurance', 'type' => 'Profit commission', 'title' => '2025 treaty profit commission', 'policy' => 'TRT-DEMO-001', 'date' => '2026-09-15', 'status' => 'Review completed', 'documents' => ['Profit commission calculation.xlsx'], 'history' => [['2026-09-15', 'Submitted', 'Cedant', 'Calculation submitted for review.'], ['2026-09-22', 'Review completed', 'Broker', 'Document review completed. This does not confirm payment.']]],
    ],
    'feedback' => [
        ['id' => 'HELP-DEMO-001', 'company' => 'Savanna Assurance', 'subject' => 'Statement clarification', 'message' => 'Please clarify the September marine premium entry.', 'reply' => 'The entry relates to POL-DEMO-002. Please refer to debit note DN-DEMO-102.', 'status' => 'Answered'],
        ['id' => 'HELP-DEMO-002', 'company' => 'Highland Insurance', 'subject' => 'Document format', 'message' => 'Can we provide the adjustment schedule as a spreadsheet?', 'reply' => '', 'status' => 'Awaiting reply'],
    ],
];

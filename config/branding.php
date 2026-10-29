<?php

return [
    'company_name' => env('COMPANY_NAME', 'Afro-Asian Reinsurance Brokers Tanzania Ltd'),
    'report_name' => env('REPORT_COMPANY_NAME', env('COMPANY_NAME', 'Afro-Asian Reinsurance Brokers Tanzania Ltd')),
    'tagline' => env('COMPANY_TAGLINE', 'Your covers, statements and claims'),
    // Optional URL or public path, e.g. /images/company-logo.png.
    'logo_url' => env('COMPANY_LOGO_URL'),
];

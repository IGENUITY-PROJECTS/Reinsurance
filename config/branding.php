<?php

return [
    'company_name' => env('COMPANY_NAME', env('APP_NAME', 'Client Portal')),
    'report_name' => env('REPORT_COMPANY_NAME', env('COMPANY_NAME', 'Afro-Asian Reinsurance Brokerage')),
    'tagline' => env('COMPANY_TAGLINE', 'Your covers, statements and claims'),
    // Optional URL or public path, e.g. /images/company-logo.png.
    'logo_url' => env('COMPANY_LOGO_URL'),
];

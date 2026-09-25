<?php

return [

    'public_base_url' => rtrim(env('PUBLIC_BASE_URL', env('APP_URL', 'http://localhost')), '/'),

    'tracking_secret' => env('TRACKING_SECRET', ''),

    'admin_api_key' => env('ADMIN_API_KEY', ''),

    'allowed_pipelines' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ALLOWED_PIPELINES', ''))
    ))),

    'hidden_stages' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('HIDDEN_STAGES', ''))
    ))),

    'show_delay_reason' => filter_var(env('SHOW_DELAY_REASON', true), FILTER_VALIDATE_BOOLEAN),

    'cache_ttl_seconds' => (int) env('CACHE_TTL_SECONDS', 60),

    'company' => [
        'name' => env('COMPANY_NAME', 'The First Good Man Group'),
        'phone' => env('CONTACT_PHONE', ''),
        'line_url' => env('CONTACT_LINE_URL', ''),
    ],

];

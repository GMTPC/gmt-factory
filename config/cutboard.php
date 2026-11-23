<?php

return [
    'connection' => env('CUTBOARD_DB_CONNECTION', 'bplus'),
    'source_connection' => env('CUTBOARD_SOURCE_CONNECTION', 'legacy'),
    'legacy_bridge' => env('CUTBOARD_LEGACY_BRIDGE', true),
    'manager_usernames' => array_values(array_filter(array_map('strtolower', explode(',', (string) env('CUTBOARD_MANAGER_USERNAMES', 'manager'))))),
    'legacy_ziptape' => env('CUTBOARD_LEGACY_ZIPTAPE', '0.01'),
    'min_year' => (int) env('CUTBOARD_MIN_YEAR', 2025),
    'grades' => [
        'product_type_position' => 2,
        'b_product_type' => '2',
        'c_brand_code' => '09',
    ],
    'statuses' => [
        'job' => [
            'draft',
            'scanning',
            'qc_completed',
            'receiving',
            'received',
            'completed',
            'cancelled',
        ],
        'line' => [
            'pending',
            'passed',
            'rejected',
        ],
    ],
];

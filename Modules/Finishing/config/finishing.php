<?php

return [
    'timezone' => env('APP_TIMEZONE', 'Asia/Bangkok'),
    'shifts' => [
        'day' => ['starts_at' => '08:00', 'ends_at' => '17:00'],
        'night' => ['starts_at' => '17:00', 'ends_at' => '08:00'],
    ],
    'admin_usernames' => array_values(array_filter(array_map(
        'strtolower',
        explode(',', (string) env('FINISHING_ADMIN_USERNAMES', 'manager')),
    ))),
    'defect_alert_threshold' => (float) env('FINISHING_DEFECT_ALERT_THRESHOLD', 5),
];

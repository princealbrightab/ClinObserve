<?php

return [
    'enabled' => (bool) env('BROWSER_MAINTENANCE_ENABLED', false),
    'token' => env('BROWSER_MAINTENANCE_TOKEN', ''),
    'allow_demo_seed' => (bool) env('BROWSER_MAINTENANCE_ALLOW_DEMO_SEED', false),
    'initial_hod' => [
        'name' => env('INITIAL_HOD_NAME', ''),
        'email' => env('INITIAL_HOD_EMAIL', ''),
        'password' => env('INITIAL_HOD_PASSWORD', ''),
    ],
];

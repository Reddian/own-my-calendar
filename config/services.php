<?php

return [
    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY', ''),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
        'price_id' => env('STRIPE_PRICE_ID', ''),
    ],
];

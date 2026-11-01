<?php

return [
    // Secret API key dari Dashboard Xendit > Settings > API Keys. Mode test/live mengikuti jenis key.
    'secret_key' => env('XENDIT_SECRET_KEY'),
    // Verification token dari Dashboard Xendit > Settings > Webhooks.
    'callback_token' => env('XENDIT_CALLBACK_TOKEN'),
    'base_url' => env('XENDIT_BASE_URL', 'https://api.xendit.co'),
];

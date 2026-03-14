<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Driver de traduction (deepl | google)
    | DeepL : plus stable, moins de 500. Clé gratuite : https://www.deepl.com/pro-api
    |--------------------------------------------------------------------------
    */
    'driver' => env('TRANSLATION_DRIVER', 'deepl'),

    'source_language' => 'fr',

    'target_languages' => ['en', 'es', 'ar'],

    'deepl' => [
        'api_key' => env('DEEPL_API_KEY'),
        'base_url' => env('DEEPL_API_URL', 'https://api-free.deepl.com'),
    ],

    'timeout_seconds' => (int) env('TRANSLATION_TIMEOUT', 5),
];

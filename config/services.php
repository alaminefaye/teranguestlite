<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | Firebase (notifications push FCM)
    | Comme gestion-compagny : FIREBASE_CREDENTIALS_PATH = chemin absolu ou relatif à storage/
    | Ex. .env : FIREBASE_CREDENTIALS_PATH=app/firebase/teranguest-74262-bad96dcbc8cd.json
    | Ancienne variable FIREBASE_CREDENTIALS (nom de fichier) : storage/app/firebase/<fichier>
    */
    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'credentials' => env('FIREBASE_CREDENTIALS_PATH')
            ? (str_starts_with(env('FIREBASE_CREDENTIALS_PATH'), '/')
                ? env('FIREBASE_CREDENTIALS_PATH')
                : storage_path(env('FIREBASE_CREDENTIALS_PATH')))
            : (env('FIREBASE_CREDENTIALS') ? storage_path('app/firebase/' . basename(trim(env('FIREBASE_CREDENTIALS')))) : null),
    ],

];

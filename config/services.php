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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'invoice_printer_adapter' => [
        'app_download_url' => 'https://www.dropbox.com/scl/fi/ny84hfsi5k9rfy6zs8e1x/Invoice-Printer-Adapter.zip?rlkey=3yq0y8ay2hs8gxbvx16j3j8qu&dl=1',
    ],

    'websocket' => [
        'url' => env('WEBSOCKET_URL'),
        'http_url' => env('WEBSOCKET_HTTP_URL'),
        'app_source' => env('WEBSOCKET_APP_SOURCE'),
    ],
];

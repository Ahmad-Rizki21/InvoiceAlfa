<?php

return [
    'enabled' => env('HTTP_MESSAGE_CRYPTER_ENABLED', env('APP_ENV') === 'production'),
    'salt' => env('HTTP_MESSAGE_CRYPTER_SALT', 'secretessecretes'),
];

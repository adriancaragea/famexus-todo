<?php

return [
    // Public key of the gateway, used to verify the JWT on every incoming request.
    'jwt' => [
        'public_key' => env('JWT_PUBLIC_KEY_PATH', storage_path('keys/jwt-public.pem')),
        'issuer' => env('JWT_ISSUER', 'famexus-gateway'),
        'leeway' => (int) env('JWT_LEEWAY_SECONDS', 5),
    ],
];

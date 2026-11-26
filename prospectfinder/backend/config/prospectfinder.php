<?php

return [
    'campaigns' => [
        // MVP limit on businesses per campaign. Enforced in request validation.
        'max_target_count' => 10,
    ],

    'auth' => [
        // Login attempts allowed per minute per email + IP combination.
        'login_attempts_per_minute' => 5,
        'minimum_password_length' => 12,
    ],
];

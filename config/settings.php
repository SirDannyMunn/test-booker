<?php

$features = [
    'Access to the web app',
    'See live cancellations'
];

return [
    'plans' => [
        'free' => [
            'price' => 0,
            'pennies' => 0,
            'colour' => 'secondary',
            'name' => 'Freewheeler',
            'features' => [
            ],
        ],
        'basic' => [
            'price' => 19.99,
            'pennies' => 1999,
            'colour' => 'success',
            'name' => 'Fast', 
            'features'=> [
            ]
        ],
        'premium' => [
            'price' => 29.99,
            'pennies' => 2999,
            'colour' => 'success',
            'name' => 'Quickest!', 
            'features'=> [
                'Select any test from dashboard',
                'Test in 2 weeks or full refund',
            ]
        ]
    ]
];
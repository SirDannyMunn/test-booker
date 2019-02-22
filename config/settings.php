<?php

return [
    'plans' => [
        'free' => [
            'price' => 0,
            'pennies' => 0,
            // 'price' => 0,
            'name' => 'Just Checking :)'
        ],
        'basic' => [
            'price' => 19.99,
            'pennies' => 1999,
            // 'price' => 19.99,
             'name' => 'Eager', 
            'features'=> [
                '3 additional test centres',
                'searches every 9 minutes'
            ]
        ],
        'premium' => [
            'price' => 29.99,
            'pennies' => 2999,
            // 'price' => 29.99,
            'name' => 'Now Please!', 
            'features'=> [
                '5 additional test centres',
                'Searches every 5 minutes',
                'Get first priority'
            ]
        ]
    ]
];
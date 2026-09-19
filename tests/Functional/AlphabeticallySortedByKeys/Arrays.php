<?php

declare(strict_types=1);

$ignoredShortArray = [
    'choices' => [
        'Man' => 'male',
        'Woman' => 'female',
        'Other' => 'other',
    ],
];

$unsortedRegularArray = [
    'zebra' => true,
    'alpha' => true,
];

$sortedRegularArray = [
    'alpha' => true,
    'zebra' => true,
];

$unsortedOuterArray = [
    'zebra' => true,
    'choices' => [
        "Man" => 'male',
        "Woman" => 'female',
        "Other" => 'other',
    ],
    'alpha' => true,
];

$similarKey = [
    'other_choices' => [
        'zebra' => true,
        'alpha' => true,
    ],
];

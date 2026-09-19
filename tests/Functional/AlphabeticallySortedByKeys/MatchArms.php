<?php

declare(strict_types=1);

$mode = getenv('MODE') ?: 'choices';

$resolved = match ($mode) {
    'choices' => [
        'zebra' => true,
        'alpha' => true,
    ],
    default => [
        'zebra' => true,
        'alpha' => true,
    ],
};

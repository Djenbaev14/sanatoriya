<?php

use App\Filament\Resources\ActivityLogResource;

return [
    'resources' => [
        'label' => 'Activity Log',
        'plural_label' => 'Activity Logs',
        'navigation_group' => null,
        'navigation_icon' => 'heroicon-o-shield-check',
        'navigation_sort' => null,
        'navigation_count_badge' => false,
        'resource' => ActivityLogResource::class,
    ],
    'datetime_format' => 'd/m/Y H:i:s',
];

<?php

return [
    'status_map' => [
        'New' => 'processing',
        'ReadyToShip' => 'shipped',
        'PickedUp' => 'processing',
        'InTransit' => 'shipped',
        'OutForDelivery' => 'shipped',
        'Delivered' => 'completed',
        'Exception' => 'on_hold',
        'Canceled' => 'canceled',
        'CanceledByCarrier' => 'canceled',
        'PackageAccepted' => 'shipped',
        'Shipped' => 'shipped',
    ],
    'final_statuses' => ['completed', 'canceled', 'refunded'],
];

<?php

return [
    'admin.orders' => [
        'index' => 'order::permissions.index',
        'show' => 'order::permissions.show',
        'edit' => 'order::permissions.edit',
    ],
    'admin.cart_links' => [
        'create' => 'order::permissions.cart_links_create',
    ],
];

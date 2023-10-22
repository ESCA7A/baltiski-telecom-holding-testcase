<?php

return [
    [
        'name' => 'admin',
        'title' => 'Администратор',
        'permissions' => [
            'product.create',
            'product.read',
            'product.update',
            'product.delete',
        ],
    ],
    [
        'name' => 'client',
        'title' => 'Клиент',
        'permissions' => [
            'product.read',
        ],
    ],
];

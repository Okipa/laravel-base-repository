<?php

return [
    // repository file types
    'file_types' => [
        'images',
        'files',
    ],
    // users configuration
    'users'      => [
        'images'       => [
            'photo' => [
                'name'                  => 'user-photo',
                'authorized_extensions' => ['jpg', 'jpeg', 'png'],
                'available_sizes'       => [
                    'admin'   => [
                        'width'  => 40,
                        'height' => 40,
                    ],
                    'picture' => [
                        'width'  => 145,
                        'height' => 160,
                    ],
                    'zoom'    => [
                        'width'  => 260,
                        'height' => 300,
                    ],
                ],
            ],
        ],
        'files'        => [],
        'json_storage' => false,
        'storage_path' => 'app/users',
        'public_path'  => 'app/users',
    ],
];
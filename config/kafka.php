<?php

return [

    'brokers' => [
        'default' => [
            'broker' => '',
            'auth' => [
                'protocol' => 'ssl',
                'ca' => storage_path('ca.pem'),
                'certificate' => storage_path('kafka.cert'),
                'key' => storage_path('kafka.key'),
            ],
        ],
    ],

    'topics' => [
        'default' => [
            'topic' => 'default',
            'broker' => 'default',
            'consumer-groups' => [
                'default' => [
                    'offset' => 'initial',
                    'consumer' => '',
                ],
           ],
        ],
    ],
];

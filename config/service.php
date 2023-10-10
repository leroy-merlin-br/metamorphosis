<?php

return [
    'avro_schema' => [
        'url' => '',
        'request_options' => [
            'headers' => [
                'Authorization' => [
                    'Basic' . base64_encode(
                        env('AVRO_SCHEMA_USERNAME') . ':' . env(
                            'AVRO_SCHEMA_PASSWORD'
                        )
                    ),
                ],
            ],
        ],
        'ssl_verify' => true,
        'username' => 'USERNAME',
        'password' => 'PASSWORD',
    ],
    'broker' => [
        'connections' => env('KAFKA_BROKER_CONNECTIONS', 'kafka:9092'),
        'auth' => [
            'type' => 'ssl', // ssl and none
            'ca' => storage_path('ca.pem'),
            'certificate' => storage_path('kafka.cert'),
            'key' => storage_path('kafka.key'),
        ],
    ],
];

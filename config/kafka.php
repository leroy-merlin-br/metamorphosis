<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Avro Schemas
    |--------------------------------------------------------------------------
    |
    | Here you may specify the schema details configured on topic's broker key.
    |
    */

    'avro_schemas' => [
        'default' => [
            'url' => '',
            'request_options' => [
                'headers' => [
                    'Authorization' => [
                        'Basic '.base64_encode(
                            env('AVRO_SCHEMA_USERNAME').':'.env('AVRO_SCHEMA_PASSWORD')
                        ),
                    ],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Brokers
    |--------------------------------------------------------------------------
    |
    | Here you may specify the connections details for each broker configured
    | on topic's broker key.
    |
    */

    'brokers' => [
        'default' => [
            'connections' => 'kafka:6680',
            'auth' => [
                'type' => 'ssl', // ssl and none
                'ca' => storage_path('ca.pem'),
                'certificate' => storage_path('kafka.cert'),
                'key' => storage_path('kafka.key'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Topics
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration for the topics to be consumed.
    |
    | Every topic must have a unique identification key. This key must be passed
    | as argument when using the command to consume topics.
    |
    | For every topic you may define the following properties:
    |
    | 'topic_id': The topic name to subscribe to
    | 'broker': The broker identification key
    | 'middlewares': an array of middlewares applied for this topic and all consumer_groups inside
    | 'consumer_groups': You may define more than one consumer group per topic.
    |       If there is just one defined, it will be used by default,
    |       otherwise, you may pass which consumer group should be used
    |       when using the consumer command.
    |       For every consumer group, you may define:
    | 'consumer_groups.*.offset_reset': action to take when there is no initial
    |       offset in offset store or the desired offset is out of range.
    |       This config will be passed to 'auto.offset.reset'.
    |       The valid options are: smallest, earliest, beginning, largest, latest, end, error.
    | 'consumer_groups.*.offset': the offset at which to start consumption. This only applies if partition is set.
    |       You can use a positive integer or any of the constants: RD_KAFKA_OFFSET_BEGINNING,
    |       RD_KAFKA_OFFSET_END, RD_KAFKA_OFFSET_STORED.
    | 'consumer_groups.*.partition': the partition to consume. It can be null, if you don't wish do specify one.
    | 'consumer_groups.*.handler': a consumer class that implements ConsumerTopicHandler
    | 'consumer_groups.*.middlewares': an array of middlewares applied only for this consumer_group
    |
    */

    'topics' => [
        'default' => [
            'topic_id' => 'SOME-TOPIC-KAFKA-ID',
            'broker' => 'default',
            'consumer_groups' => [
                'default' => [
                    'offset_reset' => 'largest',
                    'offset' => 0,
                    'handler' => '\App\Kafka\Consumers\ConsumerExample',
                    'timeout' => 20000,
                    'middlewares' => [],
                ],
            ],
        ],
        'producer' => [
            'requiredAcknowledgment' => true,
            'isASync' => true,
            'middlewares' => [],
            'timeout_responses' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Middlewares
    |--------------------------------------------------------------------------
    |
    | Here you may specify the global middlewares that will be applied for every
    | consumed topic. Middlewares work between the received data from broker and
    | before being passed into consumers.
    | Available middlewares: log, avro-decode
    */

    'middlewares' => [
        'consumer' => [
            \Metamorphosis\Middlewares\Log::class,
        ],
        'producer' => [
            \Metamorphosis\Middlewares\Log::class,
        ],
        'global' => [
            \Metamorphosis\Middlewares\Log::class,
        ],
    ],
];

<?php

return [
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
            'connections' => '',
            'auth' => [
                'protocol' => 'ssl',
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
    | 'topic': The topic name to subscribe to
    | 'broker': The broker identification key
    | 'middlewares': an array of middlewares applied for this topic and all consumer-groups inside
    | 'consumer-groups': You may define more than one consumer group per topic.
    |                    If there is just one defined, it will be used by default,
    |                    otherwise, you may pass which consumer group should be used
    |                    when using the consumer command.
    |                    For every consumer group, you may define:
    |                    'offset': to be used as the 'auto.offset.reset'
    |                    'consumer': a consumer class that implements ConsumerTopicHandler
    |                    'middlewares': an array of middlewares applied only for this consumer-group
    |
    */

    'topics' => [
        'default' => [
            'topic' => 'default',
            'broker' => 'default',
            'consumer-groups' => [
                'default' => [
                    'offset' => 'initial',
                    'consumer' => '\App\Kafka\Consumers\ConsumerExample',
                ],
            ],
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
    ],
];

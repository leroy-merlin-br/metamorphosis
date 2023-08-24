<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AVRO Schemas
    |--------------------------------------------------------------------------
    |
    | Here you may specify the schema details configured on topic's broker key.
    | Schema are kind of "contract" between the producer and consumer.
    | For now, we are just decoding AVRO, not encoding.
    |
    */

    'avro_schemas' => [
        'default' => [
            'url' => '',
            // Disable SSL verification on schema request.
            'ssl_verify' => true,
            // This option will be put directly into a Guzzle http request
            // Use this to do authorizations or send any headers you want.
            // Here is a example of basic authentication on AVRO schema.
            'request_options' => [
                'headers' => [
                    'Authorization' => [
                        'Basic ' . base64_encode(
                            env('AVRO_SCHEMA_USERNAME')
                            . ':'
                            . env('AVRO_SCHEMA_PASSWORD')
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
            'connections' => env('KAFKA_BROKER_CONNECTIONS', 'kafka:9092'),

            // If your broker doest not have authentication, you can
            // remove this configuration, or set as empty.
            // The Authentication types may be "ssl" or "none"
            'auth' => [
                'type' => 'ssl', // ssl and none
                'ca' => storage_path('ca.pem'),
                'certificate' => storage_path('kafka.cert'),
                'key' => storage_path('kafka.key'),
            ],
        ],
    ],

    'topics' => [
        // This is your topic "keyword" where you will put all configurations needed
        // on this specific topic.
        'default' => [
            // The topic id is where you want to send or consume
            // your messages from kafka.
            'topic_id' => 'kafka-test',

            // Here you may point the key of the broker configured above.
            'broker' => 'default',

            // Configurations specific for consumer
            'consumer' => [
                // You may define more than one consumer group per topic.
                // If there is just one defined, it will be used by default,
                // otherwise, you may pass which consumer group should be used
                // when using the consumer command.
                'consumer_groups' => [
                    'test-consumer-group' => [

                        // Action to take when there is no initial
                        // offset in offset store or the desired offset is out of range.
                        // This config will be passed to 'auto.offset.reset'.
                        // The valid options are: smallest, earliest, beginning, largest, latest, end, error.
                        'offset_reset' => 'earliest',

                        // The offset at which to start consumption. This only applies if partition is set.
                        // You can use a positive integer or any of the constants: RD_KAFKA_OFFSET_BEGINNING,
                        // RD_KAFKA_OFFSET_END, RD_KAFKA_OFFSET_STORED.
                        'offset' => 0,

                        // The partition to consume. It can be null,
                        // if you don't wish do specify one.
                        'partition' => 0,

                        // A consumer class that implements ConsumerTopicHandler
                        'handler' => '\App\Kafka\Consumers\ConsumerExample',

                        // A Timeout to listen to a message. That means: how much
                        // time we need to wait until receiving a message?
                        'timeout' => 20000,

                        // A max interval for consumer to make poll calls. That means: how much
                        // time we need to wait for poll calls until consider the consumer has inactive.
                        'max_poll_interval_ms' => 300000,

                        // Once you've enabled this, the Kafka consumer will commit the
                        // offset of the last message received in response to its poll() call
                        'auto_commit' => true,

                        // If commit_async is false process block until offsets are committed or the commit fails.
                        // Only works when auto_commit is false
                        'commit_async' => false,

                        // An array of middlewares applied only for this consumer_group
                        'middlewares' => [],
                    ],
                ],
            ],

            // Configurations specific for producer
            'producer' => [

                // Sets to true if you want to know if a message was successfully posted.
                'required_acknowledgment' => true,

                // Whether if you want to receive the response asynchronously.
                'is_async' => true,

                // The amount of records to be sent in every iteration
                // That means that at each 500 messages we check if messages was sent.
                'max_poll_records' => 500,

                // The amount of attempts we will try to run the flush.
                // There's no magic number here, it depends on any factor
                // Try yourself a good number.
                'flush_attempts' => 10,

                // Middlewares specific for this producer.
                'middlewares' => [],

                // We need to set a timeout when polling the messages.
                // That means: how long we'll wait a response from poll
                'timeout' => 10000,

                // Here you can configure which partition you want to send the message
                // it can be -1 (RD_KAFKA_PARTITION_UA) to let Kafka decide, or an int with the partition number
                'partition' => defined('RD_KAFKA_PARTITION_UA')
                    ? constant('RD_KAFKA_PARTITION_UA')
                    : -1,
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
    |
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

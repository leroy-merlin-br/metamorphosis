<?php

return [
    'topics' => [
        'default' => [
            'topic_id' => 'kafka-test',
            'consumer' => [
                'consumer_group' => 'test-consumer-group',
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

                // Once you've enabled this, the Kafka consumer will commit the
                // offset of the last message received in response to its poll() call
                'auto_commit' => true,

                // If commit_async is false process block until offsets are committed or the commit fails.
                // Only works when auto_commit is false
                'commit_async' => false,

                // An array of middlewares applied only for this consumer_group
                'middlewares' => [],
            ],

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
                'partition' => constant('RD_KAFKA_PARTITION_UA') ?? -1,
            ],
        ],
    ],
];

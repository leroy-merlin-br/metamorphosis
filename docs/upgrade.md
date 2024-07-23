<a name="upgrade-guide"></a>
## Upgrade guide

To upgrade from version X.x to version X.y:

Move your `avroschema` and `broker` section from old `config/kafka.php` file into a new file: 


```php
<?php
// config/service.php
return [
    'topics' => [
        'this_is_your_topic_name' => [
            'topic_id' => "this_is_your_topic_id",
            'consumer' => [
                'consumer_group' => 'your-consumer-group',
                'offset_reset' => 'earliest',
                'offset' => 0,
                'partition' => 0,
                'handler' => '\App\Kafka\Consumers\ConsumerExample',
                'timeout' => 20000,
                'auto_commit' => true,
                'commit_async' => false,
                'middlewares' => [],
            ],
  
            'producer' => [
                'required_acknowledgment' => true,
                'is_async' => true,
                'max_poll_records' => 500,
                'flush_attempts' => 10,
                'middlewares' => [],
                'timeout' => 10000,
                'partition' => constant('RD_KAFKA_PARTITION_UA') ?? -1,
            ],
        ]
    ],
];
```

Upgrade your topic configuration files:

```php
<?php
// config/kafka.php
return [
    'topics' => [
        'this_is_your_topic_name' => [
            'topic_id' => "this_is_your_topic_id",
            'consumer' => [
                'consumer_group' => 'your-consumer-group',
                'offset_reset' => 'earliest',
                'offset' => 0,
                'partition' => 0,
                'handler' => '\App\Kafka\Consumers\ConsumerExample',
                'timeout' => 20000,
                'auto_commit' => true,
                'commit_async' => false,
                'middlewares' => [],
            ],
  
            'producer' => [
                'required_acknowledgment' => true,
                'is_async' => true,
                'max_poll_records' => 500,
                'flush_attempts' => 10,
                'middlewares' => [],
                'timeout' => 10000,
                'partition' => constant('RD_KAFKA_PARTITION_UA') ?? -1,
            ],
        ]
    ],
];
```


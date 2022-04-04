## Quick Usage Guide

- [Configure with files](#config)
- [Configure using data objects](#config-dto)
- [Consumer](#consumer)
   - [Creating Consumer](#creating-consumer)
   - [Running](#running-consumer)
- [Producer](#producer)
  - [Produce Message](#produce-message)

<a name="config"></a>
### Configure using  files

To get started using configuration files, at least two files are needed. A file to keep the topics
configuration and a file to keep the broker and schema configuration. In this example, we will use the files  `config/kafka.php` and `config/service.php`.

### File `config/kafka.php`:

This file keeps configurations about topics, consumers and producers.
It should return an array of topics containing the topic name, topic_id,  consumer, producer and the settings for each one of them:


```php
<?php

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

### File `config/service.php`

This file keeps configurations about **broker** and **schema** utilized.


```php
<?php

return [
    'avro_schema' => [
        'url' => '',
        'request_options' => [
            'headers' => [
                'Authorization' => [
                    'Basic ' . base64_encode(
                        env('AVRO_SCHEMA_USERNAME').':'.env('AVRO_SCHEMA_PASSWORD')
                    ),
                ],
            ],
        ],

        'ssl_verify' => true,
        'username' => 'USERNAME',
        'password' => 'PASSWORD',
    ],
    
    'broker' => [
        'connections' => 'kafka:9092',
        'auth' => [
            'type' => 'ssl', 
            'ca' => storage_path('ca.pem'),
            'certificate' => storage_path('kafka.cert'),
            'key' => storage_path('kafka.key'),
        ],
    ],
];
```

<a name="consumer"></a>
### Consumer

After setting up the required configuration, you must create a consumer to handle records received
from the specified topic in your configuration.

<a name="creating-consumer"></a>
#### Creating a Consumer

To create a consumer run the following command:
```bash
$ php artisan make:kafka-consumer PriceUpdateConsumer
```
This will create a KafkaConsumer class on the app/Kafka/Consumers/ directory with the following
content:

```php
use App\Kafka\Consumers\PriceUpdateConsumer;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;
use Metamorphosis\Record\RecordInterface;

class PriceUpdateConsumer extends AbstractHandler
{
    public $repository;

    /**
     * Create a new consumer topic handler instance.
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle payload.
     */
    public function handle(RecordInterface $record): void
    {
        $product = $record->getPayload();

        $this->repository->update($product['id'], $product['price']);
    }
}
```

<a name="running-consumer"></a>
#### Running the consumer

To start consuming the topic, the simplest way to see it working is by running the kafka:consume command along with the topic name, topic configuration file and service configuration file:


```bash
$ php artisan kafka:consume this_is_your_topic_name --config_name=config.file --service_name=service.file
``` 

This command will run in a `while true`, that means, it will never stop running.
But, errors can happen, so we strongly advice you to run this command along with [supervisor](http://supervisord.org/running.html),
like this example below:

```bash
[program:kafka-consumer-price-update]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/default/artisan kafka:consume price-update --timeout=-1
autostart=true
autorestart=true
user=root
numprocs=6
redirect_stderr=true
stdout_logfile=/var/log/default/kafka-consumer-price-update.log
```

### Using data objects

To configure and consume using classes:

```php
    use Metamorphosis\Consumer;
    use Metamorphosis\TopicHandler\ConfigOptions\Factories\ConsumerFactory;
    
    $topic = config('yourConfig.topics.topic-id');
    $broker = config('yourService.broker');
    $avro = config('yourService.avro_schema');
    
    $consumerConfiguration = ConsumerFactory::make($broker, $topic, $avro);
    $consumer = app(Consumer::class, ['configOptions' => $consumerConfiguration]);
    
    $consumer->consume();
```

That's it. For more information about usage, middlewares, broker authentication, consumer groups and other advanced topics, please have a look at our [Advanced Usage Guide](advanced.md).

<a name="produce-message"></a>
### Produce Message

To create a producer handler, create a class that extends `Metamorphosis\TopicHandler\Producer\AbstractHandler` class:

```php
<?php

use Metamorphosis\TopicHandler\Producer\AbstractHandler;

class ProductUpdated extends AbstractHandler
{
}
```

Creating payload and produce kafka message.

The payload must be a array. This array can even store other arrays as values.

The second parameter indicates which kafka topic will receive the message and the third indicates the message key.
```php
$record = ['name' => 'test', 'id' => 88989898, 'price' => 18.99];
$key = 88989898;
$producer = new ProductUpdated($record, 'product-updated', $key)

Metamorphosis::produce($producer);
```

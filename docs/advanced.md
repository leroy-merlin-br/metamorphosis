## Advanced Guide

- [Authentication](#authentication)
- [Middlewares](#middlewares)
- [Brokers](#brokers)
- [Commands](#commands)
   - [Creating Consumer](#commands-consumer)
   - [Creating Middleware](#commands-middleware)
   - [Running Consumer](#commands-running-consumer)

<a name="authentication"></a>
### Authentication
You can set which type of authentication each broker will need to connect.

This is possible by filling the `auth` key in the broker config:

``` php
'brokers' => [
      'price-brokers' => [
          'connections' => 'localhost:8091,localhost:8092',
          'auth' => [
              'protocol' => 'ssl',
              'ca' => storage_path('ca.pem'),
              'certificate' => storage_path('kafka.cert'),
              'key' => storage_path('kafka.key'),
          ],
      ],
      'stock-brokers' => [
          'connections' => ['localhost:8091', 'localhost:8092'],
          'auth' => [], // can be an empty array or even don't have this key in the broker config
      ],
  ],
```

If the protocol key is set to `ssl`, it will make a SSL Authentication, and it will need some extra fields along with protocol.
The fields are `ca` with the `ca.pem` file, `certificate` with the `.cert` file and the `.key` file

If the broker do not need any authentication to connect, you can leave the `auth` key as a empty `array` or even delete it.

---

<a name="middlewares"></a>
### Middlewares

Middlewares work between the received data from broker and before being handled by consumers.

They behave similar to [PSR-15](https://www.php-fig.org/psr/psr-15/) middlewares. The main difference is that instead
of returning a `Response`, they are intended to transform, validate or do any kind of manipulation on the record's payload.
After that, they delegate the proccess back to the `MiddlewareHandler`. They can prevent the record to reach the consumer class by throwing an exception.

This package comes with the following middlewares:

- `\Metamorphosis\Middlewares\AvroDecode`
- `\Metamorphosis\Middlewares\JsonDecode`
- `\Metamorphosis\Middlewares\Log`

You can easily create your own middleware using the command `php artisan make:kafka-middleware`.

Example:

Let's say all records you consume on Kafka are json serialized. You could use a middleware to deserialize them. You may generate a new middleware using the command:

```bash
$ php artisan make:kafka-middleware JsonDeserializer
```

The generated class will be placed on `app/Kafka/Middlewares` directory, and will look like this:

```php
<?php
namespace App\Kafka\Middlewares;

use Metamorphosis\Middlewares\Handler\MiddlewareHandler;
use Metamorphosis\Middlewares\Middleware;
use Metamorphosis\Record;

class JsonDeserializer implements Middleware
{
    public function process(Record $record, MiddlewareHandler $handler): void
    {
        // Here you can manipulate your record before handle it in your consumer

        $handler->handle($record);
    }
}

```

You may overwrite the record payload by calling `$record->setPayload()`:

```php
public function process(Record $record, MiddlewareHandler $handler): void
{
    $payload = $record->getPayload();

    $record->setPayload(json_decode($payload));

    $handler->handle($record);
}
```

Then you may configure this new middleware to be executed for every record by adding it on the config file `config/kafka.php`:

```php
// ...
'middlewares' => [
    'consumer' => [
        \Metamorphosis\Middlewares\Log::class,
        \App\Kafka\Middlewares\JsonDeserializer::class,
    ],
],
// ...
```

If you wish, you may set a middleware to run of a topic level or a consumer group level:

```php
'topics' => [
    'price-update' => [
        'topic' => 'products.price.update',
        'broker' => 'price-brokers',
        'consumer-groups' => [
            'default' => [
                'offset' => 'initial',
                'consumer' => '\App\Kafka\Consumers\PriceUpdateConsumer',
                'middlewares' => [
                    \App\Kafka\Middlewares\ConsumerGroupMiddlewareExample::class,
                ],
            ],
        ],
        'middlewares' => [
            \App\Kafka\Middlewares\TopicMiddlewareExample::class,
        ],
    ],
],
```

The order matters here, they'll be execute as queue, from the most global scope to the most specific (global scope > topic scope > group-consumers scope).


<a name="commands"></a>
### Commands
There's a few commands to help automate the creation of classes and to run the consumer.

<a name="commands-consumer"></a>
#### Creating Consumer
You can create a consumer class, that will handle all records received from the topic using the follow command:
```bash
$ php artisan make:kafka-consumer PriceUpdateConsumer
```
This will create a KafkaConsumer class inside the application, on the `app/Kafka/Consumers/` directory.

There, you'll have a `handler` method, which will send all records from the topic to the Consumer.
Methods will be available for handling exceptions:
 - `warning` method will be call whenever something not critical is received from the topic.
    Like a message informing that there's no more records to consume.
 - `failure` method will be call whenever something critical happens, like an error to decode the record.

```php
use App\Repository;
use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Record;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;

class PriceUpdateConsumer extends AbstractHandler
{
    public $repository;

    /**
     * Create a new consumer topic handler instance.
     *
     * @return void
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle payload.
     *
     * @param Record $record
     *
     * @return void
     */
    public function handle(Record $record): void
    {
        $product = $record->getPayload();

        $this->repository->update($product['id'], $product['price']);
    }

    public function warning(ResponseWarningException $exception): void
    {
        // handle warning exception
    }

    public function failed(Exception $exception): void
    {
        // handle failure exception
    }
}
```


<a name="commands-middleware"></a>
#### Creating Middleware
You can create a middleware class, that works between the received data from broker and before being passed into consumers, using the follow command:

```bash
$ php artisan make:kafka-middleware PriceTransformerMiddleware
```

This will create a PriceTransformerMiddleware class inside the application, on the `app/Kafka/Middlewares/` directory.
You can configure this inside the `config/kafka.php` file, putting in one of the three levels, depending on how generic or specific is the middleware.

For more details about middlewares, see [this section](#middlewares).

<a name="commands-running-consumer"></a>
#### Running Consumer
This command serves to start consuming from kafka and receiving data inside your consumer.
The most basic usage it's by just using the follow command:

```bash
$ php artisan kafka:consume price-update
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

Although you can run this simple command, we've some options you can pass to make it more flexible to your needs.

If you wish do specify in which partition the consumer must be attached, you can set the option `--partition=`

And if you need to start the consumption of a topic in a specific offset (it can be useful for debug purposes)
you can pass the `--offset=` option, but for this, it will be required to specify the partition too.

An example of how it would be using all this options, for example, we would run a consumer for a price topic
getting records from the second partition and an offset of 34:

```bash
$ php artisan kafka:consume price-update --partition=2 --offset=34
```

Also, you can specify what would be the timeout for the consumer, by using the `--timeout=` option, the time is in milliseconds.
```bash
$ php artisan kafka:consume price-update --timeout=23000
```


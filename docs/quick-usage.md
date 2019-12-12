## Quick Usage Guide

- [Config file](#config)
- [Consumer](#consumer)
   - [Creating Consumer](#creating-consumer)
   - [Running](#running-consumer)

<a name="config"></a>
### Config file: `config/kafka.php`

The config file holds all information about brokers, topics, consumer groups and middlewares.

To quickly start using, we can focus in two sections:
- Brokers

    An array of brokers, with connection and authentication configurations:

    - `connections`: *required*. can be a `string` with multiple connections separated by comma or an `array` of connections (as `string`)

    - `auth`: *optional*. out of the box, the package can connect with SSL Authentication only or without any authentication

    ```php
      'brokers' => [
          'price_brokers' => [
              'connections' => 'localhost:8091,localhost:8092',
              'auth' => [
                  'type' => 'ssl',
                  'ca' => storage_path('ca.pem'),
                  'certificate' => storage_path('kafka.cert'),
                  'key' => storage_path('kafka.key'),
              ],
          ],
          'stock_brokers' => [
              'connections' => ['localhost:8091', 'localhost:8092'],
              'auth' => [], // can be an empty array or even don't have this key in the broker config
          ],
      ],
    ```

- Topics

    An array of topics configuration, such as the topic name, which broker connection should use, consumer groups and middlewares.

    Here we can specify the group consumers, each topic can have multiple groups,
    and each group holds the configuration for which consumer, offset_reset (for setting initial offset) and middleware it must use.

    ```php
      'topics' => [
          'price_update' => [
              'topic' => 'products.price.update',
              'broker' => 'price_brokers',
              'consumer_groups' => [
                  'default' => [
                      'offset_reset' => 'smallest',
                      'handler' => '\App\Kafka\Consumers\PriceUpdateConsumer',
                  ],
              ],
          ],
      ],
    ```

<a name="consumer"></a>
### Consumer

After setting up the required configs, you need to create the consumer, which will handle all records received
from the topic specified in the config.

<a name="creating-consumer"></a>
#### Creating Consumer

Creating the consumer is easy as running the following command:
```bash
$ php artisan make:kafka-consumer PriceUpdateConsumer
```
This will create a KafkaConsumer class inside the application, on the app/Kafka/Consumers/ directory

There, you'll have a handler method, which will send all records from the topic to the Consumer,
also, methods will be available for handling exceptions

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
#### Running consumer

Now you just need to start consuming the topic.

The simplest way to see it working is by running the kafka:consume command along with the topic name
declared in the topics config key:

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

That's it. For more information about usage, middlewares, broker authentication, consumer groups and other advanced topics, please have a look at our [Advanced Usage Guide](advanced.md).

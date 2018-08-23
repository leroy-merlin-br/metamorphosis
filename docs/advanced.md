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

This is possible filling the `auth` key in the broker config:

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

If the broker do not need any authentication to connect, you can leave the `auth` key as a empty array or event delete it.

---

<a name="middlewares"></a>
### Middlewares
   Middlewares work between the received data from broker and before being passed into consumers.
   
   You can log or transform records before reach your application consumer.
   
   This package brigns with two middlewares, Log and AvroDecode, but you can create your own
   using the `php artisan make:kafka-middleware` command.
   
   You can use global middlewares, topic middlewares or consumer-group middlewares, just setting in the config/kafka.php
   
   The order matters here, they'll be execute as queue, from the most specific to most global scope (group-consumers scope > topic scope > global scope)


<a name="commands"></a>
### Commands
There's a few commands to help automate the creation of classes and to run the consumer.

<a name="commands-consumer"></a>
#### Creating Consumer
You can create a consumer class, that will handle all records received from the topic using the follow command:
```bash
$ php artisan make:kafka-consumer PriceUpdateConsumer
```
This will create a KafkaConsumer class inside the application, on the app/Kafka/Consumers/ directory

There, you'll have a handler method, which will send all records from the topic to the Consumer.
Methods will be available for handling exceptions:
 - `warning` method will be call whenever something not critical is received from the topic.
    Like a message informing that there's no more records to consume.
 - `failure` method will be call whenever something critical happens, like an error to decode the record.

```php
use App\Kafka\Consumers\PriceUpdateConsumer;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;
use Metamorphosis\Record;

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
    }

    public function failed(Exception $exception): void
    {
    }
}
```


<a name="commands-middleware"></a>
#### Creating Middleware
You can create a middleware class, that works between the received data from broker and before being passed into consumers, using the follow command:

```bash
$ php artisan make:kafka-middleware PriceTransformerMiddleware
```

This will create a PriceTransformerMiddleware class inside the application, on the app/Kafka/Middlewares/ directory.
You can configure this inside the `config/kafka.php` file, putting in one of the three levels, depending on how generic or specific is the middleware.

For more details about middlewares, see [this section](#middlewares).

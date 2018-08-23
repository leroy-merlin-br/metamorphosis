## Advanced Guide

- [Authentication](#authentication)
- [Middlewares](#middlewares)
- [Brokers](#brokers)
- [Commands](#commands)

<a name="authentication"></a>
### Authentication
You can set what kind of authentication each broker will need to connect. 
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
The fields are `ca` with the ca.pem file, `certificate` with the `.cert` file and the `.key` file

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

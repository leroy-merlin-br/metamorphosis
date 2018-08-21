### Advanced Guide

- [Authentication](#authentication)
- [Middlewares](#middlewares)
- [Brokers](#brokers)


<a name="middlewares"></a>
#### Middlewares
   Middlewares work between the received data from broker and before being passed into consumers.
   
   You can log or transform records before reach your application consumer.
   
   This package brigns with two middlewares, Log and AvroDecode, but you can create your own
   using the `php artisan make:kafka-middleware` command.
   
   You can use global middlewares, topic middlewares or consumer-group middlewares, just setting in the config/kafka.php
   
   The order matters here, they'll be execute as queue, from the most specific to most global scope (group-consumers scope > topic scope > global scope)

# Metamorphosis

> Easy and flexible Kafka Library for Laravel and PHP 7.

![Metamorphosis](./logo.jpg)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/leroy-merlin-br/metamorphosis.svg?style=flat-square)](https://packagist.org/packages/leroy-merlin-br/metamorphosis)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/leroy-merlin-br/metamorphosis.svg?style=flat-square)](https://packagist.org/packages/leroy-merlin-br/metamorphosis)
[![Build Status](https://travis-ci.org/leroy-merlin-br/metamorphosis.svg?branch=master)](https://travis-ci.org/leroy-merlin-br/metamorphosis)
[![Coverage Status](https://coveralls.io/repos/github/leroy-merlin-br/metamorphosis/badge.svg?branch=master)](https://coveralls.io/github/leroy-merlin-br/metamorphosis?branch=master)
[![StyleCI](https://styleci.io/repos/143462354/shield?branch=master)](https://styleci.io/repos/143462354)


- [Introduction](#introduction)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Guide](#quick-guide)
- [Advanced Guide](docs/advanced.md)
- [Contributing](docs/CONTRIBUTING.md)
- [License](#license)


<a name="introduction"></a>
## Introduction

Metamorphosis provides a simple, straight-forward implementation for working with Kafka inside Laravel applications.

<a name="requirements"></a>
## Requirements

- PHP >= 7.1
- [Kafka Driver](https://github.com/edenhill/librdkafka)
- [Kafka PHP Extension](https://github.com/arnaud-lb/php-rdkafka)

<a name="installation"></a>
## Installation

**Important:** *Make sure that you already have installed the OS driver for kafka, and the kafka php extension.*


You can install library through Composer:

```
$ composer require leroy-merlin-br/metamorphosis
```

<a name="quick-guide"></a>
## Quick Guide

1. The Config: `config/kafka.php`

    The config file holds all information about brokers, topics, consumer groups and middlewares.

    To quickly start using, we can focus in two sections:
    - Brokers
    
        An array of brokers, with connection and authentication
       
        - Connections: required. can be an string with multiple connections separated by comma or an array of connections (as string)
        
        - Authentication: optional. out of the box, the package can connect with SSL Authentication only or without any authentication

        ```php
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

    - Topics
        
        An array of topics configuration, such as the topic name, which broker connection must use, consumer groups and middlewares
        
        Here we can specify the group consumers, each topic can have multiple groups, 
        and each group holds the configuration for which consumer, offset and middleware must use  

        ```php
          'topics' => [
              'price-update' => [
                  'topic' => 'products.price.update',
                  'broker' => 'price-brokers',
                  'consumer-groups' => [
                      'default' => [
                          'offset' => 'initial',
                          'consumer' => '\App\Kafka\Consumers\PriceUpdateConsumer',
                      ],
                  ],
              ],
          ],
        ```

2. The Consumer:
    
    run:
    ```bash
    $ php artisan make:kafka-consumer LogConsumer
    ```
    this will create a KafkaConsumer class inside the application, on the app/Kafka/Consumers/ directory
    
    There, you'll have an handler method, which will send all records from the topic to him,
    also, will be available methods for handle exceptions (failure and warning)
    
    ```php
       class PriceUpdateConsumer extends AbstractHandler
       {
           public $product;
        
           /**
            * Create a new consumer topic handler instance.
            *
            * @return void
            */
           public function __construct(Product $product)
           {
               $this->product = $product;
           }
       
           /**
            * Handle payload.
            *
            * @param Message $message
            *
            * @return bool
            */
           public function handle(Message $message): bool
           {
               $price = $message->getPayload();
                  
               $this->product->updatePrice($price);
           }
        
           public function warning(ResponseWarningException $exception): void
           {
               // treat any warnings, like partition without new records to process
           }
       
           public function failed(Exception $exception): void
           {
               // treat any failed executions, like an record with unparsable payload or connection error
           }
       }
    ```


3. The Runner

    now to start consuming some topic, 
    just run php artisan kafka:consume topic-name



<a name="license"></a>
## License

Metamorphosis is free software distributed under the terms of the [MIT license](http://opensource.org/licenses/MIT)

<a name="additional_information"></a>
## Additional information

Metamorphosis was proudly built by the [Leroy Merlin Brazil](https://github.com/leroy-merlin-br) team. [See all the contributors](https://github.com/leroy-merlin-br/metamorphosis/graphs/contributors).

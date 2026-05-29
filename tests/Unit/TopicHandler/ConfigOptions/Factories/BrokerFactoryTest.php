<?php

namespace Tests\Unit\TopicHandler\ConfigOptions\Factories;

use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Factories\BrokerFactory;
use Tests\LaravelTestCase;

class BrokerFactoryTest extends LaravelTestCase
{
    public function testShouldMakeConfigOptionWithAvroSchema(): void
    {
        // Set
        $connections = (string) config(
            'service.broker.connections',
            'kafka:29092'
        );
        $data = [
            'connections' => $connections,
            'auth' => [
                'type' => 'ssl',
                'ca' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/ca.pem',
                'certificate' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/kafka.cert',
                'key' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/kafka.key',
            ],
        ];
        // Actions
        $result = BrokerFactory::make($data);

        // Assertions
        $this->assertInstanceOf(Broker::class, $result);
        $this->assertEquals($data, $result->toArray());
    }
}

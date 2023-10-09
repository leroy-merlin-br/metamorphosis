<?php

namespace Tests\Integration;

use Metamorphosis\Consumer;
use Metamorphosis\Facades\Metamorphosis;
use Metamorphosis\Middlewares\JsonDecode;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ProducerConfigOptions;
use Tests\Integration\Dummies\MessageProducerWithConfigOptions;
use Tests\LaravelTestCase;

class ConsumerTest extends LaravelTestCase
{
    public function testItShouldSetup(): void
    {
        // Set
        $connections = env('KAFKA_BROKER_CONNECTIONS', 'kafka:9092');
        $brokerOptions = new Broker($connections, new None());
        $consumerConfigOptions = new ConsumerConfigOptions(
            'single_consumer_test',
            $brokerOptions,
            null,
            null,
            null,
            'default',
            null,
            [JsonDecode::class],
            20000,
            false,
            true,
            'smallest'
        );

        $producerConfigOptions = new ProducerConfigOptions(
            'single_consumer_test',
            $brokerOptions,
            null,
            null,
            [],
            20000,
            false,
            true,
            10,
            500
        );

        $messageProducer = app(
            MessageProducerWithConfigOptions::class,
            [
                'record' => ['id' => 'MESSAGE_ID'],
                'configOptions' => $producerConfigOptions,
                'key' => 1,
            ]
        );

        $saleOrderDispatcher = Metamorphosis::build($messageProducer);
        $saleOrderDispatcher->handle($messageProducer->createRecord());

        $consumer = $this->app->make(
            Consumer::class,
            ['configOptions' => $consumerConfigOptions]
        );
        $expected = '{"id":"MESSAGE_ID"}';

        // Actions
        $result = $consumer->consume()->getPayload();

        // Assertions
        $this->assertSame($expected, $result);
    }
}

<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\Log;
use Metamorphosis\Facades\Metamorphosis;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ProducerConfigOptions;
use Tests\Integration\Dummies\MessageConsumer;
use Tests\Integration\Dummies\MessageProducerWithConfigOptions;
use Tests\LaravelTestCase;

class ProducerWithConfigOptionsTest extends LaravelTestCase
{
    private ProducerConfigOptions $producerConfigOptions;

    private ConsumerConfigOptions $consumerConfigOptions;

    public function testShouldRunAProducerMessagesWithConfigOptions(): void
    {
        // Given That I
        $this->haveAHandlerConfigured();

        // I Expect That
        $this->myMessagesHaveBeenProduced();
        $this->expectNotToPerformAssertions();

        // When I
        $this->haveSomeRandomMessageProduced();

        // I Expect That
        $this->myMessagesHaveBeenLogged();

        // When I
        $this->runTheConsumer();
    }

    protected function runTheConsumer(): void
    {
        $dummy = new MessageConsumer($this->consumerConfigOptions);
        $this->instance('\App\Kafka\Consumers\ConsumerOverride', $dummy);
        config([
            'kafka_new_config' => [
                'brokers' => [
                    'override' => [
                        'connections' => env(
                            'KAFKA_BROKER_CONNECTIONS',
                            'kafka:9092'
                        ),
                    ],
                ],
                'topics' => [
                    'default' => [
                        'broker' => 'override',
                        'consumer' => [
                            'consumer_groups' => [
                                'test-consumer-group' => [
                                    'handler' => '\App\Kafka\Consumers\ConsumerOverride',
                                    'offset_reset' => 'earliest',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->artisan(
            'kafka:consume',
            [
                'topic' => 'default',
                'consumer_group' => 'test-consumer-group',
                '--timeout' => 20000,
                '--times' => 2,
                '--config_name' => 'kafka_new_config',
            ]
        );
    }

    protected function haveAHandlerConfigured(): void
    {
        $connections = env('KAFKA_BROKER_CONNECTIONS', 'kafka:9092');
        $broker = new Broker($connections, new None());
        $this->producerConfigOptions = new ProducerConfigOptions(
            'sale_order_override',
            $broker,
            null,
            null,
            [],
            20000,
            false,
            true,
            10,
            100
        );

        $this->consumerConfigOptions = new ConsumerConfigOptions(
            'sale_order_override',
            $broker,
            '\App\Kafka\Consumers\ConsumerOverride',
            null,
            null,
            'test-consumer-group',
            null,
            [],
            20000,
            false,
            true
        );
    }

    private function haveSomeRandomMessageProduced(): void
    {
        $saleOrderProducer = app(
            MessageProducerWithConfigOptions::class,
            [
                'record' => ['saleOrderId' => 'SALE_ORDER_ID'],
                'configOptions' => $this->producerConfigOptions,
                'key' => 1,
            ]
        );

        $saleOrderDispatcher = Metamorphosis::build($saleOrderProducer);
        $saleOrderDispatcher->handle($saleOrderProducer->createRecord());
    }

    private function myMessagesHaveBeenLogged()
    {
        Log::shouldReceive('alert')
            ->with('{"saleOrderId":"SALE_ORDER_ID"}');
    }

    private function myMessagesHaveBeenProduced()
    {
        Log::shouldReceive('info')
            ->with('Record successfully sent to broker.', [
                'topic' => 'sale_order_override',
                'payload' => '{"saleOrderId":"SALE_ORDER_ID"}',
                'key' => '1',
                'partition' => 0,
            ]);
    }
}

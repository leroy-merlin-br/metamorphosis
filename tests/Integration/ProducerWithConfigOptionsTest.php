<?php
namespace Tests\Integration;

use Illuminate\Support\Facades\Log;
use Metamorphosis\Facades\Metamorphosis;
use Metamorphosis\TopicHandler\ConfigOptions;
use Tests\Integration\Dummies\MessageConsumer;
use Tests\Integration\Dummies\MessageProducerWithConfigOptions;
use Tests\LaravelTestCase;

class ProducerWithConfigOptionsTest extends LaravelTestCase
{
    /**
     * @var ConfigOptions
     */
    private $configOptions;

    public function testShouldRunAProducerMessagesWithConfigOptions(): void
    {
        // Given That I
        $this->haveAHandlerConfigured();

        // I Expect That
        $this->myMessagesHaveBeenProduced();

        // When I
        $this->haveSomeRandomMessageProduced();

        // I Expect That
        $this->myMessagesHaveBeenLogged();

        // When I
        $this->runTheConsumer();
    }

    protected function runTheConsumer(): void
    {
        $dummy = new MessageConsumer($this->configOptions);
        $this->instance('\App\Kafka\Consumers\ConsumerOverride', $dummy);
        config([
            'kafka_new_config' => [
                'brokers' => [
                    'override' => [
                        'connections' => 'kafka:9092',
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
        $this->configOptions = new ConfigOptions(
            'sale_order_override',
            ['connections' => 'kafka:9092'],
            null,
            [],
            [],
            20000,
            false,
            true,
            600,
            10
        );
    }

    private function haveSomeRandomMessageProduced(): void
    {
        $saleOrderProducer = app(
            MessageProducerWithConfigOptions::class,
            [
                'record' => ['saleOrderId' => 'SALE_ORDER_ID'],
                'configOptions' => $this->configOptions,
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

<?php
namespace Tests\Unit\Connectors\Consumer;

use Metamorphosis\Connectors\Consumer\Config;
use Metamorphosis\Connectors\Consumer\Factory;
use Metamorphosis\Consumers\HighLevel;
use Metamorphosis\Consumers\LowLevel;
use Tests\LaravelTestCase;
use Tests\Unit\Dummies\ConsumerHandlerDummy;

class FactoryTest extends LaravelTestCase
{
    public function testItMakesManagerWithLowLevelConsumer(): void
    {
        // Set
        $this->haveAConsumerWithPartitionConfigured();

        $config = new Config();
        $configConsumer = $config->make(['timeout' => 61], ['topic' => 'topic_key', 'consumer_group' => 'with-partition']);
        $manager = Factory::make($configConsumer);

        // Assertions
        $this->assertInstanceOf(LowLevel::class, $manager->getConsumer());
    }

    public function testItMakesManagerWithHighLevelConsumerWhenPartitionIsNotValid(): void
    {
        // Set
        $this->haveAConsumerWithoutPartitionConfigured();
        $config = new Config();
        $configConsumer = $config->make(['timeout' => 61, 'partition' => -1], ['topic' => 'topic_key', 'consumer_group' => 'with-partition']);
        $manager = Factory::make($configConsumer);

        // Assertions
        $this->assertInstanceOf(HighLevel::class, $manager->getConsumer());
    }

    public function testItMakesHighLevelClass(): void
    {
        // Set
        $this->haveAConsumerWithoutPartitionConfigured();
        $config = new Config();
        $configConsumer = $config->make(['timeout' => 61], ['topic' => 'topic_key', 'consumer_group' => 'without-partition']);
        $manager = Factory::make($configConsumer);

        // Assertions
        $this->assertInstanceOf(HighLevel::class, $manager->getConsumer());
    }

    private function haveAConsumerWithPartitionConfigured()
    {
        config([
            'kafka' => [
                'topics' => [
                    'topic_key' => [
                        'topic_id' => 'topic_name',
                        'consumer' => [
                            'consumer_group' => 'with-partition',
                            'offset_reset' => 'earliest',
                            'offset' => 0,
                            'partition' => 0,
                            'handler' => ConsumerHandlerDummy::class,
                        ],
                    ],
                ],
            ],
            'service' => [
                'broker' => [
                    'connections' => 'kafka:123',
                ],
            ],
        ]);
    }

    private function haveAConsumerWithoutPartitionConfigured()
    {
        config([
            'kafka' => [
                'topics' => [
                    'topic_key' => [
                        'topic_id' => 'topic_name',
                        'consumer' => [
                            'consumer_group' => 'without-partition',
                            'offset_reset' => 'earliest',
                            'handler' => ConsumerHandlerDummy::class,
                        ],
                    ],
                ],
            ],
            'service' => [
                'broker' => [
                    'connections' => 'kafka:123',
                ],
            ],
        ]);
    }
}

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
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'kafka' => [
                'brokers' => [
                    'default' => [
                        'connections' => 'kafka:9092',
                    ],
                ],
                'topics' => [
                    'topic_key' => [
                        'topic_id' => 'topic_name',
                        'broker' => 'default',
                        'consumer' => [
                            'consumer_groups' => [
                                'with-partition' => [
                                    'offset_reset' => 'earliest',
                                    'offset' => 0,
                                    'partition' => 0,
                                    'handler' => ConsumerHandlerDummy::class,
                                ],
                                'without-partition' => [
                                    'offset_reset' => 'earliest',
                                    'handler' => ConsumerHandlerDummy::class,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function testItMakesManagerWithLowLevelConsumer(): void
    {
        // Set
        $config = new Config();
        $configManager = $config->make(['timeout' => 61], ['topic' => 'topic_key', 'consumer_group' => 'with-partition']);
        $manager = Factory::make($configManager);

        // Assertions
        $this->assertInstanceOf(LowLevel::class, $manager->getConsumer());
    }

    public function testItMakesManagerWithLowLevelConsumerWhenPartitionIsNotValid(): void
    {
        // Set
        $config = new Config();
        $configManager = $config->make(['timeout' => 61], ['topic' => 'topic_key', 'consumer_group' => 'with-partition', 'partition' => -1]);
        $manager = Factory::make($configManager);

        // Assertions
        $this->assertInstanceOf(HighLevel::class, $manager->getConsumer());
    }

    public function testItMakesHighLevelClass(): void
    {
        // Set
        $config = new Config();
        $configManager = $config->make(['timeout' => 61], ['topic' => 'topic_key', 'consumer_group' => 'without-partition']);
        $manager = Factory::make($configManager);

        // Assertions
        $this->assertInstanceOf(HighLevel::class, $manager->getConsumer());
    }
}

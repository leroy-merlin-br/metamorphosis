<?php
namespace Tests\Connectors\Consumer;

use Metamorphosis\Connectors\Consumer\Config;
use Metamorphosis\Connectors\Consumer\ConnectorFactory;
use Metamorphosis\Connectors\Consumer\HighLevel;
use Metamorphosis\Connectors\Consumer\LowLevel;
use Tests\Dummies\ConsumerHandlerDummy;
use Tests\LaravelTestCase;

class ConnectorFactoryTest extends LaravelTestCase
{
    protected function setUp()
    {
        parent::setUp();

        config([
            'kafka' => [
                'brokers' => [
                    'default' => [
                        'connections' => 'kafka:123',
                    ],
                ],
                'topics' => [
                    'topic_key' => [
                        'topic_id' => 'topic_name',
                        'broker' => 'default',
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
        ]);
    }

    public function testItMakesLowLevelClass(): void
    {
        // Set
        $config = new Config();
        $config->setOption(['timeout' => 61], ['topic' => 'topic_key', 'consumer_group' => 'with-partition']);
        $lowLevelConnector = ConnectorFactory::make();

        // Assertions
        $this->assertInstanceOf(LowLevel::class, $lowLevelConnector);
    }

    public function testItMakesHighLevelClass(): void
    {
        // Set
        $config = new Config();
        $config->setOption(['timeout' => 61], ['topic' => 'topic_key', 'consumer_group' => 'without-partition']);
        $highLevelConnector = ConnectorFactory::make();

        // Assertions
        $this->assertInstanceOf(HighLevel::class, $highLevelConnector);
    }
}

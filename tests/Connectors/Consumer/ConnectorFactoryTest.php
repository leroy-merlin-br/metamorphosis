<?php
namespace Tests\Connectors\Consumer;

use Metamorphosis\Config;
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
                        'connections' => '',
                    ],
                ],
                'topics' => [
                    'topic-key' => [
                        'topic' => 'topic-name',
                        'broker' => 'default',
                        'consumer-groups' => [
                            'with-partition' => [
                                'offset-reset' => 'earliest',
                                'offset' => 0,
                                'partition' => 0,
                                'consumer' => ConsumerHandlerDummy::class,
                            ],
                            'without-partition' => [
                                'offset-reset' => 'earliest',
                                'offset' => 0,
                                'consumer' => ConsumerHandlerDummy::class,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function it_makes_low_level_class()
    {
        $config = new Config('topic-key', 'with-partition', 3, 0);

        $lowLevelConnector = ConnectorFactory::make($config);

        $this->assertInstanceOf(LowLevel::class, $lowLevelConnector);
    }

    /** @test */
    public function it_makes_high_level_class()
    {
        $config = new Config('topic-key', 'without-partition');

        $highLevelConnector = ConnectorFactory::make($config);

        $this->assertInstanceOf(HighLevel::class, $highLevelConnector);
    }
}

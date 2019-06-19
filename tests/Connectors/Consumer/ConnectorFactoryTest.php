<?php
namespace Tests\Connectors\Consumer;

use Metamorphosis\Config\Consumer;
use Metamorphosis\Connectors\Consumer\ConnectorFactory;
use Metamorphosis\Connectors\Consumer\HighLevel;
use Metamorphosis\Connectors\Consumer\LowLevel;
use Tests\Dummies\ConsumerHandlerDummy;
use Tests\LaravelTestCase;

class ConnectorFactoryTest extends LaravelTestCase
{
    protected function setUp(): void
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

    public function testItMakesLowLevelClass()
    {
        $config = new Consumer('topic-key', 'with-partition', 3, 0);

        $lowLevelConnector = ConnectorFactory::make($config);

        $this->assertInstanceOf(LowLevel::class, $lowLevelConnector);
    }

    public function testItMakesHighLevelClass()
    {
        $config = new Consumer('topic-key', 'without-partition');

        $highLevelConnector = ConnectorFactory::make($config);

        $this->assertInstanceOf(HighLevel::class, $highLevelConnector);
    }
}

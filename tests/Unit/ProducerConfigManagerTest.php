<?php

namespace Tests\Unit;

use Metamorphosis\ProducerConfigManager;
use Metamorphosis\TopicHandler\Producer\AbstractProducer;
use Tests\LaravelTestCase;
use Tests\Unit\Dummies\MiddlewareDummy;

class ProducerConfigManagerTest extends LaravelTestCase
{
    public function testShouldSetTheProducerConfig(): void
    {
        $config = [
            'middlewares' => [MiddlewareDummy::class],
            'handler' => AbstractProducer::class,
            'broker' => [
                'default' => [
                    'connections' => 'kafka:9092',
                ],
            ],
            'topic_id' => 'kafka-test',
        ];

        $configManager = new ProducerConfigManager();

        $expected = [
            'handler' => AbstractProducer::class,
            'broker' => [
                'default' => [
                    'connections' => 'kafka:9092',
                ],
            ],
            'topic_id' => 'kafka-test',
        ];

        // Actions
        $configManager->set($config);

        // Expectations
        $this->assertSame($expected, $configManager->get());
        $this->assertInstanceOf(
            MiddlewareDummy::class,
            current($configManager->middlewares())
        );
    }
}

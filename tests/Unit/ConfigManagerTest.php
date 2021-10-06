<?php

namespace Tests\Unit;

use Metamorphosis\ConfigManager;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;
use Mockery as m;
use Tests\LaravelTestCase;

class ConfigManagerTest extends LaravelTestCase
{
    public function testShouldOverrideConfig(): void
    {
        // Set
        $handler = $this->instance(AbstractHandler::class, m::mock(AbstractHandler::class));
        $config = [
            'middlewares' => [],
            'handler' => AbstractHandler::class,
            'broker' => [
                'default' => [
                    'connections' => 'kafka:9092',
                ],
            ],
            'topic_id' => 'kafka-test',
        ];
        $overrideConfig = [
            'topic_id' => 'kafka-override',
        ];

        $configManager = new ConfigManager();

        // Expectations
        $handler->expects()
            ->getConfigOptions()
            ->andReturn($overrideConfig);

        // Actions
        $configManager->set($config);

        // Expectations
        $this->assertSame('kafka-override', $configManager->get('topic_id'));
    }
}

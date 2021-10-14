<?php
namespace Tests\Unit;

use Metamorphosis\ConfigManager;
use Metamorphosis\TopicHandler\ConfigOptions;
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
        $configOptions = new ConfigOptions(
            'kafka-override',
            ['connections' => 'kafka:9092'],
            1,
            [],
            [],
            200,
            false,
            true,
            200,
            1
        );

        $configManager = new ConfigManager();

        // Expectations
        $handler->expects()
            ->getConfigOptions()
            ->andReturn($configOptions);

        // Actions
        $configManager->set($config);

        // Expectations
        $this->assertSame('kafka-override', $configManager->get('topic_id'));
    }
}

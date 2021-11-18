<?php
namespace Tests\Unit;

use Metamorphosis\ConsumerConfigManager;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;
use Mockery as m;
use Tests\LaravelTestCase;
use Tests\Unit\Dummies\MiddlewareDummy;

class ConsumerConfigManagerTest extends LaravelTestCase
{
    public function testShouldOverrideConsumerConfigManager(): void
    {
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
        $broker = new Broker('kafka:9092', new None());
        $configOptions = new ConsumerConfigOptions(
            'kafka-override',
            $broker,
            '\App\Kafka\Consumers\ConsumerExample',
            null,
            null,
            'default',
            null,
            [MiddlewareDummy::class],
            200,
            false
        );

        $expected = [
            'topic_id' => 'kafka-override',
            'connections' => 'kafka:9092',
            'auth' => null,
            'timeout' => 1000,
            'handler' => '\App\Kafka\Consumers\ConsumerExample',
            'partition' => -1,
            'consumer_group' => 'default',
            'auto_commit' => false,
            'commit_async' => true,
            'offset_reset' => 'smallest',
            'times' => 2,
        ];

        $configManager = new ConsumerConfigManager();

        $commandConfig = [
            'timeout' => 1000,
            'times' => 2,
        ];

        // Expectations
        $handler->expects()
            ->getConfigOptions()
            ->andReturn($configOptions);

        // Actions
        $configManager->set($config, $commandConfig);

        // Expectations
        $this->assertEquals($expected, $configManager->get());
    }
}

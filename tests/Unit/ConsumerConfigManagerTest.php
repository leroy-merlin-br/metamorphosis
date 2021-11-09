<?php
namespace Tests\Unit;

use Metamorphosis\ConsumerConfigManager;
use Metamorphosis\TopicHandler\ConfigOptions;
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
        $configOptions = new ConfigOptions(
            'kafka-override',
            ['connections' => 'kafka:9092'],
            '\App\Kafka\Consumers\ConsumerExample',
            null,
            'default',
            [],
            [MiddlewareDummy::class],
            200,
            false,
            true,
            200,
            1,
            false,
            true
        );

        $expected = [
            'topic_id' => 'kafka-override',
            'connections' => 'kafka:9092',
            'auth' => null,
            'timeout' => 1000,
            'is_async' => false,
            'handler' => '\App\Kafka\Consumers\ConsumerExample',
            'partition' => -1,
            'consumer_group' => 'default',
            'required_acknowledgment' => true,
            'max_poll_records' => 200,
            'flush_attempts' => 1,
            'url' => null,
            'ssl_verify' => null,
            'request_options' => null,
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
        $this->assertSame($expected, $configManager->get());
    }
}

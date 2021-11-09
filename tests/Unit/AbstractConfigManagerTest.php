<?php
namespace Tests\Unit;

use Metamorphosis\ConsumerConfigManager;
use Metamorphosis\TopicHandler\ConfigOptions;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;
use Mockery as m;
use Tests\LaravelTestCase;
use Tests\Unit\Dummies\MiddlewareDummy;

class AbstractConfigManagerTest extends LaravelTestCase
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
            'timeout' => 200,
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
        ];

        $configManager = new ConsumerConfigManager();

        // Expectations
        $handler->expects()
            ->getConfigOptions()
            ->andReturn($configOptions);

        // Actions
        $configManager->set($config);

        // Expectations
        $this->assertSame($expected, $configManager->get());
    }
}

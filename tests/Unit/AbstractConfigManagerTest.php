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
            false,
            true,
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

<?php
namespace Tests\Unit\Connectors\Consumer;

use Metamorphosis\Connectors\Consumer\Config;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;
use Mockery as m;
use Tests\LaravelTestCase;
use Tests\Unit\Dummies\ConsumerHandlerDummy;

class ConfigTest extends LaravelTestCase
{
    public function testShouldValidateConsumerConfig(): void
    {
        // Set
        config(['kafka.topics.default.consumer.consumer_groups.test-consumer-group.handler' => ConsumerHandlerDummy::class]);
        $consumerHandler = $this->instance(ConsumerHandlerDummy::class, m::mock(ConsumerHandlerDummy::class));
        $configOptions = m::mock(ConsumerConfigOptions::class);

        $config = new Config();
        $options = [
            'partition' => 0,
            'offset' => 0,
            'broker' => 'default',
        ];
        $arguments = [
            'topic' => 'default',
            'consumer_group' => 'test-consumer-group',
        ];

        $expected = [
            'topic' => 'default',
            'topic_id' => 'kafka-override',
            'broker' => 'default',
            'offset_reset' => 'earliest',
            'offset' => 0,
            'partition' => 0,
            'handler' => 'Tests\Unit\Dummies\ConsumerHandlerDummy',
            'timeout' => 20000,
            'consumer_group' => 'test-consumer-group',
            'connections' => 'kafka:9092',
            'auth' => [
                'type' => 'ssl',
                'ca' => storage_path('ca.pem'),
                'certificate' => storage_path('kafka.cert'),
                'key' => storage_path('kafka.key'),
            ],
            'url' => '',
            'request_options' => [],
        ];

        // Expectations
        $consumerHandler->expects()
            ->getConfigOptions()
            ->andReturn($configOptions);

        $configOptions->expects()
            ->toArray()
            ->andReturn([
                'topic' => 'default',
                'topic_id' => 'kafka-override',
                'broker' => 'default',
                'offset_reset' => 'earliest',
                'offset' => 0,
                'partition' => 0,
                'handler' => 'Tests\Unit\Dummies\ConsumerHandlerDummy',
                'timeout' => 20000,
                'consumer_group' => 'test-consumer-group',
                'connections' => 'kafka:9092',
                'auth' => [
                    'type' => 'ssl',
                    'ca' => storage_path('ca.pem'),
                    'certificate' => storage_path('kafka.cert'),
                    'key' => storage_path('kafka.key'),
                ],
                'url' => '',
                'request_options' => [],
            ]);

        // Actions
        $configManager = $config->make($options, $arguments);

        // Assertions
        $this->assertArraySubset($expected, $configManager->get());
    }

    public function testShouldNotSetRuntimeConfigWhenOptionsIsInvalid(): void
    {
        // Set
        $config = new Config();
        $options = [
            'partition' => 'one',
            'offset' => 0,
            'broker' => 'default',
        ];
        $arguments = [
            'topic' => 'default',
            'consumer_group' => 'default',
        ];

        // Actions
        $this->expectException(ConfigurationException::class);
        $configManager = $config->make($options, $arguments);

        // Assertions
        $this->assertEmpty($configManager->get());
    }

    public function testShouldNotSetRuntimeConfigWhenKafkaConfigIsInvalid(): void
    {
        // Set
        config(['kafka.brokers.default.connections' => null]);
        $config = new Config();
        $options = [
            'partition' => 0,
            'offset' => 0,
            'broker' => 'default',
        ];
        $arguments = [
            'topic' => 'default',
            'consumer_group' => 'default',
        ];

        // Actions
        $this->expectException(ConfigurationException::class);
        $configManager = $config->make($options, $arguments);

        // Assertions
        $this->assertEmpty($configManager->get());
    }
}

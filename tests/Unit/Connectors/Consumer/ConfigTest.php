<?php
namespace Tests\Unit\Connectors\Consumer;

use Metamorphosis\Connectors\Consumer\Config;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\Facades\Manager;
use Tests\LaravelTestCase;

class ConfigTest extends LaravelTestCase
{
    public function testShouldValidateConsumerConfig(): void
    {
        // Set
        $validate = new Config();
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
            'topic_id' => 'kafka-test',
            'broker' => 'default',
            'offset_reset' => 'largest',
            'offset' => 0,
            'partition' => 0,
            'handler' => '\App\Kafka\Consumers\ConsumerExample',
            'timeout' => 20000,
            'consumer_group' => 'test-consumer-group',
            'connections' => 'kafka:9092',
            'auth' => [
                'type' => 'ssl',
                'ca' => storage_path('ca.pem'),
                'certificate' => storage_path('kafka.cert'),
                'key' => storage_path('kafka.key'),
            ],
        ];

        // Actions
        $validate->setOption($options, $arguments);

        // Assertions
        $this->assertArraySubset($expected, Manager::get());
    }

    public function testShouldNotSetRuntimeConfigWhenOptionsIsInvalid(): void
    {
        // Set
        $validate = new Config();
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
        $validate->setOption($options, $arguments);

        // Assertions
        $this->assertEmpty(Manager::get());
    }

    public function testShouldNotSetRuntimeConfigWhenKafkaConfigIsInvalid(): void
    {
        // Set
        config(['kafka.brokers.default.connections' => null]);
        $validate = new Config();
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
        $validate->setOption($options, $arguments);

        // Assertions
        $this->assertEmpty(Manager::get());
    }
}

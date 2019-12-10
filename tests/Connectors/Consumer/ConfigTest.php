<?php
namespace Tests\Connectors\Consumer;

use Metamorphosis\Connectors\Consumer\Config;
use Metamorphosis\Exceptions\ConfigurationException;
use Tests\LaravelTestCase;

class ConfigTest extends LaravelTestCase
{
    public function testShouldValidateConsumerConfig()
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
            'consumer-group' => 'default'
        ];

        $expected = [
            'topic' => 'default',
            'broker' => 'default',
            'offset-reset' => 'largest',
            'offset' => 0,
            'partition' => 0,
            'handler' => '\App\Kafka\Consumers\ConsumerExample',
            'timeout' => 20000,
            'consumer-group' => 'default',
            'connections' => 'kafka:6680',
            'schemaUri' => '',
            'auth' => [
                'type' => 'ssl',
                'ca' => '/application/vendor/orchestra/testbench-core/src/Concerns/../../laravel/storage/ca.pem',
                'certificate' => '/application/vendor/orchestra/testbench-core/src/Concerns/../../laravel/storage/kafka.cert',
                'key' => '/application/vendor/orchestra/testbench-core/src/Concerns/../../laravel/storage/kafka.key',
            ],
        ];

        // Actions
        $validate->setOptionConfig($options, $arguments);

        // Assertions
        $this->assertArraySubset($expected, config('kafka.runtime'));
    }

    public function testShouldNotSetRuntimeConfigWhenOptionsIsInvalid()
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
            'consumer-group' => 'default'
        ];

        // Actions
        $this->expectException(ConfigurationException::class);
        $validate->setOptionConfig($options, $arguments);

        // Assertions
        $this->assertEmpty(config('kafka.runtime'));
    }

    public function testShouldNotSetRuntimeConfigWhenKafkaConfigIsInvalid()
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
            'consumer-group' => 'default'
        ];

        // Actions
        $this->expectException(ConfigurationException::class);
        $validate->setOptionConfig($options, $arguments);

        // Assertions
        $this->assertEmpty(config('kafka.runtime'));
    }
}

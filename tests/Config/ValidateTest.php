<?php
namespace Tests\Config;

use Metamorphosis\Config\Validate;
use Metamorphosis\Exceptions\ConfigurationException;
use Tests\LaravelTestCase;

class ValidateTest extends LaravelTestCase
{
    public function testShouldValidateConsumerConfig()
    {
        // Set
        $validate = new Validate();
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
            'isAvroSchema' => false,
            'offset-reset' => 'largest',
            'offset' => 0,
            'partition' => 0,
            'handle' => '\App\Kafka\Consumers\ConsumerExample',
            'timeout' => 20000,
            'consumerGroupId' => 'default',
            'connections' => 'kafka:6680',
            'schemaUri' => '',
            'auth' => [
                'protocol' => 'ssl',
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
        $validate = new Validate();
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
        $validate = new Validate();
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

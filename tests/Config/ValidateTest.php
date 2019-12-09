<?php
namespace Tests\Config;

use Metamorphosis\Config\Validate;
use Tests\LaravelTestCase;

class ValidateTest extends LaravelTestCase
{
    public function testShouldValidateConsumerConfig()
    {
        // Set
        $validate = new Validate();
        $option = [
            'partition' => 0,
            'offset' => 0,
            'broker' => 'default',
        ];
        $argument = [
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
        $validate->setOptionConfig($option, $argument);

        // Assertions
        $this->assertArraySubset($expected, config('kafka.runtime'));
    }
}

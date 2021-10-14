<?php
namespace Tests\Unit\TopicHandler;

use Metamorphosis\TopicHandler\ConfigOptionsFactory;
use Tests\LaravelTestCase;

class ConfigOptionsFactoryTest extends LaravelTestCase
{
    public function testShouldMakeConfigOptionWithAvroSchema(): void
    {
        // Set
        $factory = new ConfigOptionsFactory();

        $expected = [
            'topic_id' => 'kafka-test',
            'connections' => 'kafka:9092',
            'auth' => [
                'type' => 'ssl',
                'ca' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/ca.pem',
                'certificate' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/kafka.cert',
                'key' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/kafka.key',
            ],
            'timeout' => 1000,
            'is_async' => true,
            'partition' => 0,
            'required_acknowledgment' => false,
            'max_poll_records' => 500,
            'flush_attempts' => 10,
            'middlewares' => [],
            'avro_schema' => [
                'url' => '',
                'ssl_verify' => true,
                'request_options' => [
                    'headers' => [
                        'Authorization' => [
                            'Basic Og==',
                        ],
                    ],
                ],
            ],
        ];
        // Actions
        $result = $factory->makeByConfigNameWithSchema(
            'kafka',
            'default',
            'default',
            'default'
        );

        // Assertions
        $this->assertSame($expected, $result->toArray());
    }

    public function testShouldMakeConfigOptionWithoutAvro(): void
    {
        // Set
        config(['kafka.brokers.new' => ['connections' => 'localhost:9092']]);
        $factory = new ConfigOptionsFactory();

        $expected = [
            'topic_id' => 'kafka-test',
            'connections' => 'localhost:9092',
            'auth' => null,
            'timeout' => 1000,
            'is_async' => true,
            'partition' => 0,
            'required_acknowledgment' => false,
            'max_poll_records' => 500,
            'flush_attempts' => 10,
            'middlewares' => [],
            'avro_schema' => [],
        ];

        // Actions
        $result = $factory->makeByConfigName(
            'kafka',
            'default',
            'new'
        );

        // Assertions
        $this->assertSame($expected, $result->toArray());
    }
}

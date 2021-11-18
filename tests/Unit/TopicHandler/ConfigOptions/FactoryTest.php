<?php
namespace Tests\Unit\TopicHandler\ConfigOptions;

use Metamorphosis\TopicHandler\ConfigOptions\Factory;
use Tests\LaravelTestCase;

class FactoryTest extends LaravelTestCase
{
    public function testShouldMakeConfigOptionWithAvroSchema(): void
    {
        // Set
        $factory = new Factory();

        $expected = [
            'topic_id' => 'kafka-test',
            'connections' => 'kafka:9092',
            'auth' => [
                'type' => 'ssl',
                'ca' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/ca.pem',
                'certificate' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/kafka.cert',
                'key' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/kafka.key',
            ],
            'timeout' => 20000,
            'handler' => '\App\Kafka\Consumers\ConsumerExample',
            'partition' => 0,
            'offset' => 0,
            'consumer_group' => 'test-consumer-group',
            'middlewares' => [],
            'url' => '',
            'ssl_verify' => true,
            'request_options' => [
                'headers' => [
                    'Authorization' => [
                        'Basic Og==',
                    ],
                ],
            ],
            'auto_commit' => true,
            'commit_async' => true,
            'offset_reset' => 'earliest',
        ];
        // Actions
        $result = $factory->makeConsumerConfigOptions(
            config('kafka.brokers.default'),
            config('kafka.topics.default'),
            config('kafka.avro_schemas.default')
        );

        // Assertions
        $this->assertEquals($expected, $result->toArray());
    }

    public function testShouldMakeConfigOptionWithoutAvro(): void
    {
        // Set
        config(['kafka.brokers.new' => ['connections' => 'localhost:9092']]);
        $factory = new Factory();

        $expected = [
            'topic_id' => 'kafka-test',
            'connections' => 'localhost:9092',
            'auth' => null,
            'timeout' => 20000,
            'handler' => '\App\Kafka\Consumers\ConsumerExample',
            'partition' => 0,
            'offset' => 0,
            'consumer_group' => 'test-consumer-group',
            'middlewares' => [],
            'auto_commit' => true,
            'commit_async' => true,
            'offset_reset' => 'earliest',
        ];

        // Actions
        $result = $factory->makeConsumerConfigOptions(
            config('kafka.brokers.new'),
            config('kafka.topics.default')
        );

        // Assertions
        $this->assertEquals($expected, $result->toArray());
    }

    public function testShouldMakeProducerConfigOptions(): void
    {
        // Set
        $factory = new Factory();

        $expected = [
            'topic_id' => 'kafka-test',
            'connections' => 'kafka:9092',
            'auth' => [
                'type' => 'ssl',
                'ca' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/ca.pem',
                'certificate' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/kafka.cert',
                'key' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/kafka.key',
            ],
            'timeout' => 10000,
            'is_async' => true,
            'partition' => RD_KAFKA_PARTITION_UA,
            'required_acknowledgment' => true,
            'max_poll_records' => 500,
            'flush_attempts' => 10,
            'middlewares' => [],
        ];

        // Actions
        $result = $factory->makeProducerConfigOptions(
            config('kafka.brokers.default'),
            config('kafka.topics.default')
        );

        // Assertions
        $this->assertEquals($expected, $result->toArray());
    }
}

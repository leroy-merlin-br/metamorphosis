<?php
namespace Tests\Unit\TopicHandler\ConfigOptions\Factories;

use Metamorphosis\TopicHandler\ConfigOptions\Consumer;
use Metamorphosis\TopicHandler\ConfigOptions\Factories\ConsumerFactory;
use Tests\LaravelTestCase;

class ConsumerFactoryTest extends LaravelTestCase
{
    public function testShouldMakeConfigOptionWithAvroSchema(): void
    {
        // Set
        $brokerData = [
            'connections' => 'kafka:9092',
            'auth' => [
                'type' => 'ssl',
                'ca' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/ca.pem',
                'certificate' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/kafka.cert',
                'key' => '/var/www/html/vendor/orchestra/testbench-core/laravel/storage/kafka.key',
            ],
        ];
        $avroSchemaData = [
            'url' => '',
            'ssl_verify' => true,
            'request_options' => [
                'headers' => [
                    'Authorization' => [
                        'Basic Og==',
                    ],
                ],
            ],
        ];
        $topicData = [
            'topic_id' => 'kafka-test',
            'consumer' => [
                'consumer_group' => 'test-consumer-group',
                'middlewares' => [],
                'auto_commit' => true,
                'commit_async' => true,
                'offset_reset' => 'earliest',
                'handler' => '\App\Kafka\Consumers\ConsumerExample',
                'partition' => 0,
                'offset' => 0,
                'timeout' => 20000,
            ],
        ];
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
        $result = ConsumerFactory::make($brokerData, $topicData, $avroSchemaData);

        // Assertions
        $this->assertInstanceOf(Consumer::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }
}

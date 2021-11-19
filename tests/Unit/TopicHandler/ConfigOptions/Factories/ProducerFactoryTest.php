<?php
namespace Tests\Unit\TopicHandler\ConfigOptions\Factories;

use Metamorphosis\TopicHandler\ConfigOptions\Factories\ProducerFactory;
use Metamorphosis\TopicHandler\ConfigOptions\Producer;
use Tests\LaravelTestCase;

class ProducerFactoryTest extends LaravelTestCase
{
    public function testShouldMakeProducerConfigOptions(): void
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
        $avroSchemaData = [];
        $topicData = [
            'topic_id' => 'kafka-test',
            'producer' => [
                'timeout' => 10000,
                'is_async' => true,
                'partition' => RD_KAFKA_PARTITION_UA,
                'required_acknowledgment' => true,
                'max_poll_records' => 500,
                'flush_attempts' => 10,
                'middlewares' => [],
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
            'timeout' => 10000,
            'is_async' => true,
            'partition' => RD_KAFKA_PARTITION_UA,
            'required_acknowledgment' => true,
            'max_poll_records' => 500,
            'flush_attempts' => 10,
            'middlewares' => [],
        ];

        // Actions
        $result = ProducerFactory::make($brokerData, $topicData, $avroSchemaData);

        // Assertions
        $this->assertInstanceOf(Producer::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }
}

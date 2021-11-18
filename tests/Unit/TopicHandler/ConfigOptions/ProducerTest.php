<?php
namespace Tests\Unit\TopicHandler\ConfigOptions;

use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Producer;
use Tests\LaravelTestCase;

class ProducerTest extends LaravelTestCase
{
    public function testShouldConvertConfigOptionsToArray(): void
    {
        // Set
        $broker = new Broker('kafka:9092', new None());
        $configOptions = new Producer(
            'topic-id',
            $broker,
            null,
            null,
            [],
            4000,
            false,
            true,
            500,
            10
        );

        $expected = [
            'topic_id' => 'topic-id',
            'connections' => 'kafka:9092',
            'auth' => null,
            'timeout' => 4000,
            'is_async' => false,
            'partition' => RD_KAFKA_PARTITION_UA,
            'middlewares' => [],
            'required_acknowledgment' => true,
            'max_poll_records' => 500,
            'flush_attempts' => 10,
        ];

        // Actions
        $result = $configOptions->toArray();

        // Expectations
        $this->assertEquals($expected, $result);
    }
}

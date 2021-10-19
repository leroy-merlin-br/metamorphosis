<?php
namespace Tests\Unit\TopicHandler;

use Metamorphosis\TopicHandler\ConfigOptions;
use Tests\LaravelTestCase;
use Tests\Unit\Dummies\ProducerHandlerDummy;

class ConfigOptionsTest extends LaravelTestCase
{
    public function testShouldConvertConfigOptionsToArray(): void
    {
        // Set
        $configOptions = new ConfigOptions(
            'topic-id',
            ['connections' => 'kafka:9092'],
            ProducerHandlerDummy::class,
            null,
            'some_consumer_group',
            [],
            [],
            200,
            false,
            true,
            200,
            1
        );

        $expected = [
            'topic_id' => 'topic-id',
            'connections' => 'kafka:9092',
            'auth' => null,
            'timeout' => 200,
            'is_async' => false,
            'handler' => 'Tests\Unit\Dummies\ProducerHandlerDummy',
            'partition' => RD_KAFKA_PARTITION_UA,
            'consumer_group' => 'some_consumer_group',
            'required_acknowledgment' => true,
            'max_poll_records' => 200,
            'flush_attempts' => 1,
            'middlewares' => [],
            'avro_schema' => [],
            'auto_commit' => true,
            'commit_async' => true,
            'offset_reset' => 'smallest',
        ];

        // Actions
        $result = $configOptions->toArray();

        // Expectations
        $this->assertSame($expected, $result);
    }
}

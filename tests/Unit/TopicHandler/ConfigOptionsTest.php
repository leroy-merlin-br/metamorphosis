<?php
namespace Tests\Unit\TopicHandler;

use Metamorphosis\TopicHandler\ConfigOptions;
use Tests\LaravelTestCase;

class ConfigOptionsTest extends LaravelTestCase
{
    public function testShouldConvertConfigOptionsToArray(): void
    {
        // Set
        $configOptions = new ConfigOptions(
            'topic-id',
            ['connections' => 'kafka:9092'],
            1,
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
            'partition' => 1,
            'required_acknowledgment' => true,
            'max_poll_records' => 200,
            'flush_attempts' => 1,
            'middlewares' => [],
            'avro_schema' => [],
        ];

        // Actions
        $result = $configOptions->toArray();

        // Expectations
        $this->assertSame($expected, $result);
    }
}

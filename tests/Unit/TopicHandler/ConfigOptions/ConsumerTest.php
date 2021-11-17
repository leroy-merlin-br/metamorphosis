<?php
namespace Tests\Unit\TopicHandler\ConfigOptions;

use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer;
use Tests\LaravelTestCase;
use Tests\Unit\Dummies\ProducerHandlerDummy;

class ConsumerTest extends LaravelTestCase
{
    public function testShouldConvertConfigOptionsToArray(): void
    {
        // Set
        $broker = new Broker('kafka:9092', new None());
        $configOptions = new Consumer(
            'topic-id',
            $broker,
            ProducerHandlerDummy::class,
            null,
            null,
            'some_consumer_group',
            null,
            [],
            200,
            false,
            true,
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
            'middlewares' => [],
            'url' => null,
            'ssl_verify' => null,
            'request_options' => null,
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

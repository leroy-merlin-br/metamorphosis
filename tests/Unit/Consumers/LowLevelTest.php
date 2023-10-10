<?php

namespace Tests\Unit\Consumers;

use Metamorphosis\Consumers\LowLevel;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\AvroSchema as AvroSchemaConfigOptions;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;
use Mockery as m;
use RdKafka\ConsumerTopic;
use RdKafka\Message;
use Tests\LaravelTestCase;

class LowLevelTest extends LaravelTestCase
{
    public function testItShouldConsume(): void
    {
        // Set
        $timeout = 2;
        $partition = 3;

        $brokerOptions = new Broker('kafka:9092', new None());
        $consumerConfigOptions = new ConsumerConfigOptions(
            'kafka-test',
            $brokerOptions,
            null,
            $partition,
            null,
            '',
            new AvroSchemaConfigOptions('http://url.teste'),
            [],
            $timeout
        );

        $consumerTopic = m::mock(ConsumerTopic::class);
        $message = new Message();

        $lowLevelConsumer = new LowLevel(
            $consumerTopic,
            $consumerConfigOptions
        );

        // Expectations
        $consumerTopic->expects()
            ->consume($partition, $timeout)
            ->andReturn($message);

        // Actions
        $message = $lowLevelConsumer->consume();

        // Assertions
        $this->assertInstanceOf(Message::class, $message);
    }
}

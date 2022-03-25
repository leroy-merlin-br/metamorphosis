<?php

namespace Tests\Unit\Producer;

use Metamorphosis\Producer\Poll;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\AvroSchema as AvroSchemaConfigOptions;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ProducerConfigOptions;
use Mockery as m;
use RdKafka\Producer as KafkaProducer;
use RuntimeException;
use Tests\LaravelTestCase;

class PollTest extends LaravelTestCase
{
    public function testItShouldHandleMessageWithoutAcknowledgment(): void
    {
        // Set
        $broker = new Broker('kafka:9092', new None());
        $producerConfigOptions = new ProducerConfigOptions(
            'topic_name',
            $broker,
            null,
            new AvroSchemaConfigOptions('string', []),
            [],
            4000,
            true,
            false,
            500,
            10
        );
        $kafkaProducer = m::mock(KafkaProducer::class);
        $poll = new Poll($kafkaProducer, $producerConfigOptions);

        // Expectations
        $kafkaProducer->expects()
            ->poll(0);

        $kafkaProducer->shouldReceive('flush')
            ->never();

        // Actions
        $poll->handleResponse();
    }

    public function testShouldThrowExceptionWhenFlushFailed(): void
    {
        // Set
        $broker = new Broker('kafka:9092', new None());
        $producerConfigOptions = new ProducerConfigOptions(
            'topic_name',
            $broker,
            null,
            new AvroSchemaConfigOptions('string', []),
            [],
            100,
            false,
            true,
            500,
            10
        );
        $kafkaProducer = m::mock(KafkaProducer::class);
        $poll = new Poll($kafkaProducer, $producerConfigOptions);

        // Expectations
        $kafkaProducer->expects()
            ->poll(0);

        $kafkaProducer->expects()
            ->flush(1000)
            ->times(3)
            ->andReturn(1);

        $this->expectException(RuntimeException::class);

        // Actions
        $poll->handleResponse();
    }

    public function testItShouldHandleResponseEveryTimeWhenAsyncModeIsTrue(): void
    {
        // Set
        $broker = new Broker('kafka:9092', new None());
        $producerConfigOptions = new ProducerConfigOptions(
            'topic_name',
            $broker,
            null,
            new AvroSchemaConfigOptions('string', []),
            [],
            4000,
            false,
            true,
            10,
            500
        );

        $kafkaProducer = m::mock(KafkaProducer::class);
        $poll = new Poll($kafkaProducer, $producerConfigOptions);

        // Expectations
        $kafkaProducer->expects()
            ->poll(0)
            ->times(3);

        $kafkaProducer->expects()
            ->flush(4000)
            ->times(3)
            ->andReturn(0);

        // Actions
        $poll->handleResponse();
        $poll->handleResponse();
        $poll->handleResponse();
    }
}

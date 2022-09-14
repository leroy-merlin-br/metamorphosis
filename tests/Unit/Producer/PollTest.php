<?php
namespace Tests\Unit\Producer;

use Metamorphosis\Producer\Poll;
use Metamorphosis\ProducerConfigManager;
use Mockery as m;
use RdKafka\Producer as KafkaProducer;
use RuntimeException;
use Tests\LaravelTestCase;

class PollTest extends LaravelTestCase
{
    public function testItShouldHandleMessageWithoutAcknowledgment(): void
    {
        // Set
        $configManager = new ProducerConfigManager();
        $configManager->set([
            'topic_id' => 'topic_name',
            'timeout' => 4000,
            'is_async' => true,
            'max_poll_records' => 500,
            'flush_attempts' => 10,
            'required_acknowledgment' => false,
        ]);
        $kafkaProducer = m::mock(KafkaProducer::class);
        $poll = new Poll($kafkaProducer, $configManager);

        // Expectations
        $kafkaProducer->shouldReceive('flush')
            ->never();

        // Actions
        $poll->handleResponse();
    }

    public function testShouldThrowExceptionWhenFlushFailed(): void
    {
        // Set
        $configManager = new ProducerConfigManager();
        $configManager->set([
            'topic_id' => 'topic_name',
            'timeout' => 4000,
            'is_async' => false,
            'max_poll_records' => 500,
            'flush_attempts' => 10,
            'required_acknowledgment' => true,
            'partition' => 0,
        ]);

        $kafkaProducer = m::mock(KafkaProducer::class);
        $poll = new Poll($kafkaProducer, $configManager);

        // Expectations
        $kafkaProducer->expects('flush')
            ->with(4000)
            ->times(10)
            ->andReturn(1);

        $this->expectException(RuntimeException::class);

        // Actions
        $poll->handleResponse();
    }

    public function testItShouldHandleResponseEveryTimeWhenAsyncModeIsTrue(): void
    {
        // Set
        $configManager = new ProducerConfigManager();
        $configManager->set([
            'topic_id' => 'topic_name',
            'timeout' => 4000,
            'is_async' => false,
            'max_poll_records' => 500,
            'flush_attempts' => 10,
            'required_acknowledgment' => true,
            'partition' => 0,
        ]);
        $kafkaProducer = m::mock(KafkaProducer::class);
        $poll = new Poll($kafkaProducer, $configManager);

        // Expectations
        $kafkaProducer->expects('flush')
            ->with(4000)
            ->times(3)
            ->andReturn(0);

        // Actions
        $poll->handleResponse();
        $poll->handleResponse();
        $poll->handleResponse();
    }
}

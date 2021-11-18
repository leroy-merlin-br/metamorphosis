<?php
namespace Tests\Unit\Middlewares\Handler;

use Closure;
use Metamorphosis\Middlewares\Handler\Producer;
use Metamorphosis\Producer\Poll;
use Metamorphosis\ProducerConfigManager;
use Metamorphosis\Record\ProducerRecord;
use Mockery as m;
use RdKafka\ProducerTopic as KafkaTopicProducer;
use Tests\LaravelTestCase;

class ProducerTest extends LaravelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $configManager = new ProducerConfigManager();
        $configManager->set([
            'topic_id' => 'topic_name',
            'timeout' => 4000,
            'is_async' => true,
            'max_poll_records' => 500,
            'flush_attempts' => 10,
            'required_acknowledgment' => true,
            'partition' => 0,
        ]);
    }

    public function testItShouldSendMessageToKafkaBroker(): void
    {
        // Set
        $poll = m::mock(Poll::class);
        $closure = Closure::fromCallable(function () {
        });
        $producerTopic = m::mock(KafkaTopicProducer::class);

        $record = json_encode(['message' => 'original record']);
        $record = new ProducerRecord($record, 'topic_key');

        // Expectations
        $poll->expects()
            ->handleResponse();

        $poll->expects()
            ->flushMessage();

        $producerTopic->expects()
            ->produce(1, 0, $record->getPayload(), null);

        // Actions
        $producerHandler = new Producer($producerTopic, $poll, 1);
        $producerHandler->process($record, $closure);
    }
}

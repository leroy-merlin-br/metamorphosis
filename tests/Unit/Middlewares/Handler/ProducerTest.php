<?php
namespace Tests\Unit\Middlewares\Handler;

use Closure;
use Metamorphosis\Middlewares\Handler\Producer;
use Metamorphosis\Producer\Poll;
use Metamorphosis\Record\ProducerRecord;
use Mockery as m;
use RdKafka\ProducerTopic as KafkaTopicProducer;
use Tests\LaravelTestCase;

class ProducerTest extends LaravelTestCase
{
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

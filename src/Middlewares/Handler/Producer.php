<?php

namespace Metamorphosis\Middlewares\Handler;

use Closure;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Producer\Poll;
use Metamorphosis\Record\RecordInterface;
use RdKafka\ProducerTopic;

class Producer implements MiddlewareInterface
{
    private int $partition;

    private ProducerTopic $topic;

    private Poll $poll;

    public function __construct(ProducerTopic $topic, Poll $poll, int $partition)
    {
        $this->topic = $topic;
        $this->poll = $poll;
        $this->partition = $partition;
    }

    public function __destruct()
    {
        $this->poll->flushMessage();
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function process(RecordInterface $record, Closure $next): void
    {
        $this->topic->produce(
            $this->getPartition($record),
            0,
            $record->getPayload(),
            $record->getKey()
        );

        $this->poll->handleResponse();
    }

    public function getPartition(RecordInterface $record): int
    {
        return is_null(
            $record->getPartition()
        )
            ? $this->partition
            : $record->getPartition();
    }
}

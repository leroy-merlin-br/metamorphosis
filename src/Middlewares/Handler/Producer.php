<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Producer\Poll;
use Metamorphosis\Record\RecordInterface;
use RdKafka\ProducerTopic;

class Producer implements MiddlewareInterface
{
    /**
     * @var int
     */
    private $partition;

    /**
     * @var \RdKafka\ProducerTopic
     */
    private $topic;

    /**
     * @var Poll
     */
    private $poll;

    public function __construct(ProducerTopic $topic, Poll $poll, int $partition)
    {
        $this->topic = $topic;
        $this->poll = $poll;
        $this->partition = $partition;
    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $this->topic->produce($this->getPartition($record), 0, $record->getPayload(), $record->getKey());

        $this->poll->handleResponse();
    }

    public function __destruct()
    {
        $this->poll->flushMessage();
    }

    public function getPartition(RecordInterface $record): int
    {
        return is_null($record->getPartition()) ? $this->partition : $record->getPartition();
    }
}

<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Producer\Pool;
use Metamorphosis\Record\RecordInterface;
use RdKafka\ProducerTopic;

class Producer implements MiddlewareInterface
{
    /**
     * @var int
     */
    private $partition;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var \RdKafka\ProducerTopic
     */
    private $topic;

    /**
     * @var Pool
     */
    private $pool;

    public function __construct(ProducerTopic $topic, Pool $pool, int $partition)
    {
        $this->topic = $topic;
        $this->pool = $pool;
        $this->partition = $partition;
    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $this->topic->produce($this->getPartition($record), 0, $record->getPayload(), $record->getKey());

        $this->pool->handleResponse();
    }

    public function __destruct()
    {
        $this->pool->flushMessage();
    }

    public function getPartition(RecordInterface $record): int
    {
        return is_null($record->getPartition()) ? $this->partition : $record->getPartition();
    }
}

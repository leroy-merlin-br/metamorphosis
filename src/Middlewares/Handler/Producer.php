<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Facades\Manager;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;
use RuntimeException;

class Producer implements MiddlewareInterface
{
    const MAX_POLL_RECORDS = 500;

    const FLUSH_ATTEMPTS = 10;

    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var HandlerInterface
     */
    private $producerHandler;

    /**
     * @var \RdKafka\Producer
     */
    private $producer;

    private $processMessageCount = 0;

    public function __construct(Connector $connector, HandlerInterface $producerHandler)
    {
        $this->connector = $connector;
        $this->producerHandler = $producerHandler;

        $this->producer = $this->connector->getProducerTopic($producerHandler);
        $this->topic = $this->producer->newTopic(Manager::get('topic_id'));
    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $this->topic->produce($record->getPartition(), 0, $record->getPayload(), $record->getKey());
        $this->processMessageCount++;

        if ($this->processMessageCount % self::MAX_POLL_RECORDS === 0) {
            $this->pollResponse();
        }
    }

    public function __destruct()
    {
        for ($flushAttempts = 0; $flushAttempts < self::FLUSH_ATTEMPTS; $flushAttempts++) {
            $result = $this->producer->flush(Manager::get('timeout'));
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                return;
            }
        }

        throw new RuntimeException('Was unable to flush, messages might be lost!');
    }

    public function pollResponse(): void
    {
        while ($this->producer->getOutQLen() > 0) {
            $this->producer->poll(Manager::get('timeout'));
        }
    }
}

<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Connectors\Producer\Queue;
use Metamorphosis\Facades\Manager;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Producer\HandleableResponseInterface;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;

class Producer implements MiddlewareInterface
{
    const MAX_POLL_RECORDS = 500;

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

        $this->producer = $this->connector->getProducerTopic($this->producerHandler);
        $this->topic = $this->producer->produce->newTopic(Manager::get('topic_id'));
        $this->connector->handleResponsesFromBroker();
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
        $this->producer->flush(Manager::get('timeout'));
    }

    public function pollResponse(): void
    {
        while ($this->producer->getOutQLen() > 0) {
            $this->producer->poll(Manager::get('timeout'));
        }
    }
}

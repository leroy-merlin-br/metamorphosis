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
     * @var Config
     */
    private $config;

    /**
     * @var \RdKafka\Producer
     */
    private $producer;

    public function __construct(Connector $connector, Config $config, HandlerInterface $producerHandler)
    {
        $this->connector = $connector;
        $this->config = $config;
        $this->producerHandler = $producerHandler;

        $this->config->setOption($this->producerHandler->getTopic());
        $this->producer = $this->connector->getProducerTopic($this->producerHandler);
        $this->topic = $this->producer->produce->newTopic(Manager::get('topic_id'));
        $this->connector->handleResponsesFromBroker();

    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $this->topic->produce($record->getPartition(), 0, $record->getPayload(), $record->getKey());
    }

    public function pollResponse(): void
    {
        while ($this->producer->getOutQLen() > 0) {
            $this->producer->poll(Manager::get('timeout'));
        }
    }

    public function terminateProducer(): void
    {
        $this->producer->flush(Manager::get('timeout'));
    }

    private function canHandleResponse(): bool
    {
        return $this->handler instanceof HandleableResponseInterface;
    }
}

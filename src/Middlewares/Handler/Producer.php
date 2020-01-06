<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Facades\Manager;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;

class Producer implements MiddlewareInterface
{
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

    public function __construct(Connector $connector, Config $config)
    {
        $this->connector = $connector;
        $this->config = $config;
    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $this->config->setOption($record->getTopicName());
        $this->connector->setHandler($this->producerHandler);

        $producer = $this->connector->getProducerTopic();

        $topic = $producer->newTopic(Manager::get('topic_id'));
        $topic->produce($record->getPartition(), 0, $record->getPayload(), $record->getKey());

        $producer->flush(Manager::get('timeout'));
        $this->connector->handleResponsesFromBroker();
    }

    public function setProducerHandler(HandlerInterface $handler)
    {
        $this->producerHandler = $handler;
    }
}

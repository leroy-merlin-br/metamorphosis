<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Connectors\Producer\Connector;
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
        $this->config->setOptionConfig($record->getTopicName());
        $this->connector->setHandler($this->producerHandler);

        $producer = $this->connector->getProducerTopic();

        $producer->produce($record->getPartition(), 0, $record->getPayload(), $record->getKey());

        $this->connector->handleResponsesFromBroker();
    }

    public function setProducerHandler(HandlerInterface $handler)
    {
        $this->producerHandler = $handler;
    }
}

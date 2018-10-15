<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Config\Producer as ProducerConfig;
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

    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $config = app(ProducerConfig::class, ['topic' => $record->getTopicName()]);

        $this->connector->setHandler($this->producerHandler);

        $producer = $this->connector->getProducerTopic($config);

        $producer->produce($record->getPartition(), 0, $record->getPayload(), $record->getKey());

        $this->connector->handleResponsesFromBroker();
    }

    public function setProducerHandler(HandlerInterface $handler)
    {
        $this->producerHandler = $handler;
    }
}

<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Config\Producer as ProducerConfig;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Middlewares\Middleware;
use Metamorphosis\Record\Record;
use Metamorphosis\TopicHandler\Producer\Handler;

class Producer implements Middleware
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var Handler
     */
    private $producerHandler;

    public function __construct(Connector $connector, Handler $producerHandler)
    {
        $this->connector = $connector;
        $this->producerHandler = $producerHandler;
    }

    public function process(Record $record, MiddlewareHandler $handler): void
    {
        $config = app(ProducerConfig::class, ['topic' => $record->getTopicName()]);

        $this->connector->setHandler($this->producerHandler);

        $producer = $this->connector->getProducerTopic($config);

        $producer->produce($record->getPartition(), 0, $record->getPayload(), $record->getKey());

        $this->connector->handleResponsesFromBroker();
    }
}

<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Config\Producer as ProducerConfig;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Middlewares\Middleware;
use Metamorphosis\Record\Record;

class Producer implements Middleware
{
    /**
     * @var Connector
     */
    private $connector;

    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    public function process(Record $record, MiddlewareHandler $handler): void
    {
        $config = app(ProducerConfig::class, ['topic' => $record->getTopicName()]);

        $producer = $this->connector->getProducer($config);

        $producer->produce($record->getPartition(), 0, $record->getPayload(), $record->getKey());
    }
}

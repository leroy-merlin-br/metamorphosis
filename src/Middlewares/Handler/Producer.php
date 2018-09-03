<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Config;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Middlewares\Middleware;
use Metamorphosis\Record\Record;

class Producer implements Middleware
{
    public function process(Record $record, MiddlewareHandler $handler): void
    {
        $config = new Config($record->getTopicName());

        $connector = new Connector($config);
        $producer = $connector->getProducer();

        $producer->produce($record->getPartition(), 0, $record->getPayload(), $record->getKey());
    }
}

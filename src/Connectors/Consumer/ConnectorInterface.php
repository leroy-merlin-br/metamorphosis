<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Consumers\ConsumerInterface;

interface ConnectorInterface
{
    public function getConsumerManager(): Manager;

    public function getConsumer(): ConsumerInterface;
}

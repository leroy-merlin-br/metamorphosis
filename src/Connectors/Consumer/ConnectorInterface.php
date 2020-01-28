<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Consumers\ConsumerInterface;

interface ConnectorInterface
{
    public function getConsumer(): ConsumerInterface;
}

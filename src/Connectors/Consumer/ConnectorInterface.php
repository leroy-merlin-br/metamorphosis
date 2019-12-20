<?php
namespace Metamorphosis\Connectors\Consumer;

use Kafka\Consumer;

interface ConnectorInterface
{
    public function getConsumer(): Consumer;
}

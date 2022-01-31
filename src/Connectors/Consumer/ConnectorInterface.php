<?php

namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConfigOptions;
use Metamorphosis\Consumers\ConsumerInterface;

interface ConnectorInterface
{
    public function getConsumer(bool $autoCommit, ConfigOptions $configOptions): ConsumerInterface;
}

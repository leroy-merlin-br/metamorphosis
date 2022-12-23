<?php

namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\AbstractConfigManager;
use Metamorphosis\Consumers\ConsumerInterface;

interface ConnectorInterface
{
    public function getConsumer(bool $autoCommit, AbstractConfigManager $configManager): ConsumerInterface;
}

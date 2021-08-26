<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\ConfigManager;
use Metamorphosis\Consumers\ConsumerInterface;

interface ConnectorInterface
{
    public function getConsumer(bool $autoCommit, ConfigManager $configManager): ConsumerInterface;
}

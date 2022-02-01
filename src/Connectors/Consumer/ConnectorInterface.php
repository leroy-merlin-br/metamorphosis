<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConfigOptions;

interface ConnectorInterface
{
    public function getConsumer(bool $autoCommit, ConfigOptions $configOptions): ConsumerInterface;
}

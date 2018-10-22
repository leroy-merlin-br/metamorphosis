<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Config\Consumer;

class ConnectorFactory
{
    public static function make(Consumer $config): ConnectorInterface
    {
        if (self::requiresPartition($config)) {
            return new LowLevel($config);
        }

        return new HighLevel($config);
    }

    protected static function requiresPartition(Consumer $config): bool
    {
        return !is_null($config->getConsumerPartition());
    }
}

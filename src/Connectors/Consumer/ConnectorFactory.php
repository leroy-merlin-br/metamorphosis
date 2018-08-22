<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Config;

class ConnectorFactory
{
    public static function make(Config $config): ConnectorInterface
    {
        if (self::requiresPartition($config)) {
            return new LowLevel($config);
        }

        return new HighLevel($config);
    }

    protected static function requiresPartition(Config $config): bool
    {
        return !is_null($config->getPartition());
    }
}

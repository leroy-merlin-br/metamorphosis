<?php
namespace Metamorphosis\Connectors\Consumer;

class ConnectorFactory
{
    public static function make(): ConnectorInterface
    {
        if (self::requiresPartition()) {
            return app(LowLevel::class);
        }

        return app(HighLevel::class);
    }

    protected static function requiresPartition(): bool
    {
        return !is_null(config('kafka.runtime.partition'));
    }
}

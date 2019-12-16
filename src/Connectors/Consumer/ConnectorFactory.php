<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Facades\Manager;

/**
 * This factory will determine what kind of connector will be used.
 * Basically, if the user pass --partition and --offset as argument
 * means that we will use the low level approach.
 */
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
        return !is_null(Manager::get('partition'));
    }
}

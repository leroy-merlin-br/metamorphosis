<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Facades\Manager as ConfigManager;
use Metamorphosis\Middlewares\Handler\Consumer as ConsumerMiddleware;
use Metamorphosis\Middlewares\Handler\Dispatcher;

/**
 * This factory will determine what kind of connector will be used.
 * Basically, if the user pass --partition and --offset as argument
 * means that we will use the low level approach.
 */
class ConnectorFactory
{
    public static function make(): Manager
    {
        $consumer = self::getConsumer();
        $handler = app(ConfigConfigManager::get('handler'));
        $dispatcher = self::getMiddlewareDispatcher($handler, ConfigConfigManager::middlewares());

        return new Manager($consumer, $handler);
    }

    protected static function requiresPartition(): bool
    {
        return ConfigConfigManager::has('partition');
    }

    private static function getConsumer(): ConsumerInterface
    {
        if (self::requiresPartition()) {
            return app(LowLevel::class);
        }

        return app(HighLevel::class);
    }

    private static function getMiddlewareDispatcher($handler, array $middlewares): Dispatcher
    {
        $middlewares[] = new ConsumerMiddleware($handler);

        return new Dispatcher($middlewares);
    }
}

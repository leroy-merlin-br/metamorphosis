<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Facades\ConfigManager;
use Metamorphosis\Middlewares\Handler\Consumer as ConsumerMiddleware;
use Metamorphosis\Middlewares\Handler\Dispatcher;

/**
 * This factory will determine what kind of connector will be used.
 * Basically, if the user pass --partition and --offset as argument
 * means that we will use the low level approach.
 */
class Factory
{
    public static function make(): Manager
    {
        $consumer = self::getConsumer();
        $handler = app(ConfigManager::get('handler'));
        $dispatcher = self::getMiddlewareDispatcher($handler, ConfigManager::middlewares());

        return new Manager($consumer, $handler, $dispatcher);
    }

    protected static function requiresPartition(): bool
    {
        return ConfigManager::has('partition');
    }

    private static function getConsumer(): ConsumerInterface
    {
        if (self::requiresPartition()) {
            return app(LowLevel::class)->getConsumer();
        }

        return app(HighLevel::class)->getConsumer();
    }

    private static function getMiddlewareDispatcher($handler, array $middlewares): Dispatcher
    {
        $middlewares[] = new ConsumerMiddleware($handler);

        return new Dispatcher($middlewares);
    }
}

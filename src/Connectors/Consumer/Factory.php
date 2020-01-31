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
        $autoCommit = ConfigManager::get('auto_commit', true);
        $commitAsync = ConfigManager::get('commit_async', true);

        $consumer = self::getConsumer($autoCommit);
        $handler = app(ConfigManager::get('handler'));
        $dispatcher = self::getMiddlewareDispatcher($handler, ConfigManager::middlewares());

        return new Manager($consumer, $handler, $dispatcher, $autoCommit, $commitAsync);
    }

    protected static function requiresPartition(): bool
    {
        return ConfigManager::has('partition');
    }

    private static function getConsumer(bool $autoCommit): ConsumerInterface
    {
        if (self::requiresPartition() || !$autoCommit) {
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

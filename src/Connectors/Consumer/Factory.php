<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConfigOptions;

/**
 * This factory will determine what kind of connector will be used.
 * Basically, if the user pass --partition and --offset as argument
 * means that we will use the low level approach.
 */
class Factory
{
    public static function make(ConfigOptions $configOptions): Manager
    {
        $autoCommit = $configOptions->isAutoCommit();
        $commitAsync = $configOptions->isCommitASync();

        $consumer = self::getConsumer($autoCommit, $configOptions);
        $handler = app($configOptions->getHandler());

        $dispatcher = self::getMiddlewareDispatcher($configOptions->getMiddlewares());

        return new Manager($consumer, $handler, $dispatcher, $autoCommit, $commitAsync);
    }

    protected static function requiresPartition(ConfigOptions $configOptions): bool
    {
        $partition = $configOptions->getPartition();

        return !is_null($partition) && $partition >= 0;
    }

    public static function getConsumer(bool $autoCommit, ConfigOptions $configOptions): ConsumerInterface
    {
        if (self::requiresPartition($configOptions)) {
            return app(LowLevel::class)->getConsumer($autoCommit, $configOptions);
        }

        return app(HighLevel::class)->getConsumer($autoCommit, $configOptions);
    }

    private static function getMiddlewareDispatcher(array $middlewares): Dispatcher
    {
        return new Dispatcher($middlewares);
    }
}

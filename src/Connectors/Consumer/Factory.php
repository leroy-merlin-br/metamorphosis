<?php

namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\AbstractConfigManager;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Middlewares\Handler\Dispatcher;

/**
 * This factory will determine what kind of connector will be used.
 * Basically, if the user pass --partition and --offset as argument
 * means that we will use the low level approach.
 */
class Factory
{
    public static function make(AbstractConfigManager $configManager): Manager
    {
        $autoCommit = $configManager->get('auto_commit', true);
        $commitAsync = $configManager->get('commit_async', true);

        $consumer = self::getConsumer($autoCommit, $configManager);
        $handler = app($configManager->get('handler'));

        $dispatcher = self::getMiddlewareDispatcher(
            $configManager->middlewares()
        );

        return new Manager(
            $consumer,
            $handler,
            $dispatcher,
            $autoCommit,
            $commitAsync
        );
    }

    public static function getConsumer(bool $autoCommit, AbstractConfigManager $configManager): ConsumerInterface
    {
        if (self::requiresPartition($configManager)) {
            return app(LowLevel::class)->getConsumer(
                $autoCommit,
                $configManager
            );
        }

        return app(HighLevel::class)->getConsumer($autoCommit, $configManager);
    }

    protected static function requiresPartition(AbstractConfigManager $configManager): bool
    {
        $partition = $configManager->get('partition');

        return !is_null($partition) && $partition >= 0;
    }

    private static function getMiddlewareDispatcher(array $middlewares): Dispatcher
    {
        return new Dispatcher($middlewares);
    }
}

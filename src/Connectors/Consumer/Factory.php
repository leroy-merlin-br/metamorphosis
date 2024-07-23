<?php

namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Middlewares\Handler\Consumer as ConsumerMiddleware;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;

/**
 * This factory will determine what kind of connector will be used.
 * Basically, if the user pass --partition and --offset as argument
 * means that we will use the low level approach.
 */

class Factory
{
    public static function make(ConsumerConfigOptions $configOptions): Manager
    {
        $autoCommit = $configOptions->isAutoCommit();
        $commitAsync = $configOptions->isCommitASync();

        $consumer = self::getConsumer($autoCommit, $configOptions);

        $handler = app($configOptions->getHandler());

        $middlewares = $configOptions->getMiddlewares();
        foreach ($middlewares as &$middleware) {
            $middleware = is_string($middleware)
                ? app(
                    $middleware,
                    ['consumerConfigOptions' => $configOptions]
                )
                : $middleware;
        }

        $middlewares[] = app(
            ConsumerMiddleware::class,
            ['consumerTopicHandler' => $handler]
        );

        $dispatcher = self::getMiddlewareDispatcher($middlewares);

        return new Manager(
            $consumer,
            $handler,
            $dispatcher,
            $autoCommit,
            $commitAsync
        );
    }

    public static function getConsumer(bool $autoCommit, ConsumerConfigOptions $configOptions): ConsumerInterface
    {
        if (self::requiresPartition($configOptions)) {
            return app(LowLevel::class)->getConsumer(
                $autoCommit,
                $configOptions
            );
        }

        return app(HighLevel::class)->getConsumer($autoCommit, $configOptions);
    }

    protected static function requiresPartition(ConsumerConfigOptions $configOptions): bool
    {
        $partition = $configOptions->getPartition();

        return !is_null($partition) && $partition >= 0;
    }

    private static function getMiddlewareDispatcher(array $middlewares): Dispatcher
    {
        return new Dispatcher($middlewares);
    }
}

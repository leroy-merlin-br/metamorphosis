<?php

namespace Metamorphosis;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Producer\Poll;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ProducerConfigOptions;
use Metamorphosis\TopicHandler\Producer\AbstractProducer;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;

class Producer
{
    private Config $config;

    private Connector $connector;

    public function __construct(Config $config, Connector $connector)
    {
        $this->config = $config;
        $this->connector = $connector;
    }

    public function produce(HandlerInterface $producerHandler): void
    {
        $middlewareDispatcher = $this->build($producerHandler);

        $middlewareDispatcher->handle($producerHandler->createRecord());
    }

    public function build(HandlerInterface $producerHandler): Dispatcher
    {
        $producerConfigOptions = $producerHandler->getConfigOptions();

        $middlewares = $producerConfigOptions->getMiddlewares();

        foreach ($middlewares as &$middleware) {
            $middleware = is_string($middleware) ? app($middleware, ['producerConfigOptions' => $producerConfigOptions]) : $middleware;
        }

        $middlewares[] = $this->getProducerMiddleware($producerHandler, $producerConfigOptions);

        return new Dispatcher($middlewares);
    }

    public function getProducerMiddleware(HandlerInterface $producerHandler, ProducerConfigOptions $producerConfigOptions): ProducerMiddleware
    {
        $producer = $this->connector->getProducerTopic($producerHandler, $producerConfigOptions);

        $topic = $producer->newTopic($producerConfigOptions->getTopicId());
        $poll = app(Poll::class, ['producer' => $producer, 'producerConfigOptions' => $producerConfigOptions]);
        $partition = $producerConfigOptions->getPartition();

        return app(
            ProducerMiddleware::class,
            compact('topic', 'poll', 'partition')
        );
    }

    private function getConfigManager(HandlerInterface $producerHandler): AbstractConfigManager
    {
        if ($producerHandler instanceof AbstractProducer) {
            return $this->config->make($producerHandler->getConfigOptions());
        }

        return $this->config->makeByTopic($producerHandler->getTopic());
    }
}

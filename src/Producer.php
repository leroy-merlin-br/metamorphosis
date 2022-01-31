<?php

namespace Metamorphosis;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Producer\Poll;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ConfigOptions;
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
        $configOptions = $producerHandler->getConfigOptions();

        $middlewares = $configOptions->getMiddlewares();
        $middlewares[] = $this->getProducerMiddleware($producerHandler, $configOptions);

        return new Dispatcher($middlewares);
    }

    public function getProducerMiddleware(HandlerInterface $producerHandler, ConfigOptions $configOptions): ProducerMiddleware
    {
        $producer = $this->connector->getProducerTopic($producerHandler, $configOptions);

        $topic = $producer->newTopic($configOptions->getTopicId());
        $poll = app(Poll::class, ['producer' => $producer, 'configOptions' => $configOptions]);
        $partition = $configOptions->getPartition();

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

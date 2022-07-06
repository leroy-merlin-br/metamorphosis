<?php

namespace Metamorphosis;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Producer\Poll;
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
        $configManager = $this->getConfigManager($producerHandler);

        $middlewares = $configManager->middlewares();
        $middlewares[] = $this->getProducerMiddleware(
            $producerHandler,
            $configManager
        );

        return new Dispatcher($middlewares);
    }

    public function getProducerMiddleware(
        HandlerInterface $producerHandler,
        AbstractConfigManager $configManager
    ): ProducerMiddleware {
        $producer = $this->connector->getProducerTopic(
            $producerHandler,
            $configManager
        );

        $topic = $producer->newTopic($configManager->get('topic_id'));
        $poll = app(
            Poll::class,
            ['producer' => $producer, 'configManager' => $configManager]
        );
        $partition = $configManager->get('partition');

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

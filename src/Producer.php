<?php
namespace Metamorphosis;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Exceptions\JsonException;
use Metamorphosis\Facades\ConfigManager;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Producer\Pool;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;

class Producer
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Connector
     */
    private $connector;

    public function __construct(Config $config, Connector $connector)
    {
        $this->config = $config;
        $this->connector = $connector;
    }

    /**
     * @throws JsonException When an array is passed and something wrong happens while encoding it into json
     */
    public function produce(HandlerInterface $producerHandler): void
    {
        $middlewareDispatcher = $this->build($producerHandler);

        $middlewareDispatcher->handle($producerHandler->createRecord());
    }

    public function build(HandlerInterface $producerHandler): Dispatcher
    {
        $this->config->setOption($producerHandler->getTopic());

        $middlewares = ConfigManager::middlewares();
        $middlewares[] = $this->getProducerMiddleware($producerHandler);

        return new Dispatcher($middlewares);
    }

    public function getProducerMiddleware(HandlerInterface $producerHandler): ProducerMiddleware
    {
        $producer = $this->connector->getProducerTopic($producerHandler);

        $topic = $producer->newTopic(ConfigManager::get('topic_id'));
        $pool = app(Pool::class, ['producer' => $producer]);
        $partition = ConfigManager::get('partition');

        return app(ProducerMiddleware::class, [
            'topic' => $topic,
            'pool' => $pool,
            'partition' => $partition,
        ]);
    }
}

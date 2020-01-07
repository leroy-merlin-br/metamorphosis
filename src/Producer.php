<?php
namespace Metamorphosis;

use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Exceptions\JsonException;
use Metamorphosis\Facades\Manager;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Record\ProducerRecord;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;

class Producer
{
    public function __construct(Config $config)
    {
        $this->config = $config;
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
        $middlewares = Manager::middlewares();
        $middlewares[] = app(ProducerMiddleware::class, ['producerHandler' => $producerHandler]);

        return new Dispatcher($middlewares);
    }
}

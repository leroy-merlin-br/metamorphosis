<?php
namespace Metamorphosis;

use Metamorphosis\Config\Producer as ProducerConfig;
use Metamorphosis\Exceptions\JsonException;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Record\ProducerRecord;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;

class Producer
{
    /**
     * @var Dispatcher
     */
    public $middlewareDispatcher;

    /**
     * @var HandlerInterface
     */
    private $producerHandler;

    /**
     * @param HandlerInterface $producerHandler
     *
     * @throws \Metamorphosis\Exceptions\JsonException When an array is passed and something wrong happens while encoding it into json
     */
    public function produce(HandlerInterface $producerHandler): void
    {
        $this->producerHandler = $producerHandler;

        $config = new ProducerConfig($producerHandler->getTopic());
        $this->setMiddlewareDispatcher($config->getMiddlewares());

        $record = $producerHandler->getRecord();

        if (is_array($record)) {
            $record = $this->encodeRecord($record);
        }

        $topic = $producerHandler->getTopic();
        $partition = $producerHandler->getPartition();
        $key = $producerHandler->getKey();

        $record = new ProducerRecord($record, $topic, $partition, $key);
        $this->middlewareDispatcher->handle($record);
    }

    protected function setMiddlewareDispatcher(array $middlewares)
    {
        $producerMiddleware = app(ProducerMiddleware::class);
        $producerMiddleware->setProducerHandler($this->producerHandler);
        $middlewares[] = $producerMiddleware;

        $this->middlewareDispatcher = new Dispatcher($middlewares);
    }

    private function encodeRecord(array $record): string
    {
        $record = json_encode($record);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonException('Cannot convert data into a valid JSON. Reason: '.json_last_error_msg());
        }

        return $record;
    }
}

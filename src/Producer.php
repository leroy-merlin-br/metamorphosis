<?php
namespace Metamorphosis;

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
     * @throws JsonException When an array is passed and something wrong happens while encoding it into json
     */
    public function produce(HandlerInterface $producerHandler): void
    {
        $this->producerHandler = $producerHandler;

        $this->setMiddlewareDispatcher(Manager::get('middlewares'));

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

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonException('Cannot convert data into a valid JSON. Reason: '.json_last_error_msg());
        }

        return $record;
    }
}

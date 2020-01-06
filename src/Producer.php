<?php
namespace Metamorphosis;

use Metamorphosis\Exceptions\JsonException;
use Metamorphosis\Facades\Manager;
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
        $middlewareDispatcher = $this->build($producerHandler);

        $record = $producerHandler->getRecord();

        if (is_array($record)) {
            $record = $this->encodeRecord($record);
        }

        $topic = $producerHandler->getTopic();
        $partition = $producerHandler->getPartition();
        $key = $producerHandler->getKey();

        $record = new ProducerRecord($record, $topic, $partition, $key);
        $middlewareDispatcher->handle($record);
    }

    public function build(HandlerInterface $producerHandler): Dispatcher
    {
        $middlewares = Manager::middlewares();
        $middlewares[] = app(ProducerMiddleware::class, ['producerHandler' => $producerHandler]);

        return new Dispatcher($middlewares);
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

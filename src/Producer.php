<?php
namespace Metamorphosis;

use JsonException;
use Metamorphosis\Config\Producer as ProducerConfig;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Record\ProducerRecord;

class Producer
{
    /**
     * @var Dispatcher
     */
    public $middlewareDispatcher;

    public function produce($record, $topic, $partition = null, $key = null): void
    {
        $config = new ProducerConfig($topic);

        $this->setMiddlewareDispatcher($config->getMiddlewares());

        if (is_array($record)) {
            $record = $this->encodeRecord($record);
        }

        $record = new ProducerRecord($record, $topic, $partition, $key);
        $this->middlewareDispatcher->handle($record);
    }

    protected function setMiddlewareDispatcher(array $middlewares)
    {
        $middlewares[] = app(ProducerMiddleware::class);
        $this->middlewareDispatcher = new Dispatcher($middlewares);
    }

    private function encodeRecord(array $record): string
    {
        $record = json_encode($record);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonException('Cannot convert data into a valid JSON: '.json_last_error_msg());
        }

        return $record;
    }
}

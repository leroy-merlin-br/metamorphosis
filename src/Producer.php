<?php
namespace Metamorphosis;

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
        $config = new Config($topic);

        $this->setMiddlewareDispatcher($config->getMiddlewares());

        $record = new ProducerRecord($record, $topic, $partition, $key);
        $this->middlewareDispatcher->handle($record);
    }

    protected function setMiddlewareDispatcher(array $middlewares)
    {
        $middlewares[] = app(ProducerMiddleware::class);
        $this->middlewareDispatcher = new Dispatcher($middlewares);
    }
}
// usage:
//(new Producer)->produce($record, $topic, $partition, $key);

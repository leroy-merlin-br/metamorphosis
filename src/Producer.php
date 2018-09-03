<?php
namespace Metamorphosis;

use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Middlewares\Handler\Producer as ProducerMiddleware;
use Metamorphosis\Record\ProducerRecord as RecordProducer;

class Producer
{
    /**
     * @var Dispatcher
     */
    public $middlewareDispatcher;

    protected $config;

    public function produce($record, $topic, $partition = null, $key = null)
    {
        $config = new Config($topic);

        $this->setMiddlewareDispatcher($config->getMiddlewares());

        $record = new RecordProducer($record, $topic, $partition, $key);
        $this->middlewareDispatcher->handle($record);
    }

    protected function setMiddlewareDispatcher(array $middlewares)
    {
        $middlewares[] = new ProducerMiddleware();
        $this->middlewareDispatcher = new Dispatcher($middlewares);
    }
}
// usage:
//(new Producer)->produce($record, $topic, $partition, $key);

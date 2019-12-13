<?php
namespace Metamorphosis;

use Exception;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Middlewares\Handler\Consumer as ConsumerMiddleware;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Consumer\Handler;

abstract class AbstractConsumerRunner
{
    /**
     * @var Dispatcher
     */
    protected $middlewareDispatcher;

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var ConsumerInterface
     */
    private $consumer;

    /**
     * @var null
     */
    protected $cachedSchema;

    public function __construct(ConsumerInterface $consumer, $cachedSchema = null)
    {
        $this->consumer = $consumer;
        $this->cachedSchema = $cachedSchema;
    }

    public function run(): void
    {
        $handler = app(config('kafka.runtime.handler'));

        $this->setMiddlewareDispatcher($handler, config('kafka.runtime.middlewares', []));

        while (true) {
            $response = $this->consumer->consume();

            try {
                $record = $this->handleConsumerResponse($response);
                $this->middlewareDispatcher->handle($record);
            } catch (ResponseWarningException $exception) {
                $handler->warning($exception);
            } catch (Exception $exception) {
                $handler->failed($exception);
            }
        }
    }

    abstract protected function handleConsumerResponse($record): RecordInterface;

    protected function setMiddlewareDispatcher($handler, array $middlewares): void
    {
        $middlewares[] = new ConsumerMiddleware($handler);
        $this->middlewareDispatcher = new Dispatcher($middlewares);
    }
}

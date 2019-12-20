<?php
namespace Metamorphosis\Consumers;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Facades\Manager;
use Metamorphosis\Middlewares\Handler\Consumer as ConsumerMiddleware;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Record\ConsumerRecord;
use Metamorphosis\TopicHandler\Consumer\Handler;
use Kafka\Consumer;

class Runner
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
     * @var Consumer
     */
    private $consumer;

    public function __construct(Consumer $consumer)
    {
        $this->consumer = $consumer;
    }

    public function run(): void
    {
        $handler = app(Manager::get('handler'));

        $this->setMiddlewareDispatcher($handler, Manager::middlewares());

        $this->consumer->start(function ($topic, $part, $message) use ($handler): void {
            try {
                $record = app(ConsumerRecord::class, compact('message'));
                $this->middlewareDispatcher->handle($record);
            } catch (ResponseWarningException $exception) {
                $handler->warning($exception);
            } catch (Exception $exception) {
                $handler->failed($exception);
            }
        });
    }

    protected function setMiddlewareDispatcher($handler, array $middlewares): void
    {
        $middlewares[] = new ConsumerMiddleware($handler);
        $this->middlewareDispatcher = new Dispatcher($middlewares);
    }
}

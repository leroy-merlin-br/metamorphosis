<?php
namespace Metamorphosis\Consumers;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Facades\Manager;
use Metamorphosis\Middlewares\Handler\Consumer as ConsumerMiddleware;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Record\ConsumerRecord;
use Metamorphosis\TopicHandler\Consumer\Handler;

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
     * @var ConsumerInterface
     */
    private $consumer;

    public function __construct(ConsumerInterface $consumer)
    {
        $this->consumer = $consumer;
    }

    public function run(): void
    {
        $handler = app(Manager::get('handler'));

        $this->setMiddlewareDispatcher($handler, Manager::middlewares());

        while (true) {
            $response = $this->consumer->consume();

            try {
                $record = app(ConsumerRecord::class, compact('response'));
                $this->middlewareDispatcher->handle($record);
            } catch (ResponseWarningException $exception) {
                $handler->warning($exception);
            } catch (Exception $exception) {
                $handler->failed($exception);
            }
        }
    }

    protected function setMiddlewareDispatcher($handler, array $middlewares): void
    {
        $middlewares[] = new ConsumerMiddleware($handler);
        $this->middlewareDispatcher = new Dispatcher($middlewares);
    }
}

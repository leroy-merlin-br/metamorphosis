<?php
namespace Metamorphosis;

use Exception;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Middlewares\Handler\Consumer as ConsumerMiddleware;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\TopicHandler\Consumer\Handler;

class Runner
{
    /**
     * @var int
     */
    public $timeout = 2000000;

    /**
     * @var Dispatcher
     */
    protected $middlewareDispatcher;

    /**
     * @var Handler
     */
    protected $handler;

    public function run(Config $config, ConsumerInterface $consumer): void
    {
        $this->handler = $config->getConsumerHandler();

        $this->setMiddlewareDispatcher($config->getMiddlewares());

        while (true) {
            $response = $consumer->consume($this->timeout);

            try {
                $record = new Record($response);
                $this->middlewareDispatcher->handle($record);
            } catch (ResponseWarningException $exception) {
                $this->handler->warning($exception);
            } catch (Exception $exception) {
                $this->handler->failed($exception);
            }
        }
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    protected function setMiddlewareDispatcher(array $middlewares): void
    {
        $middlewares[] = new ConsumerMiddleware($this->handler);
        $this->middlewareDispatcher = new Dispatcher($middlewares);
    }
}

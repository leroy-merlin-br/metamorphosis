<?php
namespace Metamorphosis;

use Exception;
use Metamorphosis\Config\Consumer;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Middlewares\Handler\Consumer as ConsumerMiddleware;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Record\ConsumerRecord;
use Metamorphosis\TopicHandler\Consumer\Handler;

class ConsumerRunner
{
    /**
     * @var int
     */
    public $timeout = 2000000;

    /**
     * @var int
     */
    public $times = 0;

    /**
     * @var Dispatcher
     */
    protected $middlewareDispatcher;

    /**
     * @var Handler
     */
    protected $handler;

    public function run(Consumer $config, ConsumerInterface $consumer): void
    {
        $this->handler = $config->getConsumerHandler();

        $this->setMiddlewareDispatcher($config->getMiddlewares());

        while (true) {
            $response = $consumer->consume($this->timeout);

            try {
                $record = new ConsumerRecord($response);
                $this->middlewareDispatcher->handle($record);
            } catch (ResponseWarningException $exception) {
                $this->handler->warning($exception);
            } catch (Exception $exception) {
                $this->handler->failed($exception);
            }

            $this->shouldStopRunning();
        }
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function times(int $times): self
    {
        $this->times = $times;

        return $this;
    }

    protected function setMiddlewareDispatcher(array $middlewares): void
    {
        $middlewares[] = new ConsumerMiddleware($this->handler);
        $this->middlewareDispatcher = new Dispatcher($middlewares);
    }

    protected function shouldStopRunning()
    {
        if ($this->times !== 0) {
            exit(9);
        }
    }
}

<?php
namespace Metamorphosis;

use Exception;
use Metamorphosis\Config\Consumer as ConsumerConfig;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Middlewares\Handler\Consumer as ConsumerMiddleware;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Consumer\Handler;

abstract class AbstractConsumerRunner
{
    /**
     * @var int
     */
    public $timeout;

    /**
     * @var Dispatcher
     */
    protected $middlewareDispatcher;

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var ConsumerConfig
     */
    private $config;

    /**
     * @var ConsumerInterface
     */
    private $consumer;

    public function __construct(ConsumerConfig $config, ConsumerInterface $consumer, int $timeout)
    {
        $this->config = $config;
        $this->consumer = $consumer;
        $this->timeout = $timeout;
    }

    public function run(): void
    {
        $this->handler = $this->config->getConsumerHandler();

        $this->setMiddlewareDispatcher($this->config->getMiddlewares());

        while (true) {
            $response = $this->consumer->consume($this->timeout);

            try {
                $record = $this->handleConsumerResponse($response);
                $this->middlewareDispatcher->handle($record);
            } catch (ResponseWarningException $exception) {
                $this->handler->warning($exception);
            } catch (Exception $exception) {
                $this->handler->failed($exception);
            }
        }
    }

    abstract protected function handleConsumerResponse($record): RecordInterface;

    protected function setMiddlewareDispatcher(array $middlewares): void
    {
        $middlewares[] = new ConsumerMiddleware($this->handler);
        $this->middlewareDispatcher = new Dispatcher($middlewares);
    }

    protected function getConfig(): ConsumerConfig
    {
        return $this->config;
    }
}

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
     * @var Dispatcher
     */
    protected $middlewareDispatcher;

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var MemoryManager
     */
    private $memoryManager;

    public function __construct(MemoryManager $memoryManager)
    {
        $this->memoryManager = $memoryManager;
    }

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

            $this->stopIfNecessary($config);
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

    /**
     * It checks to see if we have exceeded our memory limits. If so, we'll stop
     * this worker and let whatever is "monitoring" it restart the process.
     */
    protected function stopIfNecessary(Consumer $config): void
    {
        if ($this->memoryManager->memoryExceeded($config->getMemoryLimit())) {
            $this->stop(12);
        }
    }

    /**
     * Stop consuming and bail out of the script.
     */
    protected function stop(int $status = 0): void
    {
        exit($status);
    }
}

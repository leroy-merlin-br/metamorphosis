<?php
namespace Metamorphosis;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Middlewares\Handler\Consumer as ConsumerMiddleware;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\TopicHandler\Consumer\Handler;
use RdKafka\KafkaConsumer;

class Consumer
{
    /**
     * @var int
     */
    public $timeout = 2000000;

    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var KafkaConsumer
     */
    protected $kafkaConsumer;

    public function __construct(Config $config, KafkaConsumer $kafkaConsumer)
    {
        $this->kafkaConsumer = $kafkaConsumer;
        $this->handler = $config->getConsumerGroupHandler();

        $this->setMiddlewareDispatcher($config->getMiddlewares());
    }

    public function run(): void
    {
        while (true) {
            $response = $this->kafkaConsumer->consume($this->timeout);

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

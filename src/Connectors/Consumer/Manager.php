<?php
namespace Metamorphosis\Connectors\Consumer;

use Exception;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Record\ConsumerRecord;
use Metamorphosis\TopicHandler\Consumer\Handler as ConsumerHandler;

class Manager
{
    /**
     * @var ConsumerInterface
     */
    private $consumer;

    /**
     * @var ConsumerHandler
     */
    private $consumerHandler;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var bool
     */
    private $autoCommit;

    /**
     * @var bool
     */
    private $commitAsync;

    public function __construct(
        ConsumerInterface $consumer,
        ConsumerHandler $consumerHandler,
        Dispatcher $dispatcher,
        bool $autoCommit,
        bool $commitAsync
    ) {
        $this->consumer = $consumer;
        $this->consumerHandler = $consumerHandler;
        $this->dispatcher = $dispatcher;
        $this->autoCommit = $autoCommit;
        $this->commitAsync = $commitAsync;
    }

    public function getConsumer(): ConsumerInterface
    {
        return $this->consumer;
    }

    public function handleMessage(): void
    {
        try {
            $response = $this->consumer->consume();
            $record = app(ConsumerRecord::class, compact('response'));
            $this->dispatcher->handle($record);
            $this->commit();
        } catch (ResponseWarningException $exception) {
            $this->consumerHandler->warning($exception);
        } catch (Exception $exception) {
            $this->consumerHandler->failed($exception);
        }
    }

    private function commit(): void
    {
        if ($this->autoCommit) {
            return;
        }

        if ($this->commitAsync) {
            $this->consumer->commitAsync();
            return;
        }

        $this->consumer->commit();
    }
}

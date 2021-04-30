<?php
namespace Metamorphosis\Connectors\Consumer;

use Exception;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Consumers\LowLevel as ConsumerLowLevel;
use Metamorphosis\Exceptions\ResponseTimeoutException;
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

    /**
     * @var bool
     */
    private $finished = false;

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
            if (!$response = $this->consumer->consume()) {
                $this->handleTimeOut();
                return;
            }

            $record = app(ConsumerRecord::class, compact('response'));
            $this->dispatcher->handle($record);
            $this->commit();
        } catch (ResponseTimeoutException $exception) {
            $this->handleTimeOut();
            return;
        } catch (ResponseWarningException $exception) {
            $this->consumerHandler->warning($exception);
        } catch (Exception $exception) {
            $this->consumerHandler->failed($exception);
        }

        $this->finished = false;
    }

    private function commit(): void
    {
        // when running low level consumer, we dont need
        // to commit the messages as they've already committed.
        if ($this->autoCommit || $this->consumer instanceof ConsumerLowLevel) {
            return;
        }

        if ($this->commitAsync) {
            $this->consumer->commitAsync();
            return;
        }

        $this->consumer->commit();
    }

    private function handleTimeOut(): void
    {
        if (!$this->finished) {
            $this->consumerHandler->finished();
            $this->finished = true;
        }
    }
}

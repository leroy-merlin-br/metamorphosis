<?php
namespace Metamorphosis\Connectors\Consumer;

use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Exceptions\ResponseTimeoutException;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Record\ConsumerRecord;
use Metamorphosis\TopicHandler\Consumer\Handler as ConsumerHandler;
use RdKafka\Message;
use Throwable;

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
     * @var Message
     */
    private $lastResponse;

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

    public function consume(): ?Message
    {
        return $this->getConsumer()->consume();
    }

    public function handleMessage(): void
    {
        try {
            if ($response = $this->consume()) {
                $record = app(ConsumerRecord::class, compact('response'));
                $this->dispatcher->handle($record);
                $this->commit();
            }
        } catch (ResponseTimeoutException $exception) {
            $response = null;
        } catch (ResponseWarningException $exception) {
            $this->consumerHandler->warning($exception);
            return;
        } catch (Throwable $throwable) {
            $this->consumerHandler->failed($throwable);
            return;
        }

        $this->handleFinished($response);
    }

    private function commit(): void
    {
        if ($this->autoCommit || !$this->consumer->canCommit()) {
            return;
        }

        if ($this->commitAsync) {
            $this->consumer->commitAsync();
            return;
        }

        $this->consumer->commit();
    }

    private function handleFinished(?Message $response): void
    {
        if ($this->lastResponse && !$response) {
            $this->consumerHandler->finished();
        }

        $this->lastResponse = $response;
    }
}

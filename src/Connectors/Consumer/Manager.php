<?php

namespace Metamorphosis\Connectors\Consumer;

use Exception;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Exceptions\ResponseTimeoutException;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Record\ConsumerRecord;
use Metamorphosis\TopicHandler\Consumer\Handler as ConsumerHandler;
use RdKafka\Message;

class Manager
{
    private ConsumerInterface $consumer;

    private ConsumerHandler $consumerHandler;

    private Dispatcher $dispatcher;

    private bool $autoCommit;

    private bool $commitAsync;

    private Message $lastResponse;

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
        } catch (Exception $exception) {
            $this->consumerHandler->failed($exception);

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

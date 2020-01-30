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

    public function __construct(ConsumerInterface $consumer, ConsumerHandler $consumerHandler, Dispatcher $dispatcher)
    {
        $this->consumer = $consumer;
        $this->consumerHandler = $consumerHandler;
        $this->dispatcher = $dispatcher;
    }

    public function getConsumer(): ConsumerInterface
    {
        return $this->consumer;
    }

    public function handleMessage(): void
    {
        $response = $this->consumer->consume();

        try {
            $record = app(ConsumerRecord::class, compact('response'));
            $this->dispatcher->handle($record);
        } catch (ResponseWarningException $exception) {
            $this->consumerHandler->warning($exception);
        } catch (Exception $exception) {
            $this->consumerHandler->failed($exception);
        }
    }

    public function handleMessageCommitSync(): void
    {
        $response = $this->consumer->consume();

        try {
            $record = app(ConsumerRecord::class, compact('response'));
            $this->dispatcher->handle($record);
            $this->consumer->commit();
        } catch (ResponseWarningException $exception) {
            $this->consumerHandler->warning($exception);
        } catch (Exception $exception) {
            $this->consumerHandler->failed($exception);
        }
    }
}

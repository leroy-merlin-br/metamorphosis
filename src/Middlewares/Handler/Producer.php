<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Facades\Manager;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;
use RuntimeException;

class Producer implements MiddlewareInterface
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var HandlerInterface
     */
    private $producerHandler;

    /**
     * @var \RdKafka\Producer
     */
    private $producer;

    /**
     * @var int
     */
    private $processMessageCount = 0;

    /**
     * @var int
     */
    private $partition;

    public function __construct(Connector $connector, HandlerInterface $producerHandler)
    {
        $this->connector = $connector;
        $this->producerHandler = $producerHandler;

        $this->producer = $this->connector->getProducerTopic($producerHandler);
        $this->topic = $this->producer->newTopic(Manager::get('topic_id'));
        $this->partition = Manager::get('partition');
    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $this->topic->produce($this->getPartition($record), 0, $record->getPayload(), $record->getKey());
        $this->handleResponse();
    }

    public function __destruct()
    {
        $this->flushMessage();
    }

    private function handleResponse(): void
    {
        $this->processMessageCount++;

        if (!Manager::get('is_async')) {
            $this->flushMessage();

            return;
        }

        if (0 === ($this->processMessageCount % Manager::get('max_poll_records'))) {
            $this->flushMessage();
        }
    }

    private function flushMessage(): void
    {
        if (!Manager::get('required_acknowledgment')) {
            return;
        }

        for ($flushAttempts = 0; $flushAttempts < Manager::get('flush_attempts'); $flushAttempts++) {
            if (0 === $this->producer->poll(Manager::get('timeout'))) {
                return;
            }
        }

        throw new RuntimeException('Unable to flush, messages might be lost!');
    }

    public function getPartition(RecordInterface $record): int
    {
        return is_null($record->getPartition()) ? $this->partition : $record->getPartition();
    }
}

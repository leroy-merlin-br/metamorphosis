<?php
namespace Metamorphosis\Connectors\Producer;

use RdKafka\Producer;

class Queue
{
    /**
     * @var Producer
     */
    private $producer;

    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
    }

    /**
     * Polls queue for retrieving new messages from broker about latest sent message.
     */
    public function poll(int $timeout): void
    {
        while ($this->hasContent()) {
            $this->producer->poll($timeout);
        }
    }

    /**
     * Checks if the kafka inner-queue for response messages has any content being retrieved.
     */
    protected function hasContent(): bool
    {
        return $this->producer->getOutQLen() > 0;
    }
}

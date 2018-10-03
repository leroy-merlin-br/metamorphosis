<?php
namespace Metamorphosis\Connectors\Producer;

use RdKafka\Producer;

class Queue
{
    private $producer;

    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
    }

    /**
     * Polls queue for retrieving new messages from broker about latest message send.
     *
     * @param int $timeout
     */
    public function poll(int $timeout): void
    {
        while ($this->hasContent()) {
            $this->producer->poll($timeout);
        }
    }

    /**
     * Checks if the kafka inner-queue for response messages has any content for being retrieved.
     *
     * @return bool
     */
    protected function hasContent(): bool
    {
        return $this->producer->getOutQLen() > 0;
    }
}

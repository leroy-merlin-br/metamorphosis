<?php
namespace Metamorphosis;

use RdKafka\Message as KafkaMessage;

class Message
{
    /**
     * @var KafkaMessage
     */
    protected $original;

    protected $payload;

    public function __construct(KafkaMessage $original)
    {
        $this->original = $original;

        $this->setPayload($original->payload);

        if ($this->hasError()) {
            throw new \Exception('Invalid message');
        }
    }

    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function hasError(): bool
    {
        return RD_KAFKA_RESP_ERR_NO_ERROR !== $this->original->err;
    }
}

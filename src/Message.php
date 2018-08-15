<?php
namespace Metamorphosis;

use Exception;
use RdKafka\Message as KafkaMessage;

class Message
{
    /**
     * @var KafkaMessage
     */
    protected $original;

    /**
     * @var string
     */
    protected $payload;

    public function __construct(KafkaMessage $original)
    {
        $this->original = $original;

        $this->setPayload($original->payload);

        if ($this->hasError()) {
            throw new Exception('Invalid message. Error code: '.$this->original->err);
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

    public function getOriginal(): KafkaMessage
    {
        return $this->original;
    }

    public function hasError(): bool
    {
        return RD_KAFKA_RESP_ERR_NO_ERROR !== $this->original->err;
    }
}

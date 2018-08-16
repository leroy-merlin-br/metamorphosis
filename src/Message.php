<?php
namespace Metamorphosis;

use Metamorphosis\Exceptions\KafkaResponseErrorException;
use Metamorphosis\Exceptions\KafkaResponseHandleableErrorException;
use RdKafka\Message as KafkaMessage;

class Message
{
    /**
     * List of error codes that stop message processing,
     * but are handled gracefully.
     */
    const KAFKA_ERROR_WHITELIST = [
        RD_KAFKA_RESP_ERR__PARTITION_EOF,
    ];

    /**
     * @var KafkaMessage
     */
    protected $original;

    /**
     * @var mixed
     */
    protected $payload;

    public function __construct(KafkaMessage $original)
    {
        $this->original = $original;

        $this->setPayload($original->payload);

        if ($this->hasError()) {
            $this->throwResponseErrorException();
        }
    }

    /**
     * @param mixed $payload
     */
    public function setPayload($payload): void
    {
        $this->payload = $payload;
    }

    /**
     * @return mixed
     */
    public function getPayload()
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

    private function throwResponseErrorException(): void
    {
        if (in_array($this->original->err, self::KAFKA_ERROR_WHITELIST)) {
            throw new KafkaResponseHandleableErrorException(
                'Handleable error.',
                $this->original->err
            );
        }

        throw new KafkaResponseErrorException(
            'Invalid message.',
            $this->original->err
        );
    }
}

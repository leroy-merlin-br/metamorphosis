<?php
namespace Metamorphosis;

use Metamorphosis\Exceptions\ResponseErrorException;
use Metamorphosis\Exceptions\ResponseWarningException;
use RdKafka\Message;

class Record
{
    /**
     * List of error codes that stop record processing,
     * but are handled gracefully.
     */
    const KAFKA_ERROR_WHITELIST = [
        RD_KAFKA_RESP_ERR__PARTITION_EOF,
    ];

    /**
     * @var Message
     */
    protected $original;

    /**
     * @var mixed
     */
    protected $payload;

    public function __construct(Message $original)
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

    public function getOriginal(): Message
    {
        return $this->original;
    }

    public function getTopicName(): string
    {
        return $this->original->topic_name;
    }

    public function getPartition(): int
    {
        return $this->original->partition;
    }

    public function getKey(): string
    {
        return $this->original->key;
    }

    public function getOffset(): int
    {
        return $this->original->offset;
    }

    private function hasError(): bool
    {
        return RD_KAFKA_RESP_ERR_NO_ERROR !== $this->original->err;
    }

    private function throwResponseErrorException(): void
    {
        if (in_array($this->original->err, self::KAFKA_ERROR_WHITELIST)) {
            throw new ResponseWarningException(
                'Invalid response.',
                $this->original->err
            );
        }

        throw new ResponseErrorException(
            'Error response.',
            $this->original->err
        );
    }
}

<?php
namespace Metamorphosis\Record;

use Metamorphosis\Exceptions\ResponseErrorException;
use Metamorphosis\Exceptions\ResponseWarningException;
use RdKafka\Message;

class ConsumerRecord implements RecordInterface
{
    /**
     * List of error codes that stop record processing,
     * but are handled gracefully.
     */
    const KAFKA_ERROR_WHITELIST = [
        RD_KAFKA_RESP_ERR__PARTITION_EOF,
        RD_KAFKA_RESP_ERR__TIMED_OUT,
    ];

    /**
     * @var Message
     */
    protected $original;

    /**
     * @var mixed
     */
    protected $payload;

    /**
     * @throws \Metamorphosis\Exceptions\ResponseErrorException
     * @throws \Metamorphosis\Exceptions\ResponseWarningException
     */
    public function __construct(Message $response)
    {
        $this->original = $response;
        $this->setPayload($response->payload);

        if ($this->hasError()) {
            $this->throwResponseErrorException();
        }
    }

    /**
     * Overwrite record payload.
     * It is possible to get the original payload
     * after overwriting it by calling: $record->getOriginal()->payload.
     *
     * @param mixed $payload
     */
    public function setPayload($payload): void
    {
        $this->payload = $payload;
    }

    /**
     * Get the record payload.
     * It can either be the original value sent to Kafka or
     * a version modified by a middleware.
     *
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Get original message returned when consuming the topic.
     * With this object, it is possible to get original payload.
     *
     * @see https://arnaud-lb.github.io/php-rdkafka/phpdoc/class.rdkafka-message.html
     */
    public function getOriginal(): Message
    {
        return $this->original;
    }

    /**
     * Get the topic name where the record was published.
     */
    public function getTopicName(): string
    {
        return $this->original->topic_name;
    }

    /**
     * Get the partition number where the record was published.
     */
    public function getPartition(): int
    {
        return $this->original->partition;
    }

    /**
     * Get the record key.
     */
    public function getKey(): string
    {
        return $this->original->key;
    }

    /**
     * Get the record offset.
     */
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
                'Invalid response: '.$this->original->errstr(),
                $this->original->err
            );
        }

        throw new ResponseErrorException(
            'Error response: '.$this->original->errstr(),
            $this->original->err
        );
    }
}

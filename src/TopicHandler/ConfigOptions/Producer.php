<?php

namespace Metamorphosis\TopicHandler\ConfigOptions;

class Producer
{
    private Broker $broker;

    private AvroSchema $avroSchema;

    /**
     * The amount of attempts we will try to run the flush.
     * There's no magic number here, it depends on any factor
     * Try yourself a good number.
     *
     */
    private int $flushAttempts;

    /**
     * Whether if you want to receive the response asynchronously.
     *
     */
    private bool $isAsync;

    /**
     * The amount of records to be sent in every iteration
     * That means that at each 500 messages we check if messages was sent.
     *
     */
    private int $maxPollRecords;

    /**
     * Middlewares specific for this producer.
     *
     * @var array<mixed>
     */
    private array $middlewares;

    /**
     * Sets to true if you want to know if a message was successfully posted.
     *
     */
    private bool $requiredAcknowledgment;

    /**
     * We need to set a timeout when polling the messages.
     * That means: how long we'll wait a response from poll
     *
     */
    private int $timeout;

    private string $topicId;

    private ?int $partition = null;

    public function __construct(
        string $topicId,
        Broker $broker,
        ?int $partition = null,
        ?AvroSchema $avroSchema = null,
        array $middlewares = [],
        int $timeout = 1000,
        bool $isAsync = true,
        bool $requiredAcknowledgment = false,
        int $maxPollRecords = 500,
        int $flushAttempts = 10
    ) {
        $this->broker = $broker;
        $this->middlewares = $middlewares;
        $this->timeout = $timeout;
        $this->isAsync = $isAsync;
        $this->requiredAcknowledgment = $requiredAcknowledgment;
        $this->maxPollRecords = $maxPollRecords;
        $this->flushAttempts = $flushAttempts;
        $this->topicId = $topicId;
        $this->avroSchema = $avroSchema;
        $this->partition = $partition;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function isRequiredAcknowledgment(): bool
    {
        return $this->requiredAcknowledgment;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getMaxPollRecords(): int
    {
        return $this->maxPollRecords;
    }

    public function isAsync(): bool
    {
        return $this->isAsync;
    }

    public function getFlushAttempts(): int
    {
        return $this->flushAttempts;
    }

    public function getBroker(): Broker
    {
        return $this->broker;
    }

    public function getTopicId(): string
    {
        return $this->topicId;
    }

    public function getAvroSchema(): ?AvroSchema
    {
        return $this->avroSchema;
    }

    public function toArray(): array
    {
        $data = [
            'topic_id' => $this->getTopicId(),
            'timeout' => $this->getTimeout(),
            'partition' => $this->getPartition(),
            'is_async' => $this->isAsync(),
            'required_acknowledgment' => $this->isRequiredAcknowledgment(),
            'max_poll_records' => $this->getMaxPollRecords(),
            'flush_attempts' => $this->getFlushAttempts(),
            'middlewares' => $this->getMiddlewares(),
        ];

        if ($avroSchema = $this->getAvroSchema()) {
            $data = array_merge($data, $avroSchema->toArray());
        }

        return array_merge($this->broker->toArray(), $data);
    }

    public function getPartition(): int
    {
        return $this->partition ?? RD_KAFKA_PARTITION_UA;
    }
}

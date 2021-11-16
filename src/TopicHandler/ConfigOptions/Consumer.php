<?php
namespace Metamorphosis\TopicHandler\ConfigOptions;

class Consumer
{
    /**
     * @var Broker
     */
    private $broker;

    /**
     * @var AvroSchema
     */
    private $avroSchema;

    /**
     * Middlewares specific for this producer.
     *
     * @var array
     */
    private $middlewares;

    /**
     * We need to set a timeout when polling the messages.
     * That means: how long we'll wait a response from poll
     *
     * @var int
     */
    private $timeout;

    /**
     * @var ?int
     */
    private $partition;

    /**
     * @var string
     */
    private $topicId;

    /**
     * @var string
     */
    private $consumerGroup;

    /**
     * @var string
     */
    private $handler;

    /**
     * @var bool
     */
    private $autoCommit;

    /**
     * @var bool
     */
    private $commitASync;

    /**
     * @var string
     */
    private $offsetReset;

    public function __construct(
        string $topicId,
        Broker $broker,
        string $handler,
        ?int $partition = null,
        ?int $offset = null,
        string $consumerGroup = 'default',
        ?AvroSchema $avroSchema = null,
        array $middlewares = [],
        int $timeout = 1000,
        bool $autoCommit = true,
        bool $commitASync = true,
        string $offsetReset = 'smallest'
    ) {
        $this->broker = $broker;
        $this->middlewares = $middlewares;
        $this->timeout = $timeout;
        $this->partition = $partition;
        $this->offset = $offset;
        $this->topicId = $topicId;
        $this->avroSchema = $avroSchema;
        $this->consumerGroup = $consumerGroup;
        $this->handler = $handler;
        $this->autoCommit = $autoCommit;
        $this->commitASync = $commitASync;
        $this->offsetReset = $offsetReset;
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

    public function getBroker(): array
    {
        return $this->broker;
    }

    public function getPartition(): int
    {
        return $this->partition ?? RD_KAFKA_PARTITION_UA;
    }

    public function getTopicId(): string
    {
        return $this->topicId;
    }

    public function getAvroSchema(): array
    {
        return $this->avroSchema;
    }

    public function getConsumerGroup(): string
    {
        return $this->consumerGroup;
    }

    public function getHandler(): string
    {
        return $this->handler;
    }

    public function isAutoCommit(): bool
    {
        return $this->autoCommit;
    }

    public function isCommitASync(): bool
    {
        return $this->commitASync;
    }

    public function getOffsetReset(): string
    {
        return $this->offsetReset;
    }

    public function toArray(): array
    {
        $data = [
            'topic_id' => $this->getTopicId(),
            'timeout' => $this->getTimeout(),
            'is_async' => $this->isAsync(),
            'handler' => $this->getHandler(),
            'partition' => $this->getPartition(),
            'consumer_group' => $this->getConsumerGroup(),
            'required_acknowledgment' => $this->isRequiredAcknowledgment(),
            'max_poll_records' => $this->getMaxPollRecords(),
            'flush_attempts' => $this->getFlushAttempts(),
            'middlewares' => $this->getMiddlewares(),
            'auto_commit' => $this->isAutoCommit(),
            'commit_async' => $this->isCommitASync(),
            'offset_reset' => $this->getOffsetReset(),
        ];

        if ($avroSchema = $this->getAvroSchema()) {
            $data = array_merge($data, $avroSchema->toArray());
        }

        return array_merge($this->broker->toArray(), $data);
    }
}

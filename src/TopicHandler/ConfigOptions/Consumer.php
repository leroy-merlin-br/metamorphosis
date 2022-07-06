<?php

namespace Metamorphosis\TopicHandler\ConfigOptions;

class Consumer
{
    private Broker $broker;

    private AvroSchema $avroSchema;

    /**
     * Middlewares specific for this producer.
     *
     * @var array<mixed>
     */
    private array $middlewares;

    /**
     * We need to set a timeout when polling the messages.
     * That means: how long we'll wait a response from poll
     *
     */
    private int $timeout;

    private ?int $partition = null;

    private string $topicId;

    private string $consumerGroup;

    private string $handler;

    private bool $autoCommit;

    private bool $commitASync;

    private string $offsetReset;

    private ?int $offset = null;

    public function __construct(
        string $topicId,
        Broker $broker,
        ?string $handler = null,
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

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getBroker(): Broker
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

    public function getAvroSchema(): ?AvroSchema
    {
        return $this->avroSchema;
    }

    public function getConsumerGroup(): string
    {
        return $this->consumerGroup;
    }

    public function getHandler(): ?string
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
            'handler' => $this->getHandler(),
            'partition' => $this->getPartition(),
            'offset' => $this->getOffset(),
            'consumer_group' => $this->getConsumerGroup(),
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

    public function getOffset(): ?int
    {
        return $this->offset;
    }
}

<?php

namespace Metamorphosis\Record;

use Override;

class ProducerRecord implements RecordInterface
{
    /**
     * The message after any transformations that will be send to the topic.
     *
     * @var mixed
     */
    protected $payload;

    /**
     * @var mixed|null
     */
    protected $key;

    /**
     * The partition which the message will be send inside the topic.
     *
     */
    protected ?int $partition = null;

    /**
     * Original message before passed by any kind of transformations.
     *
     * @var mixed
     */
    protected $original;

    /**
     * Topic which the message will be send to.
     *
     */
    protected string $topic;

    public function __construct(string $payload, string $topic, ?int $partition = null, ?string $key = null)
    {
        $this->payload = $payload;
        $this->original = $payload;
        $this->partition = $partition;
        $this->topic = $topic;
        $this->key = $key;
    }

    #[Override]
    public function setPayload($payload): void
    {
        $this->payload = $payload;
    }

    #[Override]
    public function getPayload()
    {
        return $this->payload;
    }

    #[Override]
    public function getTopicName(): string
    {
        return $this->topic;
    }

    #[Override]
    public function getPartition(): ?int
    {
        return $this->partition;
    }

    #[Override]
    public function getKey(): ?string
    {
        return $this->key;
    }

    #[Override]
    public function getOriginal()
    {
        return $this->original;
    }
}

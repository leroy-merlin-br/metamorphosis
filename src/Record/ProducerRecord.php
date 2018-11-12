<?php
namespace Metamorphosis\Record;

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
     * @var int|null
     */
    protected $partition;

    /**
     * Original message before passed by any kind of transformations.
     *
     * @var mixed
     */
    protected $original;

    /**
     * Topic which the message will be send to.
     *
     * @var string
     */
    protected $topic;

    public function __construct(string $payload, string $topic, int $partition = null, string $key = null)
    {
        $this->payload = $payload;
        $this->original = $payload;
        $this->partition = $partition;
        $this->topic = $topic;
        $this->key = $key;
    }

    public function setPayload($payload): void
    {
        $this->payload = $payload;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function getTopicName(): string
    {
        return $this->topic;
    }

    public function getPartition(): ?int
    {
        return $this->partition;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getOriginal()
    {
        return $this->original;
    }
}

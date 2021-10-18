<?php
namespace Metamorphosis\TopicHandler;

class ConfigOptions
{
    /**
     * If your broker doest not have authentication, you can
     * remove this configuration, or set as empty.
     * The Authentication types may be "ssl" or "none"
     *
     * @example [
     *    'connections' => 'kafka:9092',
     *    'auth' => [
     *        'type' => 'ssl', // ssl and none
     *        'ca' => storage_path('ca.pem'),
     *        'certificate' => storage_path('kafka.cert'),
     *        'key' => storage_path('kafka.key'),
     *     ]
     * ]
     *
     * @var array
     */
    private $broker;

    /**
     * @example [
     *     'url' => 'http://schema-registry:8081',
     *     // Disable SSL verification on schema request.
     *     'ssl_verify' => true,
     *     // This option will be put directly into a Guzzle http request
     *     // Use this to do authorizations or send any headers you want.
     *     // Here is a example of basic authentication on AVRO schema.
     *     'request_options' => [
     *         'headers' => [
     *              'Authorization' => [
     *                  'Basic AUTHENTICATION'
     *              ],
     *         ],
     *     ],
     * ]
     *
     * @var array
     */
    private $avroSchema;

    /**
     * The amount of attempts we will try to run the flush.
     * There's no magic number here, it depends on any factor
     * Try yourself a good number.
     *
     * @var int
     */
    private $flushAttempts;

    /**
     * Whether if you want to receive the response asynchronously.
     *
     * @var bool
     */
    private $isAsync;

    /**
     * The amount of records to be sent in every iteration
     * That means that at each 500 messages we check if messages was sent.
     *
     * @var int
     */
    private $maxPollRecords;

    /**
     * Middlewares specific for this producer.
     *
     * @var array
     */
    private $middlewares;

    /**
     * Sets to true if you want to know if a message was successfully posted.
     *
     * @var bool
     */
    private $requiredAcknowledgment;

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

    public function __construct(
        string $topicId,
        array $broker,
        ?int $partition = null,
        array $avroSchema = [],
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
        $this->partition = $partition;
        $this->topicId = $topicId;
        $this->avroSchema = $avroSchema;
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

    public function toArray(): array
    {
        $broker = $this->getBroker();

        return [
            'topic_id' => $this->getTopicId(),
            'connections' => $broker['connections'] ?? null,
            'auth' => $broker['auth'] ?? null,
            'timeout' => $this->getTimeout(),
            'is_async' => $this->isAsync(),
            'partition' => $this->getPartition(),
            'required_acknowledgment' => $this->isRequiredAcknowledgment(),
            'max_poll_records' => $this->getMaxPollRecords(),
            'flush_attempts' => $this->getFlushAttempts(),
            'middlewares' => $this->getMiddlewares(),
            'avro_schema' => $this->getAvroSchema(),
        ];
    }

    public function getTopicId(): string
    {
        return $this->topicId;
    }

    public function getAvroSchema(): array
    {
        return $this->avroSchema;
    }
}

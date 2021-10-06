<?php

namespace Metamorphosis\TopicHandler\Producer;

class ConfigOptions
{
    /**
     * If your broker doest not have authentication, you can
     * remove this configuration, or set as empty.
     * The Authentication types may be "ssl" or "none"
     * @example [
     *    'type' => 'ssl', // ssl and none
     *    'ca' => storage_path('ca.pem'),
     *    'certificate' => storage_path('kafka.cert'),
     *    'key' => storage_path('kafka.key'),
     * ]
     *
     * @var array
     */
    private $auth;

    /**
     * Here you may point the key of the broker configured above.
     *
     * @var string
     */
    private $broker;

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
     * Disable SSL verification on schema request.
     *
     * @var bool
     */
    private $sslVerify;

    /**
     * @var string
     */
    private $topicConfigName;

    /**
     * @var int|null
     */
    private $partition;

    public function __construct(
        string $broker,
        string $topicConfigName,
        ?int $partition = null,
        array $auth = [],
        array $middlewares = [],
        int $timeout = 1000,
        bool $isAsync = true,
        bool $requiredAcknowledgment = false,
        int $maxPollRecords = 500,
        int $flushAttempts = 10,
        bool $sslVerify = true
    ) {
        $this->topicConfigName = $topicConfigName;
        $this->broker = $broker;
        $this->auth = $auth;
        $this->middlewares = $middlewares;
        $this->timeout = $timeout;
        $this->isAsync = $isAsync;
        $this->requiredAcknowledgment = $requiredAcknowledgment;
        $this->maxPollRecords = $maxPollRecords;
        $this->flushAttempts = $flushAttempts;
        $this->sslVerify = $sslVerify;
        $this->partition = $partition;
    }

    public function isSslVerify(): bool
    {
        return $this->sslVerify;
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

    public function getAuth(): array
    {
        return $this->auth;
    }

    public function getBroker(): string
    {
        return $this->broker;
    }

    public function getTopicConfigName(): string
    {
        return $this->topicConfigName;
    }

    public function getPartition(): ?int
    {
        return $this->partition;
    }

    public function toArray(): array
    {
        return [
            'topic' => $this->getTopicConfigName(),
            'broker' => $this->getBroker(),
            'timeout' => $this->getTimeout(),
            'is_async' => $this->isAsync(),
            'required_acknowledgment' => $this->isRequiredAcknowledgment(),
            'max_poll_records' => $this->getMaxPollRecords(),
            'flush_attempts' => $this->getFlushAttempts(),
            'auth' => $this->getAuth(),
            'middlewares' => $this->getMiddlewares(),
            'ssl_verify' => $this->isSslVerify(),
        ];
    }
}

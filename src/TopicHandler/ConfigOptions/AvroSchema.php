<?php

namespace Metamorphosis\TopicHandler\ConfigOptions;

use Metamorphosis\Exceptions\ConfigurationException;

class AvroSchema
{
    /**
     * @example 'http://schema-registry:8081'
     */
    private string $url;

    /**
     * Disable SSL verification on schema request.
     *
     */
    private bool $sslVerify;

    /**
     * This option will be put directly into a Guzzle http request
     * Use this to do authorizations or send any headers you want.
     * Here is an example of basic authentication on AVRO schema.
     *
     * @example  [
     *      'headers' => [
     *          'Authorization' => [
     *                'Basic AUTHENTICATION',
     *           ],
     *       ],
     * ],
     *
     * @var mixed[]
     */
    private array $requestOptions;

    /**
     * @var mixed[]
     */
    private array $configOptions;

    public function __construct(
        string $url,
        array $requestOptions = [],
        bool $sslVerify = true,
        array $configOptions = []
    ) {
        $this->url = $url;
        $this->requestOptions = $requestOptions;
        $this->sslVerify = $sslVerify;
        $this->configOptions = $configOptions;
    }

    public function toArray(): array
    {
        return [
            'url' => $this->getUrl(),
            'request_options' => $this->getRequestOptions(),
            'ssl_verify' => $this->isSslVerify(),
            'config_options' => $this->getConfigOptions(),
        ];
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isSslVerify(): bool
    {
        return $this->sslVerify;
    }

    public function getRequestOptions(): array
    {
        return $this->requestOptions;
    }

    public function getConfigOptions(): array
    {
        return $this->configOptions;
    }

    /**
     * @throws ConfigurationException
     */
    public function getValueSubjectName(string $topicName): string
    {
        $strategy = $this->configOptions['subject']['value']['strategy'] ?? 'TopicNameStrategy';
        $namespace = $this->configOptions['subject']['value']['namespace'] ?? null;

        return match ($strategy) {
            'TopicNameStrategy' => "{$topicName}-value",
            'TopicRecordNameStrategy' => $namespace ? "{$topicName}-{$namespace}" : throw new ConfigurationException('Namespace is required for TopicRecordNameStrategy'),
            'RecordNameStrategy' => throw new ConfigurationException('RecordNameStrategy is not supported. Please use TopicNameStrategy (default) or TopicRecordNameStrategy instead.'),
            default => throw new ConfigurationException('Invalid name strategy'),
        };
    }
}

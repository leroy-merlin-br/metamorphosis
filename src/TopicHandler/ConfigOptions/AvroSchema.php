<?php

namespace Metamorphosis\TopicHandler\ConfigOptions;

class AvroSchema
{
    /**
     * @example 'http://schema-registry:8081'
     *
     * @var string
     */
    private $url;

    /**
     * Disable SSL verification on schema request.
     *
     * @var bool
     */
    private $sslVerify;

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
     * @var array
     */
    private $requestOptions;

    public function __construct(string $url, array $requestOptions = [], bool $sslVerify = true)
    {
        $this->url = $url;
        $this->requestOptions = $requestOptions;
        $this->sslVerify = $sslVerify;
    }

    public function toArray(): array
    {
        return [
            'url' => $this->getUrl(),
            'request_options' => $this->getRequestOptions(),
            'ssl_verify' => $this->isSslVerify(),
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
}

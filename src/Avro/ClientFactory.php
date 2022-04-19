<?php

namespace Metamorphosis\Avro;

use GuzzleHttp\Client as GuzzleClient;
use Metamorphosis\TopicHandler\ConfigOptions\AvroSchema;

class ClientFactory
{
    protected const REQUEST_TIMEOUT = 2000;

    public function make(AvroSchema $avroSchema): CachedSchemaRegistryClient
    {
        $guzzleHttp = $this->getGuzzleHttpClient($avroSchema);

        $client = app(Client::class, ['client' => $guzzleHttp]);

        return app(CachedSchemaRegistryClient::class, compact('client'));
    }

    private function getGuzzleHttpClient(AvroSchema $avroSchema): GuzzleClient
    {
        $config = $avroSchema->getRequestOptions();
        $config['timeout'] = self::REQUEST_TIMEOUT;
        $config['base_uri'] = $avroSchema->getUrl();
        $config['headers'] = array_merge(
            $this->getDefaultHeaders(),
            $config['headers'] ?? []
        );
        $config['verify'] = $avroSchema->getRequestOptions()['ssl_verify'] ?? false;

        return app(GuzzleClient::class, compact('config'));
    }

    private function getDefaultHeaders(): array
    {
        return [
            'Accept' => 'application/vnd.schemaregistry.v1+json, application/vnd.schemaregistry+json, application/json',
        ];
    }
}

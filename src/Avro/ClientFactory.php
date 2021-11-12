<?php
namespace Metamorphosis\Avro;

use GuzzleHttp\Client as GuzzleClient;
use Metamorphosis\AbstractConfigManager;

class ClientFactory
{
    public function make(AbstractConfigManager $configManager): CachedSchemaRegistryClient
    {
        $guzzleHttp = $this->getGuzzleHttpClient($configManager);

        $client = app(Client::class, ['client' => $guzzleHttp]);

        return app(CachedSchemaRegistryClient::class, compact('client'));
    }

    private function getGuzzleHttpClient(AbstractConfigManager $configManager): GuzzleClient
    {
        $config = $configManager->get('request_options') ?: [];
        $config['timeout'] = $configManager->get('timeout');
        $config['base_uri'] = $configManager->get('url');
        $config['headers'] = array_merge(
            $this->getDefaultHeaders(),
            $config['headers'] ?? []
        );
        $config['verify'] = $configManager->get('ssl_verify') ?? false;

        return app(GuzzleClient::class, compact('config'));
    }

    private function getDefaultHeaders(): array
    {
        return [
            'Accept' => 'application/vnd.schemaregistry.v1+json, application/vnd.schemaregistry+json, application/json',
        ];
    }
}

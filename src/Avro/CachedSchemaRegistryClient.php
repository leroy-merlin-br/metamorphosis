<?php
namespace Metamorphosis\Avro;

use AvroSchemaParseException;
use GuzzleHttp\Client as GuzzleClient;
use Metamorphosis\ConfigManager;
use RuntimeException;

class CachedSchemaRegistryClient
{
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Schema[]
     */
    private $idToSchema = [];

    /** Schemas by Version
     *
     * @var Schema[][]
     */
    private $subjectVersionToSchema = [];

    public function setClientConfig(ConfigManager $configManager): void
    {
        $guzzleHttp = $this->getGuzzleHttpClient($configManager);

        $this->client = new Client($guzzleHttp);
    }

    /**
     * GET /schemas/ids/{int: id}
     * Retrieve a parsed avro schema by id or None if not found
     *
     * @param string|int $schemaId
     */
    public function getById($schemaId): Schema
    {
        if (isset($this->idToSchema[$schemaId])) {
            return $this->idToSchema[$schemaId];
        }

        $schema = app(Schema::class);
        $url = sprintf('/schemas/ids/%d', $schemaId);
        [$status, $response] = $this->client->get($url);

        if (404 === $status) {
            throw new RuntimeException('Schema not found');
        } elseif (!($status >= 200 && $status < 300)) {
            throw new RuntimeException('Unable to get schema for the specific ID: '.$status);
        }

        $schema = $schema->parse($response['schema'], $schemaId);

        $this->cacheSchema($schema);

        return $this->idToSchema[$schemaId];
    }

    /**
     * @param string     $subject
     * @param int|string $version Version number or 'latest'
     *
     * @throws AvroSchemaParseException
     * @throws RuntimeException
     */
    public function getBySubjectAndVersion($subject, $version): Schema
    {
        if (isset($this->subjectVersionToSchema[$subject][$version])) {
            return $this->subjectVersionToSchema[$subject][$version];
        }
        $schema = app(Schema::class);

        $version = 'latest' === $version ? 'latest' : (int) $version;
        $url = sprintf('/subjects/%s/versions/%s', $subject, $version);
        [$status, $response] = $this->client->get($url);

        if (404 === $status) {
            throw new RuntimeException('Schema not found');
        } elseif (!($status >= 200 && $status < 300)) {
            throw new RuntimeException('Unable to get schema for the specific ID: '.$status);
        }

        $schemaId = $response['id'];
        $schema = $schema->parse($response['schema'], $schemaId, $subject, $version);

        $this->cacheSchema($schema);

        return $this->subjectVersionToSchema[$subject][$version];
    }

    private function cacheSchema(Schema $schema): void
    {
        if ($schema->getSubject() && $schema->getVersion()) {
            $this->subjectVersionToSchema[$schema->getSubject()][$schema->getVersion()] = $schema;
        }

        $this->idToSchema[$schema->getSchemaId()] = $schema;
    }

    private function getGuzzleHttpClient(ConfigManager $configManager): GuzzleClient
    {
        $config = $configManager->get('request_options') ?: [];
        $config['timeout'] = $configManager->get('timeout');
        $config['base_uri'] = $configManager->get('url');
        $config['headers'] = array_merge(
            $this->getDefaultHeaders(),
            $config['headers'] ?? []
        );

        return app(GuzzleClient::class, compact('config'));
    }

    private function getDefaultHeaders(): array
    {
        return [
            'Accept' => 'application/vnd.schemaregistry.v1+json, application/vnd.schemaregistry+json, application/json',
        ];
    }
}

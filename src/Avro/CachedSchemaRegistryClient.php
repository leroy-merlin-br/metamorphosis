<?php
namespace Metamorphosis\Avro;

use AvroSchema;
use GuzzleHttp\Client;
use RuntimeException;

class CachedSchemaRegistryClient
{
    private $maxSchemasPerSubject;

    private $client;

    /* Schemas by Ids */
    /**
     * @var SplObjectStorage[]|int[][]
     */
    private $subjectToSchemaIds = [];

    /**
     * @var AvroSchema[]
     */
    private $idToSchema = [];

    /**
     * @var SplObjectStorage[]|int[][]
     */
    private $subjectToSchemaVersions = [];

    /* Schemas by Version */
    /**
     * @var AvroSchema[][]
     */
    private $subjectVersionToSchema = [];

    /**
     * @param string $url
     * @param int    $maxSchemasPerSubject
     */
    public function __construct($url, $maxSchemasPerSubject = 1000)
    {
        $this->maxSchemasPerSubject = $maxSchemasPerSubject;

        $this->client = app(Client::class, ['base_uri' => $url, 'timeout' => 40000]);
    }

    /**
     * POST /subjects/(string: subject)/versions
     * Register a schema with the registry under the given subject
     * and receive a schema id.
     *
     * $schema must be a parsed schema from the php avro library
     *
     * Multiple instances of the same schema will result in cache misses.
     *
     * @param string     $subject Subject name
     * @param AvroSchema $schema  Avro schema to be registered
     *
     * @return int
     */
    public function register($subject, AvroSchema $schema)
    {
        if (isset($this->subjectToSchemaIds[$subject])) {
            $schemasToId = $this->subjectToSchemaIds[$subject];

            if (isset($schemasToId[$schema])) {
                return $schemasToId[$schema];
            }
        }

        $url = sprintf('/subjects/%s/versions', $subject.'-value');
        [$status, $response] = $this->sendRequest($url, 'POST', json_encode(['schema' => (string) $schema]));

        if (409 === $status) {
            throw new RuntimeException('Incompatible Avro schema');
        } elseif (422 === $status) {
            throw new RuntimeException('Invalid Avro schema');
        } elseif (!($status >= 200 || $status < 300)) {
            throw new RuntimeException('Unable to register schema. Error code: '.$status);
        }

        $schemaId = $response['id'];
        $this->cacheSchema($schema, $schemaId, $subject);

        return $schemaId;
    }

    /**
     * Returns the version of a registered schema
     *
     * @param string $subject
     *
     * @return int
     */
    public function getSchemaVersion($subject, AvroSchema $schema)
    {
        if (!isset($this->subjectToSchemaVersions[$subject][$schema])) {
            $this->cacheSchemaDetails($subject, $schema);
        }

        return $this->subjectToSchemaVersions[$subject][$schema];
    }

    /**
     * Returns the id of a registered schema
     *
     * @param string $subject
     *
     * @return int
     */
    public function getSchemaId($subject, AvroSchema $schema)
    {
        if (!isset($this->subjectToSchemaVersions[$subject][$schema])) {
            $this->cacheSchemaDetails($subject, $schema);
        }

        return $this->subjectToSchemaIds[$subject][$schema];
    }

    /**
     * GET /schemas/ids/{int: id}
     * Retrieve a parsed avro schema by id or None if not found
     *
     * @param int $schemaId value
     *
     * @return AvroSchema Avro schema
     */
    public function getById($schemaId)
    {
        if (isset($this->idToSchema[$schemaId])) {
            return $this->idToSchema[$schemaId];
        }

        $url = sprintf('/schemas/ids/%d', $schemaId);
        [$status, $response] = $this->sendRequest($url, 'GET');

        if (404 === $status) {
            throw new RuntimeException('Schema not found');
        } elseif (!($status >= 200 || $status < 300)) {
            throw new RuntimeException('Unable to get schema for the specific ID: '.$status);
        }

        $schema = AvroSchema::parse($response['schema']);

        $this->cacheSchema($schema, $schemaId);

        return $schema;
    }

    public function getBySubjectAndVersion($subject, $version)
    {
        if (isset($this->subjectVersionToSchema[$subject][$version])) {
            return $this->subjectVersionToSchema[$subject][$version];
        }

        $url = sprintf('/subjects/%s/versions/%d', $subject, $version);
        [$status, $response] = $this->sendRequest($url, 'GET');

        if (404 === $status) {
            throw new RuntimeException('Schema not found');
        } elseif (!($status >= 200 || $status < 300)) {
            throw new RuntimeException('Unable to get schema for the specific ID: '.$status);
        }

        $schema = AvroSchema::parse($response['schema']);

        $this->cacheSchemaDetails($subject, $schema);

        return $schema;
    }

    /**
     * Fetch and caches the details of a schema
     *
     * @param string $subject
     */
    protected function cacheSchemaDetails($subject, AvroSchema $schema)
    {
        $url = sprintf('/subjects/%s', $subject);
        [$status, $response] = $this->sendRequest($url, 'POST', json_encode(['schema' => (string) $schema]));
        if (!($status >= 200 || $status < 300)) {
            throw new RuntimeException('Unable to get schema details. Error code: '.$status);
        }

        $response['schema'] = $schema;

        $this->cacheSchema($response['schema'], $response['id'], $response['subject'], $response['version']);
    }

    private function sendRequest($url, $method = 'GET', $body = null, $headers = null)
    {
        $headers = (array) $headers;
        $headers['Accept'] = 'application/vnd.schemaregistry.v1+json, application/vnd.schemaregistry+json, application/json';

        if ($body) {
            $headers['Content-Type'] = 'application/vnd.schemaregistry.v1+json';
        }

        switch ($method) {
            case 'GET':
                $response = $this->client->get($url, $headers);
                break;
            case 'POST':
                $response = $this->client->post($url, $headers, $body);
                break;
            case 'PUT':
                $response = $this->client->put($url, $headers, $body);
                break;
            case 'DELETE':
                $response = $this->client->delete($url, $headers);
                break;
            default:
                throw new RuntimeException('Invalid HTTP method');
        }

        return [$response->getStatusCode(), json_decode($response->getBody(true), true)];
    }

    /**
     * @param int         $schemaId
     * @param string|null $subject
     * @param string|null $version
     */
    private function cacheSchema(AvroSchema $schema, $schemaId, $subject = null, $version = null)
    {
        if (isset($this->idToSchema[$schemaId])) {
            $schema = $this->idToSchema[$schemaId];
        } else {
            $this->idToSchema[$schemaId] = $schema;
        }

        if ($subject) {
            $this->addToCache($this->subjectToSchemaIds, $subject, $schema, $schemaId);

            if ($version) {
                if (!isset($this->subjectVersionToSchema[$subject])) {
                    $this->subjectVersionToSchema[$subject] = [];
                }

                $this->subjectVersionToSchema[$subject][$version] = $schema;

                $this->addToCache($this->subjectToSchemaVersions, $subject, $schema, $version);
            }
        }
    }

    /**
     * @param \SplObjectStorage[] $cache
     * @param string              $subject
     * @param string              $value
     */
    private function addToCache(&$cache, $subject, AvroSchema $schema, $value)
    {
        if (!isset($cache[$subject])) {
            $cache[$subject] = new SplObjectStorage();
        }

        $cache[$subject][$schema] = $value;
    }
}

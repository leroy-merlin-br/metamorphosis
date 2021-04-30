<?php
namespace Metamorphosis\Avro;

use AvroSchema;
use RuntimeException;
use SplObjectStorage;

class CachedSchemaRegistryClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * In the first time we register an schema and receive a schema
     * Id, we will cache it to new requests.
     *
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

    /** Schemas by Version
     *
     * @var AvroSchema[][]
     */
    private $subjectVersionToSchema = [];

    public function __construct(Client $client)
    {
        $this->client = $client;
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
    public function register(string $subject, AvroSchema $schema)
    {
        if (isset($this->subjectToSchemaIds[$subject])) {
            $schemasToId = $this->subjectToSchemaIds[$subject];

            if (isset($schemasToId[$schema])) {
                return $schemasToId[$schema];
            }
        }

        $url = sprintf('/subjects/%s-value/versions', $subject);
        [$status, $response] = $this->client->post($url, ['schema' => (string) $schema]);

        if (409 === $status) {
            throw new RuntimeException('Incompatible Avro schema');
        } elseif (422 === $status) {
            throw new RuntimeException('Invalid Avro schema');
        } elseif (!($status >= 200 && $status < 300)) {
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
        [$status, $response] = $this->client->get($url);

        if (404 === $status) {
            throw new RuntimeException('Schema not found');
        } elseif (!($status >= 200 && $status < 300)) {
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
        [$status, $response] = $this->client->get($url);

        if (404 === $status) {
            throw new RuntimeException('Schema not found');
        } elseif (!($status >= 200 && $status < 300)) {
            throw new RuntimeException('Unable to get schema for the specific ID: '.$status);
        }

        $schemaId = $response['id'];
        $schema = AvroSchema::parse($response['schema']);

        $this->cacheSchema($schema, $schemaId, $subject, $version);

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
        [$status, $response] = $this->client->post($url, ['schema' => (string) $schema]);
        if (!($status >= 200 && $status < 300)) {
            throw new RuntimeException('Unable to get schema details. Error code: '.$status);
        }

        $response['schema'] = $schema;

        $this->cacheSchema($response['schema'], $response['id'], $response['subject'], $response['version']);
    }

    private function cacheSchema(AvroSchema $schema, string $schemaId, string $subject = null, string $version = null): void
    {
        if (isset($this->idToSchema[$schemaId])) {
            $schema = $this->idToSchema[$schemaId];
        } else {
            $this->idToSchema[$schemaId] = $schema;
        }

        if ($subject) {
            $this->addSchemaIdsToCache($subject, $schema, $schemaId);

            if ($version) {
                if (!isset($this->subjectVersionToSchema[$subject])) {
                    $this->subjectVersionToSchema[$subject] = [];
                }

                $this->subjectVersionToSchema[$subject][$version] = $schema;
                $this->addSchemaVersionToCache($subject, $schema, $version);
            }
        }
    }

    private function addSchemaIdsToCache(string $subject, AvroSchema $schema, string $schemaId): void
    {
        if (!isset($this->subjectToSchemaIds[$subject])) {
            $this->subjectToSchemaIds[$subject] = new SplObjectStorage();
        }

        $this->subjectToSchemaIds[$subject][$schema] = $schemaId;
    }

    private function addSchemaVersionToCache(string $subject, AvroSchema $schema, string $version): void
    {
        if (!isset($this->subjectToSchemaVersions[$subject])) {
            $this->subjectToSchemaVersions[$subject] = new SplObjectStorage();
        }

        $this->subjectToSchemaVersions[$subject][$schema] = $version;
    }
}

<?php

namespace Tests\Unit\Avro;

use AvroSchema;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\Client;
use Metamorphosis\Avro\Schema;
use Mockery as m;
use RuntimeException;
use Tests\LaravelTestCase;

class CachedSchemaRegistryClientTest extends LaravelTestCase
{
    public function testGetById(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schemaString = $this->getSchemaTest();
        $parsedSchema = AvroSchema::parse($this->getSchemaTest());
        $schema = new Schema();
        $schema->setAvroSchema($parsedSchema);
        $schema->setSchemaId('123');

        $response = [
            'schema' => $schemaString,
            'id' => '123',
            'version' => '1.2',
        ];
        $status = 200;

        // Expectations
        $httpClient->expects()
            ->get('schemas/ids/123')
            ->andReturn([$status, $response]);

        // Actions
        $result = $client->getById('123');

        // Assertions
        $this->assertEquals($schema, $result);
    }

    public function testGetByIdShouldHitCache(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(Schema::class);
        $schemaString = $this->getSchemaTest();

        $response = [
            'schema' => $schemaString,
            'id' => '123',
            'version' => '1.2',
        ];
        $status = 200;

        // Expectations
        $httpClient->expects()
            ->get('schemas/ids/123')
            ->once()
            ->andReturn([$status, $response]);

        // Actions
        $client->getById('123', $schema);
        $client->getById('123', $schema);
        $client->getById('123', $schema);
        $client->getById('123', $schema);
    }

    public function testGetByIdWithoutSchemas(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(Schema::class);
        $schemaString = $this->getSchemaTest();

        $response = [
            'schema' => $schemaString,
            'id' => '123',
            'version' => '1.2',
        ];
        $status = 404;

        // Expectations
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Schema not found');
        $httpClient->expects()
            ->get('schemas/ids/123')
            ->andReturn([$status, $response]);

        // Actions
        $client->getById('123', $schema);
    }

    public function testGetByIdMayReturnErrors(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(Schema::class);
        $schemaString = $this->getSchemaTest();

        $response = [
            'schema' => $schemaString,
            'id' => '123',
            'version' => '1.2',
        ];
        $status = 199;

        // Expectations
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Unable to get schema for the specific ID: {$status}"
        );
        $httpClient->expects()
            ->get('schemas/ids/123')
            ->andReturn([$status, $response]);

        // Actions
        $client->getById('123', $schema);
    }

    public function testGetBySubjectAndVersion(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schemaString = $this->getSchemaTest();
        $parsedSchema = AvroSchema::parse($schemaString);
        $schema = new Schema();
        $schema->setAvroSchema($parsedSchema);
        $schema->setSchemaId('123');
        $schema->setVersion(1);
        $schema->setSubject('some-kafka-topic');

        $response = [
            'schema' => $schemaString,
            'id' => '123',
            'version' => 1,
            'subject' => 'some-kafka-topic',
        ];
        $status = 200;

        // Expectations
        $httpClient->expects()
            ->get('subjects/some-kafka-topic/versions/1')
            ->andReturn([$status, $response]);

        // Actions
        $result = $client->getBySubjectAndVersion('some-kafka-topic', '1.2');

        // Assertions
        $this->assertEquals($schema, $result);
    }

    public function testGetBySubjectAndVersionWithNotFoundSchema(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schemaString = $this->getSchemaTest();

        $response = [
            'schema' => $schemaString,
            'id' => '123',
            'version' => '1.2',
            'subject' => 'some-kafka-topic',
        ];
        $status = 404;

        // Expectations
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Schema not found');
        $httpClient->expects()
            ->get('subjects/some-kafka-topic/versions/1')
            ->andReturn([$status, $response]);

        // Actions
        $client->getBySubjectAndVersion('some-kafka-topic', '1.2');
    }

    public function testGetBySubjectAndVersionMayReturnErrors(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schemaString = $this->getSchemaTest();

        $response = [
            'schema' => $schemaString,
            'id' => '123',
            'version' => '1.2',
            'subject' => 'some-kafka-topic',
        ];
        $status = 199;

        // Expectations
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Unable to get schema for the specific ID: {$status}"
        );
        $httpClient->expects()
            ->get('subjects/some-kafka-topic/versions/1')
            ->andReturn([$status, $response]);

        // Actions
        $client->getBySubjectAndVersion('some-kafka-topic', '1.2');
    }

    public function testGetBySubjectAndVersionShouldHitCache(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schemaString = $this->getSchemaTest();

        $response = [
            'schema' => $schemaString,
            'id' => '123',
            'version' => 'latest',
            'subject' => 'some-kafka-topic',
        ];
        $status = 200;

        // Expectations
        $httpClient->expects()
            ->get('subjects/some-kafka-topic/versions/latest')
            ->once()
            ->andReturn([$status, $response]);

        // Actions
        $client->getBySubjectAndVersion('some-kafka-topic', 'latest');
        $client->getBySubjectAndVersion('some-kafka-topic', 'latest');
        $client->getBySubjectAndVersion('some-kafka-topic', 'latest');
        $client->getBySubjectAndVersion('some-kafka-topic', 'latest');
    }

    private function getSchemaTest(): string
    {
        return file_get_contents(
            __DIR__ . '/../fixtures/schemas/sales_price.avsc'
        );
    }
}

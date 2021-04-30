<?php
namespace Tests\Unit\Avro;

use AvroSchema;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\Client;
use Mockery as m;
use RuntimeException;
use Tests\LaravelTestCase;

class CachedSchemaRegistryClientTest extends LaravelTestCase
{
    public function testRegister(): void
    {
        // Set
        config(['kafka.avro_schemas.default.url' => 'http://test.com']);
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(AvroSchema::class);
        $response = ['id' => '123'];
        $status = 200;

        // Expectations
        $httpClient->expects()
            ->post('/subjects/some-kafka-topic-value/versions', compact('schema'))
            ->andReturn([$status, $response]);

        // Actions
        $result = $client->register('some-kafka-topic', $schema);

        // Assertions
        $this->assertSame('123', $result);
    }

    public function testRegisterShouldHitCache(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(AvroSchema::class);
        $response = ['id' => '123'];
        $status = 200;

        // Expectations
        $httpClient->expects()
            ->post('/subjects/some-kafka-topic-value/versions', compact('schema'))
            ->once()
            ->andReturn([$status, $response]);

        // Actions
        $client->register('some-kafka-topic', $schema);
        $client->register('some-kafka-topic', $schema);
    }

    public function testRegisterWithIncompatibleAvroSchema(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(AvroSchema::class);
        $response = ['id' => '123'];
        $status = 409;

        // Expectations
        $this->expectException(RuntimeException::class);
        $httpClient->expects()
            ->post('/subjects/some-kafka-topic-value/versions', compact('schema'))
            ->andReturn([$status, $response]);

        // Actions
        $client->register('some-kafka-topic', $schema);
    }

    public function testRegisterWithInvalidAvroSchema(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(AvroSchema::class);
        $response = ['id' => '123'];
        $status = 422;

        // Expectations
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid Avro schema');
        $httpClient->expects()
            ->post('/subjects/some-kafka-topic-value/versions', compact('schema'))
            ->andReturn([$status, $response]);

        // Actions
        $client->register('some-kafka-topic', $schema);
    }

    public function testRegisterMayBeUnableToRegisterSchema(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(AvroSchema::class);
        $response = ['id' => '123'];
        $status = 199;

        // Expectations
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to register schema. Error code: {$status}");
        $httpClient->expects()
            ->post('/subjects/some-kafka-topic-value/versions', compact('schema'))
            ->andReturn([$status, $response]);

        // Actions
        $client->register('some-kafka-topic', $schema);
    }

    public function testGetSchemaVersion(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(AvroSchema::class);
        $response = [
            'subject' => 'some-kafka-topic',
            'id' => '123',
            'version' => '1.2',
        ];
        $status = 200;

        // Expectations
        $httpClient->expects()
            ->post('/subjects/some-kafka-topic', compact('schema'))
            ->andReturn([$status, $response]);

        // Actions
        $result = $client->getSchemaVersion('some-kafka-topic', $schema);

        // Assertions
        $this->assertSame('1.2', $result);
    }

    public function testGetSchemaVersionShouldHitCache(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(AvroSchema::class);
        $response = [
            'subject' => 'some-kafka-topic',
            'id' => '123',
            'version' => '1.2',
        ];
        $status = 200;

        // Expectations
        $httpClient->expects()
            ->post('/subjects/some-kafka-topic', compact('schema'))
            ->once()
            ->andReturn([$status, $response]);

        // Actions
        $client->getSchemaVersion('some-kafka-topic', $schema);
        $client->getSchemaVersion('some-kafka-topic', $schema);
        $client->getSchemaVersion('some-kafka-topic', $schema);
        $client->getSchemaVersion('some-kafka-topic', $schema);
    }

    public function testGetSchemaId(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(AvroSchema::class);
        $response = [
            'subject' => 'some-kafka-topic',
            'id' => '123',
            'version' => '1.2',
        ];
        $status = 200;

        // Expectations
        $httpClient->expects()
            ->post('/subjects/some-kafka-topic', compact('schema'))
            ->andReturn([$status, $response]);

        // Actions
        $result = $client->getSchemaId('some-kafka-topic', $schema);

        // Assertions
        $this->assertSame('123', $result);
    }

    public function testGetSchemaIdShouldHitCache(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(AvroSchema::class);
        $response = [
            'subject' => 'some-kafka-topic',
            'id' => '123',
            'version' => '1.2',
        ];
        $status = 200;

        // Expectations
        $httpClient->expects()
            ->post('/subjects/some-kafka-topic', compact('schema'))
            ->once()
            ->andReturn([$status, $response]);

        // Actions
        $client->getSchemaId('some-kafka-topic', $schema);
        $client->getSchemaId('some-kafka-topic', $schema);
        $client->getSchemaId('some-kafka-topic', $schema);
        $client->getSchemaId('some-kafka-topic', $schema);
    }

    public function testGetById(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(AvroSchema::class);
        $schemaString = $this->getSchemaTest();
        $parsedSchema = AvroSchema::parse($this->getSchemaTest());

        $response = [
            'schema' => $schemaString,
            'id' => '123',
            'version' => '1.2',
        ];
        $status = 200;

        // Expectations
        $httpClient->expects()
            ->get('/schemas/ids/123')
            ->andReturn([$status, $response]);

        // Actions
        $result = $client->getById('123', $schema);

        // Assertions
        $this->assertEquals($parsedSchema, $result);
    }

    public function testGetByIdShouldHitCache(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(AvroSchema::class);
        $schemaString = $this->getSchemaTest();

        $response = [
            'schema' => $schemaString,
            'id' => '123',
            'version' => '1.2',
        ];
        $status = 200;

        // Expectations
        $httpClient->expects()
            ->get('/schemas/ids/123')
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
        $schema = m::mock(AvroSchema::class);
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
            ->get('/schemas/ids/123')
            ->andReturn([$status, $response]);

        // Actions
        $client->getById('123', $schema);
    }

    public function testGetByIdMayReturnErrors(): void
    {
        // Set
        $httpClient = m::mock(Client::class);
        $client = new CachedSchemaRegistryClient($httpClient);
        $schema = m::mock(AvroSchema::class);
        $schemaString = $this->getSchemaTest();

        $response = [
            'schema' => $schemaString,
            'id' => '123',
            'version' => '1.2',
        ];
        $status = 199;

        // Expectations
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to get schema for the specific ID: {$status}");
        $httpClient->expects()
            ->get('/schemas/ids/123')
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

        $response = [
            'schema' => $schemaString,
            'id' => '123',
            'version' => '1.2',
            'subject' => 'some-kafka-topic',
        ];
        $status = 200;

        // Expectations
        $httpClient->expects()
            ->get('/subjects/some-kafka-topic/versions/1')
            ->andReturn([$status, $response]);

        // Actions
        $result = $client->getBySubjectAndVersion('some-kafka-topic', '1.2');

        // Assertions
        $this->assertEquals($parsedSchema, $result);
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
            ->get('/subjects/some-kafka-topic/versions/1')
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
        $this->expectExceptionMessage("Unable to get schema for the specific ID: {$status}");
        $httpClient->expects()
            ->get('/subjects/some-kafka-topic/versions/1')
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
            ->get('/subjects/some-kafka-topic/versions/latest')
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
        return file_get_contents(__DIR__.'/../fixtures/schemas/sales_price.avsc');
    }
}

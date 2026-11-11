<?php

namespace Tests\Unit\Middlewares;

use AvroSchema;
use Closure;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\ClientFactory;
use Metamorphosis\Avro\Schema;
use Metamorphosis\Avro\Serializer\Encoders\SchemaId;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\Middlewares\AvroSchemaMixedEncoder;
use Metamorphosis\Record\ProducerRecord;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\AvroSchema as AvroSchemaConfigOptions;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ProducerConfigOptions;
use Mockery as m;
use Tests\LaravelTestCase;

class AvroSchemaMixedEncoderTest extends LaravelTestCase
{
    public function testShouldEncodeRecord(): void
    {
        // Set
        $avroSchema = $this->getSchemaFixture();
        $connections = (string) config(
            'service.broker.connections',
            'kafka:29092'
        );
        $broker = new Broker($connections, new None());
        $producerConfigOptions = new ProducerConfigOptions(
            'kafka-test',
            $broker,
            null,
            new AvroSchemaConfigOptions(
                'subjects/kafka-test-value/versions/latest',
                []
            )
        );
        $avroSchemaConfigOptions = $producerConfigOptions->getAvroSchema();
        $clientFactory = m::mock(ClientFactory::class);
        $cachedClient = m::mock(CachedSchemaRegistryClient::class);
        $schemaIdEncoder = m::mock(SchemaId::class, [$cachedClient]);

        $schema = new Schema();
        $parsedSchema = $schema->parse(
            $avroSchema,
            '123',
            'kafka-test-value',
            'latest'
        );
        $record = $this->getRecord($parsedSchema->getAvroSchema());
        $producerRecord = new ProducerRecord($record, 'kafka-test');

        $closure = Closure::fromCallable(function ($producerRecord) {
            return $producerRecord;
        });

        $payload = json_decode($producerRecord->getPayload(), true);
        $encodedMessage = 'binary_message';

        // Expectations
        $clientFactory->expects()
            ->make($avroSchemaConfigOptions)
            ->andReturn($cachedClient);

        $cachedClient->expects()
            ->getBySubjectAndVersion('kafka-test-value', 'latest')
            ->andReturn($schema);

        $schemaIdEncoder->expects()
            ->encode($schema, $payload)
            ->andReturn($encodedMessage);

        // Actions
        $avroSchemaMixedEncoder = new AvroSchemaMixedEncoder(
            $schemaIdEncoder,
            $clientFactory,
            $producerConfigOptions
        );
        $result = $avroSchemaMixedEncoder->process($producerRecord, $closure);

        // Assertions
        $this->assertSame($record, $result->getOriginal());
        $this->assertSame($encodedMessage, $result->getPayload());
    }

    public function testShouldEncodeRecordWhenTopicNameHasTopicRecordNameStrategy(): void
    {
        // Set
        $avroSchema = $this->getSchemaFixture();
        $connections = (string) config(
            'service.broker.connections',
            'kafka:29092'
        );

        $broker = new Broker($connections, new None());
        $avroConfig = new AvroSchemaConfigOptions(
            'schema.registry.url',
            [],
            true,
            [
                'subject' => [
                    'value' => [
                        'strategy' => 'TopicRecordNameStrategy',
                        'namespace' => 'com.some.namespace',
                    ],
                ],
            ]
        );

        $producerConfigOptions = new ProducerConfigOptions(
            'kafka-test',
            $broker,
            null,
            $avroConfig
        );

        $avroSchemaConfigOptions = $producerConfigOptions->getAvroSchema();
        $clientFactory = m::mock(ClientFactory::class);
        $cachedClient = m::mock(CachedSchemaRegistryClient::class);
        $schemaIdEncoder = m::mock(SchemaId::class, [$cachedClient]);

        $schema = new Schema();
        $parsedSchema = $schema->parse(
            $avroSchema,
            '123',
            'kafka-test-com.some.namespace',
            'latest'
        );
        $record = $this->getRecord($parsedSchema->getAvroSchema());
        $producerRecord = new ProducerRecord($record, 'kafka-test');

        $closure = Closure::fromCallable(function ($producerRecord) {
            return $producerRecord;
        });

        $payload = json_decode($producerRecord->getPayload(), true);
        $encodedMessage = 'binary_message';

        // Expectations
        $clientFactory->expects()
            ->make($avroSchemaConfigOptions)
            ->andReturn($cachedClient);

        $cachedClient->expects()
            ->getBySubjectAndVersion('kafka-test-com.some.namespace', 'latest')
            ->andReturn($schema);

        $schemaIdEncoder->expects()
            ->encode($schema, $payload)
            ->andReturn($encodedMessage);

        // Actions
        $avroSchemaMixedEncoder = new AvroSchemaMixedEncoder(
            $schemaIdEncoder,
            $clientFactory,
            $producerConfigOptions
        );
        $result = $avroSchemaMixedEncoder->process($producerRecord, $closure);

        // Assertions
        $this->assertSame($record, $result->getOriginal());
        $this->assertSame($encodedMessage, $result->getPayload());
    }

    public function testShouldNotEncodeRecordWhenTopicNameHasRecordNameStrategy(): void
    {
        // Set
        $connections = (string) config('service.broker.connections', 'kafka:29092');
        $broker = new Broker($connections, new None());

        $avroConfig = new AvroSchemaConfigOptions(
            'schema.registry.url',
            [],
            true,
            [
                'subject' => [
                    'value' => [
                        'strategy' => 'RecordNameStrategy',
                    ],
                ],
            ]
        );

        $producerConfigOptions = new ProducerConfigOptions(
            'kafka-test',
            $broker,
            null,
            $avroConfig
        );

        $avroSchemaConfigOptions = $producerConfigOptions->getAvroSchema();
        $clientFactory = m::mock(ClientFactory::class);
        $cachedClient = m::mock(CachedSchemaRegistryClient::class);
        $schemaIdEncoder = m::mock(SchemaId::class, [$cachedClient]);

        // Expectations
        $clientFactory->expects()
            ->make($avroSchemaConfigOptions)
            ->andReturn($cachedClient);

        $this->expectException(ConfigurationException::class);

        $this->expectExceptionMessage('RecordNameStrategy is not supported. Please use TopicNameStrategy (default) or TopicRecordNameStrategy instead.');

        // Actions
        new AvroSchemaMixedEncoder($schemaIdEncoder, $clientFactory, $producerConfigOptions);
    }

    private function getRecord(AvroSchema $avroSchema): string
    {
        $defaultValues = [
            'null' => null,
            'boolean' => true,
            'string' => 'abc',
            'int' => 1,
            'long' => 1.0,
            'float' => 1.0,
            'double' => 1.0,
            'array' => [],
        ];

        $result = [];
        foreach ($avroSchema->fields() as $field) {
            $result[$field->name()] = $defaultValues[$field->type->type];
        }

        return json_encode($result);
    }

    private function getSchemaFixture(): string
    {
        return file_get_contents(
            __DIR__ . '/../fixtures/schemas/sales_price.avsc'
        );
    }
}

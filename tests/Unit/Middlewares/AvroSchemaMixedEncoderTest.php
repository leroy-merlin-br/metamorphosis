<?php

namespace Tests\Unit\Middlewares;

use AvroSchema;
use Closure;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\ClientFactory;
use Metamorphosis\Avro\Schema;
use Metamorphosis\Avro\Serializer\Encoders\SchemaId;
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
    public function testShouldEncodeRecord()
    {
        // Set
        $avroSchema = $this->getSchemaFixture();
        $broker = new Broker('kafka:9092', new None());
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

        $cachedSchemaRegistryClient = m::mock(
            CachedSchemaRegistryClient::class
        );
        $schemaIdEncoder = m::mock(
            SchemaId::class,
            [$cachedSchemaRegistryClient]
        );

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
            ->andReturn($cachedSchemaRegistryClient);

        $cachedSchemaRegistryClient->expects()
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

<?php

namespace Tests\Unit\Avro\Serializer;

use AvroSchema;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\Schema;
use Metamorphosis\Avro\Serializer\MessageEncoder;
use Metamorphosis\Avro\Serializer\SchemaFormats;
use Mockery as m;
use RuntimeException;
use Tests\LaravelTestCase;

class MessageEncoderTest extends LaravelTestCase
{
    public function testShouldEncodeRecordWithSchemaId(): void
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageEncoder($registry);
        $topic = 'my-topic';
        $schema = m::mock(Schema::class);
        $avroSchema = new AvroSchema('array');
        $record = [];
        $id = 3;

        // Expectations
        $schema->expects()
            ->getSchemaId()
            ->andReturn('my-topic');

        $schema->expects()
            ->getAvroSchema()
            ->andReturn($avroSchema);

        // Actions
        $result = $serializer->encodeMessage($topic, $schema, $record);

        // Assertions
        $this->assertSame("\x00\x00\x00\x00\x00\x00", $result);
    }

    public function testShouldEncodeRecordWithSchemaIdRegisteringMissingSchemas(): void
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageEncoder(
            $registry,
            ['register_missing_schemas' => true, 'default_encoding_format' => SchemaFormats::MAGIC_BYTE_SCHEMAID]
        );
        $topic = 'my-topic';
        $schema = m::mock(Schema::class);
        $avroSchema = new AvroSchema('array');
        $record = [];
        $id = 3;

        // Expectations
        $schema->expects()
            ->getSchemaId()
            ->andReturn('my-topic');

        $schema->expects()
            ->getAvroSchema()
            ->andReturn($avroSchema);

        // Actions
        $result = $serializer->encodeMessage(
            $topic,
            $schema,
            $record,
            true,
            SchemaFormats::MAGIC_BYTE_SCHEMAID
        );

        // Assertions
        $this->assertSame("\x00\x00\x00\x00\x00\x00", $result);
    }

    public function testShouldEncodeRecordWithSubjectAndVersion(): void
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageEncoder($registry);
        $topic = 'my-topic';
        $subject = "{$topic}-value";
        $schema = m::mock(Schema::class);
        $avroSchema = new AvroSchema('array');
        $record = [];
        $version = 5;

        // Expectations
        $schema->expects()
            ->getVersion()
            ->andReturn('some-version');

        $schema->expects()
            ->getSubject()
            ->andReturn($subject);

        $schema->expects()
            ->getAvroSchema()
            ->andReturn($avroSchema);

        // Actions
        $result = $serializer->encodeMessage(
            $topic,
            $schema,
            $record,
            false,
            SchemaFormats::MAGIC_BYTE_SUBJECT_VERSION
        );

        // Assertions
        $this->assertSame(
            "\x01\x00\x00\x00\x0Emy-topic-value\x00\x00\x00\x00\x00",
            $result
        );
    }

    public function testShouldNotEncodeRecordWithInvalidFormat(): void
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageEncoder($registry);
        $topic = 'my-topic';
        $schema = m::mock(Schema::class);
        $avroSchema = new AvroSchema('array');
        $record = [];

        // Expectations
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsuported format: 2');

        // Actions
        $serializer->encodeMessage($topic, $schema, $record, true, 2);
    }

    public function testShouldEncodeRecordWithSubjectAndVersionRegisteringMissingSchemas(): void
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageEncoder(
            $registry,
            ['register_missing_schemas' => true]
        );
        $topic = 'my-topic';
        $subject = "{$topic}-value";
        $schema = m::mock(Schema::class);
        $avroSchema = new AvroSchema('string');
        $record = 'my awesome message';
        $version = 5;

        // Expectations
        $schema->expects()
            ->getVersion()
            ->andReturn('some-version');

        $schema->expects()
            ->getSubject()
            ->andReturn($subject);

        $schema->expects()
            ->getAvroSchema()
            ->andReturn($avroSchema);

        // Actions
        $result = $serializer->encodeMessage(
            $topic,
            $schema,
            $record,
            true,
            SchemaFormats::MAGIC_BYTE_SUBJECT_VERSION
        );

        // Assertions
        $this->assertSame(
            "\x01\x00\x00\x00\x0Emy-topic-value\x00\x00\x00\x00\$my awesome message",
            $result
        );
    }
}

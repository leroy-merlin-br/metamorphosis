<?php
namespace Tests\Avro\Serializer;

use AvroSchema;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\Serializer\MessageEncoder;
use Mockery as m;
use RuntimeException;
use Tests\LaravelTestCase;

class MessageEncoderTest extends LaravelTestCase
{
    public function testShouldEncodeRecordWithSchemaId()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageEncoder($registry);
        $topic = 'my-topic';
        $schema = new AvroSchema('array');
        $record = [];
        $id = 3;

        // Expectations
        $registry->expects()
            ->getSchemaId("{$topic}-value", $schema)
            ->andReturn($id);

        // Actions
        $result = $serializer->encodeRecordWithSchema($topic, $schema, $record);

        // Assertions
        $this->assertSame("\x00\x00\x00\x00\x03\x00", $result);
    }

    public function testShouldEncodeRecordWithSchemaIdRegisteringMissingSchemas()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageEncoder($registry, ['register_missing_schemas' => true, 'default_encoding_format' => MessageEncoder::MAGIC_BYTE_SCHEMAID]);
        $topic = 'my-topic';
        $schema = new AvroSchema('array');
        $record = [];
        $id = 3;

        // Expectations
        $registry->expects()
            ->getSchemaId("{$topic}-key", $schema)
            ->andThrow(new RuntimeException());

        $registry->expects()
            ->register("{$topic}-key", $schema);

        $registry->expects()
            ->getSchemaId("{$topic}-key", $schema)
            ->andReturn($id);

        // Actions
        $result = $serializer->encodeRecordWithSchema($topic, $schema, $record, true, MessageEncoder::MAGIC_BYTE_SCHEMAID);

        // Assertions
        $this->assertSame("\x00\x00\x00\x00\x03\x00", $result);
    }

    public function testShouldNotEncodeRecordWhenSchemaIsMissing()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageEncoder($registry);
        $topic = 'my-topic';
        $schema = new AvroSchema('array');
        $record = [];

        // Expectations
        $registry->expects()
            ->getSchemaId("{$topic}-key", $schema)
            ->andThrow(new RuntimeException('Whoops'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Whoops');

        // Actions
        $serializer->encodeRecordWithSchema($topic, $schema, $record, true, MessageEncoder::MAGIC_BYTE_SCHEMAID);
    }

    public function testShouldNotEncodeRecordWithInvalidFormat()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageEncoder($registry);
        $topic = 'my-topic';
        $schema = new AvroSchema('array');
        $record = [];

        // Expectations
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsuported format: 2');

        // Actions
        $serializer->encodeRecordWithSchema($topic, $schema, $record, true, 2);
    }

    public function testShouldEncodeRecordWithSubjectAndVersion()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageEncoder($registry);
        $topic = 'my-topic';
        $schema = new AvroSchema('array');
        $record = [];
        $version = 5;

        // Expectations
        $registry->expects()
            ->getSchemaVersion("{$topic}-value", $schema)
            ->andReturn($version);

        // Actions
        $result = $serializer->encodeRecordWithSchema($topic, $schema, $record, false, MessageEncoder::MAGIC_BYTE_SUBJECT_VERSION);

        // Assertions
        $this->assertSame("\x01\x00\x00\x00\x0Emy-topic-value\x00\x00\x00\x05\x00", $result);
    }


    public function testShouldEncodeRecordWithSubjectAndVersionRegisteringMissingSchemas()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageEncoder($registry, ['register_missing_schemas' => true]);
        $topic = 'my-topic';
        $schema = new AvroSchema('string');
        $record = 'my awesome message';
        $version = 5;

        // Expectations
        $registry->expects()
            ->getSchemaVersion("{$topic}-key", $schema)
            ->andThrow(new RuntimeException());

        $registry->expects()
            ->register("{$topic}-key", $schema);

        $registry->expects()
            ->getSchemaVersion("{$topic}-key", $schema)
            ->andReturn($version);

        // Actions
        $result = $serializer->encodeRecordWithSchema($topic, $schema, $record, true, MessageEncoder::MAGIC_BYTE_SUBJECT_VERSION);

        // Assertions
        $this->assertSame("\x01\x00\x00\x00\fmy-topic-key\x00\x00\x00\x05\$my awesome message", $result);
    }

    public function testShouldNotEncodeRecordWithSubjectAndVersionWhenSchemaIsMissing()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageEncoder($registry);
        $topic = 'my-topic';
        $schema = new AvroSchema('array');
        $record = [];

        // Expectations
        $registry->expects()
            ->getSchemaVersion("{$topic}-key", $schema)
            ->andThrow(new RuntimeException('Whoops'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Whoops');

        // Actions
        $serializer->encodeRecordWithSchema($topic, $schema, $record, true, MessageEncoder::MAGIC_BYTE_SUBJECT_VERSION);
    }
}

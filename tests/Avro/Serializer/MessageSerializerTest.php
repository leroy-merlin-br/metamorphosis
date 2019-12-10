<?php
namespace Tests\Avro\Serializer;

use AvroSchema;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\Serializer\MessageSerializer;
use Mockery as m;
use RuntimeException;
use Tests\LaravelTestCase;

class MessageSerializerTest extends LaravelTestCase
{
    public function testShouldEncodeRecordWithSchemaId()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageSerializer($registry);
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
        $serializer = new MessageSerializer($registry, ['register_missing_schemas' => true, 'default_encoding_format' => MessageSerializer::MAGIC_BYTE_SCHEMAID]);
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
        $result = $serializer->encodeRecordWithSchema($topic, $schema, $record, true, MessageSerializer::MAGIC_BYTE_SCHEMAID);

        // Assertions
        $this->assertSame("\x00\x00\x00\x00\x03\x00", $result);
    }

    public function testShouldNotEncodeRecordWhenSchemaIsMissing()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageSerializer($registry);
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
        $serializer->encodeRecordWithSchema($topic, $schema, $record, true, MessageSerializer::MAGIC_BYTE_SCHEMAID);
    }

    public function testShouldNotEncodeRecordWithInvalidFormat()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageSerializer($registry);
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
        $serializer = new MessageSerializer($registry);
        $topic = 'my-topic';
        $schema = new AvroSchema('array');
        $record = [];
        $version = 5;

        // Expectations
        $registry->expects()
            ->getSchemaVersion("{$topic}-value", $schema)
            ->andReturn($version);

        // Actions
        $result = $serializer->encodeRecordWithSchema($topic, $schema, $record, false, MessageSerializer::MAGIC_BYTE_SUBJECT_VERSION);

        // Assertions
        $this->assertSame("\x01\x00\x00\x00\x0Emy-topic-value\x00\x00\x00\x05\x00", $result);
    }


    public function testShouldEncodeRecordWithSubjectAndVersionRegisteringMissingSchemas()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageSerializer($registry, ['register_missing_schemas' => true]);
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
        $result = $serializer->encodeRecordWithSchema($topic, $schema, $record, true, MessageSerializer::MAGIC_BYTE_SUBJECT_VERSION);

        // Assertions
        $this->assertSame("\x01\x00\x00\x00\fmy-topic-key\x00\x00\x00\x05\$my awesome message", $result);
    }

    public function testShouldNotEncodeRecordWithSubjectAndVersionWhenSchemaIsMissing()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageSerializer($registry);
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
        $serializer->encodeRecordWithSchema($topic, $schema, $record, true, MessageSerializer::MAGIC_BYTE_SUBJECT_VERSION);
    }

    public function testDecodeMessageShouldFallbackToGivenMessageIfMessageIsStrange()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageSerializer($registry);
        $message = 'My encrypted message';
        $expected = 'My encrypted message';

        // Actions
        $result = $serializer->decodeMessage($message);

        // Assertions
        $this->assertSame($expected, $result);
    }

    public function testShouldNotDecodeEmptyMessage()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageSerializer($registry);
        $message = '';

        // Expectations
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Message is too small to decode');

        // Actions
        $serializer->decodeMessage($message);
    }

    public function testShouldDecodeMessageUsingSchemaId()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageSerializer($registry);
        $decoder = new AvroSchema('boolean');
        $message = "\x00\x00\x00\x00\x07\x00";
        $expected = false;

        // Expectations
        $registry->expects()
            ->getById(7)
            ->andReturn($decoder);

        // Actions
        $serializer->decodeMessage($message); // calling twice to assert that cache works
        $result = $serializer->decodeMessage($message);

        // Assertions
        $this->assertSame($expected, $result);
    }

    public function testShouldDecodeMessageUsingSchemaSubjectAndVersion()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageSerializer($registry);
        $decoder = new AvroSchema('string');
        $message = "\x01\x00\x00\x00\fmy-topic-key\x00\x00\x00\x05\$my awesome message";
        $expected = 'my awesome message';

        // Expectations
        $registry->expects()
            ->getBySubjectAndVersion('my-topic-key', 5)
            ->andReturn($decoder);

        // Actions
        $serializer->decodeMessage($message); // calling twice to assert that cache works
        $result = $serializer->decodeMessage($message);

        // Assertions
        $this->assertSame($expected, $result);
    }
}

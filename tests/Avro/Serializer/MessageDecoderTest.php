<?php
namespace Tests\Avro\Serializer;

use AvroSchema;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\Serializer\MessageDecoder;
use Mockery as m;
use RuntimeException;
use Tests\LaravelTestCase;

class MessageDecoderTest extends LaravelTestCase
{
    public function testDecodeMessageShouldFallbackToGivenMessageIfMessageIsStrange()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageDecoder($registry);
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
        $serializer = new MessageDecoder($registry);
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
        $serializer = new MessageDecoder($registry);
        $decoder = new AvroSchema('boolean');
        $message = "\x00\x00\x00\x00\x07\x00";
        $expected = false;

        // Expectations
        $registry->expects()
            ->getById(7)
            ->andReturn($decoder);

        // Actions
        $result = $serializer->decodeMessage($message);

        // Assertions
        $this->assertSame($expected, $result);
    }

    public function testShouldDecodeMessageUsingSchemaSubjectAndVersion()
    {
        // Set
        $registry = m::mock(CachedSchemaRegistryClient::class);
        $serializer = new MessageDecoder($registry);
        $decoder = new AvroSchema('string');
        $message = "\x01\x00\x00\x00\fmy-topic-key\x00\x00\x00\x05\$my awesome message";
        $expected = 'my awesome message';

        // Expectations
        $registry->expects()
            ->getBySubjectAndVersion('my-topic-key', 5)
            ->andReturn($decoder);

        // Actions
        $result = $serializer->decodeMessage($message);

        // Assertions
        $this->assertSame($expected, $result);
    }
}

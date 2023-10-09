<?php

namespace Tests\Unit\Middlewares;

use AvroSchema;
use Closure;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\ClientFactory;
use Metamorphosis\Avro\Schema;
use Metamorphosis\Middlewares\AvroSchemaDecoder;
use Metamorphosis\Record\ConsumerRecord;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\AvroSchema as AvroSchemaConfigOptions;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;
use Mockery as m;
use RdKafka\Message;
use Tests\LaravelTestCase;

class AvroSchemaDecoderTest extends LaravelTestCase
{
    public function testShouldDecodeRecord()
    {
        $brokerOptions = new Broker('kafka:9092', new None());
        $consumerConfigOptions = new ConsumerConfigOptions(
            'kafka-test',
            $brokerOptions,
            null,
            null,
            null,
            20000,
            new AvroSchemaConfigOptions('http://url.teste')
        );

        $avroSchemaConfigOptions = $consumerConfigOptions->getAvroSchema();
        $avroSchema = new AvroSchema('string');
        $decoder = m::mock(Schema::class);
        $clientFactory = m::mock(ClientFactory::class);
        $cachedSchemaRegistryClient = m::mock(
            CachedSchemaRegistryClient::class
        );
        $expected = 'my awesome message';

        $message = new Message();
        $message->payload = "\x01\x00\x00\x00\fmy-topic-key\x00\x00\x00\x05\$my awesome message";
        $message->err = 0;

        $closure = Closure::fromCallable(function ($producerRecord) {
            return $producerRecord;
        });

        $consumerRecord = new ConsumerRecord($message);

        // Expectations
        $clientFactory->expects()
            ->make($avroSchemaConfigOptions)
            ->andReturn($cachedSchemaRegistryClient);

        $cachedSchemaRegistryClient->expects()
            ->getBySubjectAndVersion('my-topic-key', 5)
            ->andReturn($decoder);

        $decoder->expects()
            ->getAvroSchema()
            ->andReturn($avroSchema);

        $avroSchemaDecoder = new AvroSchemaDecoder(
            $clientFactory,
            $consumerConfigOptions
        );

        $result = $avroSchemaDecoder->process($consumerRecord, $closure);

        $this->assertSame($expected, $result->getPayload());
    }
}

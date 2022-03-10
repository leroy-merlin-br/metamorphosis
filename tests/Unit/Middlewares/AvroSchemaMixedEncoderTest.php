<?php
namespace Tests\Unit\Middlewares;

use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\ClientFactory;
use Metamorphosis\Avro\Schema;
use Metamorphosis\Avro\Serializer\Encoders\SchemaId;
use Metamorphosis\Middlewares\AvroSchemaMixedEncoder;
use Metamorphosis\Record\ProducerRecord;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\AvroSchema;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ProducerConfigOptions;
use Mockery as m;
use Tests\LaravelTestCase;

class AvroSchemaMixedEncoderTest extends LaravelTestCase
{
    public function testConstructAvroSchemaMixedEncoder()
    {
        //arrange
        $closure = function () {
        };

        $schemaTest = $this->getSchemaTest();
        $configOptionsProducer = $this->createProducer();

        $record = new ProducerRecord($schemaTest, 'kafka-test');
        $cachedSchemaRegistryClient = m::mock(CachedSchemaRegistryClient::class);

        $schema = (new Schema())->parse($schemaTest, '123');
        $clientFactory = new ClientFactory();
        $schemaIdEncoder = m::mock(SchemaId::class, [$cachedSchemaRegistryClient])->makePartial();

        $avroSchemaMixedEncoder = new AvroSchemaMixedEncoder($schemaIdEncoder, $clientFactory, $configOptionsProducer);

        //expect
        $cachedSchemaRegistryClient->shouldReceive('getBySubjectAndVersion')->andReturn($schema);
        $schemaIdEncoder->shouldReceive('encode')->andReturn('string');

        //act
        $avroSchemaMixedEncoder->process($record, $closure);

        //assert
        $this->assertInstanceOf(AvroSchemaMixedEncoder::class, $avroSchemaMixedEncoder);
    }

    private function createProducer()
    {
        $brokerOptions = new Broker('kafka:9092', new None());
        return new ProducerConfigOptions(
            'kafka-test',
            $brokerOptions,
            null,
            new AvroSchema('http://url.teste', []),
            [],
            20000,
            false,
            true,
            10,
            500
        );
    }

    private function getSchemaTest(): string
    {
        return file_get_contents(__DIR__.'/../fixtures/schemas/sales_price.avsc');
    }
}

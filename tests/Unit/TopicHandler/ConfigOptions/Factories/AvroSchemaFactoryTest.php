<?php
namespace Tests\Unit\TopicHandler\ConfigOptions\Factories;

use Metamorphosis\TopicHandler\ConfigOptions\AvroSchema;
use Metamorphosis\TopicHandler\ConfigOptions\Factories\AvroSchemaFactory;
use Tests\LaravelTestCase;

class AvroSchemaFactoryTest extends LaravelTestCase
{
    public function testShouldMakeAvroSchema(): void
    {
        // Set
        $data = [
            'url' => 'http://avroschema',
            'ssl_verify' => true,
            'request_options' => [
                'headers' => [
                    'Authorization' => [
                        'Basic Og==',
                    ],
                ],
            ],
        ];

        // Actions
        $result = AvroSchemaFactory::make($data);

        // Assertions
        $this->assertInstanceOf(AvroSchema::class, $result);
        $this->assertEquals($data, $result->toArray());
    }

    public function testShouldNotMakeAvroSchemaWhenDataIsEmpty(): void
    {
        // Set
        $data = [];

        // Actions
        $result = AvroSchemaFactory::make($data);

        // Assertions
        $this->assertEmpty($result);
    }
}

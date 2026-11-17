<?php

namespace Tests\TopicHandler\ConfigOptions;

use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\TopicHandler\ConfigOptions\AvroSchema;
use Tests\LaravelTestCase;

class AvroSchemaTest extends LaravelTestCase
{
    private AvroSchema $avroSchema;

    /**
     * @var mixed[]
     */
    private array $data;

    public function testShouldConvertToArray(): void
    {
        // Actions
        $result = $this->avroSchema->toArray();

        // Assertions
        $this->assertSame($this->data['url'], $result['url']);
        $this->assertSame($this->data['sslVerify'], $result['ssl_verify']);
        $this->assertSame($this->data['requestOptions'], $result['request_options']);
        $this->assertSame($this->data['configOptions'], $result['config_options']);
    }

    public function testShouldGetUrl(): void
    {
        // Actions
        $result = $this->avroSchema->getUrl();

        // Assertions
        $this->assertSame($this->data['url'], $result);
    }

    public function testShouldVerifyIfIsSsl(): void
    {
        // Actions
        $result = $this->avroSchema->isSslVerify();

        // Assertions
        $this->assertTrue($result);
    }

    public function testShouldGetRequestOptions(): void
    {
        // Actions
        $result = $this->avroSchema->getRequestOptions();

        // Assertions
        $this->assertSame($this->data['requestOptions'], $result);
    }

    public function testShouldGetConfigOptions(): void
    {
        // Actions
        $result = $this->avroSchema->getConfigOptions();

        // Assertions
        $this->assertSame($this->data['configOptions'], $result);
    }

    /**
     * @dataProvider getStrategyScenarios
     */
    public function testShouldGetValueSubjectName(array $value, string $expected): void
    {
        // Set
        $data = [
            'url' => 'http://localhost:8081',
            'sslVerify' => true,
            'requestOptions' => [],
            'configOptions' => [
                'subject' => [
                    'value' => $value,
                ],
            ],
        ];
        $avroSchema = new AvroSchema(...$data);

        // Actions
        $result = $avroSchema->getValueSubjectName('topic-name');

        // Assertions
        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider getStrategyErrorScenarios
     */
    public function testShouldNotGetValueSubjectName(array $value, string $message): void
    {
        // Set
        $data = [
            'url' => 'http://localhost:8081',
            'sslVerify' => true,
            'requestOptions' => [],
            'configOptions' => [
                'subject' => [
                    'value' => $value,
                ],
            ],
        ];
        $avroSchema = new AvroSchema(...$data);

        // Expectations
        $this->expectException(ConfigurationException::class);

        $this->expectExceptionMessage($message);


        // Actions
        $avroSchema->getValueSubjectName('topic-name');
    }

    public static function getStrategyScenarios(): array
    {
        return [
            'when there is no strategy' => [
                'value' => [],
                'expected' => 'topic-name-value',
            ],
            'when strategy is TopicNameStrategy' => [
                'value' => [
                    'strategy' => 'TopicNameStrategy',
                ],
                'expected' => 'topic-name-value',
            ],
            'when strategy is TopicRecordNameStrategy' => [
                'value' => [
                    'strategy' => 'TopicRecordNameStrategy',
                    'namespace' => 'com.some.namespace.',
                ],
                'expected' => 'topic-name-com.some.namespace.',
            ],
        ];
    }

    public static function getStrategyErrorScenarios(): array
    {
        return [
            'when strategy is RecordNameStrategy' => [
                'value' => [
                    'strategy' => 'RecordNameStrategy',
                ],
                'message' => 'RecordNameStrategy is not supported. Please use TopicNameStrategy (default) or TopicRecordNameStrategy instead.',
            ],
            'when strategy is unknown' => [
                'value' => [
                    'strategy' => 'UnknownStrategy',
                ],
                'message' => 'Invalid name strategy',
            ],
            'when strategy is TopicRecordNameStrategy and namespace is missing' => [
                'value' => [
                    'strategy' => 'TopicRecordNameStrategy',
                ],
                'message' => 'Namespace is required for TopicRecordNameStrategy',
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'url' => 'http://localhost:8081',
            'sslVerify' => true,
            'requestOptions' => [
                'timeout' => 5,
                'connect_timeout' => 5,
            ],
            'configOptions' => [
                'subject' => [
                    'value' => [
                        'strategy' => 'TopicRecordNameStrategy',
                        'namespace' => 'your.namespace.here',
                    ],
                ],
            ],
        ];

        $this->avroSchema = new AvroSchema(...$this->data);
    }
}

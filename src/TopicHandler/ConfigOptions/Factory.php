<?php
namespace Metamorphosis\TopicHandler\ConfigOptions;

use Metamorphosis\TopicHandler\ConfigOptions\Auth\Factory as AuthFactory;

class Factory
{
    public static function makeConsumerConfigOptions(
        array $brokerData,
        array $topicData,
        ?array $avroSchemaData = []
    ): Consumer {
        $params = self::getConsumerTopic($topicData);

        $brokerData['auth'] = AuthFactory::make($brokerData['auth'] ?? []);
        $params['broker'] = app(Broker::class, $brokerData);

        $params['avroSchema'] = $avroSchemaData
            ? app(AvroSchema::class, self::convertAvroSchemaConfig($avroSchemaData))
            : null;

        return app(Consumer::class, $params);
    }

    public static function makeProducerConfigOptions(
        array $brokerData,
        array $topicData,
        ?array $avroSchemaData = []
    ): Producer {
        $params = self::convertProducerConfig($topicData);
        $brokerData['auth'] = AuthFactory::make($brokerData['auth'] ?? []);
        $params['broker'] = app(Broker::class, $brokerData);

        $params['avroSchema'] = $avroSchemaData ? app(AvroSchema::class, $avroSchemaData) : null;

        return app(Producer::class, $params);
    }

    private static function getConsumerTopic(array $topicData): array
    {
        $topicData['topicId'] = $topicData['topic_id'];

        $consumer = current($topicData['consumer']);
        $topicData['consumerGroup'] = key($consumer);

        return array_merge($topicData, self::convertConsumerConfig($consumer));
    }

    private static function convertConsumerConfig(array $topic): array
    {
        $consumerConfig = current($topic);

        if (isset($consumerConfig['auto_commit'])) {
            $consumerConfig['autoCommit'] = $consumerConfig['auto_commit'];
        }

        if (isset($consumerConfig['max_poll_records'])) {
            $consumerConfig['maxPollRecords'] = $consumerConfig['max_poll_records'];
        }

        if (isset($consumerConfig['flush_attempts'])) {
            $consumerConfig['flushAttempts'] = $consumerConfig['flush_attempts'];
        }

        if (isset($consumerConfig['commit_async'])) {
            $consumerConfig['commitAsync'] = $consumerConfig['commit_async'];
        }

        if (isset($consumerConfig['offset_reset'])) {
            $consumerConfig['offsetReset'] = $consumerConfig['offset_reset'];
        }

        return $consumerConfig;
    }

    private static function convertProducerConfig(array $topic): array
    {
        $config = $topic['producer'] ?? [];
        $config['topicId'] = $topic['topic_id'];

        if (isset($config['required_acknowledgment'])) {
            $config['requiredAcknowledgment'] = $config['required_acknowledgment'];
        }

        if (isset($config['is_async'])) {
            $config['isAsync'] = $config['is_async'];
        }

        if (isset($config['max_poll_records'])) {
            $config['maxPollRecords'] = $config['max_poll_records'];
        }

        if (isset($config['flush_attempts'])) {
            $config['flushAttempts'] = $config['flush_attempts'];
        }

        return $config;
    }

    private static function convertAvroSchemaConfig(array $config): array
    {
        if (isset($config['ssl_verify'])) {
            $config['sslVerify'] = $config['ssl_verify'];
        }

        if (isset($config['request_options'])) {
            $config['requestOptions'] = $config['request_options'];
        }

        return $config;
    }
}

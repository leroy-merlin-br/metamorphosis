<?php
namespace Metamorphosis\TopicHandler\ConfigOptions;

use Metamorphosis\ProducerConfigManager;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\Factory as AuthFactory;

class Factory
{
    public function makeConsumerConfigOptions(
        array $brokerData,
        array $topicData,
        ?array $avroSchemaData = []
    ): Consumer {
        $params = $this->getConsumerTopic($topicData);

        $brokerData['auth'] = AuthFactory::make($brokerData['auth'] ?? []);
        $params['broker'] = app(Broker::class, $brokerData);

        $params['avroSchema'] = $avroSchemaData ? app(AvroSchema::class, $avroSchemaData) : null;

        return app(Consumer::class, $params);
    }

    public function makeProducerConfigOptions(
        array $brokerData,
        array $topicData,
        ?array $avroSchemaData = []
    ): ProducerConfigManager {
        $params = $this->convertProducerConfig($topicData);
        $brokerData['auth'] = AuthFactory::make($brokerData['auth'] ?? []);
        $params['broker'] = app(Broker::class, $brokerData);

        $params['avroSchema'] = $avroSchemaData ? app(AvroSchema::class, $avroSchemaData) : null;

        return app(Producer::class, $params);
    }

    private function getConsumerTopic(array $topicData): array
    {
        $topicData['topicId'] = $topicData['topic_id'];

        $consumer = current($topicData['consumer']);
        $topicData['consumerGroup'] = key($consumer);

        return array_merge($topicData, $this->convertConsumerConfig($consumer));
    }

    private function convertConsumerConfig(array $topic): array
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

    private function convertProducerConfig(array $topic): array
    {
        $configs = $topic['producer'];

        if (isset($configs['required_acknowledgment'])) {
            $configs['requiredAcknowledgment'] = $configs['required_acknowledgment'];
        }

        if (isset($configs['is_async'])) {
            $configs['isAsync'] = $configs['is_async'];
        }

        if (isset($configs['max_poll_records'])) {
            $configs['maxPollRecords'] = $configs['max_poll_records'];
        }

        if (isset($configs['flush_attempts'])) {
            $configs['flushAttempts'] = $configs['flush_attempts'];
        }

        return $configs;
    }
}

<?php

namespace Metamorphosis\TopicHandler\ConfigOptions\Factories;

use Metamorphosis\TopicHandler\ConfigOptions\Consumer;

class ConsumerFactory
{
    public static function make(
        array $brokerData,
        array $topicData,
        ?array $avroSchemaData = []
    ): Consumer {
        $params = self::getConsumerGroupConfig($topicData);

        $params['broker'] = BrokerFactory::make($brokerData);
        $params['avroSchema'] = AvroSchemaFactory::make($avroSchemaData);

        return app(Consumer::class, $params);
    }

    private static function getConsumerGroupConfig(array $topicData): array
    {
        $topicData['topicId'] = $topicData['topic_id'];
        $consumer = $topicData['consumer'];
        $topicData['consumerGroup'] = $consumer['consumer_group'];

        return array_merge_recursive(
            $topicData,
            self::convertConfigAttributes($consumer)
        );
    }

    private static function convertConfigAttributes(array $consumerConfig): array
    {
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


        if (isset($consumerConfig['max_poll_interval_ms'])) {
            $consumerConfig['maxPollInterval'] = $consumerConfig['max_poll_interval_ms'];
        }

        return $consumerConfig;
    }
}

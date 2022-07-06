<?php

namespace Metamorphosis\TopicHandler\ConfigOptions\Factories;

use Metamorphosis\TopicHandler\ConfigOptions\Producer;

class ProducerFactory
{
    public static function make(
        array $brokerData,
        array $topicData,
        ?array $avroSchemaData = []
    ): Producer {
        $params = self::convertConfigAttributes($topicData);

        $params['broker'] = BrokerFactory::make($brokerData);
        $params['avroSchema'] = AvroSchemaFactory::make($avroSchemaData);

        return app(Producer::class, $params);
    }

    private static function convertConfigAttributes(array $topic): array
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
}

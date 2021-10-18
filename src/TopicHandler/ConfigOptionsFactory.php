<?php
namespace Metamorphosis\TopicHandler;

use Metamorphosis\Exceptions\ConfigurationException;

class ConfigOptionsFactory
{
    public function makeByConfigName(string $configName, string $topicName, string $brokerName): ConfigOptions
    {
        $topic = $this->getTopic($configName, $topicName);
        $broker = config($configName.'.brokers.'.$brokerName);

        $params = array_merge($topic, compact('broker'));

        return $this->makeConfigOptions($params);
    }

    public function makeByConfigNameWithSchema(
        string $configName,
        string $topicName,
        string $brokerName,
        string $schemaName
    ): ConfigOptions {
        $topic = $this->getTopic($configName, $topicName);
        $broker = config($configName.'.brokers.'.$brokerName);
        $avroSchema = config($configName.'.avro_schemas.'.$schemaName);

        $params = array_merge($topic, compact('broker', 'avroSchema'));

        return $this->makeConfigOptions($params);
    }

    private function makeConfigOptions($params): ConfigOptions
    {
        return app(ConfigOptions::class, $params);
    }

    private function getTopic(string $configName, string $topicName): array
    {
        $topic = config($configName.'.topics.'.$topicName);
        $topic['topicId'] = $topic['topic_id'];

        $consumer = current($topic['consumer']);

        $topic['consumer_group'] =  key($consumer);
        $topic['handler'] =  current($consumer)['handler'];

        return $topic;
    }
}

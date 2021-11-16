<?php
namespace Metamorphosis\TopicHandler;

class ConfigOptionsFactory
{
    public function makeByConfigName(string $configName, string $topicName, string $brokerName): BaseConfigOptions
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
    ): BaseConfigOptions {
        $topic = $this->getTopic($configName, $topicName);
        $broker = config($configName.'.brokers.'.$brokerName);
        $avroSchema = config($configName.'.avro_schemas.'.$schemaName);

        $params = array_merge($topic, compact('broker', 'avroSchema'));

        return $this->makeConfigOptions($params);
    }

    public function makeProducerConfigOptions(string $configName, string $topicName, string $brokerName): BaseConfigOptions
    {
        $topic = $this->getTopic($configName, $topicName);
        $broker = config($configName.'.brokers.'.$brokerName);

        $params = array_merge($topic, compact('broker'));
        $params['middlewares'] = [];

        return $this->makeConfigOptions($params);
    }

    private function makeConfigOptions($params): BaseConfigOptions
    {
        return app(BaseConfigOptions::class, $params);
    }

    private function getTopic(string $configName, string $topicName): array
    {
        $topic = config($configName.'.topics.'.$topicName);
        $topic['topicId'] = $topic['topic_id'];

        $consumer = current($topic['consumer']);
        $topic['consumerGroup'] = key($consumer);

        return array_merge($topic, $this->getConsumerConfig($consumer));
    }

    private function getConsumerConfig(array $consumer): array
    {
        $consumerConfig = current($consumer);

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
}

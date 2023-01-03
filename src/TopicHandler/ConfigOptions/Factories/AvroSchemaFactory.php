<?php

namespace Metamorphosis\TopicHandler\ConfigOptions\Factories;

use Metamorphosis\TopicHandler\ConfigOptions\AvroSchema;

class AvroSchemaFactory
{
    public static function make(array $avroSchemaData = []): ?AvroSchema
    {
        if (!$avroSchemaData) {
            return null;
        }

        return app(
            AvroSchema::class,
            self::convertConfigAttributes($avroSchemaData)
        );
    }

    private static function convertConfigAttributes(array $config): array
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

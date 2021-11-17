<?php
namespace Metamorphosis\TopicHandler\ConfigOptions\Auth;

use Exception;

class Factory
{
    private const AUTH_MAP = [
        EnumType::SASL_SSL_TYPE => SaslSsl::class,
        EnumType::SSL_TYPE => Ssl::class,
        EnumType::NONE_TYPE => None::class,
    ];

    public static function make(array $attributes = []): AuthInterface
    {
        if (!$attributes) {
            $attributes['type'] = EnumType::NONE_TYPE;
        }

        if (!isset(self::AUTH_MAP[$attributes['type']])) {
            throw new Exception('Invalid Auth Type on Broker Authentication.');
        }

        return app(self::AUTH_MAP[$attributes['type']], $attributes);
    }
}

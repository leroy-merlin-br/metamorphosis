<?php

namespace Metamorphosis\Authentication;

use Metamorphosis\Exceptions\AuthenticationException;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\AuthInterface;
use RdKafka\Conf;

class Factory
{
    private const TYPE_SSL = 'ssl';

    private const TYPE_SASL_SSL = 'sasl_ssl';

    private const TYPE_NONE = 'none';

    public static function authenticate(Conf $conf, AuthInterface $configOptions): void
    {
        $type = $configOptions->getType();
        switch ($type) {
            case null:
            case self::TYPE_NONE:
                app(NoAuthentication::class);

                break;
            case self::TYPE_SSL:
                app(SSLAuthentication::class, compact('conf', 'configOptions'));

                break;
            case self::TYPE_SASL_SSL:
                app(SASLAuthentication::class, compact('conf', 'configOptions'));

                break;
            default:
                throw new AuthenticationException(
                    'Invalid Protocol Configuration.'
                );
        }
    }
}

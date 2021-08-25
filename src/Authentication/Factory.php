<?php
namespace Metamorphosis\Authentication;

use Metamorphosis\ConfigManager;
use Metamorphosis\Exceptions\AuthenticationException;

use RdKafka\Conf;

class Factory
{
    const TYPE_SSL = 'ssl';

    const TYPE_SASL_SSL = 'sasl_ssl';

    const TYPE_NONE = 'none';

    public static function authenticate(Conf $conf, ConfigManager $configManager): void
    {
        $type = $configManager->get('auth.type');
        switch ($type) {
            case null:
            case self::TYPE_NONE:
                app(NoAuthentication::class);

                break;
            case self::TYPE_SSL:
                app(SSLAuthentication::class, compact('conf', 'configManager'));

                break;
            case self::TYPE_SASL_SSL:
                app(SASLAuthentication::class, compact('conf', 'configManager'));

                break;
            default:
                throw new AuthenticationException('Invalid Protocol Configuration.');
        }
    }
}

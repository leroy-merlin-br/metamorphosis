<?php
namespace Metamorphosis\Authentication;

use Metamorphosis\Exceptions\AuthenticationException;
use Metamorphosis\Facades\ConfigManager;
use RdKafka\Conf;

class Factory
{
    const TYPE_SSL = 'ssl';

    const TYPE_NONE = 'none';

    public static function authenticate(Conf $conf): void
    {
        $type = ConfigManager::get('auth.type');
        switch ($type) {
            case null:
            case self::TYPE_NONE:
                app(NoAuthentication::class);

                break;
            case self::TYPE_SSL:
                app(SSLAuthentication::class, compact('conf'));

                break;
            default:
                throw new AuthenticationException('Invalid Protocol Configuration.');
        }
    }
}

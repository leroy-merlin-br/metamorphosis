<?php
namespace Metamorphosis\Authentication;

use Kafka\Config;
use Metamorphosis\Exceptions\AuthenticationException;
use Metamorphosis\Facades\Manager;

class Factory
{
    const TYPE_SSL = 'ssl';

    const TYPE_NONE = 'none';

    public static function authenticate(Config $config): void
    {
        $type = Manager::get('auth.type');
        switch ($type) {
            case null:
            case self::TYPE_NONE:
                app(NoAuthentication::class);

                break;
            case self::TYPE_SSL:
                app(SSLAuthentication::class, compact('config'));

                break;
            default:
                throw new AuthenticationException('Invalid Protocol Configuration.');
        }
    }
}

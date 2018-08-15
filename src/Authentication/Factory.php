<?php
namespace Metamorphosis\Authentication;

use Metamorphosis\Contracts\Authentication;
use Metamorphosis\Exceptions\AuthenticationException;

class Factory
{
    const SSL_PROTOCOL = 'ssl';

    public static function make(array $authentication = null): Authentication
    {
        if (!$authentication) {
            return new NoAuthentication();
        }

        switch ($authentication['protocol'] ?? []) {
            case self::SSL_PROTOCOL:
                return new SSLAuthentication($authentication);
            default:
                throw new AuthenticationException('Invalid Protocol Configuration.');
        }
    }
}

<?php
namespace Metamorphosis\Authentication;

use Metamorphosis\Contracts\Authentication;
use Metamorphosis\Exceptions\AuthenticationException;

class Factory
{
    public static function make(array $authentication = null): Authentication
    {
        if (!$authentication) {
            return new NoAuthentication();
        }

        switch ($authentication['protocol'] ?? []) {
            case 'ssl':
                return new SSLAuthentication($authentication);
            default:
                throw new AuthenticationException('Invalid Protocol Configuration.');
        }
    }
}

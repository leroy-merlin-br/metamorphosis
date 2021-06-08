<?php
namespace Metamorphosis\Authentication;

use Metamorphosis\Facades\ConfigManager;
use RdKafka\Conf;

class SASLAuthentication implements AuthenticationInterface
{
    /**
     * @var Conf
     */
    private $conf;

    public function __construct(Conf $conf)
    {
        $this->conf = $conf;

        $this->authenticate();
    }

    private function authenticate(): void
    {
        $this->conf->set('security.protocol', ConfigManager::get('auth.type'));

        // The mechanisms key is optional when configuring this kind of authentication
        // If the user does not specify the mechanism, the default will be 'PLAIN'.
        // But, to make config more clear, we are asking the user every time.
        $this->conf->set('sasl.mechanisms', ConfigManager::get('auth.mechanisms'));
        $this->conf->set('sasl.username', ConfigManager::get('auth.username'));
        $this->conf->set('sasl.password', ConfigManager::get('auth.password'));
    }
}

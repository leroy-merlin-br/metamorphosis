<?php
namespace Metamorphosis\Authentication;

use Metamorphosis\AbstractConfigManager;
use RdKafka\Conf;

class SASLAuthentication implements AuthenticationInterface
{
    /**
     * @var Conf
     */
    private $conf;

    /**
     * @var AbstractConfigManager
     */
    private $configManager;

    public function __construct(Conf $conf, AbstractConfigManager $configManager)
    {
        $this->conf = $conf;
        $this->configManager = $configManager;

        $this->authenticate();
    }

    private function authenticate(): void
    {
        $this->conf->set('security.protocol', $this->configManager->get('auth.type'));

        // The mechanisms key is optional when configuring this kind of authentication
        // If the user does not specify the mechanism, the default will be 'PLAIN'.
        // But, to make config more clear, we are asking the user every time.
        $this->conf->set('sasl.mechanisms', $this->configManager->get('auth.mechanisms'));
        $this->conf->set('sasl.username', $this->configManager->get('auth.username'));
        $this->conf->set('sasl.password', $this->configManager->get('auth.password'));
    }
}

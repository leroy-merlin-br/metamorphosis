<?php
namespace Metamorphosis\Authentication;

use Metamorphosis\TopicHandler\ConfigOptions\Auth\SaslSsl;
use RdKafka\Conf;

class SASLAuthentication implements AuthenticationInterface
{
    /**
     * @var Conf
     */
    private $conf;

    /**
     * @var SaslSsl
     */
    private $configOptions;

    public function __construct(Conf $conf, SaslSsl $configOptions)
    {
        $this->conf = $conf;
        $this->configOptions = $configOptions;

        $this->authenticate();
    }

    private function authenticate(): void
    {
        $this->conf->set('security.protocol', $this->configOptions->getType());

        // The mechanisms key is optional when configuring this kind of authentication
        // If the user does not specify the mechanism, the default will be 'PLAIN'.
        // But, to make config more clear, we are asking the user every time.
        $this->conf->set('sasl.mechanisms', $this->configOptions->getMechanisms());
        $this->conf->set('sasl.username', $this->configOptions->getUsername());
        $this->conf->set('sasl.password', $this->configOptions->getPassword());
    }
}

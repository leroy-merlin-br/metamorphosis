<?php
namespace Metamorphosis\Authentication;

use RdKafka\Conf;

class SSLAuthentication implements AuthenticationInterface
{
    /**
     * @var Conf
     */
    private $conf;

    public function __construct(Conf $conf)
    {
        $this->conf = $conf;

        $conf->set('security.protocol', config('kafka.runtime.auth.type'));
        $conf->set('ssl.ca.location', config('kafka.runtime.auth.ca'));
        $conf->set('ssl.certificate.location', config('kafka.runtime.auth.certificate'));
        $conf->set('ssl.key.location', config('kafka.runtime.auth.key'));
    }
}

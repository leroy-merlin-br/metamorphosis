<?php
namespace Metamorphosis\Authentication;

use Metamorphosis\Contracts\Authentication;
use Metamorphosis\Exceptions\AuthenticationException;
use RdKafka\Conf;

class SSLAuthentication implements Authentication
{
    protected $ca;

    protected $certificate;

    protected $key;

    public function __construct(array $authConfig)
    {
        $this->ca = $authConfig['ca'] ?? null;
        $this->certificate = $authConfig['certificate'] ?? null;
        $this->key = $authConfig['key'] ?? null;
    }

    public function authenticate(Conf $conf)
    {
        $this->validate();

        $conf->set('security.protocol', 'ssl');
        $conf->set('ssl.ca.location', $this->ca);
        $conf->set('ssl.certificate.location', $this->certificate);
        $conf->set('ssl.key.location', $this->key);
    }

    protected function validate(): bool
    {
        if (!isset($this->ca) || !isset($this->certificate) || !isset($this->key)) {
            throw new AuthenticationException('Invalid Authentication Configuration.');
        }

        return true;
    }
}

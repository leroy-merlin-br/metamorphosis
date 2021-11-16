<?php

namespace Metamorphosis\TopicHandler\ConfigOptions\Auth;

class SaslSsl implements AuthInterface
{
    /**
     * @var string
     */
    private $mechanisms;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    public function __construct(string $mechanisms, string $username, string $password)
    {
        $this->mechanisms = $mechanisms;
        $this->username = $username;
        $this->password = $password;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getMechanisms(): string
    {
        return $this->mechanisms;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'mechanisms' => $this->getMechanisms(),
            'username' => $this->getUsername(),
            'password' => $this->getPassword(),
        ];
    }

    public function getType(): string
    {
        return 'sasl_ssl';
    }
}

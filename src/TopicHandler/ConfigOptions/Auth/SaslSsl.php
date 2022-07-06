<?php

namespace Metamorphosis\TopicHandler\ConfigOptions\Auth;

class SaslSsl implements AuthInterface
{
    private string $mechanisms;

    private string $username;

    private string $password;

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
        return EnumType::SASL_SSL_TYPE;
    }
}

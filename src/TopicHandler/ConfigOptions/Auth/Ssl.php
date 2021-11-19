<?php
namespace Metamorphosis\TopicHandler\ConfigOptions\Auth;

class Ssl implements AuthInterface
{
    /**
     * @var string
     */
    private $ca;

    /**
     * @var string
     */
    private $certificate;

    /**
     * @var string
     */
    private $key;

    public function __construct(string $ca, string $certificate, string $key)
    {
        $this->ca = $ca;
        $this->certificate = $certificate;
        $this->key = $key;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'ca' => $this->getCa(),
            'certificate' => $this->getCertificate(),
            'key' => $this->getKey(),
        ];
    }

    public function getCa(): string
    {
        return $this->ca;
    }

    public function getCertificate(): string
    {
        return $this->certificate;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getType(): string
    {
        return EnumType::SSL_TYPE;
    }
}

<?php

namespace Tests\Unit\Authentication;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\ConsumerConfigManager;
use Metamorphosis\Exceptions\AuthenticationException;
use RdKafka\Conf;
use Tests\LaravelTestCase;

class FactoryTest extends LaravelTestCase
{
    public function testItMakesSslAuthenticationClass(): void
    {
        // Set
        $configManager = new ConsumerConfigManager();
        $configManager->set([
            'auth' => [
                'type' => 'ssl',
                'ca' => 'path/to/ca',
                'certificate' => 'path/to/certificate',
                'key' => 'path/to/key',
            ],
        ]);
        $conf = new Conf();
        $expected = [
            'security.protocol' => 'ssl',
            'ssl.ca.location' => 'path/to/ca',
            'ssl.certificate.location' => 'path/to/certificate',
            'ssl.key.location' => 'path/to/key',
        ];

        // Actions
        Factory::authenticate($conf, $configManager);

        // Assertions
        $this->assertArraySubset($expected, $conf->dump());
    }

    public function testItMakesSASLAuthenticationClass(): void
    {
        // Set
        $configManager = new ConsumerConfigManager();
        $configManager->set([
            'auth' => [
                'type' => 'sasl_ssl',
                'mechanisms' => 'PLAIN',
                'username' => 'some-username',
                'password' => 'some-password',
            ],
        ]);
        $conf = new Conf();
        $expected = [
            'security.protocol' => 'sasl_ssl',
            'sasl.username' => 'some-username',
            'sasl.password' => 'some-password',
            'sasl.mechanisms' => 'PLAIN',
        ];

        // Actions
        Factory::authenticate($conf, $configManager);

        // Assertions
        $this->assertArraySubset($expected, $conf->dump());
    }

    public function testItThrowsExceptionWhenInvalidProtocolIsPassed(): void
    {
        // Set
        $configManager = new ConsumerConfigManager();
        $configManager->set(['auth' => ['type' => 'some-invalid-type']]);
        $conf = new Conf();

        $this->expectException(AuthenticationException::class);

        // Actions
        Factory::authenticate($conf, $configManager);
    }
}

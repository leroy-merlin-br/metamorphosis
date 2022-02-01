<?php
namespace Tests\Unit\Authentication;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\Exceptions\AuthenticationException;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\AuthInterface;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\SaslSsl;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\Ssl;
use Mockery as m;
use RdKafka\Conf;
use Tests\LaravelTestCase;

class FactoryTest extends LaravelTestCase
{
    public function testItMakesSslAuthenticationClass(): void
    {
        // Set
        $configOptionsSsl = new Ssl('path/to/ca', 'path/to/certificate', 'path/to/key');
        $conf = new Conf();
        $expected = [
            'security.protocol' => 'ssl',
            'ssl.ca.location' => 'path/to/ca',
            'ssl.certificate.location' => 'path/to/certificate',
            'ssl.key.location' => 'path/to/key',
        ];

        // Actions
        Factory::authenticate($conf, $configOptionsSsl);

        // Assertions
        $this->assertArraySubset($expected, $conf->dump());
    }

    public function testItMakesSASLAuthenticationClass(): void
    {
        // Set
        $configOptionsSaslSsl = new SaslSsl(
            'PLAIN',
            'some-username',
            'some-password'
        );
        $conf = new Conf();
        $expected = [
            'security.protocol' => 'sasl_ssl',
            'sasl.username' => 'some-username',
            'sasl.password' => 'some-password',
            'sasl.mechanisms' => 'PLAIN',
        ];

        // Actions
        Factory::authenticate($conf, $configOptionsSaslSsl);

        // Assertions
        $this->assertArraySubset($expected, $conf->dump());
    }

    public function testItThrowsExceptionWhenInvalidProtocolIsPassed(): void
    {
        // Set
        $invalidAuth = m::mock(AuthInterface::class);
        $conf = new Conf();

        // Expectations
        $invalidAuth->expects()
            ->getType()
            ->andReturn('some-invalid-type');

        $this->expectException(AuthenticationException::class);

        // Actions
        Factory::authenticate($conf, $invalidAuth);
    }
}

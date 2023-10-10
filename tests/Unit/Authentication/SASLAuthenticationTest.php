<?php

namespace Tests\Unit\Authentication;

use Metamorphosis\Authentication\SASLAuthentication;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\SaslSsl;
use RdKafka\Conf;
use Tests\LaravelTestCase;

class SASLAuthenticationTest extends LaravelTestCase
{
    public function testItShouldValidateAuthenticationConfigurations(): void
    {
        // Set
        $configSaslSsl = new SaslSsl(
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
        new SASLAuthentication($conf, $configSaslSsl);

        // Assertions
        $this->assertArraySubset($expected, $conf->dump());
    }
}

<?php
namespace Tests\Unit\Authentication;

use Metamorphosis\Authentication\SSLAuthentication;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\Ssl;
use RdKafka\Conf;
use Tests\LaravelTestCase;

class SSLAuthenticationTest extends LaravelTestCase
{
    public function testItShouldValidateAuthenticationConfigurations(): void
    {
        // Set
        $conf = new Conf();
        $configSsl = new Ssl('path/to/ca', 'path/to/certificate', 'path/to/key');
        $expected = [
            'security.protocol' => 'ssl',
            'ssl.ca.location' => 'path/to/ca',
            'ssl.certificate.location' => 'path/to/certificate',
            'ssl.key.location' => 'path/to/key',
        ];

        // Actions
        new SSLAuthentication($conf, $configSsl);

        // Assertions
        $this->assertArraySubset($expected, $conf->dump());
    }
}

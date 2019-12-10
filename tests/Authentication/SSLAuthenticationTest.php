<?php
namespace Tests\Authentication;

use Metamorphosis\Authentication\SSLAuthentication;
use Metamorphosis\Exceptions\AuthenticationException;
use RdKafka\Conf;
use Tests\LaravelTestCase;

class SSLAuthenticationTest extends LaravelTestCase
{
    public function testItShouldValidateAuthenticationConfigurations(): void
    {
        // Set
        config(['kafka.runtime.auth' => [
            'type' => 'ssl',
            'ca' => 'path/to/ca',
            'certificate' => 'path/to/certificate',
            'key' => 'path/to/key',
        ]]);
        $conf = new Conf();
        $expected = [
            'security.protocol' => 'ssl',
            'ssl.ca.location' => 'path/to/ca',
            'ssl.certificate.location' => 'path/to/certificate',
            'ssl.key.location' => 'path/to/key',
        ];

        // Actions
        new SSLAuthentication($conf);

        // Assertions
        $this->assertArraySubset($expected, $conf->dump());
    }
}

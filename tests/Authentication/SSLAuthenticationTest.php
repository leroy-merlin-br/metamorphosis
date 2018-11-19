<?php
namespace Tests\Authentication;

use Metamorphosis\Authentication\SSLAuthentication;
use Metamorphosis\Exceptions\AuthenticationException;
use RdKafka\Conf;
use Tests\LaravelTestCase;

class SSLAuthenticationTest extends LaravelTestCase
{
    public function testItShouldValidateAuthenticationConfigurations()
    {
        $authConfig = [
            'ca' => 'path/to/ca',
            'certificate' => 'path/to/certificate',
            'key' => 'path/to/key',
        ];

        $sslAuthentication = new SSLAuthentication($authConfig);

        $this->assertNull($sslAuthentication->setAuthentication(new Conf()));
    }

    public function testItShouldThrowsExceptionWhenInvalidAuthenticationConfigurations()
    {
        $authConfig = [
            'certificate' => 'path/to/certificate',
            'key' => 'path/to/key',
        ];

        $sslAuthentication = new SSLAuthentication($authConfig);

        $this->expectException(AuthenticationException::class);

        $sslAuthentication->setAuthentication(new Conf());
    }
}

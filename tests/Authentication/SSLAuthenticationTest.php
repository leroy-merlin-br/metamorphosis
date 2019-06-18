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
        $this->markTestSkipped();
        $authConfig = [
            'ca' => 'path/to/ca',
            'certificate' => 'path/to/certificate',
            'key' => 'path/to/key',
        ];

        $sslAuthentication = new SSLAuthentication($authConfig);

        $this->assertNull($sslAuthentication->authenticate(new Conf()));
    }

    public function testItShouldThrowsExceptionWhenInvalidAuthenticationConfigurations()
    {
        $authConfig = [
            'certificate' => 'path/to/certificate',
            'key' => 'path/to/key',
        ];

        $sslAuthentication = new SSLAuthentication($authConfig);

        $this->expectException(AuthenticationException::class);

        $sslAuthentication->authenticate(new Conf());
    }
}

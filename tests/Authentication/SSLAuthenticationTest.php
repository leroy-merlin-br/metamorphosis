<?php
namespace Tests\Authentication;

use Metamorphosis\Authentication\SSLAuthentication;
use Metamorphosis\Exceptions\AuthenticationException;
use RdKafka\Conf;
use Tests\LaravelTestCase;

class SSLAuthenticationTest extends LaravelTestCase
{
    /** @test */
    public function it_should_validate_authentication_configurations()
    {
        $authConfig = [
            'ca' => 'path/to/ca',
            'certificate' => 'path/to/certificate',
            'key' => 'path/to/key',
        ];

        $sslAuthentication = new SSLAuthentication($authConfig);

        $this->assertNull($sslAuthentication->authenticate(new Conf()));
    }

    /** @test */
    public function it_should_throws_exception_when_invalid_authentication_configurations()
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

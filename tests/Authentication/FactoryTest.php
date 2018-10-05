<?php
namespace Tests\Authentication;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\Authentication\NoAuthentication;
use Metamorphosis\Authentication\SSLAuthentication;
use Metamorphosis\Exceptions\AuthenticationException;
use Tests\LaravelTestCase;

class FactoryTest extends LaravelTestCase
{
    public function testItMakesSslAuthenticationClass()
    {
        $authenticationConfig = [
            'protocol' => 'ssl',
            'ca' => 'path/to/ca',
            'certificate' => 'path/to/certificate',
            'key' => 'path/to/key',
        ];

        $authenticationClass = Factory::make($authenticationConfig);

        $this->assertInstanceOf(SSLAuthentication::class, $authenticationClass);
    }

    public function testItMakesNoAuthenticationClass()
    {
        $this->assertInstanceOf(NoAuthentication::class, Factory::make([]));
        $this->assertInstanceOf(NoAuthentication::class, Factory::make(null));
    }

    public function testItThrowsExceptionWhenInvalidProtocolIsPassed()
    {
        $authenticationConfig = [
            'protocol' => 'some-invalid-protocol',
        ];

        $this->expectException(AuthenticationException::class);

        Factory::make($authenticationConfig);
    }

    public function testItThrowsExceptionWhenAuthenticationIsPassedWithoutProtocolKey()
    {
        $authenticationConfig = [
            'foo' => 'some-invalid-protocol',
            'bar' => [],
        ];

        $this->expectException(AuthenticationException::class);

        Factory::make($authenticationConfig);
    }
}

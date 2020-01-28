<?php
namespace Tests\Unit\Authentication;

use Metamorphosis\Authentication\Factory;
use Metamorphosis\Exceptions\AuthenticationException;
use Metamorphosis\Facades\ConfigManager;
use RdKafka\Conf;
use Tests\LaravelTestCase;

class FactoryTest extends LaravelTestCase
{
    public function testItMakesSslAuthenticationClass(): void
    {
        // Set
        ConfigManager::set([
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
        Factory::authenticate($conf);

        // Assertions
        $this->assertArraySubset($expected, $conf->dump());
    }

    public function testItThrowsExceptionWhenInvalidProtocolIsPassed(): void
    {
        // Set
        ConfigManager::set(['auth' => ['type' => 'some-invalid-type']]);
        $conf = new Conf();

        $this->expectException(AuthenticationException::class);

        // Actions
        Factory::authenticate($conf);
    }
}

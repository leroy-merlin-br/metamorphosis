<?php
namespace Tests\Unit\Authentication;

use Metamorphosis\Authentication\SASLAuthentication;

use Metamorphosis\ConfigManager;
use RdKafka\Conf;
use Tests\LaravelTestCase;

class SASLAuthenticationTest extends LaravelTestCase
{
    public function testItShouldValidateAuthenticationConfigurations(): void
    {
        // Set
        $configManager = new ConfigManager();
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
        new SASLAuthentication($conf, $configManager);

        // Assertions
        $this->assertArraySubset($expected, $conf->dump());
    }
}

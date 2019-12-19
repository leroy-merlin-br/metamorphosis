<?php
namespace Metamorphosis;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\ServiceProvider;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\Client;
use Metamorphosis\Console\ConsumerCommand;
use Metamorphosis\Console\ConsumerMakeCommand;
use Metamorphosis\Console\MiddlewareMakeCommand;
use Metamorphosis\Console\ProducerMakeCommand;
use Metamorphosis\Exceptions\ConfigurationException;

class MetamorphosisServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/kafka.php' => config_path('kafka.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../config/kafka.php', 'kafka');
    }

    public function register()
    {
        $this->commands([
            ConsumerCommand::class,
            ConsumerMakeCommand::class,
            MiddlewareMakeCommand::class,
            ProducerMakeCommand::class,
        ]);

        $this->app->bind('metamorphosis', function () {
            return new Producer();
        });

        $this->app->singleton('manager', function () {
            return new Manager();
        });

        $this->app->singleton(CachedSchemaRegistryClient::class, function ($app) {
            $guzzleHttp = $this->getGuzzleHttpClient($app->manager);
            $avroClient = new Client($guzzleHttp);

            return new CachedSchemaRegistryClient($avroClient);
        });
    }

    private function getGuzzleHttpClient(Manager $manager): GuzzleClient
    {
        $options = $manager->get('request_options') ?: [];
        $options['timeout'] = $manager->get('timeout');
        $options['base_uri'] = $manager->get('url');
        $options['headers'] = array_merge(
            $this->getDefaultHeaders(),
            $options['headers']
        );

        return new GuzzleClient($options);
    }

    private function getDefaultHeaders(): array
    {
        return [
            'Accept' => 'application/vnd.schemaregistry.v1+json, application/vnd.schemaregistry+json, application/json',
        ];
    }

    private function validateManager(Manager $manager): void
    {
        if (!$manager->get('url')) {
            throw new ConfigurationException("Avro schema url not found, it's required to use AvroSchemaDecoder Middleware");
        }
    }
}

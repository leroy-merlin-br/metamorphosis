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

        $this->app->bind('metamorphosis', function ($app) {
            return $app->make(Producer::class);
        });

        $this->app->bind(CachedSchemaRegistryClient::class, function ($app) {
            $guzzleHttp = $this->getGuzzleHttpClient($app->configManager);
            $avroClient = new Client($guzzleHttp);

            return new CachedSchemaRegistryClient($avroClient);
        });
    }

    private function getGuzzleHttpClient(ConfigManager $configManager): GuzzleClient
    {
        $config = $configManager->get('request_options') ?: [];
        $config['timeout'] = $configManager->get('timeout');
        $config['base_uri'] = $configManager->get('url');
        $config['headers'] = array_merge(
            $this->getDefaultHeaders(),
            $config['headers'] ?? []
        );

        return app(GuzzleClient::class, compact('config'));
    }

    private function getDefaultHeaders(): array
    {
        return [
            'Accept' => 'application/vnd.schemaregistry.v1+json, application/vnd.schemaregistry+json, application/json',
        ];
    }
}

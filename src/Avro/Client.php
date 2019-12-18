<?php
namespace Metamorphosis\Avro;

use GuzzleHttp\Client as GuzzleHttp;
use Metamorphosis\Facades\Manager;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var GuzzleHttp
     */
    private $client;

    /**
     * @var array
     */
    private $options;

    public function __construct(array $options)
    {
        // Construct is temporary as we will
        // put everything on the service provider.
        $this->baseUrl = $options['url'];
        $this->options = $options['request_options'] ?? [];
        $this->client = $this->getClient();
    }

    public function get(string $url): array
    {
        $url = $this->buildUrl($url);
        $headers = $this->getHeaders();

        $response = $this->client->get($url, compact('headers'));

        return $this->parseResponse($response);
    }

    public function post(string $url, array $body = []): array
    {
        $url = $this->buildUrl($url);
        $headers = $this->getHeaders(true);

        $response = $this->client->post($url, [
            'headers' => $headers,
            'form_params' => $body,
        ]);

        return $this->parseResponse($response);
    }

    public function put(string $url, array $body = []): array
    {
        $url = $this->buildUrl($url);
        $headers = $this->getHeaders(true);

        $response = $this->client->post($url, [
            'headers' => $headers,
            'form_params' => $body,
        ]);

        return $this->parseResponse($response);
    }

    public function delete(string $url): array
    {
        $url = $this->buildUrl($url);
        $headers = $this->getHeaders();

        $response = $this->client->delete($url, compact('headers'));

        return $this->parseResponse($response);
    }

    private function getHeaders(bool $shouldIncludeContentType = false): array
    {
        return array_merge(
            ['Accept' => 'application/vnd.schemaregistry.v1+json, application/vnd.schemaregistry+json, application/json'],
            $this->getIncludeContentType($shouldIncludeContentType),
            $this->options['headers'] ?? []
        );
    }

    private function parseResponse(ResponseInterface $response): array
    {
        return [$response->getStatusCode(), json_decode($response->getBody(), true)];
    }

    private function buildUrl(string $url): string
    {
        return $this->baseUrl.$url;
    }

    /**
     * @return \Illuminate\Foundation\Application|mixed
     */
    private function getClient()
    {
        return app(GuzzleHttp::class, ['config' => ['timeout' => Manager::get('timeout')]]);
    }

    private function getIncludeContentType(bool $shouldIncludeContentType): array
    {
        return $shouldIncludeContentType
            ? ['Content-Type' => 'application/vnd.schemaregistry.v1+json']
            : [];
    }
}

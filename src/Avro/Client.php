<?php
namespace Metamorphosis\Avro;

use GuzzleHttp\Client as GuzzleHttp;
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

    public function __construct(string $url)
    {
        // Construct is temporary as we will
        // put everything on the service provider.
        $this->baseUrl = $url;
        $this->client = app(GuzzleHttp::class, ['config' => ['timeout' => 40000]]);
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
        $headers = [
            'Accept' => 'application/vnd.schemaregistry.v1+json, application/vnd.schemaregistry+json, application/json'
        ];

        return $shouldIncludeContentType
            ? array_merge($headers, ['Content-Type' => 'application/vnd.schemaregistry.v1+json'])
            : $headers;
    }

    private function parseResponse(ResponseInterface $response): array
    {
        return [$response->getStatusCode(), json_decode($response->getBody(), true)];
    }

    private function buildUrl(string $url): string
    {
        return $this->baseUrl.$url;
    }
}

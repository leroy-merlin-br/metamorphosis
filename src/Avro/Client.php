<?php

namespace Metamorphosis\Avro;

use GuzzleHttp\Client as GuzzleHttp;
use Psr\Http\Message\ResponseInterface;

class Client implements AvroClientInterface
{
    private GuzzleHttp $client;

    public function __construct(GuzzleHttp $client)
    {
        $this->client = $client;
    }

    public function get(string $url): array
    {
        $response = $this->client->get($url);

        return $this->parseResponse($response);
    }

    public function post(string $url, array $body = []): array
    {
        $response = $this->client->post($url, [
            'headers' => $this->getContentTypeForPostRequest(),
            'form_params' => $body,
        ]);

        return $this->parseResponse($response);
    }

    public function put(string $url, array $body = []): array
    {
        $response = $this->client->post($url, [
            'headers' => $this->getContentTypeForPostRequest(),
            'form_params' => $body,
        ]);

        return $this->parseResponse($response);
    }

    public function delete(string $url): array
    {
        $response = $this->client->delete($url);

        return $this->parseResponse($response);
    }

    private function parseResponse(ResponseInterface $response): array
    {
        return [$response->getStatusCode(), json_decode(
            $response->getBody(),
            true
        ),
        ];
    }

    private function getContentTypeForPostRequest(): array
    {
        return ['Content-Type' => 'application/vnd.schemaregistry.v1+json'];
    }
}

<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services;

use App\Contracts\IHttpClient;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\RequestOptions;

class HttpClient implements IHttpClient
{
    /**
     * @var Guzzle
     */
    private $client;
    /**
     * @var string
     */
    private $baseUrl;
    /**
     * @var array
     */
    private $headers = [];

    public function __construct(Guzzle $client)
    {
        $this->client = $client;
    }

    public function setBaseUrl(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function setHeader(string $name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function post(string $uri, array $data)
    {
        $response = $this->client->post($this->baseUrl . $uri, [
            RequestOptions::JSON => $data
        ]);

        return json_decode((string)$response->getBody(), true);
    }

    public function get(string $uri)
    {
        $response = $this->client->request('GET', $this->baseUrl . $uri, ['headers' => $this->headers]);
        return json_decode((string)$response->getBody(), true);
    }
}

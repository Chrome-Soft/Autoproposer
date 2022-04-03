<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace App\Services\ProductImport;

class ImportOptions
{
    /** @var string */
    public $baseUrl;
    /** @var string */
    public $endpoint;
    /**
     * @var string
     */
    public $apiKeyHash;
    /**
     * @var string
     */
    public $apiKeyEndpoint;

    public function __construct(string $baseUrl, string $endpoint, string $apiKeyHash, string $apiKeyEndpoint)
    {
        $this->baseUrl = $baseUrl;
        $this->endpoint = $endpoint;
        $this->apiKeyHash = $apiKeyHash;
        $this->apiKeyEndpoint = $apiKeyEndpoint;
    }
}

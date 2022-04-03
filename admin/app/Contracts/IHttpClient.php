<?php

namespace App\Contracts;

interface IHttpClient {
    public function post(string $uri, array $data);
    public function get(string $uri);

    public function setBaseUrl(string $baseUrl);
    public function setHeader(string $name, $value);
}
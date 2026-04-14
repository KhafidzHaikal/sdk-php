<?php

namespace Bjb\QrisMpm\Http;

use Bjb\QrisMpm\Config\Config;

class HttpClient
{
    public function __construct(private readonly Config $config) {}

    public function post(string $url, array $headers, string $body): array
    {
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => $this->buildHeaderString($headers),
                'content' => $body,
                'timeout' => $this->config->timeout,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => !$this->config->isDevEnvironment(),
                'verify_peer_name' => !$this->config->isDevEnvironment(),
            ],
        ];

        $context = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            throw new \RuntimeException("HTTP request failed: {$url}");
        }

        return json_decode($response, true) ?? [];
    }

    private function buildHeaderString(array $headers): string
    {
        $lines = [];
        foreach ($headers as $key => $value) {
            $lines[] = "{$key}: {$value}";
        }
        return implode("\r\n", $lines);
    }
}

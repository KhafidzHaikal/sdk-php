<?php

namespace Bjb\QrisMpm\Service;

use Bjb\QrisMpm\Config\Config;
use Bjb\QrisMpm\Crypto\RsaSigner;
use Bjb\QrisMpm\Exception\ApiException;
use Bjb\QrisMpm\Http\HttpClient;

class AuthService
{
    public function __construct(
        private readonly Config $config,
        private readonly HttpClient $http,
    ) {}

    public function getAccessToken(string $requestId): string
    {
        $timestamp = $this->getTimestamp();
        $signature = RsaSigner::sign($this->config->clientId, $timestamp, $this->config->privateKey);
        $url = rtrim($this->config->baseUrl, '/') . '/api/v1/access-token/b2b';

        $this->log($requestId, 'TOKEN', 'REQUEST', "url={$url}");

        $headers = [
            'Content-Type' => 'application/json',
            'X-CLIENT-KEY' => $this->config->clientId,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
        ];

        $body = json_encode(['grantType' => 'client_credentials']);
        $data = $this->http->post($url, $headers, $body);

        $this->log($requestId, 'TOKEN', 'RESPONSE', "responseCode={$data['responseCode']}");

        if (($data['responseCode'] ?? '') !== '2007300') {
            throw new ApiException($data['responseCode'] ?? 'unknown', $data['responseMessage'] ?? 'Token request failed');
        }

        return $data['accessToken'];
    }

    private function getTimestamp(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('+07:00')))->format('Y-m-d\TH:i:sP');
    }

    private function log(string $requestId, string $service, string $action, string $detail): void
    {
        if ($this->config->logger) {
            ($this->config->logger)("[{$requestId}] service={$service} action={$action} {$detail}");
        }
    }
}

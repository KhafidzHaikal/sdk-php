<?php

namespace Bjb\QrisMpm\Service;

use Bjb\QrisMpm\Config\Config;
use Bjb\QrisMpm\Crypto\HmacSigner;
use Bjb\QrisMpm\Exception\ApiException;
use Bjb\QrisMpm\Http\HttpClient;

class QrisService
{
    public function __construct(
        private readonly Config $config,
        private readonly HttpClient $http,
        private readonly AuthService $auth,
    ) {}

    public function generate(array $payload): array
    {
        $requestId = bin2hex(random_bytes(8));
        $this->log($requestId, 'QRIS_MPM', 'GENERATE_QR');
        return $this->post('/v1.0/qr/qr-mpm-generate', $payload, $requestId);
    }

    public function checkStatus(array $payload): array
    {
        $requestId = bin2hex(random_bytes(8));
        $this->log($requestId, 'QRIS_MPM', 'CHECK_STATUS');
        return $this->post('/v1.0/qr/qr-mpm-query', $payload, $requestId);
    }

    private function post(string $path, array $payload, string $requestId): array
    {
        $accessToken = $this->auth->getAccessToken($requestId);
        $timestamp = (new \DateTimeImmutable('now', new \DateTimeZone('+07:00')))->format('Y-m-d\TH:i:sP');
        $body = json_encode($payload);
        $signature = HmacSigner::sign('POST', $path, $accessToken, $body, $timestamp, $this->config->clientSecret);
        $externalId = bin2hex(random_bytes(10));

        $url = rtrim($this->config->baseUrl, '/') . $path;

        $this->log($requestId, 'SNAP_API', 'REQUEST', "url={$url}");

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$accessToken}",
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'X-PARTNER-ID' => $this->config->clientId,
            'X-EXTERNAL-ID' => $externalId,
            'CHANNEL-ID' => $this->config->channelId,
            'ORIGIN' => 'www.bankbjb.co.id',
        ];

        $data = $this->http->post($url, $headers, $body);

        $this->log($requestId, 'SNAP_API', 'RESPONSE', "responseCode={$data['responseCode']}");

        if (isset($data['responseCode']) && !str_starts_with($data['responseCode'], '2')) {
            throw new ApiException($data['responseCode'], $data['responseMessage'] ?? 'API Error');
        }

        return $data;
    }

    private function log(string $requestId, string $service, string $action, string $detail = ''): void
    {
        if ($this->config->logger) {
            ($this->config->logger)("[{$requestId}] service={$service} action={$action} {$detail}");
        }
    }
}

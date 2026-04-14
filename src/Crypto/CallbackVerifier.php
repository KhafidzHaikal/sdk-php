<?php

namespace Bjb\QrisMpm\Crypto;

class CallbackVerifier
{
    public function __construct(private readonly string $clientSecret) {}

    public function verify(string $method, string $path, string $accessToken, string $body, string $timestamp, string $signature): bool
    {
        $expected = HmacSigner::sign($method, $path, $accessToken, $body, $timestamp, $this->clientSecret);
        return hash_equals($expected, $signature);
    }
}

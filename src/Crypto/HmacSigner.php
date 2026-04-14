<?php

namespace Bjb\QrisMpm\Crypto;

class HmacSigner
{
    /**
     * Symmetric signature for API calls and callback verification.
     * Algorithm: HMAC-SHA512 → lowercase hex.
     */
    public static function sign(
        string $httpMethod,
        string $endpointPath,
        string $accessToken,
        string $requestBody,
        string $timestamp,
        string $clientSecret,
    ): string {
        $bodyHash = strtolower(hash('sha256', $requestBody));
        $stringToSign = "{$httpMethod}:{$endpointPath}:{$accessToken}:{$bodyHash}:{$timestamp}";

        return strtolower(hash_hmac('sha512', $stringToSign, $clientSecret));
    }
}

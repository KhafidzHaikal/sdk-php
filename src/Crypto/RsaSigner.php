<?php

namespace Bjb\QrisMpm\Crypto;

class RsaSigner
{
    /**
     * Asymmetric signature for access token.
     * Algorithm: SHA256withRSA → lowercase hex.
     */
    public static function sign(string $clientId, string $timestamp, string $privateKeyPem): string
    {
        $stringToSign = $clientId . '|' . $timestamp;
        $privateKey = openssl_pkey_get_private($privateKeyPem);

        if (!$privateKey) {
            throw new \RuntimeException('Invalid RSA private key');
        }

        openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return strtolower(bin2hex($signature));
    }
}

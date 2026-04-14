<?php

namespace Bjb\QrisMpm\Config;

class Config
{
    /** @var callable|null */
    public readonly mixed $logger;

    public function __construct(
        public readonly string $baseUrl,
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $channelId,
        public readonly string $privateKey,
        public readonly int    $timeout = 30,
        ?callable $logger = null,
    ) {
        $this->logger = $logger;
    }

    public function isDevEnvironment(): bool
    {
        return str_contains($this->baseUrl, 'devapi');
    }
}

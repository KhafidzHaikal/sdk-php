<?php

namespace Bjb\QrisMpm\Client;

use Bjb\QrisMpm\Config\Config;
use Bjb\QrisMpm\Http\HttpClient;
use Bjb\QrisMpm\Service\AuthService;
use Bjb\QrisMpm\Service\QrisService;

class QrisClient
{
    private readonly QrisService $qrisService;

    public function __construct(array $options)
    {
        $config = new Config(
            baseUrl: $options['baseUrl'],
            clientId: $options['clientId'],
            clientSecret: $options['clientSecret'],
            channelId: $options['channelId'] ?? '',
            privateKey: $options['privateKey'],
            timeout: $options['timeout'] ?? 30,
            logger: $options['logger'] ?? null,
        );

        $http = new HttpClient($config);
        $auth = new AuthService($config, $http);
        $this->qrisService = new QrisService($config, $http, $auth);
    }

    public function qris(): QrisService
    {
        return $this->qrisService;
    }
}

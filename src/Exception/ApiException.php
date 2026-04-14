<?php

namespace Bjb\QrisMpm\Exception;

class ApiException extends \RuntimeException
{
    public function __construct(
        public readonly string $responseCode,
        public readonly string $responseMessage,
        int $httpStatus = 0,
    ) {
        parent::__construct($responseMessage, $httpStatus);
    }

    public function toArray(): array
    {
        return [
            'responseCode' => $this->responseCode,
            'responseMessage' => $this->responseMessage,
        ];
    }
}

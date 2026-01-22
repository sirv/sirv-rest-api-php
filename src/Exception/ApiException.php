<?php

declare(strict_types=1);

namespace Sirv\Exception;

/**
 * Exception thrown when an API request fails.
 */
class ApiException extends SirvException
{
    protected int $httpStatusCode;
    protected ?string $requestId;

    public function __construct(
        string $message = '',
        int $code = 0,
        int $httpStatusCode = 0,
        ?string $requestId = null,
        array $errorDetails = []
    ) {
        parent::__construct($message, $code, null, $errorDetails);
        $this->httpStatusCode = $httpStatusCode;
        $this->requestId = $requestId;
    }

    /**
     * Get the HTTP status code of the failed request.
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    /**
     * Get the request ID for debugging purposes.
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }
}

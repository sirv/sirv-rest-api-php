<?php

declare(strict_types=1);

namespace Sirv\Exception;

use Exception;

/**
 * Base exception class for all Sirv SDK exceptions.
 */
class SirvException extends Exception
{
    protected array $errorDetails = [];

    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null, array $errorDetails = [])
    {
        parent::__construct($message, $code, $previous);
        $this->errorDetails = $errorDetails;
    }

    /**
     * Get additional error details from the API response.
     */
    public function getErrorDetails(): array
    {
        return $this->errorDetails;
    }
}

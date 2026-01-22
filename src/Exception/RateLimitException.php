<?php

declare(strict_types=1);

namespace Sirv\Exception;

/**
 * Exception thrown when rate limit is exceeded.
 */
class RateLimitException extends ApiException
{
    protected int $retryAfter;
    protected int $rateLimit;
    protected int $rateLimitRemaining;

    public function __construct(
        string $message = '',
        int $retryAfter = 0,
        int $rateLimit = 0,
        int $rateLimitRemaining = 0
    ) {
        parent::__construct($message, 429, 429);
        $this->retryAfter = $retryAfter;
        $this->rateLimit = $rateLimit;
        $this->rateLimitRemaining = $rateLimitRemaining;
    }

    /**
     * Get the number of seconds to wait before retrying.
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

    /**
     * Get the rate limit for the current endpoint.
     */
    public function getRateLimit(): int
    {
        return $this->rateLimit;
    }

    /**
     * Get the remaining requests in the current window.
     */
    public function getRateLimitRemaining(): int
    {
        return $this->rateLimitRemaining;
    }
}

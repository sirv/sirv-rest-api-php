<?php

declare(strict_types=1);

namespace Sirv\Resources;

use Sirv\HttpClient;

/**
 * Abstract base class for API resources.
 */
abstract class AbstractResource
{
    protected HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }
}

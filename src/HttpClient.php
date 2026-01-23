<?php

declare(strict_types=1);

namespace Sirv;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use Psr\Http\Message\ResponseInterface;
use Sirv\Exception\ApiException;
use Sirv\Exception\AuthenticationException;
use Sirv\Exception\RateLimitException;

/**
 * HTTP client for making requests to the Sirv API.
 */
class HttpClient
{
    private const BASE_URL = 'https://api.sirv.com';
    private const TOKEN_ENDPOINT = '/v2/token';
    private const TOKEN_EXPIRY_BUFFER = 60; // Refresh token 60 seconds before expiry

    private Client $client;
    private string $clientId;
    private string $clientSecret;
    private ?string $accessToken = null;
    private ?int $tokenExpiresAt = null;
    private array $defaultHeaders = [];
    private int $timeout;

    public function __construct(
        string $clientId,
        string $clientSecret,
        int $timeout = 30,
        array $guzzleOptions = []
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->timeout = $timeout;

        $options = array_merge([
            'base_uri' => self::BASE_URL,
            'timeout' => $timeout,
            'http_errors' => false,
        ], $guzzleOptions);

        $this->client = new Client($options);
    }

    /**
     * Set default headers for all requests.
     */
    public function setDefaultHeaders(array $headers): void
    {
        $this->defaultHeaders = $headers;
    }

    /**
     * Authenticate and obtain an access token.
     *
     * @param int|null $expiresIn Optional token expiry time in seconds (5-604800)
     * @throws AuthenticationException
     */
    public function authenticate(?int $expiresIn = null): string
    {
        $payload = [
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
        ];

        if ($expiresIn !== null) {
            if ($expiresIn < 5 || $expiresIn > 604800) {
                throw new AuthenticationException(
                    'expiresIn must be between 5 and 604800 seconds',
                    400
                );
            }
            $payload['expiresIn'] = $expiresIn;
        }

        try {
            $response = $this->client->post(self::TOKEN_ENDPOINT, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($statusCode !== 200) {
                throw new AuthenticationException(
                    $body['message'] ?? 'Authentication failed',
                    $statusCode,
                    null,
                    $body
                );
            }

            $this->accessToken = $body['token'];
            $expiresIn = $body['expiresIn'] ?? 1200;
            $this->tokenExpiresAt = time() + $expiresIn - self::TOKEN_EXPIRY_BUFFER;

            return $this->accessToken;
        } catch (GuzzleException $e) {
            throw new AuthenticationException(
                'Failed to authenticate: ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * Get a valid access token, refreshing if necessary.
     *
     * @throws AuthenticationException
     */
    public function getAccessToken(): string
    {
        if ($this->accessToken === null || $this->isTokenExpired()) {
            $this->authenticate();
        }

        return $this->accessToken;
    }

    /**
     * Check if the current token is expired.
     */
    private function isTokenExpired(): bool
    {
        return $this->tokenExpiresAt === null || time() >= $this->tokenExpiresAt;
    }

    /**
     * Make a GET request.
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    /**
     * Make a POST request.
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function post(string $endpoint, array $data = [], array $query = []): array
    {
        $options = ['json' => $data];
        if (!empty($query)) {
            $options['query'] = $query;
        }
        return $this->request('POST', $endpoint, $options);
    }

    /**
     * Make a DELETE request.
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function delete(string $endpoint, array $query = []): array
    {
        return $this->request('DELETE', $endpoint, ['query' => $query]);
    }

    /**
     * Upload a file.
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function upload(string $endpoint, string $filePath, string $filename, array $query = []): array
    {
        if (!file_exists($filePath)) {
            throw new ApiException("File not found: {$filePath}", 0, 400);
        }

        $options = [
            'query' => $query,
            'body' => fopen($filePath, 'r'),
            'headers' => [
                'Content-Type' => mime_content_type($filePath) ?: 'application/octet-stream',
            ],
        ];

        return $this->request('POST', $endpoint, $options, false);
    }

    /**
     * Upload file content directly.
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function uploadContent(string $endpoint, string $content, string $contentType, array $query = []): array
    {
        $options = [
            'query' => $query,
            'body' => $content,
            'headers' => [
                'Content-Type' => $contentType,
            ],
        ];

        return $this->request('POST', $endpoint, $options, false);
    }

    /**
     * Download a file and return the raw response.
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function download(string $endpoint, array $query = []): string
    {
        $token = $this->getAccessToken();

        $headers = array_merge($this->defaultHeaders, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        try {
            $response = $this->client->get($endpoint, [
                'query' => $query,
                'headers' => $headers,
            ]);

            $this->handleErrorResponse($response);

            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new ApiException(
                'Request failed: ' . $e->getMessage(),
                $e->getCode(),
                0
            );
        }
    }

    /**
     * Make a raw request and return the response.
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function request(string $method, string $endpoint, array $options = [], bool $jsonBody = true): array
    {
        $token = $this->getAccessToken();

        $headers = array_merge($this->defaultHeaders, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        if ($jsonBody && !isset($options['headers']['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        if (isset($options['headers'])) {
            $headers = array_merge($headers, $options['headers']);
            unset($options['headers']);
        }

        $options['headers'] = $headers;

        try {
            $response = $this->client->request($method, $endpoint, $options);
            return $this->handleResponse($response);
        } catch (GuzzleException $e) {
            throw new ApiException(
                'Request failed: ' . $e->getMessage(),
                $e->getCode(),
                0
            );
        }
    }

    /**
     * Handle API response.
     *
     * @throws ApiException
     * @throws RateLimitException
     */
    private function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true) ?? [];

        // Handle rate limiting
        if ($statusCode === 429) {
            $retryAfter = (int) ($response->getHeader('Retry-After')[0] ?? 0);
            $rateLimit = (int) ($response->getHeader('X-RateLimit-Limit')[0] ?? 0);
            $rateLimitRemaining = (int) ($response->getHeader('X-RateLimit-Remaining')[0] ?? 0);

            throw new RateLimitException(
                $data['message'] ?? 'Rate limit exceeded',
                $retryAfter,
                $rateLimit,
                $rateLimitRemaining
            );
        }

        // Handle other errors
        if ($statusCode >= 400) {
            $requestId = $response->getHeader('X-Request-Id')[0] ?? null;
            throw new ApiException(
                $data['message'] ?? 'API request failed',
                $data['code'] ?? 0,
                $statusCode,
                $requestId,
                $data
            );
        }

        return $data;
    }

    /**
     * Handle error response for non-JSON endpoints.
     *
     * @throws ApiException
     */
    private function handleErrorResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true) ?? [];
            $requestId = $response->getHeader('X-Request-Id')[0] ?? null;

            throw new ApiException(
                $data['message'] ?? 'API request failed',
                $data['code'] ?? 0,
                $statusCode,
                $requestId,
                $data
            );
        }
    }

    /**
     * Get the underlying Guzzle client.
     */
    public function getGuzzleClient(): Client
    {
        return $this->client;
    }
}

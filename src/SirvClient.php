<?php

declare(strict_types=1);

namespace Sirv;

use Sirv\Resources\Account;
use Sirv\Resources\Files;
use Sirv\Resources\Stats;
use Sirv\Resources\User;

/**
 * Main client for interacting with the Sirv REST API.
 *
 * @property-read Account $account Account management operations
 * @property-read Files $files File management operations
 * @property-read Stats $stats Statistics operations
 * @property-read User $user User management operations
 */
class SirvClient
{
    public const VERSION = '1.0.0';
    public const API_VERSION = 'v2';

    private HttpClient $httpClient;
    private ?Account $account = null;
    private ?Files $files = null;
    private ?Stats $stats = null;
    private ?User $user = null;

    /**
     * Create a new Sirv client instance.
     *
     * @param string $clientId Your Sirv API client ID
     * @param string $clientSecret Your Sirv API client secret
     * @param int $timeout Request timeout in seconds
     * @param array $guzzleOptions Additional Guzzle HTTP client options
     */
    public function __construct(
        string $clientId,
        string $clientSecret,
        int $timeout = 30,
        array $guzzleOptions = []
    ) {
        $this->httpClient = new HttpClient($clientId, $clientSecret, $timeout, $guzzleOptions);
    }

    /**
     * Get the Account resource.
     */
    public function account(): Account
    {
        if ($this->account === null) {
            $this->account = new Account($this->httpClient);
        }
        return $this->account;
    }

    /**
     * Get the Files resource.
     */
    public function files(): Files
    {
        if ($this->files === null) {
            $this->files = new Files($this->httpClient);
        }
        return $this->files;
    }

    /**
     * Get the Stats resource.
     */
    public function stats(): Stats
    {
        if ($this->stats === null) {
            $this->stats = new Stats($this->httpClient);
        }
        return $this->stats;
    }

    /**
     * Get the User resource.
     */
    public function user(): User
    {
        if ($this->user === null) {
            $this->user = new User($this->httpClient);
        }
        return $this->user;
    }

    /**
     * Get the underlying HTTP client.
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * Authenticate and obtain an access token.
     *
     * @param int|null $expiresIn Optional token expiry time in seconds (5-604800)
     * @throws \Sirv\Exception\AuthenticationException
     */
    public function authenticate(?int $expiresIn = null): string
    {
        return $this->httpClient->authenticate($expiresIn);
    }

    /**
     * Magic getter for resource access.
     *
     * @param string $name
     * @return Account|Files|Stats|User|null
     */
    public function __get(string $name)
    {
        return match ($name) {
            'account' => $this->account(),
            'files' => $this->files(),
            'stats' => $this->stats(),
            'user' => $this->user(),
            default => null,
        };
    }
}

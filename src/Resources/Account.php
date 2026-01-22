<?php

declare(strict_types=1);

namespace Sirv\Resources;

use Sirv\Exception\ApiException;
use Sirv\Exception\AuthenticationException;

/**
 * Account management operations.
 *
 * @link https://apidocs.sirv.com/#account
 */
class Account extends AbstractResource
{
    /**
     * Get account information.
     *
     * Returns account details including CDN URL and account name.
     *
     * @return array Account information
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function get(): array
    {
        return $this->httpClient->get('/v2/account');
    }

    /**
     * Update account settings.
     *
     * @param array $settings Account settings to update
     *        - minify (array): Minification settings
     *        - fetching (array): URL fetching settings
     * @return array Updated account information
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function update(array $settings): array
    {
        return $this->httpClient->post('/v2/account', $settings);
    }

    /**
     * Get API request limits and usage.
     *
     * Returns information about API request allowances and current usage.
     *
     * @return array API limits information
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getLimits(): array
    {
        return $this->httpClient->get('/v2/account/limits');
    }

    /**
     * Get storage usage information.
     *
     * Returns storage usage, plan limits, and file count.
     *
     * @return array Storage information
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getStorage(): array
    {
        return $this->httpClient->get('/v2/account/storage');
    }

    /**
     * List all account users.
     *
     * Returns all users with their roles and permissions.
     *
     * @return array List of users
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getUsers(): array
    {
        return $this->httpClient->get('/v2/account/users');
    }

    /**
     * Get billing plan details.
     *
     * Returns billing plan information including pricing and allowances.
     *
     * @return array Billing plan information
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getBillingPlan(): array
    {
        return $this->httpClient->get('/v2/billing/plan');
    }

    /**
     * Search account events.
     *
     * Search events by module, type, and level.
     *
     * @param array $params Search parameters
     *        - module (string): Event module filter
     *        - type (string): Event type filter
     *        - level (string): Event level filter (info, warning, error)
     *        - from (string): Start date (ISO 8601)
     *        - to (string): End date (ISO 8601)
     * @return array Search results
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function searchEvents(array $params = []): array
    {
        return $this->httpClient->post('/v2/account/events/search', $params);
    }

    /**
     * Mark events as seen.
     *
     * @param array $eventIds Array of event IDs to mark as seen
     * @return array Result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function markEventsSeen(array $eventIds): array
    {
        return $this->httpClient->post('/v2/account/events/seen', $eventIds);
    }
}

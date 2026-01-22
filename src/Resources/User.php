<?php

declare(strict_types=1);

namespace Sirv\Resources;

use Sirv\Exception\ApiException;
use Sirv\Exception\AuthenticationException;

/**
 * User management operations.
 *
 * @link https://apidocs.sirv.com/#user
 */
class User extends AbstractResource
{
    /**
     * Get user information.
     *
     * Returns user details including name, email, and S3 credentials.
     *
     * @param string|null $userId Optional user ID. If not provided, returns current user info.
     * @return array User information
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function get(?string $userId = null): array
    {
        $query = [];
        if ($userId !== null) {
            $query['userId'] = $userId;
        }
        return $this->httpClient->get('/v2/user', $query);
    }
}

<?php

declare(strict_types=1);

namespace Sirv\Resources;

use Sirv\Exception\ApiException;
use Sirv\Exception\AuthenticationException;
use DateTimeInterface;

/**
 * Statistics operations.
 *
 * @link https://apidocs.sirv.com/#statistics
 */
class Stats extends AbstractResource
{
    /**
     * Get HTTP transfer statistics.
     *
     * Returns daily data transfer amounts during the specified period.
     *
     * @param DateTimeInterface|string $from Start date (ISO 8601 format or DateTime object)
     * @param DateTimeInterface|string $to End date (ISO 8601 format or DateTime object)
     * @return array HTTP statistics
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getHttp($from, $to): array
    {
        return $this->httpClient->get('/v2/stats/http', [
            'from' => $this->formatDate($from),
            'to' => $this->formatDate($to),
        ]);
    }

    /**
     * Get spin viewer statistics.
     *
     * Returns spin viewer statistics for the specified period (max 5 days).
     *
     * @param DateTimeInterface|string $from Start date (ISO 8601 format or DateTime object)
     * @param DateTimeInterface|string $to End date (ISO 8601 format or DateTime object)
     * @param string|null $alias Optional account alias filter
     * @return array Spin statistics
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getSpinViews($from, $to, ?string $alias = null): array
    {
        $query = [
            'from' => $this->formatDate($from),
            'to' => $this->formatDate($to),
        ];

        if ($alias !== null) {
            $query['alias'] = $alias;
        }

        return $this->httpClient->get('/v2/stats/spins/views', $query);
    }

    /**
     * Get storage statistics.
     *
     * Returns total data stored on the given day or period.
     *
     * @param DateTimeInterface|string $from Start date (ISO 8601 format or DateTime object)
     * @param DateTimeInterface|string $to End date (ISO 8601 format or DateTime object)
     * @return array Storage statistics
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getStorage($from, $to): array
    {
        return $this->httpClient->get('/v2/stats/storage', [
            'from' => $this->formatDate($from),
            'to' => $this->formatDate($to),
        ]);
    }

    /**
     * Format a date for API requests.
     *
     * @param DateTimeInterface|string $date
     * @return string
     */
    private function formatDate($date): string
    {
        if ($date instanceof DateTimeInterface) {
            return $date->format('Y-m-d\TH:i:s.v');
        }
        return $date;
    }
}

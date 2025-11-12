<?php

namespace App\Contracts;

interface RateLimiterInterface
{
    /**
     * Check if the rate limit has been exceeded.
     */
    public function isLimitExceeded(): bool;

    /**
     * Wait if necessary to respect the rate limit.
     */
    public function throttle(): void;

    /**
     * Record a new request timestamp.
     */
    public function hit(): void;

    /**
     * Get the number of remaining requests allowed.
     */
    public function remaining(): int;

    /**
     * Clear all recorded requests.
     */
    public function clear(): void;
}

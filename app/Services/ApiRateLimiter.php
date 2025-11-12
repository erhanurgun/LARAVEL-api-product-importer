<?php

namespace App\Services;

use App\Contracts\RateLimiterInterface;
use Illuminate\Support\Facades\Cache;

final class ApiRateLimiter implements RateLimiterInterface
{
    private const CACHE_KEY_PREFIX = 'api_rate_limiter:';

    private const WINDOW_SECONDS = 60;

    private readonly string $cacheKey;

    public function __construct(
        private readonly int $maxRequests = 10,
        private readonly string $identifier = 'default',
    ) {
        $this->cacheKey = self::CACHE_KEY_PREFIX.$this->identifier;
    }

    /**
     * Check if the rate limit has been exceeded.
     */
    public function isLimitExceeded(): bool
    {
        $timestamps = $this->getRequestTimestamps();

        return count($timestamps) >= $this->maxRequests;
    }

    /**
     * Wait if necessary to respect the rate limit.
     */
    public function throttle(): void
    {
        $timestamps = $this->getRequestTimestamps();

        if (count($timestamps) >= $this->maxRequests) {
            $oldestTimestamp = min($timestamps);
            $waitTime = self::WINDOW_SECONDS - (time() - $oldestTimestamp);

            if ($waitTime > 0) {
                sleep($waitTime);
            }
        }
    }

    /**
     * Record a new request timestamp.
     */
    public function hit(): void
    {
        $timestamps = $this->getRequestTimestamps();
        $timestamps[] = time();

        Cache::put(
            $this->cacheKey,
            $timestamps,
            now()->addSeconds(self::WINDOW_SECONDS + 10)
        );
    }

    /**
     * Get the number of remaining requests allowed.
     */
    public function remaining(): int
    {
        $timestamps = $this->getRequestTimestamps();

        return max(0, $this->maxRequests - count($timestamps));
    }

    /**
     * Clear all recorded requests.
     */
    public function clear(): void
    {
        Cache::forget($this->cacheKey);
    }

    /**
     * Get request timestamps within the current window.
     *
     * @return array<int>
     */
    private function getRequestTimestamps(): array
    {
        $timestamps = Cache::get($this->cacheKey, []);
        $cutoff = time() - self::WINDOW_SECONDS;

        return array_filter(
            $timestamps,
            fn (int $timestamp) => $timestamp > $cutoff
        );
    }
}

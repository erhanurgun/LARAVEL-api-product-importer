<?php

namespace App\Services;

use App\Contracts\CheckpointManagerInterface;
use Illuminate\Support\Facades\Cache;

final class CheckpointManager implements CheckpointManagerInterface
{
    public function __construct(
        private readonly string $cacheKey,
        private readonly int $ttlHours = 24,
    ) {}

    public function save(int $page): void
    {
        Cache::put(
            $this->cacheKey,
            $page,
            now()->addHours($this->ttlHours)
        );
    }

    public function get(): ?int
    {
        $page = Cache::get($this->cacheKey);

        return $page !== null ? (int) $page : null;
    }

    public function clear(): void
    {
        Cache::forget($this->cacheKey);
    }

    public function hasCheckpoint(): bool
    {
        return Cache::has($this->cacheKey);
    }
}

<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;

final class ImportStatistics extends Data
{
    public function __construct(
        public int $totalProcessed = 0,
        public int $successfulImports = 0,
        public int $failedValidations = 0,
        public float $startTime = 0,
        public int $startMemory = 0,
    ) {}

    public function incrementSuccess(): void
    {
        $this->successfulImports++;
        $this->totalProcessed++;
    }

    public function incrementFailed(): void
    {
        $this->failedValidations++;
        $this->totalProcessed++;
    }

    #[Computed]
    public function successRate(): float
    {
        if ($this->totalProcessed === 0) {
            return 0.0;
        }

        return ($this->successfulImports / $this->totalProcessed) * 100;
    }

    #[Computed]
    public function duration(): float
    {
        return microtime(true) - $this->startTime;
    }

    #[Computed]
    public function memoryUsed(): int
    {
        return memory_get_usage(true) - $this->startMemory;
    }

    #[Computed]
    public function averageTimePerItem(): float
    {
        if ($this->totalProcessed === 0) {
            return 0.0;
        }

        return ($this->duration() / $this->totalProcessed) * 1000; // in milliseconds
    }
}

<?php

namespace App\DataTransferObjects;

final class ImportStatistics
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

    public function calculateSuccessRate(): float
    {
        if ($this->totalProcessed === 0) {
            return 0.0;
        }

        return ($this->successfulImports / $this->totalProcessed) * 100;
    }

    public function getDuration(): float
    {
        return microtime(true) - $this->startTime;
    }

    public function getMemoryUsed(): int
    {
        return memory_get_usage(true) - $this->startMemory;
    }

    public function getAverageTimePerItem(): float
    {
        if ($this->totalProcessed === 0) {
            return 0.0;
        }

        return ($this->getDuration() / $this->totalProcessed) * 1000; // in milliseconds
    }

    public function toArray(): array
    {
        return [
            'total_processed' => $this->totalProcessed,
            'successful_imports' => $this->successfulImports,
            'failed_validations' => $this->failedValidations,
            'success_rate' => $this->calculateSuccessRate(),
            'duration' => $this->getDuration(),
            'memory_used' => $this->getMemoryUsed(),
            'average_time_per_item' => $this->getAverageTimePerItem(),
        ];
    }
}

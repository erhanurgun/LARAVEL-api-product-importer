<?php

namespace App\Contracts;

interface FormatterServiceInterface
{
    /**
     * Format duration in seconds to human-readable string.
     */
    public function formatDuration(float $seconds): string;

    /**
     * Format bytes to human-readable size.
     */
    public function formatBytes(int $bytes): string;

    /**
     * Format number with thousands separator.
     */
    public function formatNumber(float $number, int $decimals = 2): string;
}

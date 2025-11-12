<?php

namespace App\Services;

use App\Contracts\FormatterServiceInterface;

final class FormatterService implements FormatterServiceInterface
{
    private const BYTES_PER_KILOBYTE = 1024;

    private const SECONDS_PER_HOUR = 3600;

    private const SECONDS_PER_MINUTE = 60;

    public function formatDuration(float $seconds): string
    {
        $hours = floor($seconds / self::SECONDS_PER_HOUR);
        $minutes = floor(($seconds % self::SECONDS_PER_HOUR) / self::SECONDS_PER_MINUTE);
        $secs = $seconds % self::SECONDS_PER_MINUTE;

        if ($hours > 0) {
            return sprintf('%dh %dm %.2fs', $hours, $minutes, $secs);
        }

        if ($minutes > 0) {
            return sprintf('%dm %.2fs', $minutes, $secs);
        }

        return sprintf('%.2fs', $secs);
    }

    public function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes !== 0 ? log($bytes) : 0) / log(self::BYTES_PER_KILOBYTE));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(self::BYTES_PER_KILOBYTE, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }

    public function formatNumber(float $number, int $decimals = 2): string
    {
        return number_format($number, $decimals);
    }
}

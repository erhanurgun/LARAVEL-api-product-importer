<?php

namespace App\Services;

use App\Contracts\FormatterServiceInterface;

final class FormatterService implements FormatterServiceInterface
{
    public function formatDuration(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

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
        $pow = floor(($bytes !== 0 ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }

    public function formatNumber(float $number, int $decimals = 2): string
    {
        return number_format($number, $decimals);
    }
}

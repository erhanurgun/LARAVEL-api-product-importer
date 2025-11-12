<?php

namespace App\Traits;

trait HasEnumHelpers
{
    /**
     * Get all enum values as array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get validation rule string for Laravel validation.
     */
    public static function toValidationRule(): string
    {
        return implode(',', self::values());
    }

    /**
     * Get all enum cases with labels for dropdowns.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->toArray();
    }
}

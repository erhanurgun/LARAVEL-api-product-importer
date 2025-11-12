<?php

namespace App\Contracts;

interface ValidatorInterface
{
    /**
     * Validate product data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function validate(array $data): array;

    /**
     * Validate product data and return errors without throwing.
     *
     * @param  array<string, mixed>  $data
     * @param  bool  $checkUnique  Whether to check uniqueness
     * @return array{valid: bool, data: array<string, mixed>|null, errors: array<string, array<string>>|null}
     */
    public function validateSafe(array $data, bool $checkUnique = true): array;
}

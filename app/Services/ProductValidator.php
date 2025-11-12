<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductValidator
{
    /**
     * Validate product data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function validate(array $data): array
    {
        $validator = Validator::make($data, $this->rules($data));

        if ($validator->fails()) {
            $this->logValidationErrors($data, $validator->errors()->toArray());

            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Validate product data and return errors without throwing.
     *
     * @param  array<string, mixed>  $data
     * @param  bool  $checkUnique  Whether to check SKU uniqueness
     * @return array{valid: bool, data: array<string, mixed>|null, errors: array<string, array<string>>|null}
     */
    public function validateSafe(array $data, bool $checkUnique = true): array
    {
        $validator = Validator::make($data, $this->rules($data, $checkUnique));

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $this->logValidationErrors($data, $errors);

            return [
                'valid' => false,
                'data' => null,
                'errors' => $errors,
            ];
        }

        return [
            'valid' => true,
            'data' => $validator->validated(),
            'errors' => null,
        ];
    }

    /**
     * Get validation rules for product data.
     *
     * @param  array<string, mixed>  $data
     * @param  bool  $checkUnique  Whether to check slug uniqueness
     * @return array<string, array<int, string>>
     */
    private function rules(array $data, bool $checkUnique = true): array
    {
        $slugRules = ['required', 'string', 'max:255'];

        if ($checkUnique) {
            $slugRule = 'unique:products,slug';

            if (isset($data['id'])) {
                $slugRule .= ','.$data['id'].',id';
            }

            $slugRules[] = $slugRule;
        }

        return [
            'id' => ['required', 'string', 'uuid'],
            'title' => ['required', 'string', 'max:500'],
            'slug' => $slugRules,
            'content' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'old_price' => ['nullable', 'numeric', 'min:0'],
            'discount_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'in_stock' => ['required', 'boolean'],
            'image_cover' => ['nullable', 'string'],
            'image_thumbnail' => ['nullable', 'string'],
            'container_type' => ['nullable', 'string'],
            'container_size' => ['nullable', 'string'],
            'production_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'condition' => ['nullable', 'string', 'in:new,used,refurbished'],
            'location_city' => ['nullable', 'string'],
            'location_district' => ['nullable', 'string'],
            'location_country' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'in:sale,rent'],
            'is_new' => ['nullable', 'boolean'],
            'is_hot_sale' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_bulk_sale' => ['nullable', 'boolean'],
            'accept_offers' => ['nullable', 'boolean'],
            'status' => ['required', 'string', 'in:draft,published,archived'],
            'colors' => ['nullable', 'array'],
            'all_prices' => ['nullable', 'array'],
            'technical_specs' => ['nullable', 'array'],
            'user_info' => ['nullable', 'array'],
        ];
    }

    /**
     * Log validation errors to import_errors channel.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, array<string>>  $errors
     */
    private function logValidationErrors(array $data, array $errors): void
    {
        Log::channel('import_errors')->error(__('products.validation.failed'), [
            'timestamp' => now()->toIso8601String(),
            'product_id' => $data['id'] ?? 'N/A',
            'slug' => $data['slug'] ?? 'N/A',
            'errors' => $errors,
            'raw_data' => $data,
        ]);
    }
}

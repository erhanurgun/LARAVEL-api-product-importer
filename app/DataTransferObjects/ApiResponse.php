<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

final class ApiResponse extends Data
{
    /**
     * @param  array<array<string, mixed>>  $data
     */
    public function __construct(
        public array $data,
        #[Min(1)]
        public int $currentPage,
        #[Min(1)]
        public int $lastPage,
        #[Min(0)]
        public int $total,
    ) {}

    public static function fromArray(array $response): self
    {
        $pagination = $response['data']['pagination'] ?? [];
        $products = $response['data']['products'] ?? [];

        return new self(
            data: $products,
            currentPage: $pagination['current_page'] ?? 1,
            lastPage: self::extractLastPage($pagination),
            total: $pagination['total'] ?? count($products),
        );
    }

    #[Computed]
    public function hasData(): bool
    {
        return ! empty($this->data);
    }

    #[Computed]
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    #[Computed]
    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    #[Computed]
    public function isLastPage(): bool
    {
        return $this->currentPage >= $this->lastPage;
    }

    private static function extractLastPage(array $pagination): int
    {
        if (isset($pagination['last_page_url']) && is_string($pagination['last_page_url'])) {
            parse_str(parse_url($pagination['last_page_url'], PHP_URL_QUERY) ?? '', $params);

            return (int) ($params['page'] ?? 1);
        }

        return $pagination['total'] ?? 1;
    }
}

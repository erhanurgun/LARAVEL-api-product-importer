<?php

namespace App\DataTransferObjects;

final readonly class ApiResponse
{
    /**
     * @param  array<array<string, mixed>>  $data
     */
    public function __construct(
        public array $data,
        public int $currentPage,
        public int $lastPage,
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

    public function hasData(): bool
    {
        return ! empty($this->data);
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

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

    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage,
            'total' => $this->total,
        ];
    }
}

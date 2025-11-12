<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class ProductApiClient
{
    private const MAX_RETRIES = 3;

    private const RETRY_DELAY_MS = 1000;

    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $apiKey = null,
    ) {}

    /**
     * Fetch products from the API with pagination.
     *
     * @param  int  $page  Page number to fetch
     * @return array{data: array, current_page: int, last_page: int, total: int}
     */
    public function fetchProducts(int $page = 1): array
    {
        $response = $this->makeRequest(
            url: '',
            params: [
                'page' => $page,
            ]
        );

        return $this->normalizeResponse($response);
    }

    /**
     * Normalize API response to expected format.
     *
     * @param  array<string, mixed>  $response
     * @return array{data: array, current_page: int, last_page: int, total: int}
     */
    private function normalizeResponse(array $response): array
    {
        $pagination = $response['data']['pagination'] ?? [];
        $products = $response['data']['products'] ?? [];

        return [
            'data' => $products,
            'current_page' => $pagination['current_page'] ?? 1,
            'last_page' => $this->extractLastPage($pagination),
            'total' => $pagination['total'] ?? count($products),
        ];
    }

    /**
     * Extract last page number from pagination data.
     *
     * @param  array<string, mixed>  $pagination
     */
    private function extractLastPage(array $pagination): int
    {
        if (isset($pagination['last_page_url']) && is_string($pagination['last_page_url'])) {
            parse_str(parse_url($pagination['last_page_url'], PHP_URL_QUERY) ?? '', $params);

            return (int) ($params['page'] ?? 1);
        }

        return $pagination['total'] ?? 1;
    }

    /**
     * Make an HTTP request with retry logic.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function makeRequest(string $url, array $params = []): array
    {
        $attempt = 0;

        while ($attempt < self::MAX_RETRIES) {
            try {
                $response = $this->buildClient()
                    ->get($this->baseUrl.$url, $params)
                    ->throw();

                return $response->json();
            } catch (ConnectionException $e) {
                $this->handleRetry($attempt, $e);
            } catch (RequestException $e) {
                if ($this->shouldRetry($e)) {
                    $this->handleRetry($attempt, $e);
                } else {
                    throw $e;
                }
            }

            $attempt++;
        }

        throw new \RuntimeException(
            'Max retries exceeded for API request'
        );
    }

    private function buildClient(): PendingRequest
    {
        $client = Http::timeout(30)
            ->retry(0, 0);

        if ($this->apiKey !== null) {
            $client->withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
            ]);
        }

        return $client;
    }

    private function shouldRetry(\Throwable $exception): bool
    {
        if ($exception instanceof RequestException) {
            $statusCode = $exception->response->status();

            return in_array($statusCode, [429, 500, 502, 503, 504], true);
        }

        return false;
    }

    /**
     * @throws \Throwable
     */
    private function handleRetry(int $attempt, \Throwable $exception): void
    {
        if ($attempt >= self::MAX_RETRIES - 1) {
            throw $exception;
        }

        $delay = self::RETRY_DELAY_MS * (2 ** $attempt);
        usleep($delay * 1000);
    }
}

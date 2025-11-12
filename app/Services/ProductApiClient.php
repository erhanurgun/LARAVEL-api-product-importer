<?php

namespace App\Services;

use App\Contracts\ApiClientInterface;
use App\DataTransferObjects\ApiResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

final class ProductApiClient implements ApiClientInterface
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $apiKey = null,
        private readonly int $maxRetries = 3,
        private readonly int $retryDelayMs = 1000,
    ) {}

    public function fetchProducts(int $page = 1): ApiResponse
    {
        $response = $this->makeRequest(
            url: '',
            params: [
                'page' => $page,
            ]
        );

        return ApiResponse::fromArray($response);
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

        while ($attempt < $this->maxRetries) {
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
        if ($attempt >= $this->maxRetries - 1) {
            throw $exception;
        }

        $delay = $this->retryDelayMs * (2 ** $attempt);
        usleep($delay * 1000);
    }
}

<?php

use App\Actions\ImportProductsAction;
use App\Contracts\ApiClientInterface;
use App\Contracts\CheckpointManagerInterface;
use App\Contracts\DataMapperInterface;
use App\Contracts\RateLimiterInterface;
use App\Contracts\SerializerInterface;
use App\Contracts\ValidatorInterface;
use App\DataTransferObjects\ApiResponse;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('executes import successfully', function () {
    $apiClient = mock(ApiClientInterface::class);
    $validator = mock(ValidatorInterface::class);
    $rateLimiter = mock(RateLimiterInterface::class);
    $dataMapper = mock(DataMapperInterface::class);
    $checkpointManager = mock(CheckpointManagerInterface::class);
    $serializer = mock(SerializerInterface::class);

    $apiClient->shouldReceive('fetchProducts')
        ->once()
        ->with(1)
        ->andReturn(new ApiResponse(
            data: [
                ['id' => 'uuid1', 'title' => 'Product 1'],
            ],
            currentPage: 1,
            lastPage: 1,
            total: 1
        ));

    $rateLimiter->shouldReceive('throttle')->once();
    $rateLimiter->shouldReceive('hit')->once();

    $dataMapper->shouldReceive('mapToDatabaseFormat')
        ->once()
        ->andReturn([
            'id' => 'uuid1',
            'title' => 'Product 1',
            'slug' => 'product-1',
            'price' => 100,
            'quantity' => 10,
            'in_stock' => true,
            'status' => 'published',
        ]);

    $validator->shouldReceive('validateSafe')
        ->once()
        ->andReturn([
            'valid' => true,
            'data' => ['id' => 'uuid1', 'title' => 'Product 1'],
            'errors' => null,
        ]);

    $serializer->shouldReceive('serializeBatch')
        ->once()
        ->andReturn([['id' => 'uuid1', 'title' => 'Product 1']]);

    $checkpointManager->shouldReceive('save')->once()->with(2);

    $action = new ImportProductsAction(
        $apiClient,
        $validator,
        $rateLimiter,
        $dataMapper,
        $checkpointManager,
        $serializer
    );

    $stats = $action->execute(startPage: 1, dryRun: false);

    expect($stats->totalProcessed)->toBe(1)
        ->and($stats->successfulImports)->toBe(1)
        ->and($stats->failedValidations)->toBe(0);
});

it('skips invalid products during import', function () {
    $apiClient = mock(ApiClientInterface::class);
    $validator = mock(ValidatorInterface::class);
    $rateLimiter = mock(RateLimiterInterface::class);
    $dataMapper = mock(DataMapperInterface::class);
    $checkpointManager = mock(CheckpointManagerInterface::class);
    $serializer = mock(SerializerInterface::class);

    $apiClient->shouldReceive('fetchProducts')
        ->once()
        ->andReturn(new ApiResponse(
            data: [
                ['id' => 'valid'],
                ['id' => 'invalid'],
            ],
            currentPage: 1,
            lastPage: 1,
            total: 2
        ));

    $rateLimiter->shouldReceive('throttle')->once();
    $rateLimiter->shouldReceive('hit')->once();

    $dataMapper->shouldReceive('mapToDatabaseFormat')->twice()->andReturn([]);

    $validator->shouldReceive('validateSafe')
        ->twice()
        ->andReturn(
            ['valid' => true, 'data' => ['id' => 'valid'], 'errors' => null],
            ['valid' => false, 'data' => null, 'errors' => ['error']]
        );

    $serializer->shouldReceive('serializeBatch')->once()->andReturn([]);
    $checkpointManager->shouldReceive('save')->once();

    $action = new ImportProductsAction(
        $apiClient,
        $validator,
        $rateLimiter,
        $dataMapper,
        $checkpointManager,
        $serializer
    );

    $stats = $action->execute(startPage: 1, dryRun: false);

    expect($stats->successfulImports)->toBe(1)
        ->and($stats->failedValidations)->toBe(1);
});

it('respects dry run mode', function () {
    $apiClient = mock(ApiClientInterface::class);
    $validator = mock(ValidatorInterface::class);
    $rateLimiter = mock(RateLimiterInterface::class);
    $dataMapper = mock(DataMapperInterface::class);
    $checkpointManager = mock(CheckpointManagerInterface::class);
    $serializer = mock(SerializerInterface::class);

    $apiClient->shouldReceive('fetchProducts')
        ->once()
        ->andReturn(new ApiResponse(
            data: [['id' => 'uuid1']],
            currentPage: 1,
            lastPage: 1,
            total: 1
        ));

    $rateLimiter->shouldReceive('throttle')->once();
    $rateLimiter->shouldReceive('hit')->once();
    $dataMapper->shouldReceive('mapToDatabaseFormat')->once()->andReturn([]);
    $validator->shouldReceive('validateSafe')->once()->andReturn([
        'valid' => true,
        'data' => [],
        'errors' => null,
    ]);

    $serializer->shouldReceive('serializeBatch')->never();
    $checkpointManager->shouldReceive('save')->once();

    $action = new ImportProductsAction(
        $apiClient,
        $validator,
        $rateLimiter,
        $dataMapper,
        $checkpointManager,
        $serializer
    );

    $stats = $action->execute(startPage: 1, dryRun: true);

    expect(Product::count())->toBe(0)
        ->and($stats->successfulImports)->toBe(1);
});

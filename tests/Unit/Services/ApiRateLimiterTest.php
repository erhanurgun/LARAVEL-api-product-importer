<?php

use App\Services\ApiRateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->limiter = new ApiRateLimiter(maxRequests: 5, identifier: 'test');
});

afterEach(function () {
    Cache::flush();
});

it('allows requests within the limit', function () {
    expect($this->limiter->isLimitExceeded())->toBeFalse();

    for ($i = 0; $i < 4; $i++) {
        $this->limiter->hit();
    }

    expect($this->limiter->isLimitExceeded())->toBeFalse();
});

it('blocks requests when limit is exceeded', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->limiter->hit();
    }

    expect($this->limiter->isLimitExceeded())->toBeTrue();
});

it('returns correct remaining count', function () {
    expect($this->limiter->remaining())->toBe(5);

    $this->limiter->hit();
    expect($this->limiter->remaining())->toBe(4);

    $this->limiter->hit();
    expect($this->limiter->remaining())->toBe(3);
});

it('clears all recorded requests', function () {
    for ($i = 0; $i < 3; $i++) {
        $this->limiter->hit();
    }

    expect($this->limiter->remaining())->toBe(2);

    $this->limiter->clear();

    expect($this->limiter->remaining())->toBe(5);
});

it('uses different cache keys for different identifiers', function () {
    $limiter1 = new ApiRateLimiter(maxRequests: 5, identifier: 'test1');
    $limiter2 = new ApiRateLimiter(maxRequests: 5, identifier: 'test2');

    for ($i = 0; $i < 5; $i++) {
        $limiter1->hit();
    }

    expect($limiter1->isLimitExceeded())->toBeTrue();
    expect($limiter2->isLimitExceeded())->toBeFalse();
});

<?php

namespace App\Contracts;

use App\DataTransferObjects\ApiResponse;

interface ApiClientInterface
{
    /**
     * Fetch products from the API with pagination.
     */
    public function fetchProducts(int $page = 1): ApiResponse;
}

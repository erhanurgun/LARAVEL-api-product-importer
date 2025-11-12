# Laravel Product Importer

A robust Laravel 12 application for importing products from external APIs with advanced features like rate limiting, data validation, error recovery, and checkpoint-based resumption.

## Features

- **API Integration**: Seamlessly fetch products from external APIs with pagination support
- **Rate Limiting**: Built-in rate limiter to respect API quotas (configurable requests per minute)
- **Data Validation**: Comprehensive validation with detailed error logging
- **Error Recovery**: Automatic retry mechanism for recoverable errors (timeouts, network issues)
- **Checkpoint System**: Resume imports from last successful page in case of interruption
- **Dry Run Mode**: Validate products without saving to database
- **Multi-language Support**: English and Turkish translations
- **Detailed Logging**: Separate channels for import errors with structured logging
- **Performance Metrics**: Track processing time, memory usage, and success rates
- **UUID Support**: Products use UUID as primary keys
- **Bulk Operations**: Efficient batch processing with upsert operations

## Requirements

- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Node.js & NPM (for frontend assets)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/erhanurgun/LARAVEL-api-product-importer.git
cd LARAVEL-api-product-importer
```

### 2. Install Dependencies

```bash
composer install
bun install
```

### 3. Environment Configuration

Copy the example environment file and configure your settings:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### 4. Configure Database

Update your `.env` file with database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lara_task_app
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Configure Product API

Set the external API endpoint and rate limiting in `.env`:

```env
PRODUCT_API_BASE_URL=https://dummyjson.com/products
PRODUCT_API_KEY=
PRODUCT_API_RATE_LIMIT=10
```

**Configuration Options:**
- `PRODUCT_API_BASE_URL`: The base URL of the product API
- `PRODUCT_API_KEY`: Optional API key for authentication
- `PRODUCT_API_RATE_LIMIT`: Maximum requests per minute (default: 10)

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Build Frontend Assets

```bash
bun run build
```

## Usage

### Import Products

Import all products from the configured API:

```bash
php artisan products:import
```

### Dry Run Mode

Validate products without saving to database:

```bash
php artisan products:import --dry-run
```

This mode is useful for:
- Testing API connectivity
- Validating data before actual import
- Checking how many products would be imported
- Verifying data mapping logic

### Resume Import

If an import is interrupted, resume from the last checkpoint:

```bash
php artisan products:import --resume
```

The system automatically saves checkpoints after each successful page import.

## Architecture

### Service Layer

The application follows a clean service-oriented architecture:

#### ProductApiClient
`app/Services/ProductApiClient.php`

Handles API communication with:
- HTTP client configuration
- Automatic retry logic (max 3 attempts with exponential backoff)
- Response normalization
- Support for API authentication

#### ApiRateLimiter
`app/Services/ApiRateLimiter.php`

Manages API rate limiting:
- Configurable requests per time window
- Automatic throttling when limit is reached
- Cache-based request tracking
- Time window management (60-second windows)

#### ProductValidator
`app/Services/ProductValidator.php`

Validates product data:
- 30+ validation rules
- Safe validation mode (non-throwing)
- Unique slug validation (optional)
- Detailed error logging

#### ProductDataMapper
`app/Services/ProductDataMapper.php`

Maps API response to database format:
- Handles nested data structures (price, stock, image, location, container)
- Extracts and normalizes data
- Default value handling

### Database Schema

Products table includes:

**Basic Information:**
- `id` (UUID, primary key)
- `title`, `slug`, `content`

**Pricing:**
- `price`, `old_price`, `discount_percentage`

**Inventory:**
- `quantity`, `in_stock`

**Media:**
- `image_cover`, `image_thumbnail`

**Product Details:**
- `container_type`, `container_size`
- `production_year`, `condition`
- `type` (sale/rent)

**Location:**
- `location_city`, `location_district`, `location_country`

**Flags:**
- `is_new`, `is_hot_sale`, `is_featured`, `is_bulk_sale`
- `accept_offers`, `status`

**JSON Fields:**
- `colors` (array of available colors)
- `all_prices` (price variations)
- `technical_specs` (product specifications)
- `user_info` (seller/owner information)

### Error Handling

The application implements sophisticated error handling:

**Recoverable Errors:**
- Network timeouts
- Connection failures
- Temporary API unavailability
- HTTP 429, 500, 502, 503, 504 errors

**Critical Errors:**
- Authentication failures
- Invalid API responses
- Database connection issues
- Validation errors beyond threshold

All errors are logged to `storage/logs/import_errors.log` with:
- Timestamp
- Error type and message
- Product data that caused the error
- Stack trace (for critical errors)

## Testing

The application includes comprehensive tests:

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suite

```bash
# Feature tests
php artisan test tests/Feature/

# Unit tests
php artisan test tests/Unit/

# Specific test file
php artisan test tests/Feature/ImportProductsCommandTest.php
```

### Test Coverage

- **Unit Tests:**
  - `ApiRateLimiterTest`: Rate limiting logic
  - `ProductApiClientTest`: API client functionality
  - `ProductValidatorTest`: Validation rules

- **Feature Tests:**
  - `ImportProductsCommandTest`: End-to-end import command

## Code Quality

Format code with Laravel Pint:

```bash
vendor/bin/pint
```

## Development

### Quick Setup

Use the composer setup script for first-time installation:

```bash
composer run setup
```

This will:
1. Install composer dependencies
2. Copy .env.example to .env
3. Generate application key
4. Run migrations
5. Install npm dependencies
6. Build frontend assets

### Development Server

Run the development server with queue worker and Vite:

```bash
composer run dev
```

This starts:
- Laravel development server (http://laravel.test)
- Queue worker
- Vite dev server

## Configuration Files

### Service Provider

`app/Providers/AppServiceProvider.php` registers:
- ProductApiClient singleton with configuration
- ApiRateLimiter singleton with rate limit settings

### Logging

`config/logging.php` defines:
- `import_errors` channel for import-specific errors
- Stack configuration for multiple log destinations

### Services

`config/services.php` contains:
- Product API configuration
- Third-party service credentials

## Translation

The application supports multiple languages:

- **English:** `lang/en/products.php`
- **Turkish:** `lang/tr/products.php`

Change application locale in `.env`:

```env
APP_LOCALE=en  # or 'tr' for Turkish
```

## Performance Considerations

- **Batch Processing**: Products are imported in batches per page
- **Upsert Operations**: Efficient database updates using slug as unique key
- **Memory Management**: Progress tracking and memory usage monitoring
- **Connection Pooling**: HTTP client reuse for better performance
- **Database Indexes**: Optimized indexes on slug, status, and in_stock

## Troubleshooting

### Import Fails Immediately

Check:
1. API URL is accessible: `curl <PRODUCT_API_BASE_URL>`
2. Database connection is working
3. Sufficient disk space for logs

### Rate Limit Issues

Adjust the rate limit in `.env`:

```env
PRODUCT_API_RATE_LIMIT=5  # Reduce if getting 429 errors
```

### Validation Errors

Check `storage/logs/import_errors.log` for:
- Invalid data formats
- Missing required fields
- Constraint violations

### Memory Issues

For large imports, increase PHP memory limit in `php.ini` or `.env`:

```env
PHP_MEMORY_LIMIT=512M
```

## License

This project is open-sourced software licensed under the MIT license.

## Contributing

Contributions are welcome! Please ensure:
1. All tests pass: `php artisan test`
2. Code follows style guide: `vendor/bin/pint`
3. New features include tests
4. Documentation is updated

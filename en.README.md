# Laravel Product Importer

A robust Laravel 12 application designed for importing products from external APIs, featuring rate limiting, data validation, error recovery, and checkpoint-based resumption.

> **Türkçe Dokümantasyon**: [README.md](README.md)

## Features

- **API Integration**: Seamless product fetching from external APIs with pagination support
- **Rate Limiting**: Built-in rate limiter respecting API quotas (configurable requests per minute)
- **Data Validation**: Comprehensive validation with detailed error logging
- **Error Recovery**: Automatic retry mechanism for recoverable errors (timeouts, network issues)
- **Checkpoint System**: Resume from last successful page in case of interruption
- **Dry Run Mode**: Validate products without saving to database
- **Multi-language Support**: English and Turkish translations
- **Detailed Logging**: Structured logging channels for import errors
- **Performance Metrics**: Track processing time, memory usage, and success rates
- **UUID Support**: Products use UUID as primary key
- **Batch Processing**: Efficient batch processing with upsert operations

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

Generate the application key:

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

Set up the external API endpoint and rate limiting settings in `.env`:

```env
PRODUCT_API_BASE_URL=https://dummyjson.com/products
PRODUCT_API_KEY=
PRODUCT_API_RATE_LIMIT=10
```

**Configuration Options:**
- `PRODUCT_API_BASE_URL`: Base URL of the product API
- `PRODUCT_API_KEY`: Optional API key for authentication
- `PRODUCT_API_RATE_LIMIT`: Maximum requests per minute (default: 10)

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Compile Frontend Assets

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
- Checking how many products will be imported
- Verifying data mapping logic

### Resume Import

If an import is interrupted, resume from the last checkpoint:

```bash
php artisan products:import --resume
```

The system automatically saves checkpoints after each successful page import.

## Architecture

The application has been fully refactored following **SOLID principles**, achieving 100% compliance with clean code principles (DRY, KISS, YAGNI), using an enterprise-grade architecture.

### Architectural Layers

```
app/
├── Contracts/              # Interfaces (Dependency Inversion)
├── Enums/                  # Type-safe enum classes
├── ValueObjects/           # Immutable value objects
├── DataTransferObjects/    # Data transfer objects (Spatie Data)
├── Services/               # Business logic services
├── Console/Commands/       # CLI commands (orchestration only)
├── Models/                 # Eloquent models (scopes, accessors)
└── Providers/              # Service providers
```

### 1. Contracts (Interfaces)

All services are defined through interfaces following the **Dependency Inversion Principle**:

- `ApiClientInterface`: API communication contract
- `RateLimiterInterface`: Rate limiting contract
- `ValidatorInterface`: Validation contract
- `DataMapperInterface`: Data mapping contract
- `SerializerInterface`: Serialization contract
- `CheckpointManagerInterface`: Checkpoint management contract
- `FormatterServiceInterface`: Formatting contract

### 2. Enums (Backed Enums)

**Type-safe constant values** using PHP 8.1+ Backed Enums:

#### HasEnumHelpers Trait (`app/Traits/HasEnumHelpers.php`)

**DRY principle applied** - common methods used in all enums moved to a trait:

```php
trait HasEnumHelpers
{
    public static function values(): array
    public static function toValidationRule(): string
    public static function options(): array
}
```

#### ProductStatus (`app/Enums/ProductStatus.php`)
```php
enum ProductStatus: string
{
    use HasEnumHelpers;

    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
```

**Helper Methods** (from trait):
- `values()`: Returns all enum values as array
- `toValidationRule()`: Returns string for Laravel validation (`'draft,published,archived'`)
- `options()`: Returns key-value array for dropdowns

**Enum-Specific Methods:**
- `label()`: Returns label with multi-language support
- `isPublished()`, `isDraft()`, `isArchived()`: Check methods

#### ProductType (`app/Enums/ProductType.php`)
```php
enum ProductType: string
{
    use HasEnumHelpers;

    case SALE = 'sale';
    case RENT = 'rent';
}
```

**Trait Methods:** `values()`, `toValidationRule()`, `options()`
**Enum Methods:** `label()`, `isSale()`, `isRent()`

#### ProductCondition (`app/Enums/ProductCondition.php`)
```php
enum ProductCondition: string
{
    use HasEnumHelpers;

    case NEW = 'new';
    case USED = 'used';
    case REFURBISHED = 'refurbished';
}
```

**Trait Methods:** `values()`, `toValidationRule()`, `options()`
**Enum Methods:** `label()`, `isNew()`, `isUsed()`, `isRefurbished()`

**Usage Example:**
```php
// In validation rules
'status' => ['required', 'string', 'in:'.ProductStatus::toValidationRule()],

// Model casting
protected function casts(): array
{
    return ['status' => ProductStatus::class];
}

// For dropdowns
$statusOptions = ProductStatus::options();
```

### 3. Value Objects

Immutable, type-safe domain objects:

#### Price (`app/ValueObjects/Price.php`)
- Discount calculations
- Validation (negative price check)
- Discount percentage calculation

#### Location (`app/ValueObjects/Location.php`)
- City, district, country information
- Full address formatting
- Completeness check

#### Stock (`app/ValueObjects/Stock.php`)
- Quantity and stock status
- Availability, low stock checks
- Business logic encapsulation

#### ContainerInfo (`app/ValueObjects/ContainerInfo.php`)
- Container types and sizes
- Database format conversion

#### ProductImage (`app/ValueObjects/ProductImage.php`)
- Cover and thumbnail management
- Image presence checks

### 4. Data Transfer Objects (DTOs)

**Enhanced DTOs with [Spatie Laravel Data](https://github.com/spatie/laravel-data)** package:

```bash
composer require spatie/laravel-data
```

#### ImportStatistics (`app/DataTransferObjects/ImportStatistics.php`)
```php
final class ImportStatistics extends Data
{
    public function __construct(
        public int $totalProcessed = 0,
        public int $successfulImports = 0,
        public int $failedValidations = 0,
        public float $startTime = 0,
        public int $startMemory = 0,
    ) {}

    #[Computed]
    public function successRate(): float

    #[Computed]
    public function duration(): float

    #[Computed]
    public function memoryUsed(): int

    #[Computed]
    public function averageTimePerItem(): float
}
```

**Features:**
- Statistics tracking and calculations
- **#[Computed] attribute** for computed properties
- Success rate, duration, memory usage automatically calculated
- Average time per item calculation
- Auto-casting and type safety (Spatie Data)
- `toArray()` automatic (Spatie Data feature)

#### ApiResponse (`app/DataTransferObjects/ApiResponse.php`)
```php
final class ApiResponse extends Data
{
    public function __construct(
        public array $data,
        #[Min(1)]
        public int $currentPage,
        #[Min(1)]
        public int $lastPage,
        #[Min(0)]
        public int $total,
    ) {}

    #[Computed]
    public function hasData(): bool

    #[Computed]
    public function isEmpty(): bool

    #[Computed]
    public function hasMorePages(): bool

    #[Computed]
    public function isLastPage(): bool
}
```

**Features:**
- API response normalization
- **#[Min] validation attributes** for automatic validation
- **#[Computed] attribute** for computed properties
- Pagination helper methods automatically calculated
- Type-safe data access
- `toArray()` automatic (Spatie Data feature)

### 5. Service Layer

**Single Responsibility Principle** - each service has a single responsibility:

#### ProductApiClient
`app/Services/ProductApiClient.php` → `ApiClientInterface`

Manages API communication:
- HTTP client configuration
- Configurable retry logic (exponential backoff)
- Returns ApiResponse DTO
- API authentication support

#### ApiRateLimiter
`app/Services/ApiRateLimiter.php` → `RateLimiterInterface`

Manages API rate limiting:
- Configurable request limit
- Automatic throttling
- Cache-based tracking
- 60-second time window

#### ProductValidator
`app/Services/ProductValidator.php` → `ValidatorInterface`

Validates product data:
- 30+ validation rules
- Safe validation (no exceptions)
- Optional uniqueness check
- Structured error logging

#### ProductDataMapper
`app/Services/ProductDataMapper.php` → `DataMapperInterface`

Maps API response to database format:
- Nested data handling
- Value object integration (optional)
- Default value management

#### ProductSerializer
`app/Services/ProductSerializer.php` → `SerializerInterface`

**DRY principle** - JSON serialization logic in one place:
- Array → JSON encoding
- Batch serialization
- Configurable field list

#### CheckpointManager
`app/Services/CheckpointManager.php` → `CheckpointManagerInterface`

Isolates checkpoint management:
- Save/get/clear operations
- TTL management
- Cache-based persistence

#### FormatterService
`app/Services/FormatterService.php` → `FormatterServiceInterface`

Display formatting in one place:
- Duration formatting (h/m/s)
- Byte formatting (KB/MB/GB)
- Number formatting

### 6. Model Layer

#### Product Model (`app/Models/Product.php`)

**Enum Integration:**
```php
protected function casts(): array
{
    return [
        'status' => ProductStatus::class,
        'type' => ProductType::class,
        'condition' => ProductCondition::class,
        // ... other casts
    ];
}
```

**Global Scope - Auto-Exclude Archived:**
```php
protected static function booted(): void
{
    self::addGlobalScope('excludeArchived', function (Builder $builder) {
        $builder->where('status', '!=', ProductStatus::ARCHIVED->value);
    });
}
```

**Query Scopes:**
- `published()`: Filters published products
- `inStock()`: Filters in-stock products
- `featured()`: Filters featured products
- `hotSale()`: Filters hot sale products
- `withDiscount()`: Filters discounted products
- `withArchived()`: Disables global scope (includes archived products)
- `onlyArchived()`: Returns only archived products

**Accessors (Computed Properties):**
```php
// Formatted price
protected function formattedPrice(): Attribute
{
    return Attribute::make(
        get: fn () => number_format($this->price, 2).' $'
    );
}

// Has discount check
protected function hasDiscount(): Attribute
{
    return Attribute::make(
        get: fn () => $this->old_price && $this->old_price > $this->price
    );
}

// Discount amount
protected function discountAmount(): Attribute
{
    return Attribute::make(
        get: fn () => $this->hasDiscount
            ? $this->old_price - $this->price
            : null
    );
}

// Calculated discount percentage
protected function calculatedDiscountPercentage(): Attribute
{
    return Attribute::make(
        get: fn () => $this->hasDiscount
            ? round((($this->old_price - $this->price) / $this->old_price) * 100)
            : null
    );
}
```

**Usage Examples:**
```php
// Archived products are automatically excluded
$products = Product::all();

// Include archived products
$allProducts = Product::withArchived()->get();

// Only archived products
$archivedProducts = Product::onlyArchived()->get();

// Published and in-stock products
$availableProducts = Product::published()->inStock()->get();

// Accessor usage
$product = Product::find($id);
echo $product->formatted_price; // "1,250.00 $"
echo $product->has_discount; // true/false
echo $product->discount_amount; // 250.50
```

### 7. Command Layer

#### ImportProducts
`app/Console/Commands/ImportProducts.php`

**Single Responsibility**: Only orchestration, business logic delegated to services:
- **Reduced from 357 to 308 lines** (14% reduction)
- **6 private properties → 0** (thanks to DTO usage)
- **All helper methods** moved to respective services
- **Interface-based DI** for improved testability

### Database Schema

The products table includes:

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
- `type` (sale/rental)

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

The application implements advanced error handling:

**Recoverable Errors:**
- Network timeouts
- Connection errors
- Temporary API unavailability
- HTTP 429, 500, 502, 503, 504 errors

**Critical Errors:**
- Authentication errors
- Invalid API responses
- Database connection issues
- Validation errors above threshold

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

Use the composer setup script for first-time setup:

```bash
composer run setup
```

This command:
1. Installs composer dependencies
2. Copies .env.example to .env
3. Generates application key
4. Runs migrations
5. Installs NPM dependencies
6. Compiles frontend assets

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

### Service Providers

**Separation of Concerns** - import services are managed in a separate provider:

#### ImportServiceProvider (`app/Providers/ImportServiceProvider.php`)

**Dependency Injection Container** configuration:

```php
final class ImportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerApiServices();
        $this->registerDataServices();
        $this->registerUtilityServices();
    }
}
```

**Interface → Implementation bindings:**
- `ApiClientInterface` → `ProductApiClient`
- `RateLimiterInterface` → `ApiRateLimiter`
- `ValidatorInterface` → `ProductValidator`
- `DataMapperInterface` → `ProductDataMapper`
- `SerializerInterface` → `ProductSerializer`
- `CheckpointManagerInterface` → `CheckpointManager`
- `FormatterServiceInterface` → `FormatterService`

**Organized into 3 categories:**
1. **API Services**: Client + Rate Limiter
2. **Data Services**: Mapper + Validator + Serializer
3. **Utility Services**: Checkpoint + Formatter

**Advantages:**
- AppServiceProvider remains clean
- Import services are isolated
- Easier testing and maintenance
- Modular structure

#### Provider Registration (`bootstrap/providers.php`)
```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\ImportServiceProvider::class,
];
```

### Logging

`config/logging.php` defines:
- Dedicated `import_errors` channel for import errors
- Stack configuration for multiple log targets

### Configuration

#### config/services.php
- Product API configuration
- Third-party service credentials

#### config/import.php (NEW)
**Externalized magic strings:**
- Checkpoint settings (key, TTL)
- Retry settings (max attempts, delay)
- Batch size
- Recoverable error patterns

## Translation

The application supports multiple languages:

- **English:** `lang/en/products.php`
- **Turkish:** `lang/tr/products.php`

Change the application language from the `.env` file:

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

Adjust rate limit in `.env`:

```env
PRODUCT_API_RATE_LIMIT=5  # Reduce if getting 429 errors
```

### Validation Errors

Check `storage/logs/import_errors.log` for:
- Invalid data formats
- Missing required fields
- Constraint violations

### Memory Issues

Increase PHP memory limit in `php.ini` or `.env` for large imports:

```env
PHP_MEMORY_LIMIT=512M
```

## License

This project is open-source software licensed under the MIT license.

## Contributing

Contributions are welcome! Please ensure:
1. All tests pass: `php artisan test`
2. Code follows style guide: `vendor/bin/pint`
3. New features include tests
4. Documentation is updated

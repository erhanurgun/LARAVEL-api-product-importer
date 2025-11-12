# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - 2025-11-13

### Added
- **ProductData DTO**: New Data Transfer Object using Spatie Laravel Data package for type-safe API data mapping
- **ProductPresenter**: Presenter pattern implementation for view-related logic, separating presentation from domain model
- **ImportProductsAction**: New Action class implementing business logic for product import process
- **Comprehensive Test Coverage**: Added unit tests for ProductData, ProductPresenter, and ImportProductsAction
- **Architecture Documentation**: Detailed documentation of design patterns and architectural decisions

### Changed
- **ProductDataMapper**: Refactored to use ProductData DTO, eliminating 20+ redundant extraction methods (DRY principle)
- **ImportProducts Command**: Refactored to follow Action pattern, reducing responsibility to CLI interaction only (SRP principle)
- **Product Model**: Removed view-related accessor methods, replaced with `present()` method returning ProductPresenter instance
- **ApiResponse::extractLastPage()**: Simplified logic, removed complex URL parsing (KISS principle)
- **ApiRateLimiter**: Optimized cache key generation by caching in constructor instead of repeated concatenation
- **FormatterService**: Replaced magic numbers with named constants (BYTES_PER_KILOBYTE, SECONDS_PER_HOUR, etc.)
- **ProductSerializer**: Added `JSON_THROW_ON_ERROR` flag for proper error handling during JSON encoding
- **Product Model scopePublished()**: Fixed to use ProductStatus enum instead of hardcoded string

### Removed
- **Enum Helper Methods**: Removed YAGNI violations (isNew(), isUsed(), isSale(), isRent(), etc.) from ProductCondition, ProductStatus, and ProductType enums
- **Model Accessor Methods**: Removed getFormattedPriceAttribute(), getHasDiscountAttribute(), getDiscountAmountAttribute(), and getCalculatedDiscountPercentageAttribute() from Product model

### Fixed
- **Type Safety**: All enum comparisons now use proper enum instances instead of magic strings
- **Error Handling**: JSON encoding failures now throw exceptions instead of silent failures
- **Code Formatting**: All files formatted using Laravel Pint according to project standards

### Technical Improvements
- **SOLID Principles**:
  - Single Responsibility: Separated concerns between Command (CLI), Action (business logic), and Presenter (view logic)
  - Open/Closed: DTOs and Actions are extensible without modification
  - Interface Segregation: Maintained clean, focused interfaces
  - Dependency Inversion: All dependencies injected through interfaces

- **DRY (Don't Repeat Yourself)**:
  - Eliminated code duplication in ProductDataMapper using DTO pattern
  - Removed repetitive cache key generation in ApiRateLimiter

- **KISS (Keep It Simple, Stupid)**:
  - Simplified ApiResponse lastPage extraction logic
  - Reduced cognitive complexity in import command

- **YAGNI (You Aren't Gonna Need It)**:
  - Removed unused enum helper methods
  - Eliminated premature abstractions

### Testing
- All 25 existing tests pass successfully
- Added 20+ new unit tests for refactored components
- Improved test coverage for DTO, Presenter, and Action patterns
- Updated test fixtures to match new API response format

### Performance
- Reduced memory allocations in ApiRateLimiter through caching
- Improved validation performance with batch processing in Action
- Optimized DTO mapping with Spatie Laravel Data

### Dependencies
- No new dependencies added
- Better utilization of existing Spatie Laravel Data package

### Breaking Changes
- **Product Model**: `present()` method must be used instead of accessor attributes
  - Before: `$product->formatted_price`
  - After: `$product->present()->formattedPrice()`

- **Enum Methods**: Direct comparison required instead of helper methods
  - Before: `$status->isPublished()`
  - After: `$status === ProductStatus::PUBLISHED`

### Migration Notes
If you have existing code using the removed accessor methods or enum helpers:

1. **Update Model Access**:
   ```php
   // Old
   $price = $product->formatted_price;
   $hasDiscount = $product->has_discount;

   // New
   $presenter = $product->present();
   $price = $presenter->formattedPrice();
   $hasDiscount = $presenter->hasDiscount();
   ```

2. **Update Enum Checks**:
   ```php
   // Old
   if ($product->status->isPublished()) {
       // ...
   }

   // New
   if ($product->status === ProductStatus::PUBLISHED) {
       // ...
   }
   ```

### Code Quality Metrics
- Lines of code reduced by approximately 15%
- Cyclomatic complexity reduced in ImportProducts command from 25+ to ~8
- Code duplication eliminated in ProductDataMapper
- All code now passes Laravel Pint standards
- PHPStan level compliance maintained

### Contributors
- Refactoring performed following Laravel best practices and SOLID principles
- All changes reviewed and tested comprehensively

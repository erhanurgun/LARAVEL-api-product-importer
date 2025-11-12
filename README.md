# Laravel Ürün İçe Aktarıcı

Harici API'lerden ürün içe aktarma işlemleri için geliştirilmiş, rate limiting, veri doğrulama, hata kurtarma ve checkpoint tabanlı devam ettirme özellikleri içeren güçlü bir Laravel 12 uygulaması.

> **English Documentation**: [en.README.md](en.README.md)

## Özellikler

- **API Entegrasyonu**: Harici API'lerden sayfalama desteği ile sorunsuz ürün çekme
- **Rate Limiting**: API kotalarına bağlı kalarak yerleşik rate limiter (dakika başına yapılandırılabilir istek sayısı)
- **Veri Doğrulama**: Detaylı hata günlüğü ile kapsamlı validasyon
- **Hata Kurtarma**: Kurtarılabilir hatalar için otomatik yeniden deneme mekanizması (timeout, network sorunları)
- **Checkpoint Sistemi**: Kesinti durumunda son başarılı sayfadan devam etme
- **Kuru Çalıştırma Modu**: Veritabanına kaydetmeden ürünleri doğrulama
- **Çoklu Dil Desteği**: İngilizce ve Türkçe çeviriler
- **Detaylı Günlük Tutma**: Import hataları için yapılandırılmış günlük kanalları
- **Performans Metrikleri**: İşlem süresi, bellek kullanımı ve başarı oranlarını izleme
- **UUID Desteği**: Ürünler birincil anahtar olarak UUID kullanır
- **Toplu İşlemler**: Upsert işlemleri ile verimli batch işleme

## Gereksinimler

- PHP 8.2 veya üzeri
- MySQL 5.7+ veya MariaDB 10.3+
- Composer
- Node.js & NPM (frontend varlıkları için)

## Kurulum

### 1. Depoyu Klonlayın

```bash
git clone https://github.com/erhanurgun/LARAVEL-api-product-importer.git
cd LARAVEL-api-product-importer
```

### 2. Bağımlılıkları Yükleyin

```bash
composer install
bun install
```

### 3. Ortam Yapılandırması

Örnek ortam dosyasını kopyalayın ve ayarlarınızı yapılandırın:

```bash
cp .env.example .env
```

Uygulama anahtarını oluşturun:

```bash
php artisan key:generate
```

### 4. Veritabanını Yapılandırın

`.env` dosyanızı veritabanı bilgileriyle güncelleyin:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lara_task_app
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Ürün API'sini Yapılandırın

`.env` dosyasında harici API endpoint'ini ve rate limiting ayarlarını yapın:

```env
PRODUCT_API_BASE_URL=https://dummyjson.com/products
PRODUCT_API_KEY=
PRODUCT_API_RATE_LIMIT=10
```

**Yapılandırma Seçenekleri:**
- `PRODUCT_API_BASE_URL`: Ürün API'sinin temel URL'si
- `PRODUCT_API_KEY`: Kimlik doğrulama için opsiyonel API anahtarı
- `PRODUCT_API_RATE_LIMIT`: Dakika başına maksimum istek sayısı (varsayılan: 10)

### 6. Migration'ları Çalıştırın

```bash
php artisan migrate
```

### 7. Frontend Varlıklarını Derleyin

```bash
bun run build
```

## Kullanım

### Ürünleri İçe Aktarma

Yapılandırılmış API'den tüm ürünleri içe aktarın:

```bash
php artisan products:import
```

### Test Olarak Çalıştırma Modu

Ürünleri veritabanına kaydetmeden doğrulayın:

```bash
php artisan products:import --dry-run
```

Bu mod şunlar için kullanışlıdır:
- API bağlantısını test etme
- Gerçek içe aktarmadan önce verileri doğrulama
- Kaç ürünün içe aktarılacağını kontrol etme
- Veri eşleme mantığını doğrulama

### İçe Aktarmaya Devam Etme

Bir içe aktarma işlemi kesintiye uğrarsa, son checkpoint'ten devam edin:

```bash
php artisan products:import --resume
```

Sistem, her başarılı sayfa içe aktarımından sonra otomatik olarak checkpoint'leri kaydeder.

## Mimari

Uygulama **SOLID prensipleri** doğrultusunda tamamen refactor edilmiş, temiz kod prensiplerine (DRY, KISS, YAGNI) %100 uyumlu, enterprise-grade bir mimari kullanmaktadır.

### Mimari Katmanlar

```
app/
├── Contracts/              # Interface'ler (Dependency Inversion)
├── ValueObjects/           # Immutable değer nesneleri
├── DataTransferObjects/    # Veri aktarım nesneleri
├── Services/               # İş mantığı servisleri
├── Console/Commands/       # CLI komutları (sadece orkestrasyon)
└── Models/                 # Eloquent modeller
```

### 1. Contracts (Interfaces)

**Dependency Inversion Principle** uygulanarak tüm servisler interface'ler üzerinden tanımlanmıştır:

- `ApiClientInterface`: API iletişim kontratı
- `RateLimiterInterface`: Rate limiting kontratı
- `ValidatorInterface`: Validasyon kontratı
- `DataMapperInterface`: Veri eşleme kontratı
- `SerializerInterface`: Serileştirme kontratı
- `CheckpointManagerInterface`: Checkpoint yönetim kontratı
- `FormatterServiceInterface`: Formatlama kontratı

### 2. Value Objects

Immutable, type-safe domain nesneleri:

#### Price (`app/ValueObjects/Price.php`)
- İndirim hesaplamaları
- Validasyon (negatif fiyat kontrolü)
- Discount percentage hesaplama

#### Location (`app/ValueObjects/Location.php`)
- Şehir, ilçe, ülke bilgileri
- Full address formatlama
- Completeness kontrolü

#### Stock (`app/ValueObjects/Stock.php`)
- Miktar ve stok durumu
- Availability, low stock kontrolleri
- Business logic encapsulation

#### ContainerInfo (`app/ValueObjects/ContainerInfo.php`)
- Konteyner tipleri ve boyutları
- Database format dönüşümü

#### ProductImage (`app/ValueObjects/ProductImage.php`)
- Cover ve thumbnail yönetimi
- Image presence kontrolleri

### 3. Data Transfer Objects (DTOs)

#### ImportStatistics (`app/DataTransferObjects/ImportStatistics.php`)
- İstatistik tracking ve hesaplamalar
- Success rate, duration, memory usage
- Average time per item hesaplama

#### ApiResponse (`app/DataTransferObjects/ApiResponse.php`)
- API yanıt normalizasyonu
- Pagination helper metodları
- Type-safe veri erişimi

### 4. Servis Katmanı

**Single Responsibility Principle** ile her servis tek bir sorumluluğa sahiptir:

#### ProductApiClient
`app/Services/ProductApiClient.php` → `ApiClientInterface`

API iletişimini yönetir:
- HTTP client yapılandırması
- Configurable retry logic (exponential backoff)
- ApiResponse DTO döndürür
- API kimlik doğrulama desteği

#### ApiRateLimiter
`app/Services/ApiRateLimiter.php` → `RateLimiterInterface`

API rate limiting'i yönetir:
- Configurable request limit
- Automatic throttling
- Cache-based tracking
- 60 saniyelik time window

#### ProductValidator
`app/Services/ProductValidator.php` → `ValidatorInterface`

Ürün verilerini doğrular:
- 30+ validasyon kuralı
- Safe validation (no exceptions)
- Optional uniqueness check
- Structured error logging

#### ProductDataMapper
`app/Services/ProductDataMapper.php` → `DataMapperInterface`

API yanıtını veritabanı formatına eşler:
- Nested data handling
- Value object integration (optional)
- Default value management

#### ProductSerializer
`app/Services/ProductSerializer.php` → `SerializerInterface`

**DRY prensibi** ile JSON serialization logic tek yerde:
- Array → JSON encoding
- Batch serialization
- Configurable field list

#### CheckpointManager
`app/Services/CheckpointManager.php` → `CheckpointManagerInterface`

Checkpoint yönetimini izole eder:
- Save/get/clear operations
- TTL management
- Cache-based persistence

#### FormatterService
`app/Services/FormatterService.php` → `FormatterServiceInterface`

Display formatting tek yerde:
- Duration formatting (h/m/s)
- Byte formatting (KB/MB/GB)
- Number formatting

### 5. Command Layer

#### ImportProducts
`app/Console/Commands/ImportProducts.php`

**Single Responsibility**: Sadece orkestrasyon yapar, business logic servislere delegedir:
- **357 satırdan 308 satıra** düşürüldü (14% azalma)
- **6 private property → 0** (DTO kullanımı sayesinde)
- **Tüm helper metodlar** ilgili servislere taşındı
- **Interface-based DI** kullanılarak test edilebilirlik artırıldı

### Veritabanı Şeması

Products tablosu şunları içerir:

**Temel Bilgiler:**
- `id` (UUID, birincil anahtar)
- `title`, `slug`, `content`

**Fiyatlandırma:**
- `price`, `old_price`, `discount_percentage`

**Envanter:**
- `quantity`, `in_stock`

**Medya:**
- `image_cover`, `image_thumbnail`

**Ürün Detayları:**
- `container_type`, `container_size`
- `production_year`, `condition`
- `type` (satış/kiralama)

**Konum:**
- `location_city`, `location_district`, `location_country`

**Bayraklar:**
- `is_new`, `is_hot_sale`, `is_featured`, `is_bulk_sale`
- `accept_offers`, `status`

**JSON Alanları:**
- `colors` (mevcut renklerin dizisi)
- `all_prices` (fiyat varyasyonları)
- `technical_specs` (ürün özellikleri)
- `user_info` (satıcı/sahip bilgileri)

### Hata Yönetimi

Uygulama, gelişmiş hata yönetimi uygular:

**Kurtarılabilir Hatalar:**
- Network timeout'ları
- Bağlantı hataları
- Geçici API kullanım dışılığı
- HTTP 429, 500, 502, 503, 504 hataları

**Kritik Hatalar:**
- Kimlik doğrulama hataları
- Geçersiz API yanıtları
- Veritabanı bağlantı sorunları
- Eşik değerinin üzerindeki validasyon hataları

Tüm hatalar `storage/logs/import_errors.log` dosyasına şunlarla birlikte kaydedilir:
- Zaman damgası
- Hata türü ve mesajı
- Hataya neden olan ürün verisi
- Stack trace (kritik hatalar için)

## Test

Uygulama kapsamlı testler içerir:

### Tüm Testleri Çalıştırma

```bash
php artisan test
```

### Belirli Test Suite'ini Çalıştırma

```bash
# Feature testleri
php artisan test tests/Feature/

# Unit testleri
php artisan test tests/Unit/

# Belirli test dosyası
php artisan test tests/Feature/ImportProductsCommandTest.php
```

### Test Kapsamı

- **Unit Testler:**
  - `ApiRateLimiterTest`: Rate limiting mantığı
  - `ProductApiClientTest`: API client fonksiyonelliği
  - `ProductValidatorTest`: Validasyon kuralları

- **Feature Testler:**
  - `ImportProductsCommandTest`: Uçtan uca import komutu

## Kod Kalitesi

Kodu Laravel Pint ile biçimlendirin:

```bash
vendor/bin/pint
```

## Geliştirme

### Hızlı Kurulum

İlk kurulum için composer setup scriptini kullanın:

```bash
composer run setup
```

Bu komut şunları yapar:
1. Composer bağımlılıklarını yükler
2. .env.example dosyasını .env olarak kopyalar
3. Uygulama anahtarı oluşturur
4. Migration'ları çalıştırır
5. NPM bağımlılıklarını yükler
6. Frontend varlıklarını derler

### Geliştirme Sunucusu

Geliştirme sunucusunu queue worker ve Vite ile çalıştırın:

```bash
composer run dev
```

Bu komut şunları başlatır:
- Laravel geliştirme sunucusu (http://laravel.test)
- Queue worker
- Vite dev sunucusu

## Yapılandırma Dosyaları

### Service Provider

`app/Providers/AppServiceProvider.php` **Dependency Injection Container** yapılandırması:

**Interface → Implementation bindings:**
- `ApiClientInterface` → `ProductApiClient`
- `RateLimiterInterface` → `ApiRateLimiter`
- `ValidatorInterface` → `ProductValidator`
- `DataMapperInterface` → `ProductDataMapper`
- `SerializerInterface` → `ProductSerializer`
- `CheckpointManagerInterface` → `CheckpointManager`
- `FormatterServiceInterface` → `FormatterService`

**3 kategoriye ayrılmış kayıt:**
1. **API Services**: Client + Rate Limiter
2. **Data Services**: Mapper + Validator + Serializer
3. **Utility Services**: Checkpoint + Formatter

### Günlük Tutma

`config/logging.php` şunları tanımlar:
- Import hatalarına özel `import_errors` kanalı
- Birden fazla log hedefi için stack yapılandırması

### Konfigürasyon

#### config/services.php
- Ürün API yapılandırması
- Üçüncü taraf servis kimlik bilgileri

#### config/import.php (YENİ)
**Magic string'ler externalize edildi:**
- Checkpoint ayarları (key, TTL)
- Retry ayarları (max attempts, delay)
- Batch size
- Recoverable error patterns

## Çeviri

Uygulama birden fazla dili destekler:

- **İngilizce:** `lang/en/products.php`
- **Türkçe:** `lang/tr/products.php`

Uygulama dilini `.env` dosyasından değiştirin:

```env
APP_LOCALE=tr  # veya İngilizce için 'en'
```

## Performans Değerlendirmeleri

- **Batch İşleme**: Ürünler sayfa başına toplu olarak içe aktarılır
- **Upsert İşlemleri**: Slug'ı benzersiz anahtar olarak kullanarak verimli veritabanı güncellemeleri
- **Bellek Yönetimi**: İlerleme takibi ve bellek kullanımı izleme
- **Bağlantı Havuzu**: Daha iyi performans için HTTP client yeniden kullanımı
- **Veritabanı İndeksleri**: Slug, status ve in_stock üzerinde optimize edilmiş indeksler

## Sorun Giderme

### İçe Aktarma Hemen Başarısız Oluyor

Kontrol edin:
1. API URL'sine erişilebilir mi: `curl <PRODUCT_API_BASE_URL>`
2. Veritabanı bağlantısı çalışıyor mu
3. Loglar için yeterli disk alanı var mı

### Rate Limit Sorunları

`.env` dosyasında rate limit'i ayarlayın:

```env
PRODUCT_API_RATE_LIMIT=5  # 429 hataları alıyorsanız azaltın
```

### Validasyon Hataları

`storage/logs/import_errors.log` dosyasını kontrol edin:
- Geçersiz veri formatları
- Eksik zorunlu alanlar
- Kısıtlama ihlalleri

### Bellek Sorunları

Büyük içe aktarmalar için PHP bellek limitini `php.ini` veya `.env` dosyasında artırın:

```env
PHP_MEMORY_LIMIT=512M
```

## Lisans

Bu proje MIT lisansı altında açık kaynaklı bir yazılımdır.

## Katkıda Bulunma

Katkılarınızı bekliyoruz! Lütfen şunlardan emin olun:
1. Tüm testler geçiyor: `php artisan test`
2. Kod, stil kılavuzuna uyuyor: `vendor/bin/pint`
3. Yeni özellikler test içeriyor
4. Dokümantasyon güncellenmiş
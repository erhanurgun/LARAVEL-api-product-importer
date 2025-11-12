<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ürün İçe Aktarma Dil Satırları (Türkçe)
    |--------------------------------------------------------------------------
    |
    | Aşağıdaki dil satırları ürün içe aktarma işlemleri sırasında kullanılır.
    |
    */

    'command' => [
        'description' => 'Hız sınırlama ve doğrulama ile üçüncü parti API\'den ürünleri içe aktar',
        'option_resume' => 'Son kontrol noktasından devam et',
        'option_dry_run' => 'Veritabanına kaydetmeden ürünleri doğrula',
        'starting' => 'Ürün içe aktarımı başlatılıyor...',
        'resuming_from' => ':page numaralı sayfadan devam ediliyor',
        'found_products' => ':pages sayfa üzerinden :total ürün bulundu',
        'processing_page' => ':total sayfadan :current sayfa işleniyor',
        'progress_message' => ':successful başarılı, :failed başarısız',
        'dry_run_message' => 'Test modu: :count geçerli ürün içe aktarılacaktı',
        'dry_run_warning' => 'TEST MODU: Veritabanına hiçbir veri kaydedilmedi',
        'failed_validations_warning' => ':count ürün doğrulama başarısız oldu',
        'check_logs' => 'Ayrıntılar için storage/logs/import_errors.log dosyasını kontrol edin',
        'completed_successfully' => 'İçe aktarma başarıyla tamamlandı',
        'resume_command' => 'Şu komutla :page numaralı sayfadan devam edebilirsiniz: php artisan products:import --resume',
        'critical_error' => 'İçe aktarma sırasında kritik hata: :message',
    ],

    'errors' => [
        'recoverable' => ':page numaralı sayfada kurtarılabilir hata: :message',
        'waiting_retry' => 'Tekrar denemeden önce 5 saniye bekleniyor...',
    ],

    'summary' => [
        'title' => 'İÇE AKTARIM ÖZETİ',
        'total_processed' => 'İşlenen Toplam Ürün',
        'successful_imports' => 'Başarılı İçe Aktarmalar',
        'failed_validations' => 'Başarısız Doğrulamalar',
        'success_rate' => 'Başarı Oranı',
        'total_duration' => 'Toplam Süre',
        'memory_used' => 'Kullanılan Bellek',
        'average_time' => 'Ürün Başına Ortalama Süre',
        'metric' => 'Metrik',
        'value' => 'Değer',
    ],

    'validation' => [
        'failed' => 'Ürün doğrulama başarısız oldu',
    ],

];

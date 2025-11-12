<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Doğrulama Dil Satırları
    |--------------------------------------------------------------------------
    |
    | Aşağıdaki dil satırları, doğrulayıcı sınıfı tarafından kullanılan varsayılan
    | hata mesajlarını içerir. Bu kurallardan bazıları boyut kuralları gibi
    | birden fazla versiyona sahiptir. Bu mesajların her birini burada
    | özelleştirebilirsiniz.
    |
    */

    'accepted' => ':attribute kabul edilmelidir.',
    'accepted_if' => ':attribute, :other :value olduğunda kabul edilmelidir.',
    'active_url' => ':attribute geçerli bir URL olmalıdır.',
    'after' => ':attribute, :date tarihinden sonra bir tarih olmalıdır.',
    'after_or_equal' => ':attribute, :date tarihine eşit veya sonraki bir tarih olmalıdır.',
    'alpha' => ':attribute sadece harflerden oluşmalıdır.',
    'alpha_dash' => ':attribute sadece harf, rakam, tire ve alt çizgi içerebilir.',
    'alpha_num' => ':attribute sadece harf ve rakamlardan oluşmalıdır.',
    'any_of' => ':attribute geçersizdir.',
    'array' => ':attribute bir dizi olmalıdır.',
    'ascii' => ':attribute sadece tek baytlık alfasayısal karakterler ve semboller içermelidir.',
    'before' => ':attribute, :date tarihinden önceki bir tarih olmalıdır.',
    'before_or_equal' => ':attribute, :date tarihine eşit veya önceki bir tarih olmalıdır.',
    'between' => [
        'array' => ':attribute, :min ile :max arasında öğeye sahip olmalıdır.',
        'file' => ':attribute, :min ile :max kilobayt arasında olmalıdır.',
        'numeric' => ':attribute, :min ile :max arasında olmalıdır.',
        'string' => ':attribute, :min ile :max karakter arasında olmalıdır.',
    ],
    'boolean' => ':attribute alanı doğru veya yanlış olmalıdır.',
    'can' => ':attribute alanı yetkisiz bir değer içeriyor.',
    'confirmed' => ':attribute alanı onayı eşleşmiyor.',
    'contains' => ':attribute alanında gerekli bir değer eksik.',
    'current_password' => 'Şifre yanlış.',
    'date' => ':attribute geçerli bir tarih olmalıdır.',
    'date_equals' => ':attribute, :date tarihine eşit bir tarih olmalıdır.',
    'date_format' => ':attribute, :format formatına uygun olmalıdır.',
    'decimal' => ':attribute, :decimal ondalık basamağa sahip olmalıdır.',
    'declined' => ':attribute reddedilmelidir.',
    'declined_if' => ':attribute, :other :value olduğunda reddedilmelidir.',
    'different' => ':attribute ile :other farklı olmalıdır.',
    'digits' => ':attribute, :digits basamaklı olmalıdır.',
    'digits_between' => ':attribute, :min ile :max basamak arasında olmalıdır.',
    'dimensions' => ':attribute geçersiz resim boyutlarına sahip.',
    'distinct' => ':attribute alanında yinelenen bir değer var.',
    'doesnt_end_with' => ':attribute şunlardan biriyle bitmemelidir: :values.',
    'doesnt_start_with' => ':attribute şunlardan biriyle başlamamalıdır: :values.',
    'email' => ':attribute geçerli bir e-posta adresi olmalıdır.',
    'ends_with' => ':attribute şunlardan biriyle bitmelidir: :values.',
    'enum' => 'Seçilen :attribute geçersiz.',
    'exists' => 'Seçilen :attribute geçersiz.',
    'extensions' => ':attribute aşağıdaki uzantılardan birine sahip olmalıdır: :values.',
    'file' => ':attribute bir dosya olmalıdır.',
    'filled' => ':attribute alanının bir değeri olmalıdır.',
    'gt' => [
        'array' => ':attribute, :value öğeden fazla olmalıdır.',
        'file' => ':attribute, :value kilobayttan büyük olmalıdır.',
        'numeric' => ':attribute, :value değerinden büyük olmalıdır.',
        'string' => ':attribute, :value karakterden fazla olmalıdır.',
    ],
    'gte' => [
        'array' => ':attribute, :value veya daha fazla öğeye sahip olmalıdır.',
        'file' => ':attribute, :value kilobayt veya daha büyük olmalıdır.',
        'numeric' => ':attribute, :value veya daha büyük olmalıdır.',
        'string' => ':attribute, :value karakter veya daha fazla olmalıdır.',
    ],
    'hex_color' => ':attribute geçerli bir onaltılık renk olmalıdır.',
    'image' => ':attribute bir resim olmalıdır.',
    'in' => 'Seçilen :attribute geçersiz.',
    'in_array' => ':attribute alanı, :other içinde mevcut olmalıdır.',
    'integer' => ':attribute bir tam sayı olmalıdır.',
    'ip' => ':attribute geçerli bir IP adresi olmalıdır.',
    'ipv4' => ':attribute geçerli bir IPv4 adresi olmalıdır.',
    'ipv6' => ':attribute geçerli bir IPv6 adresi olmalıdır.',
    'json' => ':attribute geçerli bir JSON dizesi olmalıdır.',
    'list' => ':attribute bir liste olmalıdır.',
    'lowercase' => ':attribute küçük harf olmalıdır.',
    'lt' => [
        'array' => ':attribute, :value öğeden az olmalıdır.',
        'file' => ':attribute, :value kilobayttan küçük olmalıdır.',
        'numeric' => ':attribute, :value değerinden küçük olmalıdır.',
        'string' => ':attribute, :value karakterden az olmalıdır.',
    ],
    'lte' => [
        'array' => ':attribute, :value öğeden fazla olmamalıdır.',
        'file' => ':attribute, :value kilobayt veya daha küçük olmalıdır.',
        'numeric' => ':attribute, :value veya daha küçük olmalıdır.',
        'string' => ':attribute, :value karakter veya daha az olmalıdır.',
    ],
    'mac_address' => ':attribute geçerli bir MAC adresi olmalıdır.',
    'max' => [
        'array' => ':attribute, :max öğeden fazla olmamalıdır.',
        'file' => ':attribute, :max kilobayttan büyük olmamalıdır.',
        'numeric' => ':attribute, :max değerinden büyük olmamalıdır.',
        'string' => ':attribute, :max karakterden fazla olmamalıdır.',
    ],
    'max_digits' => ':attribute, :max basamaktan fazla olmamalıdır.',
    'mimes' => ':attribute şu türde bir dosya olmalıdır: :values.',
    'mimetypes' => ':attribute şu türde bir dosya olmalıdır: :values.',
    'min' => [
        'array' => ':attribute en az :min öğeye sahip olmalıdır.',
        'file' => ':attribute en az :min kilobayt olmalıdır.',
        'numeric' => ':attribute en az :min olmalıdır.',
        'string' => ':attribute en az :min karakter olmalıdır.',
    ],
    'min_digits' => ':attribute en az :min basamağa sahip olmalıdır.',
    'missing' => ':attribute alanı eksik olmalıdır.',
    'missing_if' => ':attribute alanı, :other :value olduğunda eksik olmalıdır.',
    'missing_unless' => ':attribute alanı, :other :value olmadığı sürece eksik olmalıdır.',
    'missing_with' => ':attribute alanı, :values mevcut olduğunda eksik olmalıdır.',
    'missing_with_all' => ':attribute alanı, :values mevcut olduğunda eksik olmalıdır.',
    'multiple_of' => ':attribute, :value değerinin katı olmalıdır.',
    'not_in' => 'Seçilen :attribute geçersiz.',
    'not_regex' => ':attribute formatı geçersiz.',
    'numeric' => ':attribute bir sayı olmalıdır.',
    'password' => [
        'letters' => ':attribute en az bir harf içermelidir.',
        'mixed' => ':attribute en az bir büyük ve bir küçük harf içermelidir.',
        'numbers' => ':attribute en az bir sayı içermelidir.',
        'symbols' => ':attribute en az bir sembol içermelidir.',
        'uncompromised' => 'Verilen :attribute bir veri ihlalinde ortaya çıktı. Lütfen farklı bir :attribute seçin.',
    ],
    'present' => ':attribute alanı mevcut olmalıdır.',
    'present_if' => ':attribute alanı, :other :value olduğunda mevcut olmalıdır.',
    'present_unless' => ':attribute alanı, :other :value olmadığı sürece mevcut olmalıdır.',
    'present_with' => ':attribute alanı, :values mevcut olduğunda mevcut olmalıdır.',
    'present_with_all' => ':attribute alanı, :values mevcut olduğunda mevcut olmalıdır.',
    'prohibited' => ':attribute alanı yasaktır.',
    'prohibited_if' => ':attribute alanı, :other :value olduğunda yasaktır.',
    'prohibited_unless' => ':attribute alanı, :other :values içinde olmadığı sürece yasaktır.',
    'prohibits' => ':attribute alanı, :other alanının mevcut olmasını yasaklar.',
    'regex' => ':attribute formatı geçersiz.',
    'required' => ':attribute alanı gereklidir.',
    'required_array_keys' => ':attribute alanı şunlar için giriş içermelidir: :values.',
    'required_if' => ':attribute alanı, :other :value olduğunda gereklidir.',
    'required_if_accepted' => ':attribute alanı, :other kabul edildiğinde gereklidir.',
    'required_if_declined' => ':attribute alanı, :other reddedildiğinde gereklidir.',
    'required_unless' => ':attribute alanı, :other :values içinde olmadığı sürece gereklidir.',
    'required_with' => ':attribute alanı, :values mevcut olduğunda gereklidir.',
    'required_with_all' => ':attribute alanı, :values mevcut olduğunda gereklidir.',
    'required_without' => ':attribute alanı, :values mevcut olmadığında gereklidir.',
    'required_without_all' => ':attribute alanı, :values değerlerinden hiçbiri mevcut olmadığında gereklidir.',
    'same' => ':attribute ile :other eşleşmelidir.',
    'size' => [
        'array' => ':attribute, :size öğe içermelidir.',
        'file' => ':attribute, :size kilobayt olmalıdır.',
        'numeric' => ':attribute, :size olmalıdır.',
        'string' => ':attribute, :size karakter olmalıdır.',
    ],
    'starts_with' => ':attribute şunlardan biriyle başlamalıdır: :values.',
    'string' => ':attribute bir metin olmalıdır.',
    'timezone' => ':attribute geçerli bir saat dilimi olmalıdır.',
    'ulid' => ':attribute geçerli bir ULID olmalıdır.',
    'unique' => ':attribute zaten alınmış.',
    'uploaded' => ':attribute yüklenemedi.',
    'uppercase' => ':attribute büyük harf olmalıdır.',
    'url' => ':attribute geçerli bir URL olmalıdır.',
    'uuid' => ':attribute geçerli bir UUID olmalıdır.',

    /*
    |--------------------------------------------------------------------------
    | Özel Doğrulama Dil Satırları
    |--------------------------------------------------------------------------
    |
    | Burada, öznitelikler için özel doğrulama mesajları belirtebilirsiniz.
    | "attribute.rule" şeklinde bir isimlendirme kuralı kullanarak mesajları
    | hızlı bir şekilde belirli bir öznitelik kuralı için özelleştirebilirsiniz.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'özel mesaj',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Özel Doğrulama Öznitelikleri
    |--------------------------------------------------------------------------
    |
    | Aşağıdaki dil satırları, öznitelik yer tutucusunu "e-posta adresi"
    | yerine "E-Posta" gibi daha kolay okunabilir bir şeyle değiştirmek için
    | kullanılır. Bu, mesajlarımızı daha anlaşılır hale getirmeye yardımcı olur.
    |
    */

    'attributes' => [
        'id' => 'kimlik',
        'title' => 'başlık',
        'slug' => 'slug',
        'content' => 'içerik',
        'price' => 'fiyat',
        'old_price' => 'eski fiyat',
        'discount_percentage' => 'indirim yüzdesi',
        'quantity' => 'miktar',
        'in_stock' => 'stokta',
        'image_cover' => 'kapak resmi',
        'image_thumbnail' => 'küçük resim',
        'container_type' => 'konteyner tipi',
        'container_size' => 'konteyner boyutu',
        'production_year' => 'üretim yılı',
        'condition' => 'durum',
        'location_city' => 'şehir',
        'location_district' => 'ilçe',
        'location_country' => 'ülke',
        'type' => 'tip',
        'is_new' => 'yeni mi',
        'is_hot_sale' => 'sıcak satış mı',
        'is_featured' => 'öne çıkarılan mı',
        'is_bulk_sale' => 'toptan satış mı',
        'accept_offers' => 'teklif kabul ediyor mu',
        'status' => 'durum',
        'colors' => 'renkler',
        'all_prices' => 'tüm fiyatlar',
        'technical_specs' => 'teknik özellikler',
        'user_info' => 'kullanıcı bilgisi',
    ],

];

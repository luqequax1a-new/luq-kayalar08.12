<?php

return [
    'product' => 'Ürün',
    'products' => 'Ürünler',
    'save' => 'Kaydet',
    'save_and_exit' => 'Kaydet ve Çık',
    'save_and_edit' => 'Kaydet ve Düzenle',

    'section' => [
        'expand_all' => 'Tümünü Genişlet',
        'collapse_all' => 'Tümünü Daralt',
        'order_saved' => 'Sıralama kaydedildi',
    ],

    'table' => [
        'thumbnail' => 'Küçük Görsel',
        'name' => 'Ad',
        'brand' => 'Marka',
        'price' => 'Fiyat',
        'stock' => 'Stok',
    ],

    'group' => [
        'general' => 'Genel',
        'attributes' => 'Öznitelikler',
        'downloads' => 'İndirilebilir Dosyalar',
        'variations' => 'Varyasyonlar',
        'variants' => 'Varyantlar',
        'options' => 'Seçenekler',
        'pricing' => 'Fiyatlandırma',
        'inventory' => 'Stok',
        'media' => 'Medya',
        'linked_products' => 'Bağlı Ürünler',
        'seo' => 'SEO',
        'additional' => 'Ek Bilgiler',
    ],

    'attributes' => [
        'attribute' => 'Öznitelik',
        'values' => 'Değerler',
        'add_attribute' => 'Öznitelik Ekle',
    ],

    'downloads' => [
        'file' => 'Dosya',
        'choose' => 'Seç',
        'add_file' => 'Dosya Ekle',
    ],

    'variations' => [
        'new_variation' => 'Yeni Varyasyon',
        'add_variation' => 'Varyasyon Ekle',
        'insert' => 'Ekle',
        'add_row' => 'Satır Ekle',
        'please_add_some_variations' => 'Varyant oluşturmak için lütfen bazı varyasyonlar ekleyin',
    ],

    'variants' => [
        'variants' => 'Varyantlar',
        'variant' => 'Varyant',
        'default' => 'Varsayılan',
        'inactive' => 'Pasif',
        'out_of_stock' => 'Stokta Yok',
        'apply' => 'Uygula',
        'has_product_variant' => 'Tek tek varyantlardan yönetilir',
        'bulk_variants_updated' => 'Varyantlar güncellendi',
        'variants_created' => ':count :suffix oluşturuldu',
        'variants_updated' => ':count :suffix güncellendi',
        'variants_removed' => ':count :suffix kaldırıldı',
        'variants_reordered' => 'Varyantlar yeniden sıralandı',
        'disable_default_variant' => 'Varsayılan varyant devre dışı bırakılamaz',
    ],

    'options' => [
        'new_option' => 'Yeni Seçenek',
        'add_option' => 'Seçenek Ekle',
        'add_row' => 'Satır Ekle',
        'insert' => 'Ekle',
        'option_inserted' => 'Seçenek eklendi',
    ],

    'form' => [
        'the_product_won\'t_be_shipped' => 'Ürün kargolanmayacak',
        'enable_the_product' => 'Ürünü aktifleştir',
        'special_price_types' => [
            'fixed' => 'Sabit',
            'percent' => 'Yüzde',
        ],
        'manage_stock_states' => [
            0 => 'Stok Takibi Yapma',
            1 => 'Stok Takibi Yap',
        ],
        'stock_availability_states' => [
            1 => 'Stokta Var',
            0 => 'Stokta Yok',
        ],

        'variations' => [
            'name' => 'Ad',
            'type' => 'Tür',
            'values' => 'Değerler',
            'label' => 'Etiket',
            'color' => 'Renk',
            'image' => 'Görsel',
            'variation_types' => [
                'please_select' => 'Seçiniz',
                'text' => 'Metin',
                'color' => 'Renk',
                'image' => 'Görsel',
            ],
            'select_template' => 'Şablon Seç',
        ],

        'variants' => [
            'default_variant' => 'Varsayılan Varyant',
            'bulk_edit' => 'Toplu Düzenleme',
            'all_variants' => 'Tüm Varyantlar',
            'field_type' => 'Alan Türü',
            'name' => 'Ad',
            'media' => 'Medya',
            'sku' => 'Stok Kodu',
            'is_active' => 'Durum',
            'enable_the_variants' => 'Varyantları aktifleştir',
            'price' => 'Fiyat',
            'special_price' => 'Özel Fiyat',
            'special_price_type' => 'Özel Fiyat Türü',

            'special_price_types' => [
                'fixed' => 'Sabit',
                'percent' => 'Yüzde',
            ],

            'special_price_start' => 'Özel Fiyat Başlangıcı',
            'special_price_end' => 'Özel Fiyat Bitişi',
            'manage_stock' => 'Stok Yönetimi',

            'manage_stock_states' => [
                0 => 'Stok Takibi Yapma',
                1 => 'Stok Takibi Yap',
            ],

            'qty' => 'Miktar',
            'in_stock' => 'Stok Durumu',

            'stock_availability_states' => [
                0 => 'Stokta Yok',
                1 => 'Stokta Var',
            ],
        ],

        'options' => [
            'name' => 'Ad',
            'type' => 'Tür',
            'is_required' => 'Zorunlu',
            'label' => 'Etiket',
            'price' => 'Fiyat',
            'price_type' => 'Fiyat Türü',

            'option_types' => [
                'please_select' => 'Seçiniz',
                'text' => 'Metin',
                'field' => 'Alan',
                'textarea' => 'Metin Alanı',
                'select' => 'Seç',
                'dropdown' => 'Açılır Menü',
                'checkbox' => 'Onay Kutusu',
                'checkbox_custom' => 'Özel Onay Kutusu',
                'radio' => 'Radyo Düğmesi',
                'radio_custom' => 'Özel Radyo Düğmesi',
                'multiple_select' => 'Çoklu Seçim',
                'date' => 'Tarih',
                'date_time' => 'Tarih & Saat',
                'time' => 'Saat',
            ],

            'price_types' => [
                'fixed' => 'Sabit',
                'percent' => 'Yüzde',
            ],

            'select_template' => 'Şablon Seç',
        ],
    ],
];

<?php

return [
    'admin_title' => 'Sepet Teklifleri',
    'admin_edit_subtitle' => 'Kural #:id',

    'table' => [
        'main_product' => 'Ana Ürün',
        'upsell_product' => 'Teklif Ürünü',
        'discount' => 'İndirim',
        'status' => 'Durum',
        'sort_order' => 'Öncelik',
    ],

    'fields' => [
        'status' => 'Durum',
        'sort_order' => 'Öncelik',
        'min_cart_total' => 'Minimum Sepet Tutarı',
        'max_cart_total' => 'Maksimum Sepet Tutarı',
        'internal_name' => 'Kampanya Teklifi Adı (İç Ad)',
        'main_product' => 'Ana Ürün (ID)',
        'upsell_product' => 'Teklif Ürünü (ID)',
        'preselected_variant' => 'Ön Seçili Varyant (ID)',
        'preselected_variant_placeholder' => 'Boş bırakılırsa müşteri varyant seçer',
        'discount_type' => 'İndirim Tipi',
        'discount_value' => 'İndirim Değeri',
        'title' => 'Başlık',
        'subtitle' => 'Açıklama',
        'show_on' => 'Teklif Sayfası',
        'hide_if_already_in_cart' => 'Ürün Sepetteyse Gösterme',
        'has_countdown' => 'Geri Sayım Göster',
        'countdown_minutes' => 'Geri Sayım Süresi (dakika)',
        'starts_at' => 'Başlangıç Tarihi',
        'ends_at' => 'Bitiş Tarihi',
    ],

    'discount_types' => [
        'none' => 'İndirim Yok',
        'percent' => 'Yüzdesel İndirim',
        'fixed' => 'Sabit Tutar İndirimi',
    ],

    'show_on' => [
        'checkout' => 'Ödeme (Sepet / Ödeme Sayfası)',
        'post_checkout' => 'Ödeme Sonrası (Teşekkür Sayfası)',
        'product' => 'Ürün Sayfası',
    ],
];

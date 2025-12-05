@php
    $isEdit = $rule && $rule->exists;
    $locale = locale();
    $currentTitle = old('title', $rule->title[$locale] ?? null);
    $currentSubtitle = old('subtitle', $rule->subtitle[$locale] ?? null);
    $currentDiscountType = old('discount_type', $rule->discount_type ?? 'none');
    $currentShowOn = old('show_on', $rule->show_on ?? 'checkout');
    $currentHideIfAlreadyInCart = old('hide_if_already_in_cart', $rule->hide_if_already_in_cart ?? 1);
    $currentHasCountdown = old('has_countdown', $rule->has_countdown ?? 0);
@endphp

<div class="upsell-layout">
    <div class="upsell-layout__inner">
    {{-- ANA FORM: TEK SÜTUN TAM GENİŞLİK --}}
    <div class="upsell-layout__main">
        {{-- GENEL BİLGİ --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Kampanya Teklifi Raporu</h3>
            </div>

            <div class="box-body">
                <div class="row">
                    {{-- DURUM --}}
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="status">{{ trans('cart::upsell.fields.status') }}</label>
                            <select name="status" id="status" class="form-control">
                                <option value="1" {{ old('status', $rule->status ?? 1) ? 'selected' : '' }}>
                                    {{ trans('admin::admin.table.enabled') }}
                                </option>
                                <option value="0" {{ ! old('status', $rule->status ?? 1) ? 'selected' : '' }}>
                                    {{ trans('admin::admin.table.disabled') }}
                                </option>
                            </select>
                        </div>
                    </div>

                    {{-- ÖNCELİK --}}
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="sort_order">{{ trans('cart::upsell.fields.sort_order') }}</label>
                            <input type="number"
                                   name="sort_order"
                                   id="sort_order"
                                   class="form-control"
                                   value="{{ old('sort_order', $rule->sort_order ?? 0) }}">
                        </div>
                    </div>

                    {{-- İÇ AD --}}
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="internal_name">{{ trans('cart::upsell.fields.internal_name') }}</label>
                            <input type="text"
                                   name="internal_name"
                                   id="internal_name"
                                   class="form-control"
                                   value="{{ old('internal_name', $rule->internal_name ?? null) }}"
                                   placeholder="Sadece panelde görünür (raporlama için iç ad)">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="min_cart_total">{{ trans('cart::upsell.fields.min_cart_total') }}</label>
                            <input type="number"
                                   step="0.01"
                                   name="min_cart_total"
                                   id="min_cart_total"
                                   class="form-control"
                                   placeholder="Boş: sınır yok"
                                   value="{{ old('min_cart_total', $rule->min_cart_total ?? null) }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="max_cart_total">{{ trans('cart::upsell.fields.max_cart_total') }}</label>
                            <input type="number"
                                   step="0.01"
                                   name="max_cart_total"
                                   id="max_cart_total"
                                   class="form-control"
                                   placeholder="Boş: sınır yok"
                                   value="{{ old('max_cart_total', $rule->max_cart_total ?? null) }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="hide_if_already_in_cart">{{ trans('cart::upsell.fields.hide_if_already_in_cart') }}</label>
                            <select name="hide_if_already_in_cart" id="hide_if_already_in_cart" class="form-control">
                                <option value="1" {{ $currentHideIfAlreadyInCart ? 'selected' : '' }}>{{ trans('admin::admin.table.yes') }}</option>
                                <option value="0" {{ ! $currentHideIfAlreadyInCart ? 'selected' : '' }}>{{ trans('admin::admin.table.no') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TETİKLEYİCİ --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Teklif Sayfası ve Uygulama</h3>
            </div>

            <div class="box-body">
                <div class="row">
                    {{-- UYGULA: TÜM ÜRÜNLER / BELİRLİ ÜRÜNLER --}}
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Uygula</label>
                            @php $currentTrigger = old('trigger_type', $rule->trigger_type ?? 'product_to_product'); @endphp
                            <div class="btn-group upsell-toggle-group" data-toggle="buttons" id="trigger-type-toggle">
                                <label class="btn upsell-toggle {{ $currentTrigger === 'all_products' ? 'active' : '' }}">
                                    <input type="radio" name="trigger_type" value="all_products" autocomplete="off" {{ $currentTrigger === 'all_products' ? 'checked' : '' }}>
                                    Tüm Ürünler
                                </label>
                                <label class="btn upsell-toggle {{ $currentTrigger === 'product_to_product' ? 'active' : '' }}">
                                    <input type="radio" name="trigger_type" value="product_to_product" autocomplete="off" {{ $currentTrigger === 'product_to_product' ? 'checked' : '' }}>
                                    Belirli Ürün
                                </label>
                            </div>
                            <p class="help-block">
                                "Tüm Ürünler": Sepette hangi ürün olursa olsun teklif gözüksün.
                                "Belirli Ürün": Sadece seçilen ana ürün sepet­te iken gözüksün.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="show_on">{{ trans('cart::upsell.fields.show_on') }}</label>
                            <select name="show_on" id="show_on" class="form-control">
                                <option value="checkout" {{ $currentShowOn === 'checkout' ? 'selected' : '' }}>{{ trans('cart::upsell.show_on.checkout') }}</option>
                                <option value="post_checkout" {{ $currentShowOn === 'post_checkout' ? 'selected' : '' }}>{{ trans('cart::upsell.show_on.post_checkout') }}</option>
                                <option value="product" {{ $currentShowOn === 'product' ? 'selected' : '' }}>{{ trans('cart::upsell.show_on.product') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="box box-default" style="margin-bottom:15px;">
                    <div class="box-header with-border" style="padding:8px 10px;">
                        <h4 class="box-title" style="font-size:13px; margin:0;">Tetikleyici Ürün</h4>
                    </div>
                    <div class="box-body" style="padding:10px 10px 5px;">
                        <div class="row" id="main-product-row" @if($currentTrigger === 'all_products') style="display:none;" @endif>
                            {{-- ANA ÜRÜN --}}
                            <div class="col-md-12">
                                <div class="form-group">
                            <label for="main_product_search">{{ trans('cart::upsell.fields.main_product') }}</label>
                            <small class="text-muted" style="display:block; margin-bottom:4px;">
                                Müşteri sepetinde bu ürün olduğunda kampanya tetiklenir.
                            </small>

                            <input type="hidden"
                                   name="main_product_id"
                                   id="main_product_id"
                                   value="{{ old('main_product_id', $rule->main_product_id ?? null) }}">

                                <div class="input-group">
                                    <input type="text"
                                           id="main_product_search"
                                           class="form-control"
                                           placeholder="Ürün ara..."
                                           autocomplete="off">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" onclick="document.getElementById('main_product_search').focus();">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </span>
                                </div>

                                <ul class="list-unstyled" id="main_product_results" style="margin-top:5px; max-height:200px; overflow:auto; display:none;"></ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TEKLİF ÜRÜNÜ - HER ZAMAN GÖRÜNÜR --}}
                <div class="box box-default" style="margin-bottom:15px;">
                    <div class="box-header with-border" style="padding:8px 10px;">
                        <h4 class="box-title" style="font-size:13px; margin:0;">İndirimli Sunulacak Ürün</h4>
                    </div>
                    <div class="box-body" style="padding:10px 10px 5px;">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="upsell_product_search">Kampanya Ürünü Seçin</label>

                                    <input type="hidden"
                                           name="upsell_product_id"
                                           id="upsell_product_id"
                                           value="{{ old('upsell_product_id', $rule->upsell_product_id ?? null) }}">

                                    <div class="input-group">
                                        <input type="text"
                                               id="upsell_product_search"
                                               class="form-control"
                                               placeholder="Teklif ürünü ara..."
                                               autocomplete="off">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default" onclick="document.getElementById('upsell_product_search').focus();">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </span>
                                    </div>

                                    <ul class="list-unstyled" id="upsell_product_results" style="margin-top:5px; max-height:200px; overflow:auto; display:none;"></ul>

                                    <small class="text-muted">
                                        İndirimli sunulacak ürün.
                                    </small>

                                    <p class="help-block" id="preselected_variant_summary" style="margin-top:4px;">
                                        <strong>Seçilen varyant:</strong>
                                        @if($rule->preselectedVariant)
                                            {{ $rule->preselectedVariant->name ?? ('#' . $rule->preselectedVariant->id) }}
                                        @else
                                            Yok (rastgele)
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TEKLİF İÇERİĞİ --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Teklif Kartı İçeriği</h3>
            </div>

            <div class="box-body">
                <input type="hidden"
                       name="preselected_variant_id"
                       id="preselected_variant_id"
                       value="{{ old('preselected_variant_id', $rule->preselected_variant_id ?? null) }}">

                <div class="row">
                    {{-- İNDİRİM TİPİ --}}
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="discount_type">{{ trans('cart::upsell.fields.discount_type') }}</label>
                            <select name="discount_type"
                                    id="discount_type"
                                    class="form-control"
                                    onchange="(function(el){var wrap=document.getElementById('discount-value-wrapper'); if(wrap){wrap.style.display = (el.value === 'none') ? 'none' : 'block';}})(this)">
                                <option value="none" {{ $currentDiscountType === 'none' ? 'selected' : '' }}>{{ trans('cart::upsell.discount_types.none') }}</option>
                                <option value="percent" {{ $currentDiscountType === 'percent' ? 'selected' : '' }}>{{ trans('cart::upsell.discount_types.percent') }}</option>
                                <option value="fixed" {{ $currentDiscountType === 'fixed' ? 'selected' : '' }}>{{ trans('cart::upsell.discount_types.fixed') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- İNDİRİM DEĞERİ --}}
                    <div class="col-md-12" id="discount-value-wrapper" @if($currentDiscountType === 'none') style="display:none;" @endif>
                        <div class="form-group">
                            <label for="discount_value">{{ trans('cart::upsell.fields.discount_value') }}</label>
                            @php
                                $rawDiscountValue = old('discount_value', $rule->discount_value ?? null);
                                if ($rawDiscountValue !== null && $rawDiscountValue !== '') {
                                    // Fazla sıfırları silerek sadece gerekli basamakları göster
                                    $formattedDiscountValue = rtrim(rtrim(number_format((float) $rawDiscountValue, 4, '.', ''), '0'), '.');
                                } else {
                                    $formattedDiscountValue = null;
                                }
                            @endphp
                            <input type="number"
                                   step="0.01"
                                   name="discount_value"
                                   id="discount_value"
                                   class="form-control"
                                   value="{{ $formattedDiscountValue }}">
                        </div>
                    </div>
                </div>

                {{-- AÇIKLAMA --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="subtitle">{{ trans('cart::upsell.fields.subtitle') }}</label>
                            <input type="text"
                                   name="subtitle"
                                   id="subtitle"
                                   class="form-control"
                                   value="{{ $currentSubtitle }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Aktif Tarihler ve Geri Sayım</h3>
            </div>

            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="starts_at">{{ trans('cart::upsell.fields.starts_at') }}</label>
                            <input type="text"
                                   name="starts_at"
                                   id="starts_at"
                                   class="form-control datetime-picker"
                                   data-time
                                   placeholder="YYYY-MM-DD HH:MM"
                                   value="{{ old('starts_at', optional($rule->starts_at)->format('Y-m-d H:i')) }}">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="ends_at">{{ trans('cart::upsell.fields.ends_at') }}</label>
                            <input type="text"
                                   name="ends_at"
                                   id="ends_at"
                                   class="form-control datetime-picker"
                                   data-time
                                   placeholder="YYYY-MM-DD HH:MM"
                                   value="{{ old('ends_at', optional($rule->ends_at)->format('Y-m-d H:i')) }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="has_countdown">{{ trans('cart::upsell.fields.has_countdown') }}</label>
                            <select name="has_countdown" id="has_countdown" class="form-control" onchange="(function(el){var wrap=document.getElementById('countdown-minutes-wrapper'); if(wrap){wrap.style.display = (el.value == '1') ? 'block' : 'none';}})(this)">
                                <option value="0" {{ ! $currentHasCountdown ? 'selected' : '' }}>{{ trans('admin::admin.table.no') }}</option>
                                <option value="1" {{ $currentHasCountdown ? 'selected' : '' }}>{{ trans('admin::admin.table.yes') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12" id="countdown-minutes-wrapper" @if(! $currentHasCountdown) style="display:none;" @endif>
                        <div class="form-group">
                            <label for="countdown_minutes">{{ trans('cart::upsell.fields.countdown_minutes') }}</label>
                            <input type="number"
                                   name="countdown_minutes"
                                   id="countdown_minutes"
                                   class="form-control"
                                   min="1"
                                   value="{{ old('countdown_minutes', $rule->countdown_minutes ?? null) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-default" style="margin-top: 15px;">
            <div class="box-header with-border">
                <h3 class="box-title">Teklif Kartı Canlı Önizleme</h3>
            </div>

            <div class="box-body">
                <div class="upsell-preview-card">
                    <div class="upsell-preview-card__header">
                        <span class="upsell-preview-card__badge">Önizleme</span>
                    </div>

                    <div class="upsell-preview-card__body">
                        <div class="upsell-preview-card__content">
                            <p class="upsell-preview-card__title">{{ trans('storefront::upsell.default_title') }}</p>
                            <p class="upsell-preview-card__subtitle">{{ $currentSubtitle ?: trans('storefront::upsell.default_subtitle') }}</p>

                            <p class="upsell-preview-card__meta-label">{{ trans('cart::upsell.fields.discount_type') }}</p>
                            <p class="upsell-preview-card__meta-value">
                                {{ trans('cart::upsell.discount_types.' . $currentDiscountType) }}
                            </p>

                            @php
                                $previewDiscountValue = old('discount_value', $rule->discount_value ?? null);
                                $previewDiscountText = trans('cart::upsell.discount_types.none');

                                if ($currentDiscountType === 'percent' && $previewDiscountValue !== null && $previewDiscountValue !== '') {
                                    $val = (float) $previewDiscountValue;
                                    $previewDiscountText = sprintf('%%%d indirim', (int) round($val));
                                } elseif ($currentDiscountType === 'fixed' && $previewDiscountValue !== null && $previewDiscountValue !== '') {
                                    $val = (float) $previewDiscountValue;
                                    // 2 ondalığa kadar göster, sondaki gereksiz 0 ve ayraçları temizle
                                    $formatted = number_format($val, 2, ',', '');
                                    $formatted = rtrim(rtrim($formatted, '0'), ',');
                                    $previewDiscountText = sprintf('%s indirim', $formatted);
                                }
                            @endphp

                            <p class="upsell-preview-card__discount-info" style="margin:4px 0 0; font-size:12px; color:#4b5563;">
                                {{ $previewDiscountText }}
                            </p>

                            @php
                                $previewUpsellProduct = $rule->relationLoaded('upsellProduct') ? $rule->upsellProduct : $rule->upsellProduct;
                                $previewUpsellImage = null;

                                if ($previewUpsellProduct && $previewUpsellProduct->base_image) {
                                    // base_image, storefront tarafında da kullanılan accessor: nesne ya da dizi olabilir
                                    $baseImage = $previewUpsellProduct->base_image;
                                    if (is_array($baseImage)) {
                                        $previewUpsellImage = $baseImage['path'] ?? $baseImage['thumb'] ?? null;
                                    } else {
                                        $previewUpsellImage = $baseImage->path ?? $baseImage->thumb ?? null;
                                    }
                                }
                            @endphp

                            <div class="upsell-preview-card__product" style="margin-top:10px; display:flex; gap:12px; align-items:center;">
                                <div class="upsell-preview-card__image" style="width:48px; height:48px; border-radius:8px; background:#e5e7eb; overflow:hidden;">
                                    @if($previewUpsellImage)
                                        <img src="{{ $previewUpsellImage }}" alt="{{ $previewUpsellProduct->name ?? 'Upsell ürün' }}" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">
                                    @endif
                                </div>
                                <div>
                                    <p class="upsell-preview-card__product-name" style="margin:0; font-weight:500;">
                                        {{ $previewUpsellProduct->name ?? 'Örnek Upsell Ürün Adı' }}
                                    </p>
                                    <p class="upsell-preview-card__prices" style="margin:2px 0 0; font-size:13px;">
                                        <span class="old-price" style="text-decoration:line-through; color:#9ca3af; margin-right:6px;">3.500,00</span>
                                        <span class="new-price" style="font-weight:600; color:#111827;">2.625,00</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ALT: KAYDET BUTONU --}}
        <div class="box box-default" style="margin-top: 15px;">
            <div class="box-body text-right">
                <button type="submit" class="btn btn-primary upsell-submit-btn">
                    Kaydet
                </button>
            </div>
        </div>
    </div>
    </div>
</div>

{{-- VARYANT SEÇİM MODALI --}}
<div class="modal fade" id="upsellVariantModal" tabindex="-1" role="dialog" aria-labelledby="upsellVariantModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('admin::admin.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="upsellVariantModalLabel">
                    {{ trans('cart::upsell.fields.preselected_variant') }}
                </h4>
            </div>
            <div class="modal-body">
                <p class="text-muted" id="upsellVariantHelp" style="margin-bottom: 8px;">
                    {{ trans('cart::upsell.fields.upsell_product') }} için müşteriye varsayılan gösterilecek varyantı seçin.
                </p>
                <div id="upsellVariantLoading" style="display:none; margin-bottom:8px;">
                    <span class="text-muted">Varyantlar yükleniyor...</span>
                </div>
                <div id="upsellVariantEmpty" style="display:none; margin-bottom:8px;">
                    <span class="text-muted">Bu ürün için tanımlı varyant bulunamadı.</span>
                </div>
                <ul class="list-group" id="upsellVariantList"></ul>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .upsell-layout {
            padding: 20px 0;
        }

        .upsell-layout__inner {
            width: 100%;
        }

        .upsell-layout__main > .box,
        .upsell-layout__sidebar > .box {
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
            border: 1px solid #e5e7eb;
        }

        .upsell-layout__main .box + .box {
            margin-top: 16px;
        }

        .upsell-layout__main .form-group > label {
            margin-bottom: 6px;
            display: block;
        }

        .upsell-toggle-group {
            border-radius: 9999px;
            background: #f3f4f6;
            padding: 2px;
        }

        .upsell-toggle {
            border-radius: 9999px !important;
            border: none;
            padding: 6px 16px;
            font-weight: 500;
            background: transparent;
            color: #4b5563;
        }

        .upsell-toggle.active {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
        }

        .upsell-toggle input[type="radio"] {
            display: none;
        }

        .upsell-submit-btn {
            min-width: 180px;
            border-radius: 9999px;
            font-weight: 500;
        }

        .upsell-preview-card {
            background: #f9fafb;
            border-radius: 8px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .upsell-preview-card__badge {
            display: inline-block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b7280;
        }

        .upsell-preview-card__title {
            font-weight: 600;
            margin: 0 0 4px;
        }

        .upsell-preview-card__subtitle {
            margin: 0 0 10px;
            font-size: 13px;
            color: #6b7280;
        }

        .upsell-preview-card__meta-label {
            margin: 0;
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
        }

        .upsell-preview-card__meta-value {
            margin: 0;
            font-size: 13px;
            color: #374151;
        }

        .upsell-preview-card__body {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-end;
        }

        .upsell-preview-card__actions {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: flex-end;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            var container = document.getElementById('trigger-type-toggle');
            if (!container) return;

            function updateMainProductRow(value) {
                var row = document.getElementById('main-product-row');
                if (!row) return;
                if (value === 'all_products') {
                    row.style.display = 'none';
                } else {
                    row.style.display = '';
                }
            }

            // İlk yüklemede mevcut değere göre satırı ayarla
            var initial = container.querySelector('input[name="trigger_type"]:checked');
            if (initial) {
                updateMainProductRow(initial.value);
            }

            // Toggle ana ürün satırını "Tüm Ürünler" / "Belirli Ürün" seçimine göre göster/gizle
            container.addEventListener('click', function (e) {
                var label = e.target.closest('label');
                if (!label || !container.contains(label)) return;

                var input = label.querySelector('input[name="trigger_type"]');
                if (!input) return;

                // Radio seçili olacak, buna göre satırı güncelle
                updateMainProductRow(input.value);
            });
        })();

        (function () {
            function bindProductSearch(searchInputId, resultsListId, hiddenIdField, options) {
                var input = document.getElementById(searchInputId);
                var list = document.getElementById(resultsListId);
                var hidden = document.getElementById(hiddenIdField);
                if (!input || !list || !hidden) return;

                options = options || {};

                var timer = null;
                input.addEventListener('input', function () {
                    var q = input.value.trim();
                    if (timer) clearTimeout(timer);
                    if (!q) {
                        list.style.display = 'none';
                        list.innerHTML = '';
                        return;
                    }
                    timer = setTimeout(function () {
                        var url = '{{ route('admin.products.index') }}?query=' + encodeURIComponent(q) + '&limit=10';
                        fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).then(function (res) { return res.json(); }).then(function (data) {
                            list.innerHTML = '';
                            if (!Array.isArray(data) || !data.length) {
                                list.style.display = 'none';
                                return;
                            }
                            data.forEach(function (item) {
                                var li = document.createElement('li');
                                li.style.padding = '6px 10px';
                                li.style.cursor = 'pointer';
                                li.style.display = 'flex';
                                li.style.alignItems = 'center';
                                li.style.gap = '8px';
                                li.style.borderRadius = '6px';

                                li.addEventListener('mouseenter', function () {
                                    li.style.backgroundColor = '#f3f4f6';
                                });
                                li.addEventListener('mouseleave', function () {
                                    li.style.backgroundColor = 'transparent';
                                });

                                var thumbUrl = null;
                                if (item.base_image && item.base_image.path) {
                                    thumbUrl = item.base_image.path;
                                } else if (item.image && item.image.path) {
                                    thumbUrl = item.image.path;
                                }

                                if (thumbUrl) {
                                    var img = document.createElement('img');
                                    img.src = thumbUrl;
                                    img.alt = item.name || '';
                                    img.style.width = '32px';
                                    img.style.height = '32px';
                                    img.style.objectFit = 'cover';
                                    img.style.borderRadius = '4px';
                                    li.appendChild(img);
                                }

                                var text = document.createElement('span');
                                text.textContent = (item.name || ('#' + item.id));
                                li.appendChild(text);
                                li.addEventListener('click', function () {
                                    hidden.value = item.id;
                                    input.value = item.name || ('#' + item.id);
                                    list.style.display = 'none';
                                    list.innerHTML = '';

                                    if (options.onSelect) {
                                        options.onSelect(item);
                                    }
                                });
                                list.appendChild(li);
                            });
                            list.style.display = 'block';
                        }).catch(function () {
                            list.style.display = 'none';
                            list.innerHTML = '';
                        });
                    }, 250);
                });
            }

            bindProductSearch('main_product_search', 'main_product_results', 'main_product_id');

            bindProductSearch('upsell_product_search', 'upsell_product_results', 'upsell_product_id', {
                onSelect: function (product) {
                    // Varyant seçimi için modal akışı
                    try {
                        var modal = document.getElementById('upsellVariantModal');
                        var list = document.getElementById('upsellVariantList');
                        var loadingEl = document.getElementById('upsellVariantLoading');
                        var emptyEl = document.getElementById('upsellVariantEmpty');
                        var variantInput = document.getElementById('preselected_variant_id');
                        var variantSummary = document.getElementById('preselected_variant_summary');
                        if (!modal || !list || !variantInput) return;

                        // Yeni ürün seçildiğinde varsayılan: varyant yok (rastgele)
                        variantInput.value = '';
                        if (variantSummary) {
                            variantSummary.innerHTML = '<strong>Seçilen varyant:</strong> Yok (rastgele)';
                        }

                        if (!product || !Array.isArray(product.variants) || product.variants.length === 0) {
                            // Varyant yoksa modal açma, direkt ürün seviyesi kullan
                            return;
                        }

                        list.innerHTML = '';
                        if (loadingEl) loadingEl.style.display = 'block';
                        if (emptyEl) emptyEl.style.display = 'none';

                        if (!product.variants.length) {
                            if (loadingEl) loadingEl.style.display = 'none';
                            if (emptyEl) emptyEl.style.display = 'block';
                        } else {
                            // Ürün görseli (fallback) ve varyant bazlı görseller
                            var productImageUrl = null;
                            if (product.base_image && product.base_image.path) {
                                productImageUrl = product.base_image.path;
                            } else if (product.image && product.image.path) {
                                productImageUrl = product.image.path;
                            }

                            product.variants.forEach(function (variant) {
                                var li = document.createElement('li');
                                li.className = 'list-group-item';
                                li.style.cursor = 'pointer';
                                li.style.display = 'flex';
                                li.style.alignItems = 'center';
                                li.style.gap = '8px';

                                var variantImageUrl = null;
                                if (variant.base_image && (variant.base_image.path || variant.base_image.thumb)) {
                                    variantImageUrl = variant.base_image.path || variant.base_image.thumb;
                                } else {
                                    variantImageUrl = productImageUrl;
                                }

                                if (variantImageUrl) {
                                    var vImg = document.createElement('img');
                                    vImg.src = variantImageUrl;
                                    vImg.alt = variant.name || '';
                                    vImg.style.width = '32px';
                                    vImg.style.height = '32px';
                                    vImg.style.objectFit = 'cover';
                                    vImg.style.borderRadius = '4px';
                                    li.appendChild(vImg);
                                }

                                var primary = variant.name || '';
                                var secondaryParts = [];
                                if (variant.sku) secondaryParts.push('SKU: ' + variant.sku);
                                if (typeof variant.stock !== 'undefined') secondaryParts.push('Stok: ' + variant.stock);

                                var label = primary || ('Varyant #' + variant.id);
                                if (secondaryParts.length) {
                                    label += ' (' + secondaryParts.join(' · ') + ')';
                                }

                                var textSpan = document.createElement('span');
                                textSpan.textContent = label;
                                li.appendChild(textSpan);

                                li.addEventListener('click', function () {
                                    variantInput.value = variant.id;
                                    if (variantSummary) {
                                        var summaryImageUrl = variantImageUrl || productImageUrl;
                                        if (summaryImageUrl) {
                                            variantSummary.innerHTML = '<strong>Seçilen varyant:</strong> ' +
                                                '<span style="display:inline-flex;align-items:center;gap:6px;margin-left:4px;">' +
                                                '<img src="' + summaryImageUrl + '" alt="" style="width:20px;height:20px;object-fit:cover;border-radius:4px;">' +
                                                '<span>' + label + '</span>' +
                                                '</span>';
                                        } else {
                                            variantSummary.innerHTML = '<strong>Seçilen varyant:</strong> ' + label;
                                        }
                                    }
                                    if (window.jQuery && jQuery(modal).modal) {
                                        jQuery(modal).modal('hide');
                                    } else {
                                        modal.style.display = 'none';
                                    }
                                });
                                list.appendChild(li);
                            });

                            if (loadingEl) loadingEl.style.display = 'none';
                        }

                        if (window.jQuery && jQuery(modal).modal) {
                            jQuery(modal).modal('show');
                        } else {
                            modal.style.display = 'block';
                        }
                    } catch (e) {
                        try { console.error('Upsell variant modal error', e); } catch (_) {}
                    }
                }
            });
        })();

        // ÖNİZLEME KARTINI CANLI GÜNCELLE (Açıklama + İndirim Tipi)
        (function () {
            var subtitleInput = document.getElementById('subtitle');
            var discountTypeSelect = document.getElementById('discount_type');

            var subtitleEl = document.querySelector('.upsell-preview-card__subtitle');
            var discountTypeEl = document.querySelector('.upsell-preview-card__meta-value');

            if (!subtitleEl || !discountTypeEl) return;

            var defaultSubtitle = subtitleEl.textContent;

            function updateSubtitle() {
                if (!subtitleInput) return;
                var v = subtitleInput.value.trim();
                subtitleEl.textContent = v || defaultSubtitle;
            }

            function updateDiscountType() {
                if (!discountTypeSelect) return;
                var selected = discountTypeSelect.options[discountTypeSelect.selectedIndex];
                if (selected) {
                    discountTypeEl.textContent = selected.textContent;
                }
            }

            if (subtitleInput) {
                subtitleInput.addEventListener('input', updateSubtitle);
            }
            if (discountTypeSelect) {
                discountTypeSelect.addEventListener('change', updateDiscountType);
            }
        })();
    </script>
@endpush

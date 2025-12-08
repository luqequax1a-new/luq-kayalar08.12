@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('product_feeds::messages.title'))

    <li class="active">{{ trans('product_feeds::messages.title') }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.product_feeds.settings.update') }}" class="form-horizontal">
        @csrf

        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12 text-right">
                <button type="submit" class="btn btn-primary" data-loading>
                    {{ trans('admin::admin.buttons.save') }}
                </button>
            </div>
        </div>

        <div class="accordion-content">
            <div class="accordion-box-content clearfix">
                <div class="col-md-12">
                    <div class="accordion-box-content">
                        <div class="tab-content clearfix">
                            <div class="tab-pane fade in active">
                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.global') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.enable_all') }}</label>
                                    <div class="col-md-5">
                                        <input type="hidden" name="global[enabled]" value="0">
                                        <input type="checkbox" name="global[enabled]" value="1" {{ $settings['global']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.default_brand_name') }}</label>
                                    <div class="col-md-5">
                                        <input type="text" name="global[brand_name]" class="form-control" value="{{ $settings['global']['brand_name'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.default_country') }}</label>
                                    <div class="col-md-5">
                                        <input type="text" name="global[country]" class="form-control" value="{{ $settings['global']['country'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.default_currency') }}</label>
                                    <div class="col-md-5">
                                        <input type="text" name="global[currency]" class="form-control" value="{{ $settings['global']['currency'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.include_out_of_stock') }}</label>
                                    <div class="col-md-5">
                                        <input type="hidden" name="global[include_out_of_stock]" value="0">
                                        <input type="checkbox" name="global[include_out_of_stock]" value="1" {{ $settings['global']['include_out_of_stock'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.include_unpublished') }}</label>
                                    <div class="col-md-5">
                                        <input type="hidden" name="global[include_unpublished]" value="0">
                                        <input type="checkbox" name="global[include_unpublished]" value="1" {{ $settings['global']['include_unpublished'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.include_variants') }}</label>
                                    <div class="col-md-5">
                                        <input type="hidden" name="global[include_variants]" value="0">
                                        <input type="checkbox" name="global[include_variants]" value="1" {{ $settings['global']['include_variants'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.feed_locale') }}</label>
                                    <div class="col-md-5">
                                        <input type="text" name="global[locale]" class="form-control" value="{{ $settings['global']['locale'] }}">
                                    </div>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.google') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_enabled') }}</label>
                                    <div class="col-md-5">
                                        <input type="hidden" name="google[enabled]" value="0">
                                        <input type="checkbox" name="google[enabled]" value="1" {{ $settings['google']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_feed_url') }}</label>
                                    <div class="col-md-5">
                                        <p class="form-control-static">{{ url('feeds/google-merchant.xml') }}</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_default_category') }}</label>
                                    <div class="col-md-5">
                                        <select
                                            name="google[category]"
                                            id="product-feeds-google-category"
                                        >
                                            @if($settings['google']['category'])
                                                @php
                                                    $googleCategoryLabel = html_entity_decode($settings['google']['category'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                @endphp
                                                <option value="{{ $settings['google']['category'] }}">
                                                    {{ $googleCategoryLabel }}
                                                </option>
                                            @endif
                                        </select>
                                        <span class="help-block">
                                            {{ __('Google taxonomy path, e.g. Apparel & Accessories > Clothing') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_missing_behavior') }}</label>
                                    <div class="col-md-5">
                                        <select name="google[missing_identifier_behavior]" class="form-control">
                                            <option value="empty" {{ $settings['google']['missing_identifier_behavior'] === 'empty' ? 'selected' : '' }}>Leave empty</option>
                                            <option value="mpn_from_id" {{ $settings['google']['missing_identifier_behavior'] === 'mpn_from_id' ? 'selected' : '' }}>Use product ID as MPN</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_use_store_tax') }}</label>
                                    <div class="col-md-5">
                                        <input type="hidden" name="google[use_store_tax]" value="0">
                                        <input type="checkbox" name="google[use_store_tax]" value="1" {{ $settings['google']['use_store_tax'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.google_shipping_price') }}</label>
                                    <div class="col-md-5">
                                        <input type="text" name="google[shipping_price]" class="form-control" value="{{ $settings['google']['shipping_price'] }}">
                                    </div>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.meta') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Enabled</label>
                                    <div class="col-md-5">
                                        <input type="hidden" name="meta[enabled]" value="0">
                                        <input type="checkbox" name="meta[enabled]" value="1" {{ $settings['meta']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Feed URL</label>
                                    <div class="col-md-5">
                                        <p class="form-control-static">{{ url('feeds/meta-catalog.json') }}</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.meta_categories') }}</label>
                                    <div class="col-md-5">
                                        <input type="text" name="meta[categories]" class="form-control" value="{{ $settings['meta']['categories'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.meta_use_variants') }}</label>
                                    <div class="col-md-5">
                                        <input type="hidden" name="meta[use_variants]" value="0">
                                        <input type="checkbox" name="meta[use_variants]" value="1" {{ $settings['meta']['use_variants'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.tiktok') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Enabled</label>
                                    <div class="col-md-5">
                                        <input type="hidden" name="tiktok[enabled]" value="0">
                                        <input type="checkbox" name="tiktok[enabled]" value="1" {{ $settings['tiktok']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Feed URL</label>
                                    <div class="col-md-5">
                                        <p class="form-control-static">{{ url('feeds/tiktok.json') }}</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.tiktok_shipping_profile') }}</label>
                                    <div class="col-md-5">
                                        <input type="text" name="tiktok[shipping_profile]" class="form-control" value="{{ $settings['tiktok']['shipping_profile'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.tiktok_in_stock_only') }}</label>
                                    <div class="col-md-5">
                                        <input type="hidden" name="tiktok[in_stock_only]" value="0">
                                        <input type="checkbox" name="tiktok[in_stock_only]" value="1" {{ $settings['tiktok']['in_stock_only'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.trendyol') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Enabled</label>
                                    <div class="col-md-5">
                                        <input type="hidden" name="trendyol[enabled]" value="0">
                                        <input type="checkbox" name="trendyol[enabled]" value="1" {{ $settings['trendyol']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Feed URL</label>
                                    <div class="col-md-5">
                                        <p class="form-control-static">{{ url('feeds/trendyol.xml') }}</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.trendyol_supplier_id') }}</label>
                                    <div class="col-md-5">
                                        <input type="text" name="trendyol[supplier_id]" class="form-control" value="{{ $settings['trendyol']['supplier_id'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.trendyol_brand') }}</label>
                                    <div class="col-md-5">
                                        <input type="text" name="trendyol[brand]" class="form-control" value="{{ $settings['trendyol']['brand'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.trendyol_cargo_company') }}</label>
                                    <div class="col-md-5">
                                        <input type="text" name="trendyol[cargo_company]" class="form-control" value="{{ $settings['trendyol']['cargo_company'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.trendyol_vat_rate') }}</label>
                                    <div class="col-md-5">
                                        <input type="text" name="trendyol[vat_rate]" class="form-control" value="{{ $settings['trendyol']['vat_rate'] }}">
                                    </div>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.pinterest') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Enabled</label>
                                    <div class="col-md-5">
                                        <input type="hidden" name="pinterest[enabled]" value="0">
                                        <input type="checkbox" name="pinterest[enabled]" value="1" {{ $settings['pinterest']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Feed URL</label>
                                    <div class="col-md-5">
                                        <p class="form-control-static">{{ url('feeds/pinterest.tsv') }}</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.pinterest_category') }}</label>
                                    <div class="col-md-5">
                                        <input type="text" name="pinterest[category]" class="form-control" value="{{ $settings['pinterest']['category'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.pinterest_format') }}</label>
                                    <div class="col-md-5">
                                        <select name="pinterest[format]" class="form-control">
                                            <option value="tsv" {{ $settings['pinterest']['format'] === 'tsv' ? 'selected' : '' }}>TSV</option>
                                            <option value="csv" {{ $settings['pinterest']['format'] === 'csv' ? 'selected' : '' }}>CSV</option>
                                        </select>
                                    </div>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">{{ trans('product_feeds::messages.sections.cache') }}</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_enabled') }}</label>
                                    <div class="col-md-5">
                                        <input type="hidden" name="cache[enabled]" value="0">
                                        <input type="checkbox" name="cache[enabled]" value="1" {{ $settings['cache']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_google') }}</label>
                                    <div class="col-md-3">
                                        <input type="number" min="0" name="cache[google]" class="form-control" value="{{ $settings['cache']['google'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_meta') }}</label>
                                    <div class="col-md-3">
                                        <input type="number" min="0" name="cache[meta]" class="form-control" value="{{ $settings['cache']['meta'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_tiktok') }}</label>
                                    <div class="col-md-3">
                                        <input type="number" min="0" name="cache[tiktok]" class="form-control" value="{{ $settings['cache']['tiktok'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_trendyol') }}</label>
                                    <div class="col-md-3">
                                        <input type="number" min="0" name="cache[trendyol]" class="form-control" value="{{ $settings['cache']['trendyol'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_pinterest') }}</label>
                                    <div class="col-md-3">
                                        <input type="number" min="0" name="cache[pinterest]" class="form-control" value="{{ $settings['cache']['pinterest'] }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label"></label>
                                    <div class="col-md-7">
                                        <p class="form-control-static text-muted">
                                            {{ trans('product_feeds::messages.fields.cache_info') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_token') }}</label>
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="{{ $settings['cache']['token'] }}" readonly>
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default" data-loading
                                                    onclick="document.getElementById('feed-cache-regenerate-token-form').submit();">
                                                    Regenerate
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_cron_url') }}</label>
                                    <div class="col-md-7">
                                        <p class="form-control-static"><strong>Google:</strong> {{ url('feeds/cron/google') . '?token=' . $settings['cache']['token'] }}</p>
                                        <p class="form-control-static"><strong>Meta:</strong> {{ url('feeds/cron/meta') . '?token=' . $settings['cache']['token'] }}</p>
                                        <p class="form-control-static"><strong>TikTok:</strong> {{ url('feeds/cron/tiktok') . '?token=' . $settings['cache']['token'] }}</p>
                                        <p class="form-control-static"><strong>Trendyol:</strong> {{ url('feeds/cron/trendyol') . '?token=' . $settings['cache']['token'] }}</p>
                                        <p class="form-control-static"><strong>Pinterest:</strong> {{ url('feeds/cron/pinterest') . '?token=' . $settings['cache']['token'] }}</p>
                                        <p class="help-block">
                                            {{ trans('product_feeds::messages.fields.cache_cron_help') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('admin::admin.buttons.save') }}</label>
                                    <div class="col-md-7">
                                        <button type="submit" class="btn btn-primary" data-loading>
                                            {{ trans('admin::admin.buttons.save') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_refresh_google') }}</label>
                                    <div class="col-md-7">
                                        <button type="button" class="btn btn-default btn-sm" data-loading
                                            onclick="document.getElementById('feed-cache-refresh-google-form').submit();">
                                            {{ trans('product_feeds::messages.fields.cache_refresh_google') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_refresh_meta') }}</label>
                                    <div class="col-md-7">
                                        <button type="button" class="btn btn-default btn-sm" data-loading
                                            onclick="document.getElementById('feed-cache-refresh-meta-form').submit();">
                                            {{ trans('product_feeds::messages.fields.cache_refresh_meta') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_refresh_tiktok') }}</label>
                                    <div class="col-md-7">
                                        <button type="button" class="btn btn-default btn-sm" data-loading
                                            onclick="document.getElementById('feed-cache-refresh-tiktok-form').submit();">
                                            {{ trans('product_feeds::messages.fields.cache_refresh_tiktok') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_refresh_trendyol') }}</label>
                                    <div class="col-md-7">
                                        <button type="button" class="btn btn-default btn-sm" data-loading
                                            onclick="document.getElementById('feed-cache-refresh-trendyol-form').submit();">
                                            {{ trans('product_feeds::messages.fields.cache_refresh_trendyol') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">{{ trans('product_feeds::messages.fields.cache_refresh_pinterest') }}</label>
                                    <div class="col-md-7">
                                        <button type="button" class="btn btn-default btn-sm" data-loading
                                            onclick="document.getElementById('feed-cache-refresh-pinterest-form').submit();">
                                            {{ trans('product_feeds::messages.fields.cache_refresh_pinterest') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <form id="feed-cache-regenerate-token-form" method="POST" action="{{ route('admin.product_feeds.cache.regenerate_token') }}" style="display:none;">
        @csrf
    </form>
    <form id="feed-cache-refresh-google-form" method="POST" action="{{ route('admin.product_feeds.cache.refresh', ['channel' => 'google']) }}" style="display:none;">
        @csrf
    </form>
    <form id="feed-cache-refresh-meta-form" method="POST" action="{{ route('admin.product_feeds.cache.refresh', ['channel' => 'meta']) }}" style="display:none;">
        @csrf
    </form>
    <form id="feed-cache-refresh-tiktok-form" method="POST" action="{{ route('admin.product_feeds.cache.refresh', ['channel' => 'tiktok']) }}" style="display:none;">
        @csrf
    </form>
    <form id="feed-cache-refresh-trendyol-form" method="POST" action="{{ route('admin.product_feeds.cache.refresh', ['channel' => 'trendyol']) }}" style="display:none;">
        @csrf
    </form>
    <form id="feed-cache-refresh-pinterest-form" method="POST" action="{{ route('admin.product_feeds.cache.refresh', ['channel' => 'pinterest']) }}" style="display:none;">
        @csrf
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            var el = document.getElementById('product-feeds-google-category');
            if (!el || !window.jQuery || !jQuery.fn.selectize) {
                return;
            }

            var $select = jQuery(el).selectize({
                delimiter: ',',
                persist: true,
                selectOnTab: true,
                allowEmptyOption: true,
                maxItems: 1,
                valueField: 'id',
                labelField: 'text',
                searchField: 'text',
                preload: 'focus',
                openOnFocus: true,
                loadThrottle: 250,
                load: function (query, callback) {
                    var q = (query || '').trim();

                    function decodeHtml(str) {
                        var txt = document.createElement('textarea');
                        txt.innerHTML = str;
                        return txt.value;
                    }

                    jQuery.ajax({
                        url: "{{ url('admin/google-taxonomy') }}",
                        data: q ? { q: q } : {},
                        success: function (resp) {
                            var results = resp && resp.results ? resp.results : [];

                            results = results.map(function (item) {
                                if (item && typeof item.text === 'string') {
                                    item.text = decodeHtml(item.text);
                                }

                                return item;
                            });

                            callback(results);
                        },
                        error: function () {
                            callback([]);
                        },
                    });
                },
            });

            // Ensure current value is shown nicely if present
            var currentVal = '{{ $settings['google']['category'] }}';
            if (currentVal) {
                var selectize = $select[0].selectize;
                var currentLabel = {!! json_encode(html_entity_decode($settings['google']['category'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')) !!};

                selectize.addOption({ id: currentVal, text: currentLabel || currentVal });
                selectize.setValue(currentVal);
            }
        })();
    </script>
@endpush

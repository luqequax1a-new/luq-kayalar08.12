@extends('admin::layout')

@section('title', 'Sepet Oluştur')

@component('admin::components.page.header')
    @slot('title', 'Sepet Oluştur')
    <li class="active">Sepet Oluştur</li>
@endcomponent

@section('content')
    <div class="box box-primary">
        <div class="box-body">
            <form method="POST" action="{{ route('admin.cart_links.store') }}" id="cart-link-form">
                {{ csrf_field() }}

                <div class="form-group">
                    <label>Ürün ara (isim veya SKU)</label>
                    <div class="fc-inline input-group">
                        <select class="selectize prevent-creation" id="cart-link-product-search" data-url="{{ route('admin.cart_links.products.search') }}"></select>
                        <button type="button" class="btn btn-primary fc-btn fc-btn-in" id="add-product-btn">Ekle</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover text-center" id="cart-link-products-table">
                        <thead>
                            <tr>
                                <th class="text-center">Görsel</th>
                                <th class="text-center">Ürün</th>
                                <th class="text-center">SKU</th>
                                <th class="text-center">Birim Fiyat</th>
                                <th class="text-center">Varyant</th>
                                <th class="text-center fc-col-qty">Miktar</th>
                                <th class="text-center fc-col-actions">Aksiyon</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div id="cart-link-preview" class="fc-summary text-right" style="display:none;">
                    <div><span class="text-muted">Ara Toplam</span> <span id="cl_sub" style="min-width:80px;display:inline-block;">0</span></div>
                    <div><span class="text-muted">Kargo</span> <span id="cl_ship" style="min-width:80px;display:inline-block;">0</span></div>
                    <div><span class="text-muted">İndirim</span> <span id="cl_disc" style="min-width:80px;display:inline-block;">0</span></div>
                    <div><span class="text-muted">Vergi</span> <span id="cl_tax" style="min-width:80px;display:inline-block;">0</span></div>
                    <div><strong>Toplam</strong> <span id="cl_total" style="min-width:80px;display:inline-block;"><strong>0</strong></span></div>
                </div>

                <button type="submit" class="btn btn-primary">Sepet Linki Oluştur</button>
            </form>

            @if (session('cart_link_url'))
                <div class="alert alert-success" style="margin-top:10px;display:flex;align-items:center;justify-content:space-between;gap:10px;">
                    <span id="cart-link-url" style="flex:1;word-break:break-all;">{{ session('cart_link_url') }}</span>
                    <button type="button" class="btn btn-default btn-sm" id="copy-cart-link" title="Kopyala"><i class="fa fa-copy"></i></button>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('globals')
    @include('admin::partials.selectize_remote')
    <style>
        .fc-inline { display:flex; gap:10px; align-items:center; width:100%; flex-wrap:wrap; position:relative; }
        .fc-inline .selectize-control { flex:1 1 auto; min-width:0; position:relative; }
        .selectize-control { width:100%; }
        .selectize-control .selectize-input { min-height:40px; padding:8px 12px; border-radius:8px; padding-right:92px; }
        .selectize-dropdown [data-selectable] { min-height:60px; padding:10px 12px; display:flex; align-items:center; }
        .selectize-dropdown img.fc-search-img { width:56px; height:56px; object-fit:contain; margin-right:12px; }
        .selectize-dropdown .fc-search-item { display:flex; align-items:center; }
        .selectize-dropdown .fc-search-text { display:flex; flex-direction:column; gap:4px; width:100%; }
        .selectize-dropdown .fc-search-title { font-weight:600; }
        .selectize-dropdown .fc-search-sub { display:flex; justify-content:space-between; }
        .fc-btn { height:36px; padding:0 16px; border-radius:6px; }
        .fc-btn { white-space:nowrap; }
        .fc-btn-in { position:absolute; right:12px; top:50%; transform:translateY(-50%); z-index:3; }
        .table th, .table td { vertical-align:middle !important; }
        .fc-col-qty { width:120px; }
        .fc-col-actions { width:90px; }
        .fc-prod-img { width:64px; height:64px; object-fit:contain; }
        .fc-variant-select { height:36px; }
        .qty-mini { position:relative; width:120px; margin:0 auto; }
        .fc-qty-input { height:36px; padding:6px 28px 6px 10px; border-radius:8px; }
        .qty-mini .qty-suffix { right:8px; color:#555; font-size:12px; position:absolute; top:50%; transform:translateY(-50%); pointer-events:none; }
        .qty-mini input::-webkit-outer-spin-button,
        .qty-mini input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .qty-mini input[type='number'] { -moz-appearance: textfield; }
        .fc-summary { margin-top:12px; padding:12px; border:1px solid #e4e4e4; border-radius:8px; background:#f9fafb; }
        .selectize-dropdown [data-selectable] mark { background:#ffeb3b; color:#000; padding:0 1px; border-radius:2px; }
        @media (max-width: 768px) {
            .fc-inline { flex-direction:column; align-items:stretch; gap:8px; }
            .fc-btn-in { position:static; transform:none; width:100%; }
            .selectize-control .selectize-input { padding-right:12px; }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.axios && document.querySelector('meta[name="csrf-token"]')) {
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            }
            function httpGet(url, params) {
                if (window.axios) {
                    return axios.get(url, { params: params || {} });
                }
                return new Promise(function(resolve, reject) {
                    $.ajax({ url: url, method: 'GET', data: params || {}, success: resolve, error: reject });
                });
            }
            function httpPost(url, data) {
                if (window.axios) {
                    return axios.post(url, data || {});
                }
                return new Promise(function(resolve, reject) {
                    $.ajax({ url: url, method: 'POST', data: data || {}, success: resolve, error: reject });
                });
            }
            const productSelect = document.getElementById('cart-link-product-search');
            const addProductBtn = document.getElementById('add-product-btn');
            const tableBody = document.querySelector('#cart-link-products-table tbody');
            let items = [];

            const CURRENCY = '{{ currency() }}';
            const CURRENCY_SYMBOL = (function(code){
                switch (String(code || '').toUpperCase()) {
                    case 'TRY': return '₺';
                    case 'USD': return '$';
                    case 'EUR': return '€';
                    case 'GBP': return '£';
                    default: return code || '';
                }
            })(CURRENCY);
            function fmt(a){
                if (a === null || a === '' || typeof a === 'undefined') return '';
                const n = Number(a);
                if (Number.isNaN(n)) return '';
                return CURRENCY_SYMBOL + ' ' + n.toFixed(2);
            }

            const copyBtn = document.getElementById('copy-cart-link');
            if (copyBtn) {
                copyBtn.addEventListener('click', function(){
                    const urlEl = document.getElementById('cart-link-url');
                    const text = urlEl ? (urlEl.textContent || '').trim() : '';
                    if (!text) return;
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text);
                    } else {
                        var ta = document.createElement('textarea');
                        ta.value = text;
                        document.body.appendChild(ta);
                        ta.select();
                        try { document.execCommand('copy'); } finally { document.body.removeChild(ta); }
                    }
                });
            }

            if (addProductBtn) addProductBtn.addEventListener('click', function(){
                const inst = $(productSelect)[0] && $(productSelect)[0].selectize ? $(productSelect)[0].selectize : null;
                const selectedId = $(productSelect).val();
                let p = inst && selectedId && inst.options && inst.options[selectedId] ? inst.options[selectedId] : null;
                if (p) {
                    const existing = items.find(x => String(x.product_id) === String(p.id) && (x.variant_id == null));
                    if (existing) {
                        existing.qty += 1;
                    } else {
                        items.push({
                            product_id: parseInt(p.id, 10),
                            variant_id: null,
                            qty: 1,
                            options: {},
                            sku: p.sku,
                            name: p.name,
                            image: p.image || '',
                            product_sku: p.sku,
                            product_image: p.image || '',
                            product_price: (typeof p.price !== 'undefined') ? p.price : null,
                            variants: Array.isArray(p.variants) ? p.variants : [],
                            unit_step: p.unit_step || null,
                            unit_min: p.unit_min || null,
                            unit_decimal: p.unit_decimal || null,
                            unit_suffix: p.unit_suffix || null
                        });
                    }
                    renderRows();
                    if (inst) { inst.removeOption(String(p.id)); inst.clear(true); inst.$control_input.val(''); }
                    return;
                }
                const typed = inst && inst.$control_input ? inst.$control_input.val() : '';
                if (!typed) return;
                httpGet(`{{ route('admin.cart_links.products.search') }}`, { query: typed })
                    .then(function(response){
                        var data = response.data || response;
                        const p2 = Array.isArray(data) && data.length ? data[0] : null;
                        if (!p2) return;
                        const existing2 = items.find(x => String(x.product_id) === String(p2.id) && (x.variant_id == null));
                        if (existing2) {
                            existing2.qty += 1;
                        } else {
                            items.push({
                                product_id: parseInt(p2.id, 10),
                                variant_id: null,
                                qty: 1,
                                options: {},
                                sku: p2.sku,
                                name: p2.name,
                                image: p2.image || '',
                                product_sku: p2.sku,
                                product_image: p2.image || '',
                                product_price: (typeof p2.price !== 'undefined') ? p2.price : null,
                                variants: Array.isArray(p2.variants) ? p2.variants : [],
                                unit_step: p2.unit_step || null,
                                unit_min: p2.unit_min || null,
                                unit_decimal: p2.unit_decimal || null,
                                unit_suffix: p2.unit_suffix || null
                            });
                        }
                        renderRows();
                        if (inst) { inst.removeOption(String(p2.id)); inst.clear(true); inst.$control_input.val(''); }
                    });
            });

            $(productSelect).off('change');

            function renderRows() {
                tableBody.innerHTML = '';
                items.forEach((item, idx) => {
                    const tr = document.createElement('tr');
                    const unitPrice = (function(){
                        if (item.variant_id) {
                            const v = (item.variants || []).find(x => parseInt(x.id,10) === parseInt(item.variant_id,10));
                            if (v && typeof v.price !== 'undefined') return Number(v.price);
                        }
                        return (typeof item.product_price !== 'undefined') ? Number(item.product_price) : null;
                    })();
                    const priceText = fmt(unitPrice);
                    const imgTag = item.image ? `<img src="${item.image}" alt="${item.name || ''}" class="fc-prod-img"/>` : '';
                    const step = item.unit_step ? Number(item.unit_step) : (item.unit_decimal ? 0.01 : 1);
                    const min = item.unit_min ? Number(item.unit_min) : step;
                    const variantSelect = (Array.isArray(item.variants) && item.variants.length)
                        ? `<select class="form-control fc-variant-select" data-variant="${idx}">
                               <option value="">Seçiniz</option>
                               ${item.variants.map(v => `<option value="${v.id}" ${item.variant_id === v.id ? 'selected' : ''}>${v.name || v.sku}</option>`).join('')}
                           </select>`
                        : '-';
                    const qtySuffix = item.unit_suffix ? `<span class="qty-suffix">${item.unit_suffix}</span>` : '';
                    tr.innerHTML = `
                        <td class="text-center">${imgTag}</td>
                        <td class="text-center">${item.name || ''}</td>
                        <td class="text-center">${item.sku || ''}</td>
                        <td class="text-center">${priceText}</td>
                        <td class="text-center">${variantSelect}</td>
                        <td class="text-center">
                            <div class="qty-mini">
                                <input type="number" min="${min}" step="${step}" class="form-control text-center fc-qty-input" value="${item.qty}" data-idx="${idx}" />
                                ${qtySuffix}
                            </div>
                        </td>
                        <td class="text-center"><button type="button" class="btn btn-danger btn-xs" data-del="${idx}" title="Sil"><i class="fa fa-times"></i></button></td>
                    `;
                    tableBody.appendChild(tr);
                });
                bindRowEvents();
                syncFormItems();
                refreshPreview();
            }

            function bindRowEvents() {
                tableBody.querySelectorAll('input[type="number"]').forEach(inp => {
                    inp.addEventListener('input', function() {
                        const idx = parseInt(this.dataset.idx, 10);
                        const val = parseFloat(this.value || '1');
                        items[idx].qty = val;
                        syncFormItems();
                        refreshPreview();
                    });
                });
                tableBody.querySelectorAll('select[data-variant]').forEach(sel => {
                    sel.addEventListener('change', function() {
                        const idx = parseInt(this.dataset.variant, 10);
                        const val = this.value ? parseInt(this.value, 10) : null;
                        items[idx].variant_id = val;
                        if (val) {
                            const v = (items[idx].variants || []).find(x => parseInt(x.id, 10) === val);
                            if (v) {
                                items[idx].sku = v.sku || items[idx].sku;
                                items[idx].image = v.image || items[idx].image;
                            }
                        } else {
                            items[idx].sku = items[idx].product_sku;
                            items[idx].image = items[idx].product_image;
                        }
                        renderRows();
                        refreshPreview();
                    });
                });
                tableBody.querySelectorAll('button[data-del]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idx = parseInt(this.dataset.del, 10);
                        items.splice(idx, 1);
                        renderRows();
                        refreshPreview();
                    });
                });
            }

            function syncFormItems() {
                let hidden = document.getElementById('cart-link-items');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'items';
                    hidden.id = 'cart-link-items';
                    document.getElementById('cart-link-form').appendChild(hidden);
                }
                hidden.value = JSON.stringify(items);
            }

            function setPreviewVisible(visible){
                var el = document.getElementById('cart-link-preview');
                el.style.display = visible ? 'block' : 'none';
            }

            function refreshPreview(){
                if (!items.length) { setPreviewVisible(false); return; }
                httpPost(`{{ route('admin.cart_links.preview') }}`, { items: JSON.stringify(items) })
                    .then(function(resp){
                        var data = resp.data || resp;
                        if (!data || !data.summary) { setPreviewVisible(false); return; }
                        document.getElementById('cl_sub').textContent = fmt(data.summary.sub_total);
                        document.getElementById('cl_ship').textContent = fmt(data.summary.shipping_cost);
                        document.getElementById('cl_disc').textContent = fmt(data.summary.discount);
                        document.getElementById('cl_tax').textContent = fmt(data.summary.tax);
                        document.getElementById('cl_total').textContent = fmt(data.summary.total);
                        setPreviewVisible(true);
                    });
            }
        });
    </script>
@endpush

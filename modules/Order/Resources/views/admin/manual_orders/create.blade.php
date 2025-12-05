@extends('admin::layout')

@section('title', 'Create Manual Order')

@component('admin::components.page.header')
    @slot('title', 'Create Manual Order')
    <li class="active">Create Manual Order</li>
@endcomponent

@section('content')
    <div class="box box-primary">
        <div class="box-body">
            <form method="POST" action="{{ route('admin.manual_orders.store') }}" id="manual-order-form">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading"><strong>Müşteri</strong></div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label>İsim, e-posta veya telefon ile müşteri ara</label>
                                    <select class="selectize prevent-creation" id="customer-search" data-url="{{ route('admin.manual_orders.customers.search') }}"></select>
                                    <input type="hidden" name="customer_id" id="customer_id" />
                                </div>
                                <div id="selected-customer" class="well" style="display:none;"></div>

                                <div class="form-group">
                                    <label><strong>Sevkiyat (Shipping) Adresi</strong></label>
                                    <div class="input-group">
                                        <select name="shipping_address_id" id="shipping_address_id" class="form-control" style="display:none;">
                                            <option value="">Adres seçin</option>
                                        </select>
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default" id="add-address-btn">Yeni adres</button>
                                        </span>
                                    </div>
                                    <small class="text-muted">Adres seçimi zorunlu değildir; kargo ve vergi hesapları adres ile netleşir.</small>
                                </div>
                                <div class="form-group" id="billing_address_group">
                                    <label><strong>Fatura (Billing) Adresi</strong></label>
                                    <div class="input-group">
                                        <select name="billing_address_id" id="billing_address_id" class="form-control" style="display:none;">
                                            <option value="">Adres seçin</option>
                                        </select>
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default" id="add-billing-address-btn">Yeni fatura adresi</button>
                                        </span>
                                    </div>
                                </div>
                                <div class="address-card-wrap" id="shipping-address-cards" style="margin-top:10px;"></div>
                                <div class="address-card-wrap" id="billing-address-cards" style="margin-top:10px;"></div>
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading"><strong>Kargo ve Ödeme</strong></div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label>Ödeme yöntemi</label>
                                    <select name="payment_mode" class="form-control">
                                        <option value="manual_unpaid">Ödenmedi</option>
                                        <option value="manual_paid">Ödendi (Havale/EFT)</option>
                                        <option value="payment_link">Payment Link (müşteriye gönder)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Ödeme notu</label>
                                    <input type="text" name="payment_note" class="form-control" placeholder="Dekont no, banka vs.">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading"><strong>Ürünler</strong></div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label>Ürün ara (isim veya SKU)</label>
                                    <select class="selectize prevent-creation" id="product-search" data-url="{{ route('admin.manual_orders.products.search') }}"></select>
                                </div>
                                <div id="cart-preview" class="table-responsive">
                                    <table class="table table-striped table-bordered" id="manual-order-products-table">
                                        <thead>
                                            <tr>
                                                <th>Görsel</th>
                                                <th>Ürün</th>
                                                <th>Birim Fiyat</th>
                                                <th>Varyant</th>
                                                <th style="width:120px;">Adet</th>
                                                <th style="width:80px;">Sil</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div id="table-totals" class="text-right" style="margin-top:10px;">
                                    <div><span class="text-muted">Ara Toplam</span> <span id="tt_sub" style="min-width:80px;display:inline-block;">0</span></div>
                                    <div><span class="text-muted">Kargo</span> <span id="tt_ship" style="min-width:80px;display:inline-block;">0</span></div>
                                    <div><span class="text-muted">İndirim</span> <span id="tt_disc" style="min-width:80px;display:inline-block;">0</span></div>
                                    <div><span class="text-muted">Vergi</span> <span id="tt_tax" style="min-width:80px;display:inline-block;">0</span></div>
                                    <div><strong>Toplam</strong> <span id="tt_total" style="min-width:80px;display:inline-block;"><strong>0</strong></span></div>
                                </div>
                                
                            </div>
                        </div>

                        
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">Siparişi Oluştur</button>
                </div>
            </form>

            <div class="modal fade" id="newAddressModal" tabindex="-1" role="dialog" aria-labelledby="newAddressModalLabel">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="newAddressModalLabel">Yeni Adres</h4>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6"><input type="text" class="form-control" id="na_first_name" placeholder="Ad"></div>
                        <div class="col-md-6"><input type="text" class="form-control" id="na_last_name" placeholder="Soyad"></div>
                        <div class="col-md-12" style="margin-top:8px;"><input type="text" class="form-control" id="na_address_1" placeholder="Adres 1"></div>
                        <div class="col-md-12" style="margin-top:8px;"><input type="text" class="form-control" id="na_address_2" placeholder="Adres 2"></div>
                        <div class="col-md-6" style="margin-top:8px;"><input type="text" class="form-control" id="na_city" placeholder="Şehir"></div>
                        <div class="col-md-6" style="margin-top:8px;"><input type="text" class="form-control" id="na_state" placeholder="İl/İlçe"></div>
                        <div class="col-md-6" style="margin-top:8px;"><input type="text" class="form-control" id="na_zip" placeholder="Posta Kodu"></div>
                        <div class="col-md-6" style="margin-top:8px;"><input type="text" class="form-control" id="na_country" placeholder="Ülke"></div>
                        <div class="col-md-12" style="margin-top:8px;"><input type="text" class="form-control" id="na_phone" placeholder="Telefon"></div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Kapat</button>
                    <button type="button" class="btn btn-primary" id="na_save_btn">Kaydet</button>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
@endsection

    @push('globals')
    @include('admin::partials.selectize_remote')
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
            const customerInput = document.getElementById('customer-search');
            const customerIdInput = document.getElementById('customer_id');
            const addressSelect = document.getElementById('shipping_address_id');
            const billingAddressGroup = document.getElementById('billing_address_group');
            const billingAddressSelect = document.getElementById('billing_address_id');
            const shippingAddressCards = document.getElementById('shipping-address-cards');
            const billingAddressCards = document.getElementById('billing-address-cards');
            const addAddressBtn = document.getElementById('add-address-btn');
            const addBillingAddressBtn = document.getElementById('add-billing-address-btn');
            const productSelect = document.getElementById('product-search');
            const tableBody = document.querySelector('#manual-order-products-table tbody');

            let selectedCustomerId = null;
            let items = [];
            let lastCart = null;
            let addressModalTarget = 'shipping';

            function loadAddresses(customerId) {
                httpGet(`{{ route('admin.manual_orders.customers.addresses', ['customer' => '__ID__']) }}`.replace('__ID__', customerId))
                    .then(function(response){
                        var data = response.data || response; 
                        addressSelect.innerHTML = '<option value="">Adres seçin</option>';
                        billingAddressSelect.innerHTML = '<option value="">Adres seçin</option>';
                        data.forEach(function(addr){
                            var opt = document.createElement('option');
                            opt.value = addr.id;
                            opt.textContent = `${addr.first_name} ${addr.last_name} - ${addr.address_1}, ${addr.city}`;
                            addressSelect.appendChild(opt);
                            var opt2 = opt.cloneNode(true);
                            billingAddressSelect.appendChild(opt2);
                        });
                        renderAddressCards(data);
                    });
            }

            function renderAddressCards(addresses){
                function makeCard(addr){
                    var div = document.createElement('div');
                    div.className = 'address-card';
                    div.setAttribute('data-id', addr.id);
                    div.style.border = '1px solid #ddd';
                    div.style.borderRadius = '6px';
                    div.style.padding = '10px';
                    div.style.marginBottom = '8px';
                    div.style.cursor = 'pointer';
                    div.innerHTML = `<div style="font-weight:600;">${addr.first_name || ''} ${addr.last_name || ''}</div>
                        <div>${addr.address_1 || ''}</div>
                        ${addr.address_2 ? `<div>${addr.address_2}</div>` : ''}
                        <div>${addr.city || ''}${addr.state ? ', ' + addr.state : ''}${addr.zip ? ' ' + addr.zip : ''}</div>
                        ${addr.phone ? `<div>Telefon: ${addr.phone}</div>` : ''}`;
                    return div;
                }
                shippingAddressCards.innerHTML = '';
                billingAddressCards.innerHTML = '';
                addresses.forEach(function(addr){
                    var s = makeCard(addr);
                    s.addEventListener('click', function(){
                        addressSelect.value = String(addr.id);
                        var prev = shippingAddressCards.querySelector('.address-card.active');
                        if (prev) prev.classList.remove('active');
                        s.classList.add('active');
                        if (isBillingSame()) {
                            billingAddressSelect.value = addressSelect.value;
                            highlightBilling(addr.id);
                        }
                        updateSummary();
                    });
                    shippingAddressCards.appendChild(s);
                    var b = makeCard(addr);
                    b.addEventListener('click', function(){
                        billingAddressSelect.value = String(addr.id);
                        highlightBilling(addr.id);
                        updateSummary();
                    });
                    billingAddressCards.appendChild(b);
                });
                highlightShipping(addressSelect.value);
                highlightBilling(billingAddressSelect.value);
            }
            function highlightShipping(id){
                shippingAddressCards.querySelectorAll('.address-card').forEach(function(el){
                    el.classList.toggle('active', String(el.getAttribute('data-id')) === String(id));
                    el.style.background = el.classList.contains('active') ? '#f5f5f5' : '';
                });
            }
            function highlightBilling(id){
                billingAddressCards.querySelectorAll('.address-card').forEach(function(el){
                    el.classList.toggle('active', String(el.getAttribute('data-id')) === String(id));
                    el.style.background = el.classList.contains('active') ? '#f5f5f5' : '';
                });
            }

            $(customerInput).on('change', function() {
                const value = $(customerInput).val();
                if (!value) return;
                selectedCustomerId = value;
                customerIdInput.value = selectedCustomerId;
                loadAddresses(selectedCustomerId);
                const inst = $(customerInput)[0].selectize;
                const opt = inst && inst.options && inst.options[value] ? inst.options[value] : null;
                const box = document.getElementById('selected-customer');
                box.style.display = 'block';
                box.innerHTML = opt ? (opt.name || '') : '';
                updateSummary();
                if (inst) { inst.disable(); }
            });

            $(productSelect).on('change', function() {
                const id = $(productSelect).val();
                if (!id) return;
                const inst = $(productSelect)[0].selectize;
                const p = inst && inst.options && inst.options[id] ? inst.options[id] : null;
                if (!p) return;
                const existing = items.find(x => String(x.product_id) === String(p.id));
                if (existing) {
                    existing.qty += 1;
                } else {
                    items.push({
                        product_id: parseInt(p.id, 10),
                        variant_id: null,
                        qty: p.unit_min ? parseFloat(p.unit_min) : 1,
                        options: {},
                        sku: p.sku,
                        name: p.name,
                        image: p.image || '',
                        variants: Array.isArray(p.variants) ? p.variants : [],
                        unit_step: p.unit_step || 1,
                        unit_min: p.unit_min || 1,
                        unit_decimal: !!p.unit_decimal,
                        unit_suffix: p.unit_suffix || ''
                    });
                }
                renderRows();
                updateSummary();
            });

            addAddressBtn.addEventListener('click', function() {
                addressModalTarget = 'shipping';
                $('#newAddressModal').modal('show');
            });
            addBillingAddressBtn.addEventListener('click', function() {
                addressModalTarget = 'billing';
                $('#newAddressModal').modal('show');
            });
            addressSelect.addEventListener('change', function(){
                updateSummary();
            });

            function renderRows() {
                tableBody.innerHTML = '';
                items.forEach((item, idx) => {
                    const tr = document.createElement('tr');
                    let imgSrc = item.image || '';
                    if (item.variant_id) {
                        const vv = (item.variants || []).find(x => x.id === item.variant_id);
                        if (vv && vv.image) { imgSrc = vv.image; }
                    }
                    const imgTag = imgSrc ? `<img src="${imgSrc}" alt="${item.name || ''}" style="width:48px;height:48px;object-fit:contain;"/>` : '';
                    const variantSelect = (item.variants && item.variants.length)
                        ? `<select class="form-control" data-variant-idx="${idx}">
                               <option value="">Varyant seçin</option>
                               ${item.variants.map(v => `<option value="${v.id}">${v.name || v.sku || ('#'+v.id)}</option>`).join('')}
                           </select>`
                        : '<span class="text-muted">—</span>';
                    const step = item.unit_step || 1;
                    const min = item.unit_min || 1;
                    var matched = (lastCart && Array.isArray(lastCart.items) ? lastCart.items : []).find(function(ci){
                        var pid = (ci.product && ci.product.id) ? ci.product.id : null;
                        var vid = (ci.variant && ci.variant.id) ? ci.variant.id : null;
                        return String(pid) === String(item.product_id) && String(vid||'') === String(item.variant_id||'');
                    }) || {};
                    var unitPrice = matched.unitPrice && (matched.unitPrice.formatted || matched.unitPrice.amount) ? (matched.unitPrice.formatted || matched.unitPrice.amount) : '';
                    tr.innerHTML = `
                        <td>${imgTag}</td>
                        <td><div>${item.name || ''}</div><div class="text-muted small">${item.sku || ''}</div></td>
                        <td><span class="unit-price" data-idx="${idx}">${unitPrice}</span></td>
                        <td>${variantSelect}</td>
                        <td><div class="input-group"><input type="number" min="${min}" step="${step}" class="form-control" value="${item.qty}" data-idx="${idx}" ${item.unit_decimal ? '' : 'inputmode="numeric"'} /><span class="input-group-addon">${item.unit_suffix || ''}</span></div></td>
                        <td><button type="button" class="btn btn-danger" data-del="${idx}">Sil</button></td>
                    `;
                    tableBody.appendChild(tr);
                });
                bindRowEvents();
                syncFormItems();
                updatePriceCells();
            }

            function bindRowEvents() {
                tableBody.querySelectorAll('input[type="number"]').forEach(inp => {
                    inp.addEventListener('input', function() {
                        const idx = parseInt(this.dataset.idx, 10);
                        const val = parseFloat(this.value || '1');
                        items[idx].qty = val;
                        syncFormItems();
                        updateSummary();
                    });
                });
                tableBody.querySelectorAll('select[data-variant-idx]').forEach(sel => {
                    sel.addEventListener('change', function() {
                        const idx = parseInt(this.dataset.variantIdx || this.getAttribute('data-variant-idx'), 10);
                        const vid = this.value ? parseInt(this.value, 10) : null;
                        items[idx].variant_id = vid;
                        const v = items[idx].variants.find(x => x.id === vid);
                        if (v) {
                            items[idx].sku = v.sku || items[idx].sku;
                            if (v.image) { items[idx].image = v.image; }
                            if (items[idx].image) { var preImg = new Image(); preImg.src = items[idx].image; }
                        }
                        renderRows();
                        syncFormItems();
                        updateSummary();
                    });
                });
                tableBody.querySelectorAll('button[data-del]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idx = parseInt(this.dataset.del, 10);
                        items.splice(idx, 1);
                        renderRows();
                        updateSummary();
                    });
                });
            }

            function syncFormItems() {
                let hidden = document.getElementById('form-items');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'items';
                    hidden.id = 'form-items';
                    document.getElementById('manual-order-form').appendChild(hidden);
                }
                hidden.value = JSON.stringify(items);
                updateSummary();
            }
            function updateSummary(){
                var payload = {
                    items: JSON.stringify(items),
                    customer_id: document.getElementById('customer_id').value || '',
                    shipping_address_id: document.getElementById('shipping_address_id').value || '',
                    billing_address_id: document.getElementById('billing_address_id').value || ''
                };
                httpPost("{{ route('admin.manual_orders.cart.preview') }}", payload)
                    .then(function(resp){
                        var data = resp.data || resp;
                        lastCart = data.cart || null;
                        updatePriceCells();
                        if (data && data.summary) {
                            document.getElementById('tt_sub').textContent = data.summary.sub_total;
                            document.getElementById('tt_ship').textContent = data.summary.shipping_cost;
                            document.getElementById('tt_disc').textContent = data.summary.discount;
                            document.getElementById('tt_tax').textContent = data.summary.tax;
                            document.getElementById('tt_total').textContent = data.summary.total;
                        }
                    });
            }

            function updatePriceCells(){
                var priceEls = document.querySelectorAll('#manual-order-products-table .unit-price');
                var cartItems = lastCart && Array.isArray(lastCart.items) ? lastCart.items : [];
                priceEls.forEach(function(el){
                    var idx = parseInt(el.getAttribute('data-idx'), 10);
                    var item = items[idx];
                    if (!item) return;
                    var matched = cartItems.find(function(ci){
                        var pid = (ci.product && ci.product.id) ? ci.product.id : null;
                        var vid = (ci.variant && ci.variant.id) ? ci.variant.id : null;
                        return String(pid) === String(item.product_id) && String(vid||'') === String(item.variant_id||'');
                    }) || {};
                    var unitPrice = matched.unitPrice && (matched.unitPrice.formatted || matched.unitPrice.amount) ? (matched.unitPrice.formatted || matched.unitPrice.amount) : '';
                    el.textContent = unitPrice;
                });
            }

            

            function renderShippingMethods(){
                var container = document.getElementById('shipping-methods');
                if (!container) return;
                container.innerHTML = '';
                var methods = lastCart && lastCart.availableShippingMethods ? lastCart.availableShippingMethods : [];
                var current = lastCart && lastCart.shippingMethodName ? lastCart.shippingMethodName : (window.currentShippingMethod || '');
                methods.forEach(function(m){
                    var name = m.name || m.driver || '';
                    var label = m.label || name;
                    var cost = (m.cost && (m.cost.amount || (m.cost.inCurrentCurrency ? m.cost.inCurrentCurrency.amount : ''))) || '';
                    var id = 'sm_' + String(name);
                    var div = document.createElement('div');
                    div.className = 'form-radio';
                    div.innerHTML = `
                        <input type="radio" name="shipping_method" id="${id}" value="${name}" ${current===name?'checked':''} />
                        <label for="${id}">${label}</label>
                        <span>${cost}</span>
                    `;
                    div.querySelector('input').addEventListener('change', function(){
                        window.currentShippingMethod = name;
                        updateSummary();
                    });
                    container.appendChild(div);
                });
            }
            document.getElementById('na_save_btn').addEventListener('click', function(){
                var cid = document.getElementById('customer_id').value;
                if (!cid) return;
                httpPost(`{{ route('admin.manual_orders.customers.addresses.store', ['customer' => '__ID__']) }}`.replace('__ID__', cid), {
                    first_name: document.getElementById('na_first_name').value,
                    last_name: document.getElementById('na_last_name').value,
                    address_1: document.getElementById('na_address_1').value,
                    address_2: document.getElementById('na_address_2').value,
                    city: document.getElementById('na_city').value,
                    state: document.getElementById('na_state').value,
                    zip: document.getElementById('na_zip').value,
                    country: document.getElementById('na_country').value,
                    phone: document.getElementById('na_phone').value,
                }).then(function(resp){
                    var created = (resp && resp.data) ? resp.data : null;
                    $('#newAddressModal').modal('hide');
                    httpGet(`{{ route('admin.manual_orders.customers.addresses', ['customer' => '__ID__']) }}`.replace('__ID__', cid))
                        .then(function(response){
                            var data = response.data || response;
                            addressSelect.innerHTML = '<option value="">Adres seçin</option>';
                            data.forEach(function(addr){
                                var opt = document.createElement('option');
                                opt.value = addr.id;
                                opt.textContent = `${addr.first_name} ${addr.last_name} - ${addr.address_1}, ${addr.city}`;
                                addressSelect.appendChild(opt);
                            });
                            if (created && created.id) {
                                if (addressModalTarget === 'shipping') {
                                    addressSelect.value = String(created.id);
                                    highlightShipping(created.id);
                                } else {
                                    billingAddressSelect.value = String(created.id);
                                    highlightBilling(created.id);
                                }
                            }
                        });
                });
            });
        });
    </script>
    @endpush

@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('product::products.products'))

    <li class="active">{{ trans('product::products.products') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'products')
    @slot('name', trans('product::products.product'))
    @slot('filters_form', '#product-filters')

    @slot('filters')
        <form id="product-filters" class="form-inline">
            <div class="form-group" style="margin-right: 8px;">
                <label for="filter-brand" style="margin-right:4px;">Marka</label>
                <select name="brand_id" id="filter-brand" class="form-control input-sm">
                    <option value="">Tümü</option>
                    @isset($brands)
                        @foreach ($brands as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <div class="form-group" style="margin-right: 8px;">
                <label for="filter-category" style="margin-right:4px;">Kategori</label>
                <select name="category_id" id="filter-category" class="form-control input-sm">
                    <option value="">Tümü</option>
                    @isset($categories)
                        @foreach ($categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    @endisset
                </select>
            </div>
        </form>
    @endslot

    @slot('thead')
        @include('product::admin.products.partials.thead', ['name' => 'products-index'])
    @endslot
@endcomponent

@if (session()->has('exit_flash'))
    @push('notifications')
        <div class="alert alert-success fade in alert-dismissible clearfix">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM11.25 8C11.25 7.59 11.59 7.25 12 7.25C12.41 7.25 12.75 7.59 12.75 8V13C12.75 13.41 12.41 13.75 12 13.75C11.59 13.75 11.25 13.41 11.25 13V8ZM12.92 16.38C12.87 16.51 12.8 16.61 12.71 16.71C12.61 16.8 12.5 16.87 12.38 16.92C12.26 16.97 12.13 17 12 17C11.87 17 11.74 16.97 11.62 16.92C11.5 16.87 11.39 16.8 11.29 16.71C11.2 16.61 11.13 16.51 11.08 16.38C11.03 16.26 11 16.13 11 16C11 15.87 11.03 15.74 11.08 15.62C11.13 15.5 11.2 15.39 11.29 15.29C11.39 15.2 11.5 15.13 11.62 15.08C11.86 14.98 12.14 14.98 12.38 15.08C12.5 15.13 12.61 15.2 12.71 15.29C12.8 15.39 12.87 15.5 12.92 15.62C12.97 15.74 13 15.87 13 16C13 16.13 12.97 16.26 12.92 16.38Z" fill="#555555"/>
            </svg>
            
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M5.00082 14.9995L14.9999 5.00041" stroke="#555555" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14.9999 14.9996L5.00082 5.00049" stroke="#555555" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <span class="alert-text">{{ session('exit_flash') }}</span>
        </div>
    @endpush
@endif

    @push('scripts')
    <script type="module">
        const allBrands = @json($brands ?? []);
        DataTable.set('#products-table .table', {
            routePrefix: 'products',
            routes: {
                table: 'table',
                destroy: 'destroy',
            }
        });

        const dt = new DataTable('#products-table .table', {
            stateSave: false,
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'thumbnail', orderable: false, searchable: false, width: '10%' },
                { data: 'name', name: 'translations.name', class: 'name', orderable: false, defaultContent: '' },
                { data: 'brand', name: 'brand.translations.name', orderable: false, searchable: false },
                { data: 'default_category', orderable: false, searchable: false },
                { data: 'price', searchable: false },
                { data: 'in_stock', name: 'in_stock', searchable: false, className: 'stock-cell' },
                { data: 'status', name: 'is_active', searchable: false },
                { data: 'actions', orderable: false, searchable: false },
            ]
        });

        const $brandFilter = $('#filter-brand');
        const $categoryFilter = $('#filter-category');

        $brandFilter.on('change', function () {
            DataTable.reload('#products-table .table');
        });

        $categoryFilter.on('change', function () {
            DataTable.reload('#products-table .table');
        });

        $(document).on('change', '.product-status-switch', function () {
            const checkbox = $(this);
            const id = checkbox.data('id');
            const is_active = checkbox.is(':checked') ? 1 : 0;

            checkbox.prop('disabled', true);

            axios
                .patch(`${FleetCart.baseUrl}/admin/products/${id}/status`, { is_active })
                .then(() => {
                    // no-op; switch state already reflects latest value
                })
                .catch((error) => {
                    checkbox.prop('checked', !is_active);
                })
                .finally(() => {
                    checkbox.prop('disabled', false);
                });
        });

        // Pricing drawer
        const pricingDrawer = document.getElementById('pricing-drawer');
        const pricingDrawerBackdrop = document.getElementById('pricing-drawer-backdrop');
        const pricingDrawerContent = document.getElementById('pricing-drawer-content');
        const pricingDrawerTitle = document.getElementById('pricing-drawer-title');
        const pricingDrawerSave = document.getElementById('pricing-drawer-save');
        const pricingDrawerClose = document.getElementById('pricing-drawer-close');
        let currentPricingProductId = null;

        function openPricingDrawer() {
            pricingDrawer.classList.add('open');
            pricingDrawerBackdrop.classList.add('open');
        }

        function closePricingDrawer() {
            pricingDrawer.classList.remove('open');
            pricingDrawerBackdrop.classList.remove('open');
            currentPricingProductId = null;
            pricingDrawerContent.innerHTML = '';
            pricingDrawerTitle.textContent = '';
        }

        pricingDrawerClose.addEventListener('click', closePricingDrawer);
        pricingDrawerBackdrop.addEventListener('click', closePricingDrawer);

        function buildPricingItems(product) {
            const items = [];
            const currencyStep = 0.01;
            if (product.variants && product.variants.length > 0) {
                product.variants.forEach(v => {
                    const img = (v.media && v.media[0]) ? mediaUrl(v.media[0]) : ((product.media && product.media[0]) ? mediaUrl(product.media[0]) : '');
                    const priceVal = typeof v.price !== 'undefined' && v.price !== null ? Number(v.price) : '';
                    const specialVal = typeof v.special_price !== 'undefined' && v.special_price !== null ? Number(v.special_price) : '';
                    items.push(`
                        <div class="inv-item inv-price-item">
                            <div class="inv-info">
                                <div class="inv-media"><img src="${img}" alt="" /></div>
                                <div class="inv-text">
                                    <div class="inv-name">${_.escape(v.name || '')}</div>
                                </div>
                            </div>
                            <div class="inv-actions">
                                <div class="inv-input-wrap" style="flex-direction: column; align-items: center; text-align:center;">
                                    <span style="font-size:11px;color:#6b7280;margin-bottom:3px; display:block;">Fiyat</span>
                                    <input type="number" step="${currencyStep}" min="0" inputmode="decimal" class="inv-input variant-price-input" data-id="${v.id}" value="${priceVal !== '' ? priceVal : ''}" placeholder="0.00" />
                                </div>
                                <div class="inv-input-wrap" style="flex-direction: column; align-items: center; text-align:center; margin-left:8px;">
                                    <span style="font-size:11px;color:#6b7280;margin-bottom:3px; display:block;">Özel fiyat</span>
                                    <input type="number" step="${currencyStep}" min="0" inputmode="decimal" class="inv-input variant-special-price-input" data-id="${v.id}" value="${specialVal !== '' ? specialVal : ''}" placeholder="0.00" />
                                </div>
                            </div>
                        </div>
                    `);
                });
            } else {
                const img = (product.media && product.media[0]) ? mediaUrl(product.media[0]) : '';
                const priceVal = typeof product.price !== 'undefined' && product.price !== null ? Number(product.price) : '';
                const specialVal = typeof product.special_price !== 'undefined' && product.special_price !== null ? Number(product.special_price) : '';
                items.push(`
                    <div class="inv-item inv-single inv-price-item">
                        <div class="inv-info">
                            <div class="inv-media"><img src="${img}" alt="" /></div>
                            <div class="inv-text">
                                <div class="inv-name">${_.escape(product.name || '')}</div>
                            </div>
                        </div>
                        <div class="inv-actions">
                            <div class="inv-input-wrap" style="flex-direction: column; align-items: center; text-align:center;">
                                <span style="font-size:11px;color:#6b7280;margin-bottom:3px; display:block;">Fiyat</span>
                                <input type="number" step="${currencyStep}" min="0" inputmode="decimal" class="inv-input product-price-input" value="${priceVal !== '' ? priceVal : ''}" placeholder="0.00" />
                            </div>
                            <div class="inv-input-wrap" style="flex-direction: column; align-items: center; text-align:center; margin-left:8px;">
                                <span style="font-size:11px;color:#6b7280;margin-bottom:3px; display:block;">Özel fiyat</span>
                                <input type="number" step="${currencyStep}" min="0" inputmode="decimal" class="inv-input product-special-price-input" value="${specialVal !== '' ? specialVal : ''}" placeholder="0.00" />
                            </div>
                        </div>
                    </div>
                `);
            }
            return items.join('');
        }

        $(document).on('click', '.price-cell', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            if (!id) return;
            currentPricingProductId = id;
            axios.get(`${FleetCart.baseUrl}/admin/products/${id}/pricing`).then(({ data }) => {
                const product = data.product;
                pricingDrawerTitle.textContent = product.name;
                pricingDrawerContent.innerHTML = buildPricingItems(product);
                openPricingDrawer();
            });
        });

        pricingDrawerSave.addEventListener('click', function () {
            if (!currentPricingProductId) return;
            const payload = {};
            const variantPriceInputs = pricingDrawerContent.querySelectorAll('.variant-price-input');
            const variantSpecialInputs = pricingDrawerContent.querySelectorAll('.variant-special-price-input');

            if (variantPriceInputs.length > 0) {
                payload.variants = {};
                variantPriceInputs.forEach(inp => {
                    const id = inp.getAttribute('data-id');
                    const specialInp = pricingDrawerContent.querySelector('.variant-special-price-input[data-id="' + id + '"]');
                    const vPriceRaw = (inp.value || '').trim();
                    const vSpecialRaw = (specialInp?.value || '').trim();
                    const vPayload = {};
                    if (vPriceRaw !== '') {
                        let pv = parseFloat(vPriceRaw.replace(',', '.'));
                        if (!isFinite(pv) || pv < 0) pv = 0;
                        vPayload.price = pv;
                    }
                    if (vSpecialRaw !== '') {
                        let spv = parseFloat(vSpecialRaw.replace(',', '.'));
                        if (!isFinite(spv) || spv < 0) spv = 0;
                        vPayload.special_price = spv;
                    } else {
                        vPayload.special_price = '';
                    }
                    payload.variants[id] = vPayload;
                });
            } else {
                const priceInp = pricingDrawerContent.querySelector('.product-price-input');
                const specialInp = pricingDrawerContent.querySelector('.product-special-price-input');
                if (priceInp && priceInp.value.trim() !== '') {
                    let pv = parseFloat(priceInp.value.replace(',', '.'));
                    if (!isFinite(pv) || pv < 0) pv = 0;
                    payload.price = pv;
                }
                if (specialInp) {
                    const raw = specialInp.value.trim();
                    if (raw !== '') {
                        let spv = parseFloat(raw.replace(',', '.'));
                        if (!isFinite(spv) || spv < 0) spv = 0;
                        payload.special_price = spv;
                    } else {
                        payload.special_price = '';
                    }
                }
            }

            axios.patch(`${FleetCart.baseUrl}/admin/products/${currentPricingProductId}/pricing`, payload).then(() => {
                closePricingDrawer();
                DataTable.reload('#products-table .table');
            });
        });

        // Inline brand select
        $(document).on('click', '.brand-cell', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const cell = $(this);
            const productId = cell.data('id');
            if (!productId) return;

            // Avoid opening multiple selects
            if (cell.data('editing')) {
                return;
            }
            cell.data('editing', true);

            const currentBrandId = String(cell.data('brand-id') ?? '');

            const select = $('<select/>', {
                class: 'form-control input-sm',
            });

            select.append($('<option/>', { value: '', text: 'Select' }));
            Object.keys(allBrands).forEach((id) => {
                select.append(
                    $('<option/>', {
                        value: id,
                        text: allBrands[id],
                    })
                );
            });

            select.val(currentBrandId !== '' ? currentBrandId : '');

            const originalText = cell.text();
            cell.empty().append(select);
            select.focus();

            function cleanup(text, brandId) {
                cell.data('editing', false);
                cell.data('brand-id', brandId ?? '');
                cell.text(text || '');
            }

            select.on('change', function () {
                const newBrandId = $(this).val();
                axios
                    .patch(`${FleetCart.baseUrl}/admin/products/${productId}/brand`, {
                        brand_id: newBrandId,
                    })
                    .then(() => {
                        const label = newBrandId && allBrands[newBrandId] ? allBrands[newBrandId] : '';
                        cleanup(label, newBrandId);
                    })
                    .catch(() => {
                        cleanup(originalText, currentBrandId);
                    });
            });

            select.on('blur', function () {
                // On blur without change, restore original
                if (cell.data('editing')) {
                    const brandId = String(cell.data('brand-id') ?? currentBrandId ?? '');
                    const label = brandId && allBrands[brandId] ? allBrands[brandId] : originalText;
                    cleanup(label, brandId);
                }
            });
        });

        $(document).on('click', '.action-delete', function (e) {
            e.preventDefault();
            const id = $(this).data('id');

            const confirmationModal = $('#confirmation-modal');
            confirmationModal.modal('show');
            const body = confirmationModal.find('.modal-body');

            body.find('.fc-delete-redirect').remove();

            confirmationModal
                .modal('show')
                .find('form')
                .off('submit')
                .on('submit', (ev) => {
                    ev.preventDefault();
                    confirmationModal.modal('hide');

                    axios
                        .delete(`${FleetCart.baseUrl}/admin/products/${id}`)
                        .then(() => {
                            window.location.reload();
                        })
                        .catch(() => {
                            window.location.reload();
                        });
                });
        });

        $(document).on('click', '.action-view, .action-delete, .action-edit, .product-status-switch, .price-cell', function (e) {
            e.stopPropagation();
        });

        $(document).on('click', '.switch label', function (e) {
            e.stopPropagation();
        });
        // Inventory drawer
        const drawer = document.getElementById('inventory-drawer');
        const drawerBackdrop = document.getElementById('inventory-drawer-backdrop');
        const drawerContent = document.getElementById('inventory-drawer-content');
        const drawerTitle = document.getElementById('inventory-drawer-title');
        const drawerSave = document.getElementById('inventory-drawer-save');
        const drawerClose = document.getElementById('inventory-drawer-close');
        let currentProductId = null;
        let currentRowEl = null;
        function mediaUrl(obj) {
            if (!obj) return '';
            const p = obj.path || obj;
            if (!p) return '';
            if (/^https?:\/\//.test(p)) return p;
            if (p.startsWith('/')) return FleetCart.baseUrl + p;
            if (p.startsWith('storage/')) return FleetCart.baseUrl + '/' + p;
            return FleetCart.baseUrl + '/storage/' + p;
        }
        function buildItems(product) {
            const unitSuffix = product.sale_unit_id ? (product.unit_suffix || '') : '';
            const allowDecimal = !!(product.unit_decimal);
            const unitMin = Number(product.unit_min ?? 0);
            const unitStep = allowDecimal ? Number(product.unit_step || 0.01) : 1;
            const inputMode = allowDecimal ? 'decimal' : 'numeric';
            const items = [];

            if (product.variants && product.variants.length > 0) {
                product.variants.forEach(v => {
                    const img = (v.media && v.media[0]) ? mediaUrl(v.media[0]) : ((product.media && product.media[0]) ? mediaUrl(product.media[0]) : '');
                    const sku = v.sku || v.sku_code || '';
                    items.push(`
                        <div class="inv-item">
                            <div class="inv-info">
                                <div class="inv-media"><img src="${img}" alt="" /></div>
                                <div class="inv-text">
                                    <div class="inv-name">${_.escape(v.name || '')}</div>
                                    ${sku ? `<div class="inv-sku">${_.escape(sku)}</div>` : ''}
                                </div>
                            </div>
                            <div class="inv-actions">
                                <div class="inv-input-wrap">
                                    <input type="number" step="${unitStep}" min="${unitMin}" inputmode="${inputMode}" class="inv-input variant-qty-input" data-id="${v.id}" data-decimal="${allowDecimal ? 1 : 0}" value="${Number(v.qty || 0)}" ${unitSuffix ? `data-suffix="${unitSuffix}"` : ''} />
                                </div>
                            </div>
                        </div>
                    `);
                });
            } else {
                const img = (product.media && product.media[0]) ? mediaUrl(product.media[0]) : '';
                const sku = product.sku || product.sku_code || '';
                items.push(`
                    <div class="inv-item inv-single">
                        <div class="inv-info">
                            <div class="inv-media"><img src="${img}" alt="" /></div>
                            <div class="inv-text">
                                <div class="inv-name">${_.escape(product.name || '')}</div>
                                ${sku ? `<div class="inv-sku">${_.escape(sku)}</div>` : ''}
                            </div>
                        </div>
                        <div class="inv-actions">
                            <div class="inv-input-wrap">
                                <input type="number" step="${unitStep}" min="${unitMin}" inputmode="${inputMode}" class="inv-input product-qty-input" data-decimal="${allowDecimal ? 1 : 0}" value="${Number(product.qty || 0)}" ${unitSuffix ? `data-suffix="${unitSuffix}"` : ''} />
                            </div>
                            <button type="button" class="btn btn-primary btn-xs inv-inline-save">{{ trans('admin::admin.buttons.save') }}</button>
                        </div>
                    </div>
                `);
            }

            return items.join('');
        }

        function openDrawer() {
            drawer.classList.add('open');
            drawerBackdrop.classList.add('open');
        }

        function closeDrawer() {
            drawer.classList.remove('open');
            drawerBackdrop.classList.remove('open');
            currentProductId = null;
            drawerContent.innerHTML = '';
            drawerTitle.textContent = '';
        }

        drawerClose.addEventListener('click', closeDrawer);
        drawerBackdrop.addEventListener('click', closeDrawer);

        $(document).on('click', '#products-table .table tbody tr td.stock-cell', function () {
            const dtApi = $('#products-table .table').DataTable();
            currentRowEl = $(this).closest('tr');
            const row = dtApi.row(currentRowEl).data() || {};
            const anchorId = $(this).find('.inventory-click').data('id');
            const targetId = anchorId || row.id;
            if (!targetId) return;
            axios.get(`${FleetCart.baseUrl}/admin/products/${targetId}/inventory`).then(({ data }) => {
                const product = data.product;
                currentProductId = product.id;
                drawerTitle.textContent = product.name;
                drawerContent.innerHTML = buildItems(product);
                drawerSave.style.display = (product.variants && product.variants.length > 0) ? '' : 'none';
                openDrawer();
            });
        });

        $(document).on('click', '.inventory-click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            if (!id) return;
            currentRowEl = $(this).closest('tr');
            axios.get(`${FleetCart.baseUrl}/admin/products/${id}/inventory`).then(({ data }) => {
                const product = data.product;
                currentProductId = product.id;
                drawerTitle.textContent = product.name;
                drawerContent.innerHTML = buildItems(product);
                drawerSave.style.display = (product.variants && product.variants.length > 0) ? '' : 'none';
                openDrawer();
            });
        });

        drawerSave.addEventListener('click', function () {
            if (!currentProductId) return;
            const payload = {};
            const variantInputs = drawerContent.querySelectorAll('.variant-qty-input');
            if (variantInputs.length > 0) {
                payload.variants = {};
                variantInputs.forEach(inp => {
                    const id = inp.getAttribute('data-id');
                    const allow = String(inp.getAttribute('data-decimal') || '0') === '1';
                    let v = parseFloat((inp.value || '0').replace(',', '.')) || 0;
                    if (!allow) v = Math.trunc(v);
                    payload.variants[id] = { qty: v };
                });
            } else {
                const inp = drawerContent.querySelector('.product-qty-input');
                const allow = String(inp?.getAttribute('data-decimal') || '0') === '1';
                let v = parseFloat((inp?.value || '0').replace(',', '.')) || 0;
                if (!allow) v = Math.trunc(v);
                payload.qty = v;
            }

            axios.patch(`${FleetCart.baseUrl}/admin/products/${currentProductId}/inventory`, payload).then(() => {
                const cell = $(currentRowEl).find('td.stock-cell');
                const suffixAttr = drawerContent.querySelector('.inv-input')?.getAttribute('data-suffix') || '';
                if (variantInputs.length > 0) {
                    let total = 0;
                    variantInputs.forEach(inp => { total += parseFloat((inp.value || '0').replace(',', '.')) || 0; });
                    const formatted = (function(n){ const s = (Math.round(n * 100) / 100).toFixed(2).replace(/\.00$/, ''); return s.endsWith('.0') ? s.slice(0, -2) : s.replace(/\.0$/, ''); })(total);
                    const display = suffixAttr ? (formatted + ' ' + suffixAttr) : formatted;
                    cell.find('.stock-total').text(display);
                } else {
                    const inp = drawerContent.querySelector('.product-qty-input');
                    const n = parseFloat((inp.value || '0').replace(',', '.')) || 0;
                    const formatted = (function(n){ const s = (Math.round(n * 100) / 100).toFixed(2).replace(/\.00$/, ''); return s.endsWith('.0') ? s.slice(0, -2) : s.replace(/\.0$/, ''); })(n);
                    const display = suffixAttr ? (formatted + ' ' + suffixAttr) : formatted;
                    const anchor = cell.find('.inventory-click');
                    if (anchor.length) anchor.text(display); else cell.text(display);
                }
                closeDrawer();
            });
        });

        $(document).on('input', '.inv-input', function () {
            const allow = String(this.getAttribute('data-decimal') || '0') === '1';
            if (!allow) {
                const val = String(this.value || '');
                if (val.includes('.')) {
                    this.value = String(Math.trunc(Number(val)) || '');
                }
            }
        });

        $(document).on('click', '.inv-inline-save', function () {
            if (!currentProductId) return;
            const payload = {};
            const inp = drawerContent.querySelector('.product-qty-input');
            const allow = String(inp?.getAttribute('data-decimal') || '0') === '1';
            let v = parseFloat((inp?.value || '0').replace(',', '.')) || 0;
            if (!allow) v = Math.trunc(v);
            payload.qty = v;

            axios.patch(`${FleetCart.baseUrl}/admin/products/${currentProductId}/inventory`, payload).then(() => {
                const cell = $(currentRowEl).find('td.stock-cell');
                const suffixAttr = inp?.getAttribute('data-suffix') || '';
                const formatted = (function(n){ const s = (Math.round(n * 100) / 100).toFixed(2).replace(/\.00$/, ''); return s.endsWith('.0') ? s.slice(0, -2) : s.replace(/\.0$/, ''); })(v);
                const display = suffixAttr ? (formatted + ' ' + suffixAttr) : formatted;
                const anchor = cell.find('.inventory-click');
                if (anchor.length) anchor.text(display); else cell.text(display);
                closeDrawer();
            });
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 24px);
            grid-auto-rows: 24px;
            gap: 6px;
            justify-content: center;
        }

        .actions-grid a,
        .actions-grid button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        #inventory-drawer-backdrop,
        #pricing-drawer-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.35); opacity: 0; pointer-events: none; transition: opacity .2s; z-index: 1040; }
        #inventory-drawer-backdrop.open,
        #pricing-drawer-backdrop.open { opacity: 1; pointer-events: auto; }
        #inventory-drawer,
        #pricing-drawer { position: fixed; top: 0; right: -480px; width: 480px; height: 100%; background: #fff; box-shadow: -2px 0 8px rgba(0,0,0,0.15); z-index: 1050; transition: right .25s; display: flex; flex-direction: column; }
        #inventory-drawer.open,
        #pricing-drawer.open { right: 0; }
        #inventory-drawer .drawer-header,
        #pricing-drawer .drawer-header { padding: 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #eee; }
        #inventory-drawer .drawer-body,
        #pricing-drawer .drawer-body { padding: 12px 16px; overflow-y: auto; flex: 1; }
        #inventory-drawer .drawer-footer,
        #pricing-drawer .drawer-footer { padding: 12px 16px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 8px; }
        .inv-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 12px; margin-bottom: 6px; border-radius: 8px; border: 1px solid #e5e7eb; background: #ffffff; box-shadow: 0 1px 2px rgba(15,23,42,0.02); }
        .inv-item:hover { border-color: #d1d5db; box-shadow: 0 2px 4px rgba(15,23,42,0.05); }
        .inv-info { display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1; }
        .inv-media img { width: 40px; height: 40px; object-fit: cover; border-radius: 8px; background: #f9fafb; display: block; border: 1px solid #e5e7eb; }
        .inv-text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .inv-name { font-weight: 600; color: #111827; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13px; }
        .inv-sku { font-size: 11px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .inv-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
        .inv-actions .inv-inline-save { padding: 4px 10px; font-size: 11px; line-height: 1; border-radius: 4px; height: 30px; display: inline-flex; align-items: center; }
        .inv-input-wrap { display: flex; align-items: center; }
        .inv-input { width: 70px; height: 30px; appearance: textfield; -moz-appearance: textfield; text-align: center; border-radius: 6px; border: 1px solid #e5e7eb; background: #f9fafb; color: #111; padding: 0 6px; font-size: 12px; }
        .inv-input::-webkit-outer-spin-button,
        .inv-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .inv-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); background: #fff; }
        @media (max-width: 480px) {
            .inv-item { gap: 8px; }
            .inv-input { width: 130px; height: 34px; }
            .inv-single .inv-input { max-width: 200px; }
        }
    </style>
    @endpush

    @push('notifications')
    <div id="inventory-drawer-backdrop"></div>
    <div id="inventory-drawer">
        <div class="drawer-header">
            <div id="inventory-drawer-title"></div>
            <button id="inventory-drawer-close" class="btn btn-default">×</button>
        </div>
        <div class="drawer-body" id="inventory-drawer-content"></div>
        <div class="drawer-footer">
            <button class="btn btn-default" id="inventory-drawer-close">{{ trans('admin::admin.buttons.cancel') }}</button>
            <button class="btn btn-primary" id="inventory-drawer-save">{{ trans('admin::admin.buttons.save') }}</button>
        </div>
    </div>
    <div id="pricing-drawer-backdrop"></div>
    <div id="pricing-drawer">
        <div class="drawer-header">
            <div id="pricing-drawer-title"></div>
            <button id="pricing-drawer-close" class="btn btn-default">×</button>
        </div>
        <div class="drawer-body" id="pricing-drawer-content"></div>
        <div class="drawer-footer">
            <button class="btn btn-default" id="pricing-drawer-close">{{ trans('admin::admin.buttons.cancel') }}</button>
            <button class="btn btn-primary" id="pricing-drawer-save">{{ trans('admin::admin.buttons.save') }}</button>
        </div>
    </div>
    @endpush

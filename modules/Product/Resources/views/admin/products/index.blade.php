@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('product::products.products'))

    <li class="active">{{ trans('product::products.products') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'products')
    @slot('name', trans('product::products.product'))

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
        DataTable.set('#products-table .table', {
            routePrefix: 'products',
            routes: {
                table: 'table',
                destroy: 'destroy',
            }
        });

        const dt = new DataTable('#products-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'thumbnail', orderable: false, searchable: false, width: '10%' },
                { data: 'name', name: 'translations.name', class: 'name', orderable: false, defaultContent: '' },
                { data: 'price', searchable: false },
                { data: 'in_stock', name: 'in_stock', searchable: false, className: 'stock-cell' },
                { data: 'status', name: 'is_active', searchable: false, orderable: false },
                { data: 'actions', orderable: false, searchable: false },
            ]
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

        $(document).on('click', '.action-delete', function (e) {
            e.preventDefault();
            const id = $(this).data('id');

            const confirmationModal = $('#confirmation-modal');
            confirmationModal.modal('show');
            const body = confirmationModal.find('.modal-body');
            const form = confirmationModal.find('form');

            body.find('.fc-delete-redirect').remove();

            const ui = `
                <div class="fc-delete-redirect">
                    <div class="m-t-15">
                        <div class="text-bold">Bu ürünü silmek istediğinize emin misiniz?</div>
                        <div class="text-red">Bu işlem geri alınamaz. Ürün katalogdan tamamen kaldırılacaktır.</div>
                        <div class="m-t-10">İsterseniz eski URL'yi başka bir sayfaya yönlendirebilirsiniz.</div>
                    </div>
                    <hr>
                    <div class="m-t-10"><strong>Yönlendirme seçeneği</strong></div>
                    <div class="m-t-5">
                        <label class="block"><input type="radio" name="redirect_type" value="none" checked> Yönlendirme yapma (410)</label>
                        <label class="block"><input type="radio" name="redirect_type" value="home"> Anasayfaya yönlendir</label>
                        <label class="block"><input type="radio" name="redirect_type" value="product"> Başka bir ürüne yönlendir (ID)</label>
                        <input type="number" class="form-control m-t-5" name="redirect_target_id" placeholder="Hedef ürün ID">
                        <label class="block m-t-10"><input type="radio" name="redirect_type" value="custom"> Özel URL'ye yönlendir</label>
                        <input type="text" class="form-control m-t-5" name="redirect_target_url" placeholder="https://...">
                    </div>
                    <div class="m-t-10"><strong>Status Code</strong></div>
                    <div class="m-t-5">
                        <label class="inline-block m-r-10"><input type="radio" name="redirect_status" value="301" checked> 301</label>
                        <label class="inline-block m-r-10"><input type="radio" name="redirect_status" value="302"> 302</label>
                    </div>
                </div>`;

            body.append(ui);

            confirmationModal
                .modal('show')
                .find('form')
                .off('submit')
                .on('submit', (ev) => {
                    ev.preventDefault();
                    confirmationModal.modal('hide');

                    const redirectType = form.find('input[name="redirect_type"]:checked').val();
                    const statusCode = form.find('input[name="redirect_status"]:checked').val();
                    const targetId = form.find('input[name="redirect_target_id"]').val();
                    const targetUrl = form.find('input[name="redirect_target_url"]').val();

                    axios
                        .delete(`${FleetCart.baseUrl}/admin/products/${id}`, {
                            data: {
                                redirect: {
                                    type: redirectType,
                                    status_code: statusCode,
                                    target_id: targetId || null,
                                    target_url: targetUrl || null,
                                }
                            }
                        })
                        .then(() => {
                            window.location.reload();
                        })
                        .catch(() => {
                            window.location.reload();
                        });
                });
        });

        $(document).on('click', '.action-view, .action-delete, .action-edit, .product-status-switch', function (e) {
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
            const items = [];
            if (product.variants && product.variants.length > 0) {
                product.variants.forEach(v => {
                    const img = (v.media && v.media[0]) ? mediaUrl(v.media[0]) : ((product.media && product.media[0]) ? mediaUrl(product.media[0]) : '');
                    items.push(`
                        <div class="inv-item">
                            <div class="inv-media"><img src="${img}" alt="" /></div>
                            <div class="inv-name">${_.escape(v.name || '')}</div>
                            <div class="inv-input-wrap">
                                <input type="number" step="0.5" min="0" class="inv-input variant-qty-input" data-id="${v.id}" value="${Number(v.qty || 0)}" ${unitSuffix ? `data-suffix="${unitSuffix}"` : ''} />
                            </div>
                        </div>
                    `);
                });
            } else {
                const img = (product.media && product.media[0]) ? mediaUrl(product.media[0]) : '';
                items.push(`
                    <div class="inv-item inv-single">
                        <div class="inv-top">
                            <div class="inv-media"><img src="${img}" alt="" /></div>
                            <div class="inv-name">${_.escape(product.name || '')}</div>
                        </div>
                        <div class="inv-bottom">
                            <div class="inv-input-wrap">
                                <input type="number" step="0.5" min="0" class="inv-input product-qty-input" value="${Number(product.qty || 0)}" ${unitSuffix ? `data-suffix="${unitSuffix}"` : ''} />
                            </div>
                            <button type="button" class="btn btn-primary inv-inline-save">{{ trans('admin::admin.buttons.save') }}</button>
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
                    const v = parseFloat((inp.value || '0').replace(',', '.')) || 0;
                    payload.variants[id] = { qty: v };
                });
            } else {
                const inp = drawerContent.querySelector('.product-qty-input');
                const v = parseFloat((inp.value || '0').replace(',', '.')) || 0;
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

        $(document).on('click', '.inv-inline-save', function () {
            if (!currentProductId) return;
            const payload = {};
            const inp = drawerContent.querySelector('.product-qty-input');
            const v = parseFloat((inp?.value || '0').replace(',', '.')) || 0;
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
        #inventory-drawer-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.35); opacity: 0; pointer-events: none; transition: opacity .2s; z-index: 1040; }
        #inventory-drawer-backdrop.open { opacity: 1; pointer-events: auto; }
        #inventory-drawer { position: fixed; top: 0; right: -480px; width: 480px; height: 100%; background: #fff; box-shadow: -2px 0 8px rgba(0,0,0,0.15); z-index: 1050; transition: right .25s; display: flex; flex-direction: column; }
        #inventory-drawer.open { right: 0; }
        #inventory-drawer .drawer-header { padding: 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #eee; }
        #inventory-drawer .drawer-body { padding: 12px 16px; overflow-y: auto; flex: 1; }
        #inventory-drawer .drawer-footer { padding: 12px 16px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 8px; }
        .inv-item { display: flex; align-items: center; gap: 12px; border: 1px solid #f0f0f0; border-radius: 10px; padding: 10px; margin-bottom: 10px; }
        .inv-media img { width: 44px; height: 44px; object-fit: cover; border-radius: 8px; background: #fafafa; display: block; }
        .inv-name { font-weight: 600; color: #222; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .inv-input-wrap { display: flex; align-items: center; }
        .inv-input { width: 160px; height: 36px; appearance: textfield; -moz-appearance: textfield; text-align: center; border-radius: 10px; border: 1px solid #e5e7eb; background: #f9fafb; color: #111; }
        .inv-input::-webkit-outer-spin-button,
        .inv-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .inv-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); background: #fff; }
        .inv-single { flex-direction: column; align-items: stretch; }
        .inv-single .inv-top { display: flex; align-items: center; gap: 12px; }
        .inv-single .inv-media img { width: 56px; height: 56px; border-radius: 10px; }
        .inv-single .inv-name { font-size: 14px; }
        .inv-single .inv-bottom { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
        .inv-single .inv-input-wrap { flex: 0 0 auto; }
        .inv-single .inv-input { width: 180px; max-width: 240px; }
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
    @endpush

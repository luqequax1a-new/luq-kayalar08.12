@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', 'Toplu Ürün Güncelle')

    <li><a href="{{ route('admin.products.index') }}">{{ trans('product::products.products') }}</a></li>
    <li class="active">Toplu Ürün Güncelle</li>
@endcomponent

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-body">
                    <div id="bulk-editor-alert" class="alert" style="display:none;"></div>
                    <div class="row">
                        <div class="col-md-7">
                            <h4 style="margin-top:0;">Filtre Koşulları</h4>

                            <div class="form-group" style="margin-bottom:10px;">
                                <label style="margin-right:10px;">Filtre Koşulları Nasıl Birleştirilsin</label>
                                <label style="font-weight:normal; margin-right:10px;">
                                    <input type="radio" name="bulk-filter-combine" value="and" checked> VE (AND)
                                </label>
                                <label style="font-weight:normal;">
                                    <input type="radio" name="bulk-filter-combine" value="or"> VEYA (OR)
                                </label>
                            </div>

                            <div id="bulk-filter-rows"></div>

                            <button type="button" class="btn btn-default btn-xs" id="bulk-add-filter">
                                + Yeni Filtre Koşulu Ekle
                            </button>
                        </div>

                        <div class="col-md-5">
                            <h4 style="margin-top:0;">Eşleşen Ürünler</h4>
                            <div id="bulk-matching-summary" class="alert alert-info">
                                Ürün Bulunamadı
                            </div>
                            <div class="table-responsive" style="border:1px solid #eee;">
                                <table class="table table-condensed table-striped" id="bulk-matching-table" style="margin-bottom:0;">
                                    <thead>
                                        <tr>
                                            <th style="width:60px;">ID</th>
                                            <th style="width:56px;">Görsel</th>
                                            <th>Ürün Adı</th>
                                            <th style="width:110px;" class="text-right">Fiyat</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <h4 style="margin-top:0;">Güncelleme Aksiyonları</h4>
                            <div id="bulk-action-rows"></div>
                            <button type="button" class="btn btn-default btn-xs" id="bulk-add-action">
                                + Yeni Güncelleme Aksiyonu Ekle
                            </button>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button type="button" id="bulk-apply" class="btn btn-primary" disabled>
                                Tüm Eşleşen Ürünlere Güncellemeyi Uygula
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('build/assets/jstree.min.css') }}">
    <style>
        .bulk-filter-row,
        .bulk-action-row {
            display: flex;
            align-items: center;
            margin-bottom: 6px;
            gap: 6px;
        }
        .bulk-filter-row .form-control,
        .bulk-action-row .form-control,
        .bulk-action-row textarea {
            height: 30px;
            padding: 3px 6px;
            font-size: 12px;
        }
        .bulk-action-row textarea {
            height: 60px;
            resize: vertical;
        }
        .bulk-filter-row .bulk-remove,
        .bulk-action-row .bulk-remove {
            border: none;
            background: transparent;
            color: #c0392b;
        }
        #bulk-category-tree {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 4px 6px;
            border-radius: 3px;
            background: #fff;
        }

        #bulk-matching-table tbody tr td {
            vertical-align: middle;
        }
        .bulk-match-img {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .bulk-match-name {
            font-weight: 600;
            font-size: 13px;
            color: #111827;
            margin: 0;
            white-space: normal;
            word-break: break-word;
        }
        .bulk-match-meta {
            font-size: 11px;
            color: #6b7280;
            margin: 0;
        }
        .bulk-match-price {
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
            padding-right: 10px;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('build/assets/jstree.min.js') }}"></script>
    <script>
        (function () {
            const brandOptions = @json($brands ?? []);
            const flatCategories = @json($flatCategories ?? []);
            const categoryTreeData = @json($categoryTree ?? []);

            let filterIndex = 0;
            let actionIndex = 0;

            function renderFilterRow(id) {
                const row = document.createElement('div');
                row.className = 'bulk-filter-row';
                row.dataset.id = id;

                row.innerHTML = `
                    <select class="form-control input-sm bulk-attr" style="width: 32%;">
                        <option value="name">Ürün Adı</option>
                        <option value="brand">Marka</option>
                        <option value="category">Kategori</option>
                        <option value="price">Fiyat</option>
                    </select>
                    <select class="form-control input-sm bulk-op" style="width: 25%;">
                        <option value="contains">İçerir</option>
                    </select>
                    <div class="bulk-value-wrap" style="flex:1;">
                        <input type="text" class="form-control input-sm bulk-val" />
                    </div>
                    <button type="button" class="bulk-remove" title="Sil">&times;</button>
                `;

                document.getElementById('bulk-filter-rows').appendChild(row);

                const attrSelect = row.querySelector('.bulk-attr');
                attrSelect.addEventListener('change', function () {
                    updateFilterRowValue(row, this.value);
                });

                row.querySelector('.bulk-remove').addEventListener('click', function () {
                    row.remove();
                    triggerPreview();
                });

                updateFilterRowValue(row, attrSelect.value);
            }

            function updateFilterRowValue(row, attr) {
                const opSelect = row.querySelector('.bulk-op');
                const valueWrap = row.querySelector('.bulk-value-wrap');
                valueWrap.innerHTML = '';

                if (attr === 'name') {
                    opSelect.innerHTML = '<option value="contains">İçerir</option>';
                    valueWrap.innerHTML = '<input type="text" class="form-control input-sm bulk-val" />';
                } else if (attr === 'brand') {
                    opSelect.innerHTML = '<option value="=">Eşittir</option>';
                    const select = document.createElement('select');
                    select.className = 'form-control input-sm bulk-val';
                    const optEmpty = document.createElement('option');
                    optEmpty.value = '';
                    optEmpty.textContent = 'Tümü';
                    select.appendChild(optEmpty);
                    Object.keys(brandOptions).forEach(function (id) {
                        const o = document.createElement('option');
                        o.value = id;
                        o.textContent = brandOptions[id];
                        select.appendChild(o);
                    });
                    valueWrap.appendChild(select);
                } else if (attr === 'category') {
                    opSelect.innerHTML = '<option value="in">İçinde</option>';
                    const select = document.createElement('select');
                    select.className = 'form-control input-sm bulk-val';
                    const optEmpty = document.createElement('option');
                    optEmpty.value = '';
                    optEmpty.textContent = 'Tümü';
                    select.appendChild(optEmpty);
                    Object.keys(flatCategories).forEach(function (id) {
                        const o = document.createElement('option');
                        o.value = id;
                        o.textContent = flatCategories[id];
                        select.appendChild(o);
                    });
                    valueWrap.appendChild(select);
                } else if (attr === 'price') {
                    opSelect.innerHTML = '<option value=">=">&ge;</option>' +
                        '<option value="<=">&le;</option>' +
                        '<option value=">">&gt;</option>' +
                        '<option value="<">&lt;</option>' +
                        '<option value="=">=</option>';
                    valueWrap.innerHTML = '<input type="number" step="0.01" class="form-control input-sm bulk-val" placeholder="Fiyat" />';
                }

                const inputs = valueWrap.querySelectorAll('input, select');
                inputs.forEach(function (el) {
                    el.addEventListener('change', triggerPreview);
                    el.addEventListener('keyup', debounce(triggerPreview, 400));
                });

                opSelect.addEventListener('change', triggerPreview);
                triggerPreview();
            }

            function renderActionRow(id) {
                const row = document.createElement('div');
                row.className = 'bulk-action-row';
                row.dataset.id = id;

                row.innerHTML = `
                    <select class="form-control input-sm bulk-action-attr" style="width: 32%;">
                        <option value="price">Fiyat</option>
                        <option value="special_price">Özel Fiyat</option>
                        <option value="primary_category">Varsayılan Kategori</option>
                        <option value="short_description">Kısa Açıklama</option>
                    </select>
                    <div class="bulk-action-mode-wrap" style="width: 28%;"></div>
                    <div class="bulk-action-value-wrap" style="flex:1;"></div>
                    <button type="button" class="bulk-remove" title="Sil">&times;</button>
                `;

                document.getElementById('bulk-action-rows').appendChild(row);

                const attrSelect = row.querySelector('.bulk-action-attr');
                attrSelect.addEventListener('change', function () {
                    updateActionRow(row, this.value);
                });

                row.querySelector('.bulk-remove').addEventListener('click', function () {
                    row.remove();
                    updateApplyButtonState();
                });

                updateActionRow(row, attrSelect.value);
            }

            function updateActionRow(row, attr) {
                const modeWrap = row.querySelector('.bulk-action-mode-wrap');
                const valueWrap = row.querySelector('.bulk-action-value-wrap');
                modeWrap.innerHTML = '';
                valueWrap.innerHTML = '';

                if (attr === 'price') {
                    modeWrap.innerHTML = '<select class="form-control input-sm bulk-action-mode">' +
                        '<option value="set">Değerini Belirle</option>' +
                        '<option value="increase_percent">Yüzde Arttır</option>' +
                        '<option value="decrease_percent">Yüzde Azalt</option>' +
                        '</select>';
                    valueWrap.innerHTML = '<input type="number" step="0.01" class="form-control input-sm bulk-action-value" />';
                } else if (attr === 'special_price') {
                    modeWrap.innerHTML = '<select class="form-control input-sm bulk-action-mode">' +
                        '<option value="set">Değerini Belirle</option>' +
                        '<option value="clear">Temizle</option>' +
                        '</select>';
                    valueWrap.innerHTML = '<input type="number" step="0.01" class="form-control input-sm bulk-action-value" />';
                } else if (attr === 'primary_category') {
                    modeWrap.innerHTML = '<select class="form-control input-sm bulk-action-mode">' +
                        '<option value="set">Kategorisini Belirle</option>' +
                        '</select>';
                    const select = document.createElement('select');
                    select.className = 'form-control input-sm bulk-action-value';
                    const optEmpty = document.createElement('option');
                    optEmpty.value = '';
                    optEmpty.textContent = 'Kategori Seçin';
                    select.appendChild(optEmpty);
                    Object.keys(flatCategories).forEach(function (id) {
                        const o = document.createElement('option');
                        o.value = id;
                        o.textContent = flatCategories[id];
                        select.appendChild(o);
                    });
                    valueWrap.appendChild(select);
                } else if (attr === 'short_description') {
                    modeWrap.innerHTML = '<select class="form-control input-sm bulk-action-mode">' +
                        '<option value="set">Metni Belirle</option>' +
                        '</select>';
                    valueWrap.innerHTML = '<textarea class="form-control input-sm bulk-action-value" rows="3"></textarea>';
                }

                const valueEl = valueWrap.querySelector('.bulk-action-value');
                if (valueEl) {
                    valueEl.addEventListener('change', updateApplyButtonState);
                    valueEl.addEventListener('keyup', debounce(updateApplyButtonState, 300));
                }

                updateApplyButtonState();
            }

            function collectFilters() {
                const rows = document.querySelectorAll('#bulk-filter-rows .bulk-filter-row');
                const result = [];
                rows.forEach(function (row) {
                    const attr = row.querySelector('.bulk-attr')?.value || null;
                    const op = row.querySelector('.bulk-op')?.value || null;
                    let val = null;

                    const input = row.querySelector('.bulk-val');
                    if (input) {
                        val = input.value;
                    }

                    if (!attr || op === null) return;

                    if (attr === 'name') {
                        if (!val || String(val).trim() === '') return;
                    }

                    if (attr === 'brand') {
                        if (!val || String(val) === '') return;
                    }

                    if (attr === 'category') {
                        if (!val || String(val) === '') return;
                    }

                    if (attr === 'price') {
                        if (val === null || val === '' || isNaN(Number(val))) return;
                    }

                    result.push({
                        attribute: attr,
                        operator: op,
                        value: val,
                    });
                });
                return result;
            }

            function collectActions() {
                const rows = document.querySelectorAll('#bulk-action-rows .bulk-action-row');
                const result = [];
                rows.forEach(function (row) {
                    const attr = row.querySelector('.bulk-action-attr')?.value || null;
                    const mode = row.querySelector('.bulk-action-mode')?.value || null;
                    const valEl = row.querySelector('.bulk-action-value');
                    let value = null;
                    if (valEl) {
                        if (valEl.tagName === 'TEXTAREA') {
                            value = valEl.value;
                        } else if (valEl.type === 'number') {
                            value = valEl.value;
                        } else {
                            value = valEl.value;
                        }
                    }
                    if (!attr || !mode) return;
                    result.push({ attribute: attr, mode: mode, value: value });
                });
                return result;
            }

            function showAlert(type, message) {
                const el = document.getElementById('bulk-editor-alert');
                if (!el) return;
                el.className = 'alert alert-' + type;
                el.textContent = message || '';
                el.style.display = message ? '' : 'none';
            }

            function updateApplyButtonState() {
                const btn = document.getElementById('bulk-apply');
                const actions = collectActions();
                btn.disabled = actions.length === 0;
            }

            function debounce(fn, wait) {
                let t;
                return function () {
                    const ctx = this, args = arguments;
                    clearTimeout(t);
                    t = setTimeout(function () { fn.apply(ctx, args); }, wait);
                };
            }

            const triggerPreview = debounce(function () {
                const filters = collectFilters();
                const combine = document.querySelector('input[name="bulk-filter-combine"]:checked')?.value || 'and';

                axios.get('{{ route('admin.products.bulk_preview') }}', {
                    params: {
                        filters: filters,
                        combine: combine,
                    }
                }).then(function (res) {
                    const data = res.data || {};
                    const total = data.total || 0;
                    const items = data.items || [];

                    const summary = document.getElementById('bulk-matching-summary');
                    if (total === 0) {
                        summary.className = 'alert alert-warning';
                        summary.textContent = 'Verilen Filtrelere Göre Ürün Bulunamadı';
                    } else {
                        summary.className = 'alert alert-success';
                        summary.textContent = total + ' Ürün Etkilenecek';
                    }

                    const tbody = document.querySelector('#bulk-matching-table tbody');
                    tbody.innerHTML = '';
                    items.forEach(function (item) {
                        const tr = document.createElement('tr');
                        const priceText = item.price_formatted || (item.price !== null ? String(item.price) : '');
                        const img = item.image || '';
                        const brand = item.brand || '';
                        const category = item.category || '';
                        tr.innerHTML = `
                            <td>${item.id}</td>
                            <td>
                                ${img ? `<img src="${img}" alt="" class="bulk-match-img">` : ''}
                            </td>
                            <td>
                                <p class="bulk-match-name" title="${_.escape(item.name || '')}">${_.escape(item.name || '')}</p>
                                <p class="bulk-match-meta">${_.escape(brand)}${brand && category ? ' · ' : ''}${_.escape(category)}</p>
                            </td>
                            <td class="text-right"><span class="bulk-match-price">${priceText}</span></td>
                        `;
                        tbody.appendChild(tr);
                    });
                }).catch(function () {
                    const summary = document.getElementById('bulk-matching-summary');
                    summary.className = 'alert alert-danger';
                    summary.textContent = 'Eşleşen Ürünler Yüklenirken Hata Oluştu';
                });
            }, 400);

            document.getElementById('bulk-add-filter').addEventListener('click', function () {
                renderFilterRow(filterIndex++);
            });

            document.getElementById('bulk-add-action').addEventListener('click', function () {
                renderActionRow(actionIndex++);
            });

            document.querySelectorAll('input[name="bulk-filter-combine"]').forEach(function (el) {
                el.addEventListener('change', triggerPreview);
            });

            document.getElementById('bulk-apply').addEventListener('click', function () {
                const btn = this;
                const filters = collectFilters();
                const actions = collectActions();
                const combine = document.querySelector('input[name="bulk-filter-combine"]:checked')?.value || 'and';

                if (actions.length === 0) {
                    showAlert('warning', 'Lütfen En Az Bir Güncelleme Aksiyonu Ekleyin');
                    return;
                }

                if (!window.confirm('Bu Filtrelerle Eşleşen Tüm Ürünleri Güncellemek İstediğinize Emin Misiniz? Bu İşlem Geri Alınamaz.')) {
                    return;
                }

                btn.disabled = true;

                axios.post('{{ route('admin.products.bulk_update') }}', {
                    filters: filters,
                    actions: actions,
                    combine: combine,
                }).then(function (res) {
                    const data = res.data || {};
                    showAlert('success', (data.updated || 0) + ' Ürün Başarıyla Güncellendi.');
                    triggerPreview();
                }).catch(function (err) {
                    showAlert('danger', 'Toplu Güncelleme Başarısız Oldu.');
                }).finally(function () {
                    btn.disabled = false;
                });
            });

            // initial rows
            renderFilterRow(filterIndex++);
            renderActionRow(actionIndex++);
            triggerPreview();
        })();
    </script>
@endpush

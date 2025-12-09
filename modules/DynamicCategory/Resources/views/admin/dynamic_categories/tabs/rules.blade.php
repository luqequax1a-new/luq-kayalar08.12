@php
    if (!isset($errors)) {
        $errors = app('view')->shared('errors') ?? new \Illuminate\Support\ViewErrorBag;
    }

    $existingRules = isset($dynamicCategory)
        ? $dynamicCategory->rules()->orderBy('group_no')->orderBy('position')->get()
        : collect();

    $tags = Modules\Tag\Entities\Tag::list();
    $brands = Modules\Brand\Entities\Brand::list();

    // İlk açılışta en az 1 satır
    $initialRules = $existingRules->map(function ($rule) {
        return [
            'group_no' => $rule->group_no,
            'field'    => $rule->field,
            'operator' => $rule->operator,
            'boolean'  => $rule->boolean,
            'value'    => $rule->value,
        ];
    })->values();
@endphp

<div class="tab-pane" id="rules">
    <div class="dynamic-category-rules-wrapper">
        <h4 class="tab-content-title" style="margin-top:0;">
            {{ trans('dynamic_category::dynamic_categories.rules_label') }}
        </h4>

        <p class="text-muted" style="margin-bottom: 15px;">
            {{ trans('dynamic_category::dynamic_categories.rules_help') }}
        </p>

        {{-- Kural modu: Tüm koşulları / En az bir koşulu --}}
        <div class="panel panel-default" style="margin-bottom: 15px;">
            <div class="panel-heading">
                <strong>{{ trans('dynamic_category::dynamic_categories.rules_mode.title') }}</strong>
            </div>
            <div class="panel-body">
                @php
                    $currentRulesMode = old('rules_mode', $dynamicCategory->rules_mode ?? 'all');
                @endphp

                <div class="row dc-rules-mode-row">
                    <div class="col-sm-6">
                        <div class="dc-rules-mode-option">
                            <label class="dc-rules-mode-label">
                                <input type="radio" name="rules_mode" value="all" {{ $currentRulesMode === 'any' ? '' : 'checked' }}>
                                <span class="dc-rules-mode-title">{{ trans('dynamic_category::dynamic_categories.rules_mode.all') }}</span>
                            </label>
                            <p class="dc-rules-mode-help">
                                {{ trans('dynamic_category::dynamic_categories.rules_mode.all_help') }}
                            </p>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="dc-rules-mode-option">
                            <label class="dc-rules-mode-label">
                                <input type="radio" name="rules_mode" value="any" {{ $currentRulesMode === 'any' ? 'checked' : '' }}>
                                <span class="dc-rules-mode-title">{{ trans('dynamic_category::dynamic_categories.rules_mode.any') }}</span>
                            </label>
                            <p class="dc-rules-mode-help">
                                {{ trans('dynamic_category::dynamic_categories.rules_mode.any_help') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kural listesi --}}
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>{{ trans('dynamic_category::dynamic_categories.rules_list_title') }}</strong>
            </div>

            <div class="panel-body">
                {{-- Başlık satırı (ikas tarzı) --}}
                <div class="row" style="font-weight:600; margin-bottom:5px;">
                    <div class="col-sm-3">{{ trans('dynamic_category::dynamic_categories.rules_column.condition') }}</div>
                    <div class="col-sm-2">{{ trans('dynamic_category::dynamic_categories.rules_column.method') }}</div>
                    <div class="col-sm-7">{{ trans('dynamic_category::dynamic_categories.rules_column.values') }}</div>
                </div>

                <div id="dynamic-category-rules"
                     data-initial='@json($initialRules)'>
                    {{-- JS ile doldurulacak --}}
                </div>

                <button type="button"
                        class="btn btn-default"
                        id="dynamic-category-add-rule"
                        style="margin-top: 10px;">
                    <i class="fa fa-plus"></i> {{ trans('dynamic_category::dynamic_categories.add_rule_button') }}
                </button>

                @if ($errors->has('rules'))
                    <span class="help-block text-red" style="margin-top: 10px; display:block;">
                        {{ $errors->first('rules') }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ŞABLON --}}
<script type="text/template" id="dynamic-category-rule-template">
    <div class="panel panel-default dynamic-category-rule-row" data-index="{INDEX}" style="margin-bottom:10px;">
        <div class="panel-body">
            <div class="row">
                {{-- Grup: gizli, her zaman 1 --}}
                <input type="hidden"
                       class="js-rule-group"
                       name="rules[{INDEX}][group_no]"
                       value="{GROUP_NO}">

                {{-- Alan --}}
                <div class="col-sm-3">
                    <div class="form-group">
                        <label class="control-label sr-only">
                            {{ trans('dynamic_category::dynamic_categories.rule_field') }}
                        </label>
                        <select class="form-control js-rule-field"
                                name="rules[{INDEX}][field]">
                            <option value="">--</option>
                            <option value="brand_id"{FIELD_BRAND_SELECTED}>{{ trans('dynamic_category::dynamic_categories.field.brand') }}</option>
                            <option value="price"{FIELD_PRICE_SELECTED}>{{ trans('dynamic_category::dynamic_categories.field.price') }}</option>
                            <option value="tag_id"{FIELD_TAG_SELECTED}>{{ trans('dynamic_category::dynamic_categories.field.tag') }}</option>
                            <option value="variant_value">Varyant Değeri</option>
                            <option value="discounted">İndirimli Ürünler</option>
                            <option value="created_at">Oluşturulma Tarihi</option>
                        </select>
                    </div>
                </div>

                {{-- Operatör (Ikas tarzı: İçeren / İçermeyen) --}}
                <div class="col-sm-2 js-operator-wrapper">
                    <div class="form-group">
                        <label class="control-label sr-only">
                            {{ trans('dynamic_category::dynamic_categories.rule_operator') }}
                        </label>

                        <select class="form-control js-rule-operator"
                                name="rules[{INDEX}][operator]">
                            <option value="IN"{OP_IN_SELECTED}>İçeren</option>
                            <option value="NOT_IN"{OP_NOTIN_SELECTED}>İçermeyen</option>
                        </select>
                    </div>
                </div>

                {{-- Operatör: Tag için sabit IN gösterimi --}}
                <div class="col-sm-2 js-operator-static-wrapper" style="display:none;">
                    <div class="form-group">
                        <label class="control-label sr-only">
                            {{ trans('dynamic_category::dynamic_categories.rule_operator') }}
                        </label>

                        <p class="form-control-static">IN</p>
                        <input type="hidden"
                               name="rules[{INDEX}][operator]"
                               value="IN">
                    </div>
                </div>

                {{-- Değer --}}
                <div class="col-sm-7">
                    <div class="form-group">

                        <label class="control-label sr-only">
                            {{ trans('dynamic_category::dynamic_categories.rule_value') }}
                        </label>

                        {{-- Price için: gizli değer + Minimum/Maksimum inputları --}}
                        <input type="hidden"
                               class="js-rule-value-price"
                               data-value-name="rules[{INDEX}][value]"
                               name=""
                               value="{VALUE_PRICE}">

                        <div class="dc-price-range" style="display:none;">
                            <div class="row">
                                <div class="col-xs-6" style="padding-right:3px;">
                                    <input type="number"
                                           class="form-control input-sm js-rule-price-min"
                                           placeholder="Minimum"
                                           min="0"
                                           step="0.01">
                                </div>
                                <div class="col-xs-6" style="padding-left:3px;">
                                    <input type="number"
                                           class="form-control input-sm js-rule-price-max"
                                           placeholder="Maksimum"
                                           min="0"
                                           step="0.01">
                                </div>
                            </div>
                        </div>

                        {{-- Brand için --}}
                        <div class="dc-search-select dc-search-select--brand" style="display:none;">
                            <div class="input-group" style="margin-bottom:6px;">
                                <input type="text" class="form-control input-sm js-dc-search-input" placeholder="Arama Yapınız">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            </div>
                            <select class="form-control js-rule-value-brands"
                                    name="rules[{INDEX}][value][]"
                                    multiple>
                                @foreach ($brands as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Varyant değeri için (şimdilik boş seçenekli) --}}
                        <div class="dc-search-select dc-search-select--variant" style="display:none;">
                            <div class="input-group" style="margin-bottom:6px;">
                                <input type="text" class="form-control input-sm js-dc-search-input" placeholder="Arama Yapınız">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            </div>
                            <select class="form-control js-rule-value-variants"
                                    name="rules[{INDEX}][value][]"
                                    multiple>
                                {{-- Varyant değerleri burada listelenecek --}}
                            </select>
                        </div>

                        {{-- Oluşturulma tarihi için --}}
                        <input type="hidden"
                               class="js-rule-value-created"
                               data-value-name="rules[{INDEX}][value]"
                               name=""
                               value="{VALUE_CREATED}">

                        <div class="dc-date-range dc-date-range--created" style="display:none;">
                            <div class="row">
                                <div class="col-xs-6" style="padding-right:3px;">
                                    <input type="date"
                                           class="form-control input-sm js-rule-value-created-from"
                                           placeholder="Başlangıç Tarihi">
                                </div>
                                <div class="col-xs-6" style="padding-left:3px;">
                                    <input type="date"
                                           class="form-control input-sm js-rule-value-created-to"
                                           placeholder="Bitiş Tarihi">
                                </div>
                            </div>
                        </div>

                        {{-- İndirimli ürünler için --}}
                        <div class="dc-static-text dc-static-text--discounted" style="display:none;">
                            <p class="form-control-static" style="padding-top:5px;">İndirim durumuna göre filtreleme.</p>
                        </div>

                        {{-- Tag için --}}
                        <div class="dc-search-select dc-search-select--tag" style="display:none;">
                            <div class="dc-tag-wrapper">
                                <div class="dc-tag-chips js-dc-tag-chips"></div>

                                <div class="input-group" style="margin-bottom:6px;">
                                    <input type="text" class="form-control input-sm js-dc-search-input" placeholder="Arama Yapınız">
                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                </div>

                                <select class="form-control js-rule-value-tags"
                                        name="rules[{INDEX}][value][]"
                                        multiple>
                                    @foreach ($tags as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top:5px;">
                    {{-- Bağlaç: gizli, global rules_mode'a göre belirlenecek --}}
                    <input type="hidden"
                           class="js-rule-boolean"
                           name="rules[{INDEX}][boolean]"
                           value="{BOOL_HIDDEN}">

                    <div class="col-sm-12 text-right" style="margin-top:5px;">
                        <button type="button"
                                class="btn btn-danger btn-sm js-rule-remove">
                            <i class="fa fa-trash"></i> Sil
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>

@push('scripts')
<script>
(function () {
    var container = document.getElementById('dynamic-category-rules');
    if (!container) return;

    var template = document.getElementById('dynamic-category-rule-template').innerHTML;
    var addButton = document.getElementById('dynamic-category-add-rule');
    var initialData = [];
    try {
        initialData = JSON.parse(container.getAttribute('data-initial') || '[]') || [];
    } catch (e) {
        initialData = [];
    }

    var currentIndex = 0;

    function renderRule(data) {
        var index = currentIndex++;
        var field = data.field || '';
        // IKAS mantığı: varsayılan operatör IN olmalı
        var operator = data.operator || 'IN';

        var boolean = (data.boolean || 'AND').toUpperCase();
        var isTag = field === 'tag_id';
        var isBrand = field === 'brand_id';

        var html = template
            .replace(/{INDEX}/g, index)
            .replace('{GROUP_NO}', data.group_no || 1)
            .replace('{FIELD_BRAND_SELECTED}', field === 'brand_id' ? ' selected' : '')
            .replace('{FIELD_PRICE_SELECTED}', field === 'price' ? ' selected' : '')
            .replace('{FIELD_TAG_SELECTED}', field === 'tag_id' ? ' selected' : '')
            .replace('{OP_IN_SELECTED}', operator === 'IN' ? ' selected' : '')
            .replace('{OP_NOTIN_SELECTED}', operator === 'NOT_IN' ? ' selected' : '')
            .replace('{VALUE_PRICE}', (isTag || isBrand) ? '' : (data.field === 'created_at' ? '' : (data.value || '')))
            .replace('{VALUE_CREATED}', data.field === 'created_at' ? (data.value || '') : '')
            .replace('{BOOL_HIDDEN}', boolean);

        var wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        var row = wrapper.firstElementChild;

        // Tag seçiliyse UI toggle
        applyFieldMode(row, field, data.value);

        container.appendChild(row);
    }

    function applyFieldMode(row, field, rawValue) {
        var opWrapper = row.querySelector('.js-operator-wrapper');
        var opStaticWrapper = row.querySelector('.js-operator-static-wrapper');

        var priceInput = row.querySelector('.js-rule-value-price');
        var priceRange = row.querySelector('.dc-price-range');

        var brandWrapper = row.querySelector('.dc-search-select--brand');
        var tagWrapper = row.querySelector('.dc-search-select--tag');
        var variantWrapper = row.querySelector('.dc-search-select--variant');
        var createdRange = row.querySelector('.dc-date-range--created');
        var discountedStatic = row.querySelector('.dc-static-text--discounted');
        var brandSelect = row.querySelector('.js-rule-value-brands');
        var tagSelect = row.querySelector('.js-rule-value-tags');
        var createdHidden = row.querySelector('.js-rule-value-created');

        if (!priceInput || !tagSelect || !brandSelect || !brandWrapper || !tagWrapper) return;

        // Varsayılan olarak her iki hidden input'un name'ini temizle, aktif alana birazdan set edeceğiz.
        if (priceInput) priceInput.name = '';
        if (createdHidden) createdHidden.name = '';

        // Tüm alanlar için aynı operatör mantığı: sadece IN / NOT_IN, default IN
        var opSelect = row.querySelector('.js-rule-operator');
        if (opSelect) {
            Array.prototype.forEach.call(opSelect.options, function (opt) {
                opt.disabled = false;
                opt.style.display = '';
            });

            if (!opSelect.value || (opSelect.value !== 'IN' && opSelect.value !== 'NOT_IN')) {
                opSelect.value = 'IN';
            }
        }

        if (opWrapper) opWrapper.style.display = '';
        if (opStaticWrapper) opStaticWrapper.style.display = 'none';

        if (field === 'tag_id') {
            priceInput.style.display = 'none';
            if (priceRange) priceRange.style.display = 'none';

            brandWrapper.style.display = 'none';
            tagWrapper.style.display = '';
            if (variantWrapper) variantWrapper.style.display = 'none';
            if (createdRange) createdRange.style.display = 'none';
            if (discountedStatic) discountedStatic.style.display = 'none';

            // Önceden kaydedilmiş değerleri (array veya JSON/string) seç
            if (rawValue) {
                var selected = [];
                if (Array.isArray(rawValue)) {
                    selected = rawValue.map(String);
                } else if (typeof rawValue === 'string') {
                    try {
                        var decoded = JSON.parse(rawValue);
                        if (Array.isArray(decoded)) {
                            selected = decoded.map(String);
                        } else if (rawValue) {
                            selected = [rawValue];
                        }
                    } catch (e) {
                        selected = [rawValue];
                    }
                }
                Array.prototype.forEach.call(tagSelect.options, function (opt) {
                    opt.selected = selected.indexOf(String(opt.value)) !== -1;
                });
            }
            updateTagChips(row);
        } else if (field === 'brand_id') {
            priceInput.style.display = 'none';
            if (priceRange) priceRange.style.display = 'none';

            tagWrapper.style.display = 'none';
            brandWrapper.style.display = '';
            if (variantWrapper) variantWrapper.style.display = 'none';
            if (createdRange) createdRange.style.display = 'none';
            if (discountedStatic) discountedStatic.style.display = 'none';

            if (rawValue) {
                var brandSelected = [];
                if (Array.isArray(rawValue)) {
                    brandSelected = rawValue.map(String);
                } else if (typeof rawValue === 'string') {
                    try {
                        var brandDecoded = JSON.parse(rawValue);
                        if (Array.isArray(brandDecoded)) {
                            brandSelected = brandDecoded.map(String);
                        } else if (rawValue) {
                            brandSelected = [rawValue];
                        }
                    } catch (e) {
                        brandSelected = [rawValue];
                    }
                }
                Array.prototype.forEach.call(brandSelect.options, function (opt) {
                    opt.selected = brandSelected.indexOf(String(opt.value)) !== -1;
                });
            }
        } else if (field === 'price') {
            if (priceRange) priceRange.style.display = '';
            brandWrapper.style.display = 'none';
            tagWrapper.style.display = 'none';
            if (variantWrapper) variantWrapper.style.display = 'none';
            if (createdRange) createdRange.style.display = 'none';
            if (discountedStatic) discountedStatic.style.display = 'none';
            // Eski kaydedilmiş "min-max" değeri varsa Minimum/Maksimum alanlarına dağıt
            if (priceInput && priceInput.value && priceRange) {
                var parts = String(priceInput.value).split('-');
                var minInput = row.querySelector('.js-rule-price-min');
                var maxInput = row.querySelector('.js-rule-price-max');
                if (minInput) minInput.value = parts[0] || '';
                if (maxInput) maxInput.value = parts[1] || '';
            }

            // Bu satır aktif price alanı, rules[*][value] sadece buradan gelsin
            if (priceInput && priceInput.dataset && priceInput.dataset.valueName) {
                priceInput.name = priceInput.dataset.valueName;
            }

        } else if (field === 'variant_value') {
            priceInput.style.display = 'none';
            if (priceRange) priceRange.style.display = 'none';

            brandWrapper.style.display = 'none';
            tagWrapper.style.display = 'none';
            if (variantWrapper) variantWrapper.style.display = '';
            if (createdRange) createdRange.style.display = 'none';
            if (discountedStatic) discountedStatic.style.display = 'none';
        } else if (field === 'discounted') {
            priceInput.style.display = 'none';
            if (priceRange) priceRange.style.display = 'none';

            brandWrapper.style.display = 'none';
            tagWrapper.style.display = 'none';
            if (variantWrapper) variantWrapper.style.display = 'none';
            if (createdRange) createdRange.style.display = 'none';
            if (discountedStatic) discountedStatic.style.display = '';
        } else if (field === 'created_at') {
            priceInput.style.display = 'none';
            if (priceRange) priceRange.style.display = 'none';

            brandWrapper.style.display = 'none';
            tagWrapper.style.display = 'none';
            if (variantWrapper) variantWrapper.style.display = 'none';
            if (createdRange) createdRange.style.display = '';
            if (discountedStatic) discountedStatic.style.display = 'none';
            // Eski kaydedilmiş "from|to" değerini input'lara dağıt
            if (createdHidden && createdHidden.value && createdRange) {
                var partsCreated = String(createdHidden.value).split('|');
                var fromInput = row.querySelector('.js-rule-value-created-from');
                var toInput = row.querySelector('.js-rule-value-created-to');
                if (fromInput) fromInput.value = partsCreated[0] || '';
                if (toInput) toInput.value = partsCreated[1] || '';
            }

        } else {
            priceInput.style.display = 'none';
            if (priceRange) priceRange.style.display = 'none';
            brandWrapper.style.display = 'none';
            tagWrapper.style.display = 'none';

            if (variantWrapper) variantWrapper.style.display = 'none';
            if (createdRange) createdRange.style.display = 'none';
            if (discountedStatic) discountedStatic.style.display = 'none';
        }
    }

    function updateTagChips(row) {
        var select = row.querySelector('.js-rule-value-tags');
        var chipsContainer = row.querySelector('.js-dc-tag-chips');
        if (!select || !chipsContainer) return;

        chipsContainer.innerHTML = '';

        Array.prototype.forEach.call(select.options, function (opt) {
            if (opt.selected) {
                var span = document.createElement('span');
                span.className = 'dc-tag-chip';
                span.textContent = opt.text;
                chipsContainer.appendChild(span);
            }
        });
    }

    function init() {
        if (initialData.length === 0) {
            // Yeni satır için varsayılan operatör IN
            renderRule({ group_no: 1, field: '', operator: 'IN', boolean: 'AND', value: '' });

        } else {
            initialData.forEach(function (rule) {
                renderRule(rule);
            });
        }
    }

    container.addEventListener('change', function (e) {
        var fieldSelect = e.target.closest('.js-rule-field');
        if (fieldSelect) {
            var row = fieldSelect.closest('.dynamic-category-rule-row');
            applyFieldMode(row, fieldSelect.value, null);
            return;
        }

        if (e.target.classList.contains('js-rule-value-tags')) {
            var row2 = e.target.closest('.dynamic-category-rule-row');
            updateTagChips(row2);
        }

    });

    // Basit arama: marka/etiket select'lerindeki seçenekleri filtrele
    container.addEventListener('input', function (e) {
        if (!e.target.classList.contains('js-dc-search-input')) return;

        var wrapper = e.target.closest('.dc-search-select');
        if (!wrapper) return;

        var select = wrapper.querySelector('select');
        if (!select) return;

        var term = (e.target.value || '').toLowerCase();

        Array.prototype.forEach.call(select.options, function (opt) {
            if (!term) {
                opt.style.display = '';
                return;
            }

            var text = (opt.text || '').toLowerCase();
            opt.style.display = text.indexOf(term) !== -1 ? '' : 'none';
        });
    });

    container.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-rule-remove');
        if (btn) {
            var rows = container.querySelectorAll('.dynamic-category-rule-row');
            if (rows.length <= 1) return; // en az 1 satır kalsın
            btn.closest('.dynamic-category-rule-row').remove();
        }
    });

    if (addButton) {
        addButton.addEventListener('click', function () {
            // Yeni eklenen satırlarda da varsayılan operatör IN olsun
            renderRule({ group_no: 1, field: '', operator: 'IN', boolean: 'AND', value: '' });
        });
    }

    function attachFormSerializer(formId) {
        var form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', function () {
            var rows = container.querySelectorAll('.dynamic-category-rule-row');
            Array.prototype.forEach.call(rows, function (row) {
                var fieldSelect = row.querySelector('.js-rule-field');
                if (!fieldSelect) return;

                // Fiyat alanı için min-max stringi üret
                if (fieldSelect.value === 'price') {
                    var minInput = row.querySelector('.js-rule-price-min');
                    var maxInput = row.querySelector('.js-rule-price-max');
                    var hidden = row.querySelector('.js-rule-value-price');

                    if (hidden) {
                        // Her ihtimale karşı name'i burada da set et (applyFieldMode kaçırmış olsa bile)
                        if (hidden.dataset && hidden.dataset.valueName) {
                            hidden.name = hidden.dataset.valueName;
                        }

                        var minVal = minInput && minInput.value !== '' ? minInput.value : '';
                        var maxVal = maxInput && maxInput.value !== '' ? maxInput.value : '';

                        if (minVal === '' && maxVal === '') {
                            hidden.value = '';
                        } else {
                            hidden.value = (minVal || '0') + '-' + (maxVal || '0');
                        }
                    }
                }

                // Oluşturulma tarihi için from|to stringi üret
                if (fieldSelect.value === 'created_at') {
                    var fromInput = row.querySelector('.js-rule-value-created-from');
                    var toInput = row.querySelector('.js-rule-value-created-to');
                    var createdHidden = row.querySelector('.js-rule-value-created');

                    if (createdHidden) {
                        if (createdHidden.dataset && createdHidden.dataset.valueName) {
                            createdHidden.name = createdHidden.dataset.valueName;
                        }

                        var fromVal = fromInput && fromInput.value !== '' ? fromInput.value : '';
                        var toVal = toInput && toInput.value !== '' ? toInput.value : '';

                        if (fromVal === '' && toVal === '') {
                            createdHidden.value = '';
                        } else {
                            createdHidden.value = (fromVal || '') + '|' + (toVal || '');
                        }
                    }
                }
            });
        });
    }

    attachFormSerializer('dynamic-category-create-form');
    attachFormSerializer('dynamic-category-edit-form');

    init();
})();
</script>
<style>
    /* Scoped styling for dynamic category page to mimic ikas-like cards */
    #dynamic-category-create-form,
    #dynamic-category-edit-form {
        background-color: #f5f7fb;
        padding: 24px 0 40px;
    }

    #dynamic-category-create-form .tab-pane,
    #dynamic-category-edit-form .tab-pane {
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.05);
        padding: 20px 24px 24px;
        margin: 0 auto 16px;
        max-width: 1120px;
    }

    .dynamic-category-rules-wrapper .panel {
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        border-color: #e5e7eb;
        margin-bottom: 16px;
    }

    .dynamic-category-rules-wrapper .panel-heading {
        background-color: #f9fafb;
        border-bottom-color: #e5e7eb;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
    }

    .dynamic-category-rules-wrapper .panel-body {
        padding: 12px 18px 18px;
    }

    .dynamic-category-rules-wrapper .dynamic-category-rule-row {
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        margin-bottom: 10px;
    }

    .dynamic-category-rules-wrapper .dynamic-category-rule-row .form-group {
        margin-bottom: 6px;
    }

    .dynamic-category-rules-wrapper .dynamic-category-rule-row select,
    .dynamic-category-rules-wrapper .dynamic-category-rule-row input[type="text"],
    .dynamic-category-rules-wrapper .dynamic-category-rule-row input[type="number"],
    .dynamic-category-rules-wrapper .dynamic-category-rule-row input[type="date"] {
        height: 38px;
        padding: 6px 10px;
        font-size: 13px;
        border-radius: 6px;
        border-color: #d1d5db;
    }

    .dynamic-category-rules-wrapper .dynamic-category-rule-row select:focus,
    .dynamic-category-rules-wrapper .dynamic-category-rule-row input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.35);
        outline: 0;
    }

    .dynamic-category-rules-wrapper .row[style*='font-weight:600'] {
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 6px;
        margin-bottom: 10px !important;
        font-size: 12px;
        color: #6b7280;
    }

    .dynamic-category-rules-wrapper .dc-search-select .input-group-addon {
        border-radius: 0 6px 6px 0;
    }

    .dynamic-category-rules-wrapper .dc-search-select select {
        min-height: 80px;
    }

    .dynamic-category-rules-wrapper .js-operator-static-wrapper .form-control-static {
        padding-top: 8px;
        font-weight: 500;
        color: #4b5563;
    }

    .dynamic-category-rules-wrapper .btn-default#dynamic-category-add-rule {
        border-radius: 6px;
        padding: 6px 14px;
        font-size: 13px;
        border-color: #d1d5db;
    }

    .dynamic-category-rules-wrapper .btn-danger.btn-sm {
        padding: 4px 10px;
        font-size: 12px;
        border-radius: 4px;
    }

    .dynamic-category-rules-wrapper .dc-tag-wrapper {
        background-color: #f9fafb;
        border-radius: 6px;
        padding: 6px 8px 8px;
    }

    .dynamic-category-rules-wrapper .dc-tag-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-bottom: 4px;
    }

    .dynamic-category-rules-wrapper .dc-tag-chip {
        background-color: #e5e7eb;
        border-radius: 999px;
        padding: 2px 8px;
        font-size: 11px;
        color: #374151;
        max-width: 160px;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    /* Radios for "Koşul Uygulama Şekli" must stay visible and clickable */
    .dynamic-category-rules-wrapper input[type="radio"] {
        display: inline-block !important;
        margin-right: 6px;
        position: static;
        opacity: 1;
    }
</style>
@endpush
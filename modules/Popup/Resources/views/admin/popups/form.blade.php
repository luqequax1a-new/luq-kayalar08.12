<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('popup::popup.admin_title') }}</h3>
    </div>

    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Genel Bilgiler</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label>{{ trans('popup::popup.fields.name') }}</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $popup->name) }}" required>
                        </div>

                        <div class="form-group">
                            <label>{{ trans('popup::popup.fields.status') }}</label>
                            <div class="switch">
                                <input type="checkbox" name="status" id="popup-status" value="1" {{ old('status', $popup->status) ? 'checked' : '' }}>
                                <label for="popup-status"></label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Hedefleme (Sayfa Seçimi)</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label>{{ trans('popup::popup.fields.target_scope') }}</label>
                            <select name="target_scope" id="target_scope" class="form-control">
                                @foreach(trans('popup::popup.target_scopes') as $key => $label)
                                    <option value="{{ $key }}" {{ old('target_scope', $popup->target_scope) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        @php
                            $selectedCategories = old('target_categories', data_get($popup->targeting ?? [], 'categories', []));
                        @endphp

                        <div class="form-group" id="target-categories" style="display:none;">
                            <label>{{ trans('popup::popup.fields.target_categories') }}</label>
                            <div id="popup-category-tree" class="category-tree"></div>
                            <div id="popup-category-selected"></div>
                            <p class="help-block">Gösterilmesini istediğiniz kategorileri ağaçtan seçin.</p>
                        </div>

                        <div class="form-group" id="target-products" style="display:none;">
                            <label>{{ trans('popup::popup.fields.target_products') }}</label>
                            <input type="text" name="target_products[]" class="form-control" placeholder="Ürün ID / slug (geçici basit alan)">
                            <p class="help-block">Mevcut ürün arama bileşeni ile entegre edilebilir.</p>
                        </div>

                        <div class="form-group" id="target-custom-urls" style="display:none;">
                            <label>{{ trans('popup::popup.fields.target_custom_urls') }}</label>
                            <textarea name="target_custom_urls[0][value]" class="form-control" rows="3" placeholder="/kampanya veya /tr/indirimler gibi URL parçaları"></textarea>
                            <p class="help-block">Basit kullanım için; tip: contains/starts_with alanları JSON olarak saklanır.</p>
                        </div>
                    </div>
                </div>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Cihaz &amp; Tetikleyici</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label>{{ trans('popup::popup.fields.device') }}</label>
                            <div>
                                @foreach(['desktop','mobile','both'] as $device)
                                    <label class="radio-inline">
                                        <input type="radio" name="device" value="{{ $device }}" {{ old('device', $popup->device) === $device ? 'checked' : '' }}>
                                        {{ trans('popup::popup.device.' . $device) }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ trans('popup::popup.fields.trigger_type') }}</label>
                            <select name="trigger_type" id="trigger_type" class="form-control">
                                @foreach(trans('popup::popup.trigger_types') as $key => $label)
                                    <option value="{{ $key }}" {{ old('trigger_type', $popup->trigger_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" id="trigger-on-load" style="display:none;">
                            <label>{{ trans('popup::popup.fields.trigger_value_seconds') }}</label>
                            <input type="number" name="trigger_value" class="form-control" min="0" value="{{ old('trigger_value', $popup->trigger_value) }}">
                        </div>
                    </div>
                </div>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Gösterim Sıklığı</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label>{{ trans('popup::popup.fields.frequency_type') }}</label>
                            <select name="frequency_type" id="frequency_type" class="form-control">
                                @foreach(trans('popup::popup.frequency_types') as $key => $label)
                                    <option value="{{ $key }}" {{ old('frequency_type', $popup->frequency_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" id="frequency-days" style="display:none;">
                            <label>{{ trans('popup::popup.fields.frequency_value_days') }}</label>
                            <input type="number" name="frequency_value" class="form-control" min="1" value="{{ old('frequency_value', $popup->frequency_value) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">İçerik</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label>{{ trans('popup::popup.fields.headline') }}</label>
                            <input type="text" name="headline" class="form-control" value="{{ old('headline', $popup->headline) }}">
                        </div>

                        <div class="form-group">
                            <label>{{ trans('popup::popup.fields.subheadline') }}</label>
                            <input type="text" name="subheadline" class="form-control" value="{{ old('subheadline', $popup->subheadline) }}">
                        </div>

                        <div class="form-group">
                            <label>{{ trans('popup::popup.fields.body') }}</label>
                            <textarea name="body" class="form-control" rows="5">{{ old('body', $popup->body) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>{{ trans('popup::popup.fields.cta_label') }}</label>
                            <input type="text" name="cta_label" class="form-control" value="{{ old('cta_label', $popup->cta_label) }}">
                        </div>

                        <div class="form-group">
                            <label>{{ trans('popup::popup.fields.cta_url') }}</label>
                            <input type="text" name="cta_url" class="form-control" value="{{ old('cta_url', $popup->cta_url) }}">
                        </div>

                        <div class="form-group">
                            <label>{{ trans('popup::popup.fields.close_label') }}</label>
                            <input type="text" name="close_label" class="form-control" value="{{ old('close_label', $popup->close_label ?: 'Tıkla Pencereyi Kapat') }}">
                        </div>

                        <div class="form-group">
                            @include('media::admin.image_picker.single', [
                                'title' => trans('popup::popup.fields.image'),
                                'inputName' => 'image_path',
                                'file' => $imageFile,
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="box-footer">
        <button type="submit" class="btn btn-primary">Kaydet</button>
        <a href="{{ route('admin.popups.index') }}" class="btn btn-default">İptal</a>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        function ensureJsTree(callback){
            if (window.$ && $.fn && $.fn.jstree) { callback(); return; }
            function load(src, onload){ var s=document.createElement('script'); s.src=src; s.onload=onload; document.head.appendChild(s); }
            load('https://cdn.jsdelivr.net/npm/jstree@3.3.12/dist/jstree.min.js', callback);
        }

        function initPopupCategoryTree(selected) {
            var el = $('#popup-category-tree');
            if (!el.length) return null;

            el.jstree({
                core: { data: { url: '{{ route('admin.seo.categories.tree') }}' }, check_callback: true },
                plugins: ['checkbox']
            });

            el.on('loaded.jstree', function(){
                el.jstree('open_all');
                if (Array.isArray(selected) && selected.length) {
                    selected.forEach(function(id){ el.jstree('select_node', id.toString()); });
                }
                syncPopupCategorySelection(el);
            });

            el.on('changed.jstree', function(){
                syncPopupCategorySelection(el);
            });

            return el;
        }

        function syncPopupCategorySelection(treeEl) {
            var selected = (treeEl && treeEl.jstree) ? treeEl.jstree('get_selected') : [];
            var container = document.getElementById('popup-category-selected');
            if (!container) return;

            container.innerHTML = '';
            selected.forEach(function(id){
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'target_categories[]';
                input.value = id;
                container.appendChild(input);
            });
        }

        function toggleTargetFields() {
            var scope = document.getElementById('target_scope').value;
            document.getElementById('target-categories').style.display = scope === 'category' ? 'block' : 'none';
            document.getElementById('target-products').style.display = scope === 'product' ? 'block' : 'none';
            document.getElementById('target-custom-urls').style.display = scope === 'custom_url' ? 'block' : 'none';
        }

        function toggleTriggerFields() {
            var type = document.getElementById('trigger_type').value;
            document.getElementById('trigger-on-load').style.display = type === 'on_load_delay' ? 'block' : 'none';
        }

        function toggleFrequencyFields() {
            var type = document.getElementById('frequency_type').value;
            document.getElementById('frequency-days').style.display = (type === 'per_days' || type === 'per_hours') ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', function () {
            var scopeEl = document.getElementById('target_scope');
            var triggerEl = document.getElementById('trigger_type');
            var freqEl = document.getElementById('frequency_type');
            var popupCatTree = null;

            if (scopeEl) {
                scopeEl.addEventListener('change', function () {
                    toggleTargetFields();

                    if (this.value === 'category' && !popupCatTree) {
                        ensureJsTree(function(){
                            popupCatTree = initPopupCategoryTree(@json($selectedCategories));
                        });
                    }
                });
            }
            if (triggerEl) {
                triggerEl.addEventListener('change', toggleTriggerFields);
            }
            if (freqEl) {
                freqEl.addEventListener('change', toggleFrequencyFields);
            }

            toggleTargetFields();
            toggleTriggerFields();
            toggleFrequencyFields();

            // Initialize category tree only when needed on first load
            if (document.getElementById('target_scope') && document.getElementById('target_scope').value === 'category') {
                ensureJsTree(function(){
                    popupCatTree = initPopupCategoryTree(@json($selectedCategories));
                });
            } else {
                // still ensure hidden inputs reflect any old() on first load
                var container = document.getElementById('popup-category-selected');
                if (container) {
                    var initial = @json($selectedCategories);
                    if (Array.isArray(initial) && initial.length) {
                        initial.forEach(function(id){
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'target_categories[]';
                            input.value = id;
                            container.appendChild(input);
                        });
                    }
                }
            }
        });
    })();
</script>
@endpush

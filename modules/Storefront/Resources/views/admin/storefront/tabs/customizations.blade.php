<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Grid Özelleştirmeleri</h3>
            </div>

            <div class="box-body">
                <div class="form-group">
                    <label for="storefront_grid_variant_badge_enabled">
                        Grid ekranında varyant badge aktif?
                    </label>

                    <select name="storefront_grid_variant_badge_enabled" id="storefront_grid_variant_badge_enabled" class="form-control select2">
                        <option value="0" {{ old('storefront_grid_variant_badge_enabled', setting('storefront_grid_variant_badge_enabled')) ? '' : 'selected' }}>Hayır</option>
                        <option value="1" {{ old('storefront_grid_variant_badge_enabled', setting('storefront_grid_variant_badge_enabled')) ? 'selected' : '' }}>Evet</option>
                    </select>

                    <span class="help-block">Aktif olduğunda, grid/list ekranlarında ürün görselinin sağ üst köşesinde minimal varyant etiketi (ör. "5 Renk Seçeneği") gösterilir.</span>
                </div>
            </div>
        </div>
    </div>
</div>

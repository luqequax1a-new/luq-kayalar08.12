@extends('admin::layout')

@section('title', 'Diğer Özelleştirmeler')

@component('admin::components.page.header')
    @slot('title', 'Diğer Özelleştirmeler')
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="form-horizontal" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}
        <input type="hidden" name="context" value="customizations">

        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="storefront_grid_variant_badge_enabled">
                                Grid ekranında varyant badge aktif?
                            </label>

                            <select name="storefront_grid_variant_badge_enabled" id="storefront_grid_variant_badge_enabled" class="form-control select2">
                                <option value="0" {{ old('storefront_grid_variant_badge_enabled', setting('storefront_grid_variant_badge_enabled')) ? '' : 'selected' }}>Hayır</option>
                                <option value="1" {{ old('storefront_grid_variant_badge_enabled', setting('storefront_grid_variant_badge_enabled')) ? 'selected' : '' }}>Evet</option>
                            </select>

                            <span class="help-block">
                                Aktif olduğunda, grid/list ekranlarında ürün görselinin sağ üst köşesinde minimal varyant etiketi (ör. "5 Renk Seçeneği") gösterilir.
                            </span>
                        </div>

                        {{-- Buraya ileride başka basit checkbox/seçim alanları da ekleyebiliriz. --}}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </div>
        </div>
    </form>
@endsection

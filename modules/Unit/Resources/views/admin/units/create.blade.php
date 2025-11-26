@extends('admin::layout')

@section('title', 'Yeni Birim Ekle')

@section('content')
    <div class="row">
        <div class="col-md-8">
            

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Yeni Birim Ekle</h3>
                </div>

                <form method="POST" action="{{ route('admin.units.store') }}">
                    @csrf

                    <div class="card-body">
                        

                        <div class="form-group">
                            <label for="name">Ad</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}">
                        </div>

                        <div class="form-group">
                            <label for="label">Label</label>
                            <input type="text" name="label" id="label" class="form-control" value="{{ old('label') }}">
                        </div>

                        <div class="form-group">
                            <label for="info_top">Bilgi (üst)</label>
                            <textarea name="info_top" id="info_top" class="form-control" rows="3">{{ old('info_top') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="info_bottom">Not (alt)</label>
                            <textarea name="info_bottom" id="info_bottom" class="form-control" rows="3">{{ old('info_bottom') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="short_suffix">Kısaltma</label>
                            <input type="text" name="short_suffix" id="short_suffix" class="form-control" value="{{ old('short_suffix') }}" placeholder="ör. m, mt">
                        </div>

                        <div class="form-group">
                            <label for="min">Min</label>
                            <input type="number" step="0.01" name="min" id="min" class="form-control" value="{{ old('min', 1) }}">
                        </div>

                        <div class="form-group">
                            <label for="step">Step</label>
                            <input type="number" step="0.01" name="step" id="step" class="form-control" value="{{ old('step', 1) }}">
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" name="is_decimal_stock" id="is_decimal_stock" class="form-check-input" value="1" {{ old('is_decimal_stock') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_decimal_stock">Ondalıklı Stok</label>
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" name="is_default" id="is_default" class="form-check-input" value="1" {{ old('is_default') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default">Varsayılan Birim</label>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                        <a href="{{ route('admin.units.index') }}" class="btn btn-default">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

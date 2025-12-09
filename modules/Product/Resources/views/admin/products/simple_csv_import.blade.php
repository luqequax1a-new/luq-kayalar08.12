@extends('admin::layout')

@section('title', 'Basit Ürün CSV Import (Debug)')

@section('content')
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Basit Ürün CSV Import (Debug)</h3>
        </div>
        <div class="box-body">
            <form action="{{ route('admin.products.csv.simple_import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="csv-file">CSV Dosyası</label>
                    <input type="file" name="file" id="csv-file" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="mode">İşlem tipi</label>
                    <select name="mode" id="mode" class="form-control">
                        <option value="create">Yeni ürünler ekle</option>
                        <option value="update">Mevcut ürünleri güncelle</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="identifier">Güncelleme için eşleştirme alanı</label>
                    <select name="identifier" id="identifier" class="form-control">
                        <option value="id">ID</option>
                        <option value="sku">SKU</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="delimiter">Ayraç</label>
                    <select name="delimiter" id="delimiter" class="form-control">
                        <option value="comma">, (virgül)</option>
                        <option value="semicolon">; (noktalı virgül)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">CSV Yükle ve Çalıştır</button>
            </form>
        </div>
    </div>
@endsection

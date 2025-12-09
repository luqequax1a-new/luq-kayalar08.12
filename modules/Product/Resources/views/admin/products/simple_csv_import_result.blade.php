@extends('admin::layout')

@section('title', 'Basit Ürün CSV Import Sonuçları (Debug)')

@section('content')
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Basit Ürün CSV Import Sonuçları (Debug)</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <p>Toplam satır: <strong>{{ $total }}</strong></p>
                </div>
                <div class="col-md-3">
                    <p>Oluşturulan ürün: <strong>{{ $created }}</strong></p>
                </div>
                <div class="col-md-3">
                    <p>Güncellenen ürün: <strong>{{ $updated }}</strong></p>
                </div>
                <div class="col-md-3">
                    <p>Atlanan satır: <strong>{{ $skipped }}</strong></p>
                </div>
            </div>

            @if (!empty($rows))
                <hr>
                <h4>Satır Bazında İşlem Özeti</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Satır</th>
                            <th>İşlem</th>
                            <th>Kimlik (ID / SKU)</th>
                            <th>Mesaj</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td>{{ $row['row'] }}</td>
                                <td>
                                    @if ($row['action'] === 'create')
                                        Oluşturuldu
                                    @elseif ($row['action'] === 'update')
                                        Güncellendi
                                    @else
                                        Atlandı
                                    @endif
                                </td>
                                <td>{{ $row['identifier'] }}</td>
                                <td>{{ $row['message'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <a href="{{ route('admin.products.index') }}" class="btn btn-default">Ürün listesine dön</a>
        </div>
    </div>
@endsection

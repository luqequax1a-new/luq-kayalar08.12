@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', 'Birimler')

    <li class="active">Birimler</li>
@endcomponent

@section('content')
    <div class="table-responsive">
        <table class="table table-borderless">
            <thead>
                <tr>
                    <th>Kod</th>
                    <th>Ad</th>
                    <th>Label</th>
                    <th>Kısaltma</th>
                    <th>Min</th>
                    <th>Step</th>
                    <th>Varsayılan Miktar</th>
                    <th>Bilgi (üst)</th>
                    <th>Not (alt)</th>
                    <th>Ondalıklı Stok</th>
                    <th>Aksiyonlar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($units as $unit)
                    <tr>
                        <td>{{ $unit->code }}</td>
                        <td>{{ $unit->name }}</td>
                        <td>{{ $unit->label }}</td>
                        <td>{{ $unit->short_suffix }}</td>
                        <td>{{ rtrim(rtrim(number_format($unit->min, 2, '.', ''), '0'), '.') }}</td>
                        <td>{{ rtrim(rtrim(number_format($unit->step, 2, '.', ''), '0'), '.') }}</td>
                        <td>{{ $unit->default_qty !== null ? rtrim(rtrim(number_format($unit->default_qty, 2, '.', ''), '0'), '.') : '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($unit->info_top ?? '', 40) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($unit->info_bottom ?? '', 40) }}</td>
                        <td>
                            @if ($unit->is_decimal_stock)
                                <span class="badge badge-primary">Evet</span>
                            @else

                            @endif
                        </td>
                        
                        <td>
                            <a href="{{ route('admin.units.edit', $unit) }}" class="btn btn-primary btn-sm">Düzenle</a>
                            <button class="btn btn-danger btn-sm" data-url="{{ route('admin.units.destroy', $unit) }}" onclick="deleteUnit(this)">Sil</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <a href="{{ route('admin.units.create') }}" class="btn btn-primary">Yeni Birim Ekle</a>

    @push('scripts')
        <script>
            function deleteUnit(button) {
                if (!confirm('Silmek istediğinize emin misiniz?')) return;

                const url = button.getAttribute('data-url');
                axios.delete(url).then(() => {
                    window.location.reload();
                });
            }
        </script>
    @endpush
@endsection

@extends('admin::layout')

@section('title', 'Etiket - Görsel')

@push('styles')
    <style>
        .table-tag-badges th,
        .table-tag-badges td {
            vertical-align: middle !important;
            text-align: center;
        }
    </style>
@endpush

@section('content')
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Etiket - Görsel</h3>

            <a href="{{ route('admin.tag_badges.create') }}" class="btn btn-primary btn-sm pull-right">
                <i class="fa fa-plus"></i> Yeni Etiket Görseli
            </a>
        </div>

        <div class="box-body">
            <table class="table table-bordered table-striped text-center table-tag-badges">
                <thead>
                <tr style="vertical-align: middle;">
                    <th>Önizleme</th>
                    <th>Ad</th>
                    <th>Slug</th>
                    <th>Etiket</th>
                    <th>Liste</th>
                    <th>Detay</th>
                    <th>Öncelik</th>
                    <th>Durum</th>
                    <th width="140">İşlemler</th>
                </tr>
                </thead>
                <tbody>
                @forelse($badges as $badge)
                    <tr>
                        <td>
                            <div style="width:60px;height:60px;border:1px solid #eee;border-radius:4px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#fafafa;">
                                @if($badge->image_url)
                                    <img src="{{ $badge->image_url }}" alt="{{ $badge->name }}" style="max-height:56px;max-width:56px;display:block;">
                                @endif
                            </div>
                        </td>
                        <td>{{ $badge->name }}</td>
                        <td><code>{{ $badge->slug }}</code></td>
                        <td>
                            @if($badge->tag)
                                <span class="label label-default">{{ $badge->tag->name }}</span>
                            @endif
                        </td>
                        <td>
                            @if($badge->show_on_listing)
                                <span class="label label-success">{{ $badge->listing_position }}</span>
                            @else
                                <span class="label label-default">Yok</span>
                            @endif
                        </td>
                        <td>
                            @if($badge->show_on_detail)
                                <span class="label label-success">{{ $badge->detail_position }}</span>
                            @else
                                <span class="label label-default">Yok</span>
                            @endif
                        </td>
                        <td>{{ $badge->priority }}</td>
                        <td>
                            @if($badge->is_active)
                                <span class="label label-success">Aktif</span>
                            @else
                                <span class="label label-default">Pasif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.tag_badges.edit', $badge) }}" class="btn btn-default btn-sm">
                                <i class="fa fa-pencil"></i>
                            </a>

                            <form action="{{ route('admin.tag_badges.destroy', $badge) }}"
                                  method="POST" style="display:inline-block;"
                                  onsubmit="return confirm('Silinsin mi?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9">Kayıt yok.</td></tr>
                @endforelse
                </tbody>
            </table>

            {{ $badges->links() }}
        </div>
    </div>
@endsection

@extends('admin::layout')

@section('title', $badge->exists ? 'Etiket Görselini Düzenle' : 'Yeni Etiket Görseli')

@section('content')
    <form method="POST"
          action="{{ $badge->exists ? route('admin.tag_badges.update', $badge) : route('admin.tag_badges.store') }}"
          enctype="multipart/form-data">

        @csrf
        @if($badge->exists)
            @method('PUT')
        @endif

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    {{ $badge->exists ? 'Etiket Görselini Düzenle' : 'Yeni Etiket Görseli' }}
                </h3>
            </div>

            <div class="box-body">
                <div class="form-group">
                    <label>Ad</label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name', $badge->name) }}" required>
                </div>

                <div class="form-group">
                    <label>Slug (boş bırakılırsa otomatik)</label>
                    <input type="text" name="slug" class="form-control"
                           value="{{ old('slug', $badge->slug) }}">
                </div>

                <div class="form-group">
                    <label>Etiket (Tag)</label>
                    <select name="tag_id" class="form-control" required>
                        <option value="">Seçiniz</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}"
                                {{ (int) old('tag_id', $badge->tag_id) === $tag->id ? 'selected' : '' }}>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Rozet Görseli</label>
                    @if($badge->image_url)
                        <div style="margin-bottom:10px;">
                            <img src="{{ $badge->image_url }}" alt="" style="height:60px;">
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control">
                    <p class="help-block">PNG / SVG / WEBP / AVIF, şeffaf arkaplan, max 512KB.</p>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Aktif</label>
                            <div class="switch">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    id="tag-badge-active"
                                    {{ old('is_active', $badge->is_active) ? 'checked' : '' }}
                                >
                                <label for="tag-badge-active"></label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Liste sayfasında göster</label>
                            <div class="switch">
                                <input
                                    type="checkbox"
                                    name="show_on_listing"
                                    value="1"
                                    id="tag-badge-listing"
                                    {{ old('show_on_listing', $badge->show_on_listing) ? 'checked' : '' }}
                                >
                                <label for="tag-badge-listing"></label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Liste Pozisyonu</label>
                            <select name="listing_position" class="form-control">
                                @foreach(['top_left','top_right','bottom_left','bottom_right'] as $pos)
                                    <option value="{{ $pos }}"
                                        {{ old('listing_position', $badge->listing_position ?? 'top_left') === $pos ? 'selected' : '' }}>
                                        {{ $pos }}
                                    </option>
                                @endforeach
                            </select>
                            <div style="margin-top:8px; max-width:160px;">
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:2px; font-size:11px; text-align:center; border:1px solid #ddd;">
                                    <div style="padding:4px; border-right:1px solid #eee; border-bottom:1px solid #eee;">Sol Üst (top_left)</div>
                                    <div style="padding:4px; border-bottom:1px solid #eee;">Sağ Üst (top_right)</div>
                                    <div style="padding:4px; border-right:1px solid #eee; border-top:1px solid #eee;">Sol Alt (bottom_left)</div>
                                    <div style="padding:4px; border-top:1px solid #eee;">Sağ Alt (bottom_right)</div>
                                </div>
                                <p class="help-block" style="margin-top:4px;">Liste kartlarında rozetin görüneceği köşeyi gösterir.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Ürün detayında göster</label>
                            <div class="switch">
                                <input
                                    type="checkbox"
                                    name="show_on_detail"
                                    value="1"
                                    id="tag-badge-detail"
                                    {{ old('show_on_detail', $badge->show_on_detail) ? 'checked' : '' }}
                                >
                                <label for="tag-badge-detail"></label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Detay Pozisyonu</label>
                            <select name="detail_position" class="form-control">
                                @foreach(['top_left','top_right','bottom_left','bottom_right'] as $pos)
                                    <option value="{{ $pos }}"
                                        {{ old('detail_position', $badge->detail_position ?? 'top_left') === $pos ? 'selected' : '' }}>
                                        {{ $pos }}
                                    </option>
                                @endforeach
                            </select>
                            <div style="margin-top:8px; max-width:160px;">
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:2px; font-size:11px; text-align:center; border:1px solid #ddd;">
                                    <div style="padding:4px; border-right:1px solid #eee; border-bottom:1px solid #eee;">Sol Üst (top_left)</div>
                                    <div style="padding:4px; border-bottom:1px solid #eee;">Sağ Üst (top_right)</div>
                                    <div style="padding:4px; border-right:1px solid #eee; border-top:1px solid #eee;">Sol Alt (bottom_left)</div>
                                    <div style="padding:4px; border-top:1px solid #eee;">Sağ Alt (bottom_right)</div>
                                </div>
                                <p class="help-block" style="margin-top:4px;">Ürün detay sayfasındaki büyük görsel üzerinde görünecek köşe.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Öncelik</label>
                    <input type="number" name="priority" class="form-control"
                           value="{{ old('priority', $badge->priority ?? 0) }}">
                    <p class="help-block">Büyük olan daha önde gösterilir.</p>
                </div>
            </div>

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Kaydet</button>
                <a href="{{ route('admin.tag_badges.index') }}" class="btn btn-default">İptal</a>
            </div>
        </div>
    </form>
@endsection

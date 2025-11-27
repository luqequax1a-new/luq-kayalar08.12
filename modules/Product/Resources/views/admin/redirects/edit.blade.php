@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', 'Yönlendirme Düzenle')
    @slot('subtitle', $urlRedirect->source_path)

    <li><a href="{{ route('admin.redirects.index') }}">Yönlendirmeler</a></li>
    <li class="active">Düzenle</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.redirects.update', $urlRedirect) }}" class="form-horizontal" id="redirect-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}

        @if ($errors->has('error'))
            <div class="alert alert-danger fade in alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <span class="alert-text">{{ $errors->first('error') }}</span>
            </div>
        @endif

        <div class="box box-primary">
            <div class="box-body">
                <div class="form-group">
                    <label for="source_path" class="col-md-2 control-label">Kaynak URL</label>
                    <div class="col-md-8">
                        <input type="text" name="source_path" id="source_path" class="form-control" value="{{ $urlRedirect->source_path }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="target_url" class="col-md-2 control-label">Hedef URL</label>
                    <div class="col-md-8">
                        <input type="text" name="target_url" id="target_url" class="form-control" value="{{ $urlRedirect->target_url }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="status_code" class="col-md-2 control-label">HTTP Kod</label>
                    <div class="col-md-8">
                        <select name="status_code" id="status_code" class="form-control" required>
                            <option value="301" {{ $urlRedirect->status_code == 301 ? 'selected' : '' }}>301</option>
                            <option value="302" {{ $urlRedirect->status_code == 302 ? 'selected' : '' }}>302</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">Aktif</label>
                    <div class="col-md-8">
                        <div class="switch">
                            <input type="checkbox" name="is_active" value="1" id="redirect-active" {{ $urlRedirect->is_active ? 'checked' : '' }}>
                            <label for="redirect-active"></label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Güncelle</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script type="module">
        // no-op
    </script>
@endpush

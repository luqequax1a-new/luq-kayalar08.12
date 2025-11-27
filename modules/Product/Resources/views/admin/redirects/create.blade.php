@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', 'Yönlendirme Oluştur')

    <li><a href="{{ route('admin.redirects.index') }}">Yönlendirmeler</a></li>
    <li class="active">Oluştur</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.redirects.store') }}" class="form-horizontal" id="redirect-create-form" novalidate>
        {{ csrf_field() }}

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
                        <input type="text" name="source_path" id="source_path" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="target_url" class="col-md-2 control-label">Hedef URL</label>
                    <div class="col-md-8">
                        <input type="text" name="target_url" id="target_url" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="status_code" class="col-md-2 control-label">HTTP Kod</label>
                    <div class="col-md-8">
                        <select name="status_code" id="status_code" class="form-control" required>
                            <option value="301">301</option>
                            <option value="302">302</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-2 control-label">Aktif</label>
                    <div class="col-md-8">
                        <div class="switch">
                            <input type="checkbox" name="is_active" value="1" id="redirect-active" checked>
                            <label for="redirect-active"></label>
                        </div>
                    </div>
                </div>
            </div>

        <div class="box footer">
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script type="module">
        // no-op
    </script>
@endpush

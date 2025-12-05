@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', 'Geliver Entegrasyonu')
    <li><a href="{{ route('admin.settings.edit') }}">{{ trans('setting::settings.settings') }}</a></li>
    <li class="active">Geliver</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.settings.geliver.update') }}" class="form-horizontal">
        @method('PUT')
        @csrf
        <div class="box" style="padding:20px;">
            <div class="form-group">
                <label class="col-md-3 control-label">Aktif mi?</label>
                <div class="col-md-9">
                    <input type="hidden" name="geliver_enabled" value="0">
                    <input type="checkbox" name="geliver_enabled" value="1" {{ setting('geliver_enabled') ? 'checked' : '' }}>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">API Token</label>
                <div class="col-md-9">
                    <input type="text" name="geliver_api_token" class="form-control" value="{{ setting('geliver_api_token') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Sender Address ID</label>
                <div class="col-md-9">
                    <input type="text" name="geliver_sender_address_id" class="form-control" value="{{ setting('geliver_sender_address_id') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Varsayılan Uzunluk (cm)</label>
                <div class="col-md-9">
                    <input type="text" name="geliver_default_length" class="form-control" value="{{ setting('geliver_default_length', 10.0) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Genişlik (cm)</label>
                <div class="col-md-9">
                    <input type="text" name="geliver_default_width" class="form-control" value="{{ setting('geliver_default_width', 10.0) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Yükseklik (cm)</label>
                <div class="col-md-9">
                    <input type="text" name="geliver_default_height" class="form-control" value="{{ setting('geliver_default_height', 10.0) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Ağırlık (kg)</label>
                <div class="col-md-9">
                    <input type="text" name="geliver_default_weight" class="form-control" value="{{ setting('geliver_default_weight', 1.0) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Test Modu</label>
                <div class="col-md-9">
                    <input type="hidden" name="geliver_test_mode" value="0">
                    <input type="checkbox" name="geliver_test_mode" value="1" {{ setting('geliver_test_mode', true) ? 'checked' : '' }}>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Webhook Secret</label>
                <div class="col-md-9">
                    <input type="text" name="geliver_webhook_secret" class="form-control" value="{{ setting('geliver_webhook_secret') }}">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Kaydet</button>
        </div>
    </form>
@endsection


@extends('admin::layout')

@section('title', 'WhatsApp Modülü Ayarları')

@section('content_header')
    <h3>WhatsApp Modülü Ayarları</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li>{{ trans('admin::sidebar.automations') }}</li>
        <li class="active">{{ trans('admin::sidebar.whatsapp_module') }}</li>
    </ol>

@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger fade in alert-dismissible clearfix">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <span class="alert-text">{{ trans('core::messages.the_given_data_was_invalid') }}</span>
        </div>
    @endif

    <form id="whatsapp-settings-form" method="POST" action="{{ route('admin.settings.update') }}" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}
        <input type="hidden" name="context" value="whatsapp_module">

        @php($settings = setting())
        @php($defaults = [
            'product_button_text' => 'WhatsApp’tan Sor',
            'product_message_template' => "Merhaba 👋\n{product_name} ürünü hakkında bilgi almak istiyorum.\nVaryant: {variant_name}\nMiktar: {quantity} {unit}\nÜrün linki: {product_url}",
            'cart_button_text' => 'Sepetini WhatsApp’tan Gönder',
            'cart_message_template' => "Merhaba 👋\nAşağıdaki sepet için bilgi almak istiyorum:\n\n{cart_lines}\n\nToplam: {cart_total}\nSepeti tekrar aç: {cart_restore_url}",
        ])
        @php($settingsArray = array_merge($defaults, $settings->all()))

        <div class="row">
            <div class="col-md-12">
                <div class="box-content clearfix">
                    <div class="form-group">
                        <label for="phone_number" class="control-label text-left">WhatsApp Telefon Numarası <span class="m-l-5 text-red">*</span></label>
                        <input name="phone_number" id="phone_number" class="form-control" value="{{ old('phone_number', $settingsArray['phone_number'] ?? '') }}" placeholder="9053XXXXXXXX" required>
                        <span class="help-block">Ülke kodu ile birlikte, boşluk veya + koymadan yazın. Örn: 9053XXXXXXX</span>
                        {!! $errors->first('phone_number', '<span class="help-block text-red">:message</span>') !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box-content clearfix">
                    <div class="form-group">
                        <label class="control-label text-left" for="product_button_enabled">Ürün butonu</label>
                        <div class="checkbox">
                            <input type="hidden" value="0" name="product_button_enabled">
                            <input type="checkbox" name="product_button_enabled" id="product_button_enabled" value="1" {{ old('product_button_enabled', $settingsArray['product_button_enabled'] ?? null) ? 'checked' : '' }}>
                            <label for="product_button_enabled">Ürün sayfasında WhatsApp butonunu göster</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="product_button_text" class="control-label text-left">Ürün buton metni <span class="m-l-5 text-red">*</span></label>
                        <input name="product_button_text" id="product_button_text" class="form-control" value="{{ old('product_button_text', $settingsArray['product_button_text'] ?? '') }}" required>
                        {!! $errors->first('product_button_text', '<span class="help-block text-red">:message</span>') !!}
                    </div>

                    <div class="form-group">
                        <label for="product_message_template" class="control-label text-left">Ürün mesaj şablonu</label>
                        <textarea name="product_message_template" id="product_message_template" class="form-control" rows="5">{{ old('product_message_template', $settingsArray['product_message_template'] ?? '') }}</textarea>
                        <span class="help-block">Kullanılabilir değişkenler: {product_name}, {variant_name}, {quantity}, {unit}, {product_url}</span>
                        {!! $errors->first('product_message_template', '<span class="help-block text-red">:message</span>') !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box-content clearfix">
                    <div class="form-group">
                        <label class="control-label text-left" for="cart_button_enabled">Sepet butonu</label>
                        <div class="checkbox">
                            <input type="hidden" value="0" name="cart_button_enabled">
                            <input type="checkbox" name="cart_button_enabled" id="cart_button_enabled" value="1" {{ old('cart_button_enabled', $settingsArray['cart_button_enabled'] ?? null) ? 'checked' : '' }}>
                            <label for="cart_button_enabled">Sepet sayfasında WhatsApp butonunu göster</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cart_button_text" class="control-label text-left">Sepet buton metni <span class="m-l-5 text-red">*</span></label>
                        <input name="cart_button_text" id="cart_button_text" class="form-control" value="{{ old('cart_button_text', $settingsArray['cart_button_text'] ?? '') }}" required>
                        {!! $errors->first('cart_button_text', '<span class="help-block text-red">:message</span>') !!}
                    </div>

                    <div class="form-group">
                        <label for="cart_message_template" class="control-label text-left">Sepet mesaj şablonu</label>
                        <textarea name="cart_message_template" id="cart_message_template" class="form-control" rows="5">{{ old('cart_message_template', $settingsArray['cart_message_template'] ?? '') }}</textarea>
                        <span class="help-block">Kullanılabilir değişkenler: {cart_lines}, {cart_total}, {cart_restore_url}</span>
                        {!! $errors->first('cart_message_template', '<span class="help-block text-red">:message</span>') !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="col-md-12 col-md-offset-0">
                        <button type="submit" class="btn btn-primary">{{ trans('admin::admin.buttons.save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

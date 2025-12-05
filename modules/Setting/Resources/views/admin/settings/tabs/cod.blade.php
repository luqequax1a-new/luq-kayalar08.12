<div class="row">
    <div class="col-md-8">
        {{ Form::checkbox('cod_enabled', trans('setting::attributes.cod_enabled'), trans('setting::settings.form.enable_cod'), $errors, $settings) }}
        {{ Form::text('translatable[cod_label]', trans('setting::attributes.translatable.cod_label'), $errors, $settings, ['required' => true]) }}
        {{ Form::textarea('translatable[cod_description]', trans('setting::attributes.translatable.cod_description'), $errors, $settings, ['rows' => 3, 'required' => true]) }}
        {{ Form::checkbox('cod_control_enabled', trans('setting::attributes.cod_control_enabled'), trans('setting::settings.form.enable_cod_control'), $errors, $settings) }}
        {{ Form::number('cod_min_subtotal', trans('setting::attributes.cod_min_subtotal'), $errors, $settings, ['min' => 0, 'step' => '0.01']) }}
        {{ Form::number('cod_max_subtotal', trans('setting::attributes.cod_max_subtotal'), $errors, $settings, ['min' => 0, 'step' => '0.01']) }}
        {{ Form::select('cod_fee_mode', trans('setting::attributes.cod_fee_mode'), $errors, ['fixed' => 'Fixed', 'percent' => 'Percent'], $settings) }}
        {{ Form::number('cod_fee_amount', trans('setting::attributes.cod_fee_amount'), $errors, $settings, ['min' => 0, 'step' => '0.01']) }}
        {{ Form::number('cod_fee_percent', trans('setting::attributes.cod_fee_percent'), $errors, $settings, ['min' => 0, 'step' => '0.01']) }}
        {{ Form::select('cod_fee_display_mode', trans('setting::attributes.cod_fee_display_mode'), $errors, ['add_to_shipping' => 'Add to shipping', 'separate_line' => 'Separate line'], $settings) }}
    </div>
</div>

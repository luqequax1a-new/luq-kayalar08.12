<div class="row">
    <div class="col-md-12">
        <div class="box-content clearfix">
            {{ Form::checkbox('review_request_enabled', trans('setting::attributes.review_request_enabled'), trans('setting::settings.form.enable_review_request_email'), $errors, $settings) }}
            {{ Form::number('review_request_delay_days', trans('setting::attributes.review_request_delay_days'), $errors, $settings, ['min' => 0, 'max' => 60]) }}
            {{ Form::number('review_request_second_delay_days', trans('setting::attributes.review_request_second_delay_days'), $errors, $settings, ['min' => 1, 'max' => 30]) }}

            {{ Form::checkbox('review_coupon_enabled', trans('setting::attributes.review_coupon_enabled'), trans('setting::settings.form.enable_review_coupon'), $errors, $settings) }}
            {{ Form::number('review_coupon_discount_percent', trans('setting::attributes.review_coupon_discount_percent'), $errors, $settings, ['min' => 1, 'max' => 90]) }}
            {{ Form::number('review_coupon_valid_days', trans('setting::attributes.review_coupon_valid_days'), $errors, $settings, ['min' => 1, 'max' => 365]) }}
        </div>
    </div>
</div>

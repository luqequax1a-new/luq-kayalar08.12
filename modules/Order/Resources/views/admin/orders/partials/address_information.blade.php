<div class="address-information-wrapper">
    <h4 class="section-title">{{ trans('order::orders.address_information') }}</h4>

    <div class="row">
        <div class="col-md-6">
            <div class="billing-address">
                <h5 class="pull-left">{{ trans('order::orders.billing_address') }}</h5>

                @php($billing = $order->billingAddress)
                @php($shipping = $order->shippingAddress)
                @if ($billing && $order->shipping_address_id !== $order->billing_address_id)
                    <span>
                        Firma Adı: {{ $billing->company_name }}<br>
                        Vergi No: {{ $billing->tax_number }}<br>
                        Vergi Dairesi: {{ $billing->tax_office }}<br>
                        {{ $billing->phone }}<br>
                        {{ $billing->address_line ?? $billing->address_1 }}<br>
                        {{ $billing->city ?? $billing->city_id }} / {{ $billing->state ?? $billing->district_id }}
                    </span>
                @elseif ($shipping)
                    <span>
                        {{ $shipping->first_name }} {{ $shipping->last_name }}<br>
                        {{ $shipping->phone }}<br>
                        {{ $shipping->address_line ?? $shipping->address_1 }}<br>
                        {{ $shipping->city ?? $shipping->city_id }} / {{ $shipping->state ?? $shipping->district_id }}
                    </span>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <div class="shipping-address">
                <h5 class="pull-left">{{ trans('order::orders.shipping_address') }}</h5>

                @php($shipping = $shipping)
                @if ($shipping)
                    <span>
                        {{ $shipping->first_name }} {{ $shipping->last_name }}<br>
                        {{ $shipping->phone }}<br>
                        {{ $shipping->address_line ?? $shipping->address_1 }}<br>
                        {{ $shipping->city ?? $shipping->city_id }} / {{ $shipping->state ?? $shipping->district_id }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

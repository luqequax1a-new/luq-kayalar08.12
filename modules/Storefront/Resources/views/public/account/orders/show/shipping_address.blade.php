<div class="col-lg-6 col-sm-6">
    <div class="order-shipping-details">
        <h4>{{ trans('storefront::account.view_order.shipping_address') }}</h4>

        @php($shipping = $order->shippingAddress)
        @if ($shipping)
            <address class="d-flex flex-column cursor-default">
                <div>
                    <div>{{ $shipping->first_name }} {{ $shipping->last_name }}</div>
                    <div>{{ $shipping->address_line ?? $shipping->address_1 }}</div>
                    <div>{{ $shipping->city ?? $shipping->city_id }} / {{ $shipping->state ?? $shipping->district_id }}</div>
                    @if ($shipping->phone)
                        <div>{{ $shipping->phone }}</div>
                    @endif
                </div>
            </address>
        @endif
    </div>
</div>

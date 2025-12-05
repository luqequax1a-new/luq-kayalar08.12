<div class="col-lg-6 col-sm-6">
    <div class="order-billing-details">
        <h4>{{ trans('storefront::account.view_order.billing_address') }}</h4>

        @php($billing = $order->billingAddress)
        @if ($billing && $order->shipping_address_id !== $order->billing_address_id)
            <address class="d-flex flex-column cursor-default">
                <div>
                    <div>{{ $billing->company_name }}</div>
                    <div>Vergi No: {{ $billing->tax_number }}</div>
                    <div>Vergi Dairesi: {{ $billing->tax_office }}</div>
                    <div>{{ $billing->address_line ?? $billing->address_1 }}</div>
                    <div>{{ $billing->city ?? $billing->city_id }} / {{ $billing->state ?? $billing->district_id }}</div>
                    @if ($billing->phone)
                        <div>{{ $billing->phone }}</div>
                    @endif
                </div>
            </address>
        @else
            <p>Fatura adresi kargo adresi ile aynıdır.</p>
        @endif
    </div>
</div>

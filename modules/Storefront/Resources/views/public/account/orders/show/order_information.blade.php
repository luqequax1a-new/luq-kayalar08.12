<div class="col-lg-6 col-sm-6">
    <div class="order-information">
        <h4>{{ trans('storefront::account.view_order.order_information') }}</h4>

        <address class="d-flex flex-column cursor-default">
            <div>
                <div><strong>{{ rtrim(trans('storefront::account.view_order.id'), ':') }}:</strong> {{ $order->id }}</div>
                <div><strong>{{ rtrim(trans('storefront::account.view_order.phone'), ':') }}:</strong> {{ $order->customer_phone }}</div>
                <div><strong>{{ rtrim(trans('storefront::account.view_order.email'), ':') }}:</strong> {{ $order->customer_email }}</div>
                <div><strong>{{ rtrim(trans('storefront::account.view_order.date'), ':') }}:</strong> {{ $order->created_at->toFormattedDateString() }}</div>
                <div><strong>{{ rtrim(trans('storefront::account.view_order.shipping_method'), ':') }}:</strong> {{ $order->shipping_method }}</div>
                <div><strong>{{ rtrim(trans('storefront::account.view_order.payment_method'), ':') }}:</strong> {{ $order->payment_method }}</div>

                @if ($order->payment_method === 'Bank Transfer')
                    <span style="color: #999; font-size: 13px;">{!! setting('bank_transfer_instructions') !!}</span>
                @endif

                @if ($order->note)
                    <div><strong>{{ rtrim(trans('storefront::account.view_order.order_note'), ':') }}:</strong> {{ $order->note }}</div>
                @endif

                @if ($order->tracking_reference)
                    <div x-data="{ tracking: '{{ $order->tracking_reference }}' }" class="d-flex align-items-center">
                        <strong class="m-r-5">{{ trans('storefront::account.view_order.tracking_reference') }}:</strong>
                        <div class="m-r-5" x-text="tracking.length > 30 ? tracking.slice(0, 30) + '...' : tracking"></div>

                        <div class="d-flex">
                            <button type="button" @click="navigator.clipboard.writeText(tracking).then(() => { notify('Copied to clipboard'); });" class="btn-track-order m-r-5" title="{{ trans('storefront::account.view_order.copy') }}">
                                <i class="lar la-copy"></i>
                            </button>

                            @if (filter_var($order->tracking_reference, FILTER_VALIDATE_URL))
                                <a href="{{ $order->tracking_reference }}" class="btn-track-order" target="_blank" title="{{ trans('storefront::account.view_order.open_link') }}"><i class="las la-external-link-alt"></i></a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </address>
    </div>
</div>

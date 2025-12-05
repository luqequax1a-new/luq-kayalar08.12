<template x-if="!cartIsEmpty">
    <aside class="order-summary-wrap">
        <div class="order-summary">
            <div class="order-summary-top">
                <h3 class="section-title">{{ trans('storefront::cart.cart_summary') }}</h3>
            </div>

            <div class="order-summary-middle">
                <ul class="list-inline order-summary-list">
                    <li>
                        <label>{{ trans('storefront::cart.total') }}</label>

                        <span x-text="formatCurrency($store.cart.subTotal)"></span>
                    </li>
                </ul>
            </div>

            <div class="order-summary-bottom">
                <a
                    href="{{ route('checkout.create') }}"
                    class="btn btn-primary btn-proceed-to-checkout"
                >
                    {{ trans('storefront::cart.proceed_to_checkout') }}
                </a>

                @if (setting('phone_number') && setting('cart_button_enabled'))
                    @php
                        $items = \Modules\Cart\Facades\Cart::items();
                    $lines = $items->map(function($ci){
                        $name = optional($ci->product)->name ?: '';
                        $attrs = [];
                        try {
                            foreach (($ci->variations ?? []) as $variation) {
                                $label = optional($variation->values->first())->label;
                                if ($label) { $attrs[] = $variation->name . ': ' . $label; }
                            }
                        } catch (\Throwable $e) {}
                        try {
                            foreach (($ci->options ?? []) as $option) {
                                $val = null;
                                if (method_exists($option, 'isFieldType') && $option->isFieldType()) {
                                    $val = $option->value;
                                } elseif (isset($option->values)) {
                                    $val = $option->values->implode('label', ', ');
                                }
                                if ($val) { $attrs[] = $option->name . ': ' . $val; }
                            }
                        } catch (\Throwable $e) {}
                        $attrStr = empty($attrs) ? '' : (' (' . implode(', ', $attrs) . ')');
                        $qty = $ci->qty;
                        $unit = optional($ci->product)->unit_suffix ? (' ' . $ci->product->unit_suffix) : '';
                        return $name . $attrStr . ' x ' . $qty . $unit;
                    })->implode("\n");
                        $total = \Modules\Cart\Facades\Cart::subTotal()->format();
                        $restoreUrl = route('cart.index');
                        $template = setting('cart_message_template');
                        $msg = str_replace(['{cart_lines}','{cart_total}','{cart_restore_url}'], [$lines, $total, $restoreUrl], $template);
                        $waUrl = 'https://api.whatsapp.com/send?phone=' . setting('phone_number') . '&text=' . urlencode($msg);
                    @endphp
                    <a class="btn btn-default whatsapp-cart-btn" href="{{ $waUrl }}" target="_blank" rel="noopener">
                        {{ setting('cart_button_text', 'Sepetini WhatsApp’tan Gönder') }}
                    </a>
                @endif
            </div>
        </div>
    </aside>
</template>

<?php

namespace Modules\Checkout\Http\Controllers;

use Modules\Order\Entities\Order;
use Modules\Cart\Facades\Cart;
use Modules\Shipping\Facades\ShippingMethod;

class PaymentLinkController
{
    public function show(int $orderId)
    {
        $order = Order::findOrFail($orderId);

        Cart::clear();

        foreach ($order->products as $product) {
            Cart::store(
                $product->product_id,
                $product->product_variant_id,
                $product->qty,
                []
            );
        }

        if ($order->shipping_method) {
            Cart::addShippingMethod(ShippingMethod::get($order->shipping_method));
        }

        return redirect()->route('checkout.create');
    }
}


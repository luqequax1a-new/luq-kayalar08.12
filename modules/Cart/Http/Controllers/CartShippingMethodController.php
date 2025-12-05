<?php

namespace Modules\Cart\Http\Controllers;

use Modules\Cart\Facades\Cart;
use Modules\Shipping\Facades\ShippingMethod;
use Modules\Payment\Facades\Gateway;

class CartShippingMethodController
{
    /**
     * Store a newly created resource in storage.
     *
     * @return \Modules\Cart\Cart
     */
    public function store()
    {
        $selectedPayment = request('payment_method') ?? session('checkout.payment_method');
        $gateways = Gateway::all();
        $selectedPayment = $gateways->has($selectedPayment) ? $selectedPayment : $gateways->keys()->first();
        if ($selectedPayment) {
            session(['checkout.payment_method' => $selectedPayment]);
        }

        Cart::addShippingMethod(
            ShippingMethod::get(
                request('shipping_method')
            )
        );

        if ($method = request('shipping_method')) {
            session(['checkout.shipping_method' => $method]);
        }

        return Cart::instance();
    }
}

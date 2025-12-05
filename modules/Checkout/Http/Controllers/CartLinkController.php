<?php

namespace Modules\Checkout\Http\Controllers;

use Modules\Order\Entities\SharedCart;
use Modules\Cart\Facades\Cart;

class CartLinkController
{
    public function show(string $token)
    {
        $shared = SharedCart::where('token', $token)->firstOrFail();

        Cart::clear();

        foreach (($shared->data['items'] ?? []) as $item) {
            Cart::store(
                $item['product_id'] ?? 0,
                $item['variant_id'] ?? null,
                $item['qty'] ?? 1,
                $item['options'] ?? []
            );
        }

        return redirect()->route('checkout.create');
    }
}


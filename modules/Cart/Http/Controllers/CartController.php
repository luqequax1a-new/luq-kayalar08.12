<?php

namespace Modules\Cart\Http\Controllers;

use Modules\Cart\Facades\Cart;
use Modules\Cart\Services\CartUpsellService;

class CartController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(CartUpsellService $upsellService)
    {
        try {
            \Log::info('[CART] index', [
                'session_id' => session()->getId(),
                'is_empty' => Cart::isEmpty(),
                'count' => Cart::instance()->count(),
            ]);
        } catch (\Throwable $e) {
        }

        $cart = Cart::instance();
        $upsellOffer = $upsellService->resolveBestRule($cart);

        return view('storefront::public.cart.index')->with([
            'isCartEmpty' => Cart::isEmpty(),
            'crossSellProducts' => Cart::crossSellProducts(),
            'upsellOffer' => $upsellOffer,
        ]);
    }


    public function cart()
    {
        try {
            \Log::info('[CART] get', [
                'session_id' => session()->getId(),
                'count' => Cart::instance()->count(),
            ]);
        } catch (\Throwable $e) {
        }

        return Cart::instance();
    }


    /**
     * Clear the cart.
     *
     * @return \Modules\Cart\Cart
     */
    public function clear()
    {
        Cart::clear();

        return Cart::instance();
    }
}

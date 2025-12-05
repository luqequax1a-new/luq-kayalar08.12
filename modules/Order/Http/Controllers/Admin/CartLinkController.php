<?php

namespace Modules\Order\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Modules\Order\Entities\SharedCart;
use FleetCart\Services\AdminManualCartService;
use Illuminate\Http\JsonResponse;

class CartLinkController
{
    public function create()
    {
        $categories = \Modules\Category\Entities\Category::with('translations')
            ->orderBy('id', 'asc')
            ->get(['id'])
            ->map(function($c){
                return ['id' => $c->id, 'name' => $c->name];
            })
            ->values()
            ->all();

        return view('order::admin.cart_links.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required',
            'customer_id' => 'nullable|integer|exists:users,id',
        ]);

        $items = $validated['items'];
        if (is_string($items)) {
            $items = json_decode($items, true) ?: [];
        }

        $items = collect($items)->map(function($i){
            return [
                'product_id' => (int)($i['product_id'] ?? 0),
                'variant_id' => isset($i['variant_id']) ? (int)$i['variant_id'] : null,
                'qty' => (float)($i['qty'] ?? 1),
                'options' => $i['options'] ?? [],
            ];
        })->filter(function($i){
            return $i['product_id'] > 0 && $i['qty'] > 0;
        })->values()->all();

        $token = Str::random(32);

            $link = SharedCart::create([
                'token' => $token,
                'data' => [
                    'items' => $items,
                    'customer_id' => $validated['customer_id'] ?? null,
                ],
                'created_by_admin_id' => auth()->id(),
            ]);

        $url = URL::route('checkout.cart_link.show', ['token' => $token]);

        return back()->with('success', 'Cart link created: ' . $url)->with('cart_link_url', $url);
    }

    public function preview(Request $request, AdminManualCartService $manualCartService): JsonResponse
    {
        $manualCartService->getCartFromRequest($request);

        $cart = $manualCartService->cart();

        return response()->json([
            'cart' => $cart->toArray(),
            'summary' => [
                'items_count' => $cart->items()->count(),
                'sub_total' => $cart->subTotal()->amount(),
                'shipping_cost' => $cart->shippingCost()->amount(),
                'discount' => $cart->discount()->amount(),
                'tax' => $cart->tax()->amount(),
                'total' => $cart->total()->amount(),
            ],
        ]);
    }
}

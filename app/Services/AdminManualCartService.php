<?php

namespace FleetCart\Services;

use Illuminate\Http\Request;
use Modules\Cart\Cart as StorefrontCart;
use Modules\Cart\Storages\Database as CartDatabaseStorage;
use Modules\Shipping\Facades\ShippingMethod;

class AdminManualCartService
{
    private StorefrontCart $cart;

    public function __construct()
    {
        $this->cart = new StorefrontCart(
            new CartDatabaseStorage(),
            app('events'),
            'cart',
            'admin_manual_' . session()->getId(),
            config('fleetcart.modules.cart.config')
        );
    }

    public function cart(): StorefrontCart
    {
        return $this->cart;
    }

    public function getCartFromRequest(Request $request): void
    {
        $this->cart->clear();

        $items = $request->input('items', []);
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $items = is_array($decoded) ? $decoded : [];
        }

        collect($items)->each(function ($item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $variantId = $item['variant_id'] ?? null;
            $qty = (float) ($item['qty'] ?? 1);
            $options = $item['options'] ?? [];

            if ($productId > 0 && $qty > 0) {
                $this->cart->store($productId, $variantId, $qty, $options);
            }
        });
    }

    public function calculateTotals($customer, $shippingAddress = null, $billingAddress = null, $shippingMethodName = null): void
    {
        if (!$this->cart->allItemsAreVirtual()) {
            if ($shippingMethodName) {
                $method = ShippingMethod::get($shippingMethodName);
                $this->cart->addShippingMethod($method);
            } else {
                $available = ShippingMethod::available();
                if ($available && $available->isNotEmpty()) {
                    $this->cart->addShippingMethod($available->first());
                }
            }
        }

        $billing = [
            'country' => ($billingAddress ? $billingAddress->country : ($shippingAddress->country ?? null)),
            'state' => ($billingAddress ? $billingAddress->state : ($shippingAddress->state ?? null)),
            'zip' => ($billingAddress ? $billingAddress->zip : ($shippingAddress->zip ?? null)),
        ];

        $shipping = [
            'country' => $shippingAddress->country ?? null,
            'state' => $shippingAddress->state ?? null,
            'zip' => $shippingAddress->zip ?? null,
        ];

        $this->cart->addTaxes((object) [
            'billing' => $billing,
            'shipping' => $shipping,
        ]);
    }
}

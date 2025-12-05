<?php

use Modules\Cart\Facades\Cart;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;

it('smart_shipping_enabled_does_not_reset_cart_between_requests', function () {
    // Enable SmartShipping in settings
    setting()->set('smart_shipping_enabled', true);
    setting()->set('smart_shipping_base_rate', 100);
    setting()->set('smart_shipping_free_threshold', 0);

    $product = Product::query()->first();
    expect($product)->not->toBeNull();

    $variant = ProductVariant::query()->where('product_id', $product->id)->first();

    $session = $this->withSession([]);

    // 1) Add to cart through HTTP like frontend
    $payload = [
        'product_id' => $product->id,
        'variant_id' => optional($variant)->id,
        'qty' => 1,
    ];

    $response = $session->postJson(route('cart.items.store'), $payload);
    $response->assertStatus(200);

    // After store, cart should have 1 item
    expect(Cart::count())->toBe(1);
    expect(Cart::isEmpty())->toBeFalse();

    // 2) Simulate GET /cart
    $cartResponse = $session->get(route('cart.index'));
    $cartResponse->assertStatus(200);

    // 3) Simulate GET /cart/get
    $cartGetResponse = $session->get(route('cart.get'));
    $cartGetResponse->assertStatus(200);

    // Cart must still have 1 item and not be empty
    expect(Cart::count())->toBe(1);
    expect(Cart::isEmpty())->toBeFalse();
});

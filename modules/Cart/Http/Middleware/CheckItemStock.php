<?php

namespace Modules\Cart\Http\Middleware;

use Closure;
use Modules\Cart\CartItem;
use Illuminate\Http\Request;
use Modules\Cart\Facades\Cart;
use Modules\Product\Entities\Product;
use Modules\FlashSale\Entities\FlashSale;
use Modules\Product\Entities\ProductVariant;

class CheckItemStock
{
    private CartItem|null $cartItem;
    private Product $product;
    private ProductVariant|null $variant;
    private Product|ProductVariant $item;


    public function __construct(Request $request)
    {
        if (request()->routeIs('cart.items.store')) {
            $this->product = request('product_id') ? $this->getProduct(request('product_id')) : null;
            $this->variant = request('variant_id') ? $this->getVariant(request('variant_id')) : null;
            $this->cartItem = Cart::items()->get(
                md5("product_id.{$request->product_id}.variant_id.{$request->variant_id}:options." . serialize(array_filter($request->options ?? [])))
            );
        }

        if (request()->routeIs('cart.items.update')) {
            $this->cartItem = Cart::items()->get(request('id'));
            $this->product = $this->cartItem?->refreshStock()->product;
            $this->variant = $this->cartItem?->refreshStock()->variant;
        }

        $this->item = $this->variant ?? $this->product;
    }


    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$this->passesUnitRules()) {
            return response()->json([
                    'message' => 'Geçersiz miktar: minimum miktar kuralına uyun.',
                    'cart' => Cart::instance(),
                ]
                , 400);
        }
        if ($this->item->isOutOfStock()) {
            return response()->json([
                    'message' => trans('cart::messages.out_of_stock'),
                    'cart' => Cart::instance(),
                ]
                , 400);
        }


        if (!$this->hasFlashSaleStock()) {
            return response()->json([
                    'message' => trans('cart::messages.not_have_enough_quantity_in_stock', [
                        'stock' => FlashSale::remainingQty($this->product),
                    ]),
                    'cart' => Cart::instance(),
                ]
                , 400);
        }


        if (!$this->hasStock()) {
            return response()->json([
                    'message' => trans('cart::messages.not_have_enough_quantity_in_stock', [
                        'stock' => $this->remainingStock(),
                    ]),
                    'cart' => Cart::instance(),
                ]
                , 400);
        }

        return $next($request);
    }

    private function remainingStock(): float
    {
        if (!$this->item->manage_stock) {
            return (float) $this->item->qty;
        }

        if ($this->cartItem) {
            $addedCartQty = Cart::addedQty($this->cartItem) - $this->cartItem->qty;
            return (float) $this->item->qty - $addedCartQty;
        }

        return (float) $this->item->qty;
    }

    private function passesUnitRules(): bool
    {
        $rawQty = request('qty');
        $qty = is_string($rawQty) ? (float) str_replace(',', '.', $rawQty) : (float) $rawQty;

        if ($qty <= 0) {
            return false;
        }

        $unit = $this->product?->saleUnit;

        if (!$unit) {
            return true;
        }

        return $unit->isValidQuantity($qty);
    }


    private function getProduct($id)
    {
        return Product::withName()
            ->addSelect('id', 'in_stock', 'manage_stock', 'qty', 'sale_unit_id')
            ->where('id', $id)
            ->firstOrFail();
    }


    private function getVariant($id)
    {
        return ProductVariant::addSelect('id', 'in_stock', 'manage_stock', 'qty')
            ->where('id', $id)
            ->firstOrFail();
    }


    private function hasFlashSaleStock(): bool
    {
        if ($this->variant || !FlashSale::contains($this->product)) {
            return true;
        }

        $remainingQty = FlashSale::remainingQty($this->product);

        $rawQty = request('qty');
        $qty = is_string($rawQty) ? (float) str_replace(',', '.', $rawQty) : (float) $rawQty;

        $totalAdded = $this->totalAddedQtyForItem();
        $otherAdded = $this->cartItem ? $totalAdded - (float) $this->cartItem->qty : $totalAdded;

        return ($remainingQty - $otherAdded) >= $qty;
    }


    private function hasStock(): bool
    {
        $rawQty = request('qty');
        $qty = is_string($rawQty) ? (float) str_replace(',', '.', $rawQty) : (float) $rawQty;

        $limitQty = $this->item->manage_stock
            ? (float) $this->item->qty
            : (((float) $this->item->qty) > 0 ? (float) $this->item->qty : INF);

        $totalAdded = $this->totalAddedQtyForItem();
        $otherAdded = $this->cartItem ? $totalAdded - (float) $this->cartItem->qty : $totalAdded;
        $remaining = $limitQty - $otherAdded;

        if (request()->routeIs('cart.items.update') && $this->cartItem) {
            return $remaining >= $qty;
        }

        if (request()->routeIs('cart.items.store')) {
            if ($remaining >= $qty) {
                return true;
            }

            return false;
        }

        return $remaining >= $qty;
    }

    private function totalAddedQtyForItem(): float
    {
        if (!$this->item) {
            return 0.0;
        }

        $isVariant = $this->item instanceof ProductVariant;

        return (float) Cart::items()
            ->filter(function ($cartItem) use ($isVariant) {
                if ($isVariant) {
                    return $cartItem->variant && $cartItem->variant->id === $this->item->id;
                }
                return $cartItem->product->id === $this->item->id;
            })
            ->sum('qty');
    }
}

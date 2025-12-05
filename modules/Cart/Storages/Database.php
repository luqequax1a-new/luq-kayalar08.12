<?php

namespace Modules\Cart\Storages;

use Modules\Cart\Entities\Cart;
use Darryldecode\Cart\CartCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class Database
{
    private function normalizeKey(string $key): string
    {
        $sessionId = Session::getId();

        if (str_ends_with($key, '_cart_items')) {
            return $sessionId . '_cart_items';
        }

        if (str_ends_with($key, '_cart_conditions')) {
            return $sessionId . '_cart_conditions';
        }

        return $key;
    }

    public function get($key)
    {
        $normalizedKey = $this->normalizeKey($key);

        try {
            Log::info('[CART][DB] get', [
                'key' => $normalizedKey,
                'raw_key' => $key,
                'exists' => (bool) $this->has($normalizedKey),
                'session_id' => Session::getId(),
            ]);
        } catch (\Throwable $e) {
        }

        if ($this->has($normalizedKey)) {
            return new CartCollection(Cart::find($normalizedKey)->data);
        } else {
            return [];
        }
    }

    public function put($key, $value)
    {
        $normalizedKey = $this->normalizeKey($key);

        try {
            Log::info('[CART][DB] put', [
                'key' => $normalizedKey,
                'raw_key' => $key,
                'session_id' => Session::getId(),
            ]);
        } catch (\Throwable $e) {
        }

        if ($row = Cart::find($normalizedKey)) {
            $row->data = $value;
            $row->save();
        } else {
            Cart::create([
                'id' => $normalizedKey,
                'data' => $value,
            ]);
        }
    }

    private function has($key)
    {
        return Cart::find($key);
    }
}

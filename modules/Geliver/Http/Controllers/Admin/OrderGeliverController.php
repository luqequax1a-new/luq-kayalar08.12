<?php

namespace Modules\Geliver\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Modules\Order\Entities\Order;
use Modules\Geliver\Services\GeliverService;

class OrderGeliverController
{
    public function send(Order $order, GeliverService $service): RedirectResponse
    {
        try {
            $service->sendOrderToGeliver($order);
            return redirect()->route('admin.orders.show', $order->id)
                ->withSuccess('Sipariş Geliver’e aktarıldı.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.orders.show', $order->id)
                ->withError($e->getMessage());
        }
    }
}

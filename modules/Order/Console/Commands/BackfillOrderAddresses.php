<?php

namespace Modules\Order\Console\Commands;

use Illuminate\Console\Command;
use Modules\Order\Entities\Order;
use Modules\Address\Entities\Address;

class BackfillOrderAddresses extends Command
{
    protected $signature = 'order:backfill-addresses {--limit=0 : Process only first N orders}';
    protected $description = 'Create Address records from legacy order columns and fill shipping/billing address FKs.';

    public function handle(): int
    {
        $query = Order::query()
            ->where(function ($q) {
                $q->whereNull('shipping_address_id')->orWhereNull('billing_address_id');
            })
            ->orderBy('id');

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $count = 0;
        $query->chunkById(200, function ($orders) use (&$count) {
            foreach ($orders as $order) {
                $shippingId = $order->shipping_address_id;
                $billingId = $order->billing_address_id;

                if (!$shippingId) {
                    $shippingId = Address::query()->create([
                        'type' => Address::TYPE_SHIPPING,
                        'customer_id' => $order->customer_id,
                        'user_id' => $order->customer_id,
                        'first_name' => $order->shipping_first_name,
                        'last_name' => $order->shipping_last_name,
                        'phone' => $order->shipping_phone,
                        'address_1' => $order->shipping_address_1,
                        'address_2' => $order->shipping_address_2,
                        'address_line' => $order->shipping_address_1,
                        'city' => $order->shipping_city,
                        'state' => $order->shipping_state,
                        'zip' => $order->shipping_zip,
                        'country' => $order->shipping_country,
                    ])->id;
                }

                $hasSeparateBilling = ($order->invoice_title || $order->invoice_tax_number || $order->invoice_tax_office);

                if (!$billingId) {
                    if ($hasSeparateBilling) {
                        $billingId = Address::query()->create([
                            'type' => Address::TYPE_BILLING,
                            'customer_id' => $order->customer_id,
                            'user_id' => $order->customer_id,
                            'first_name' => $order->billing_first_name,
                            'last_name' => $order->billing_last_name,
                            'phone' => $order->billing_phone,
                            'address_1' => $order->billing_address_1,
                            'address_2' => $order->billing_address_2,
                            'address_line' => $order->billing_address_1,
                            'city' => $order->billing_city,
                            'state' => $order->billing_state,
                            'zip' => $order->billing_zip,
                            'country' => $order->billing_country,
                            'company_name' => $order->invoice_title,
                            'tax_number' => $order->invoice_tax_number,
                            'tax_office' => $order->invoice_tax_office,
                        ])->id;
                    } else {
                        $billingId = $shippingId;
                    }
                }

                $order->forceFill([
                    'shipping_address_id' => $shippingId,
                    'billing_address_id' => $billingId,
                ])->save();

                $count++;
                $this->info("Backfilled order #{$order->id} (S:{$shippingId}, B:{$billingId})");
            }
        });

        $this->info("Done. Processed {$count} orders.");
        return Command::SUCCESS;
    }
}

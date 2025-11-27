<?php

namespace Modules\Order\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Order\Entities\OrderProduct;

class BackfillOrderProductsSnapshots extends Command
{
    protected $signature = 'order:backfill-snapshots {--chunk=100} {--dry : Run without writing changes}';
    protected $description = 'Backfill snapshot fields on order_products from related products in chunks.';

    public function handle(): int
    {
        $chunk = (int) $this->option('chunk') ?: 100;
        $dry = (bool) $this->option('dry');

        $query = OrderProduct::query()->with(['product' => function ($q) {
            $q->withoutGlobalScope('active');
        }, 'product_variant' => function ($q) {
            $q->withoutGlobalScope('active')->withTrashed();
        }]);

        $total = (clone $query)->count();
        $updated = 0;
        $this->info("Scanning {$total} order_products in chunks of {$chunk}...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->orderBy('id')->chunk($chunk, function ($rows) use (&$updated, $dry, $bar) {
            DB::transaction(function () use ($rows, &$updated, $dry, $bar) {
                foreach ($rows as $op) {
                    $bar->advance();

                    $p = $op->product; // may be null
                    $v = $op->product_variant; // may be null

                    $changes = [];

                    if (empty($op->product_name) && $p) {
                        $changes['product_name'] = $p->name;
                    }
                    if (empty($op->product_slug) && $p) {
                        $changes['product_slug'] = $p->slug;
                    }
                    if (empty($op->product_sku)) {
                        $changes['product_sku'] = $v?->sku ?? ($p?->sku ?? $op->product_sku);
                    }
                    if (empty($op->unit_price_at_order)) {
                        $changes['unit_price_at_order'] = $op->unit_price?->amount() ?? $op->unit_price; // fallback
                    }
                    if (empty($op->unit_label)) {
                        $changes['unit_label'] = $op->unit_label ?? null;
                    }
                    if (empty($op->unit_short_suffix)) {
                        $changes['unit_short_suffix'] = $op->unit_short_suffix ?? null;
                    }
                    if (empty($op->unit_code)) {
                        $changes['unit_code'] = $op->unit_code ?? null;
                    }

                    if (!empty($changes)) {
                        if (!$dry) {
                            $op->fill($changes);
                            $op->save();
                        }
                        $updated++;
                    }
                }
            });
        });

        $bar->finish();
        $this->newLine();
        $this->info(($dry ? '[DRY RUN] ' : '') . "Updated {$updated} of {$total} order_products.");

        return Command::SUCCESS;
    }
}

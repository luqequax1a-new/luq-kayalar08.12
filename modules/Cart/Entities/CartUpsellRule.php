<?php

namespace Modules\Cart\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;

class CartUpsellRule extends Model
{
    protected $table = 'cart_upsell_rules';

    protected $fillable = [
        'status',
        'trigger_type',
        'main_product_id',
        'upsell_product_id',
        'preselected_variant_id',
        'discount_type',
        'discount_value',
        'title',
        'subtitle',
        'internal_name',
        'show_on',
        'min_cart_total',
        'max_cart_total',
        'hide_if_already_in_cart',
        'has_countdown',
        'countdown_minutes',
        'starts_at',
        'ends_at',
        'sort_order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'discount_value' => 'decimal:4',
        'min_cart_total' => 'decimal:4',
        'max_cart_total' => 'decimal:4',
        'hide_if_already_in_cart' => 'boolean',
        'has_countdown' => 'boolean',
        'countdown_minutes' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'title' => 'array',
        'subtitle' => 'array',
    ];

    public function mainProduct()
    {
        return $this->belongsTo(Product::class, 'main_product_id');
    }

    public function upsellProduct()
    {
        return $this->belongsTo(Product::class, 'upsell_product_id');
    }

    public function preselectedVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'preselected_variant_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeForPlacement($query, string $placement)
    {
        return $query->where('show_on', $placement);
    }

    public function scopeWithinDateRange($query)
    {
        $now = now();

        return $query
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }
}

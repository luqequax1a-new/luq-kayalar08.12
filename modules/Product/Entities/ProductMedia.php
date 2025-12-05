<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Builder;
use Modules\Support\Eloquent\Model;

class ProductMedia extends Model
{
    protected $table = 'product_media';

    protected $fillable = [
        'product_id',
        'variant_id',
        'type',
        'path',
        'poster',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeVideos(Builder $query): Builder
    {
        return $query->where('type', 'video');
    }
}


<?php

namespace Modules\DynamicCategory\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Support\Eloquent\Model;

class DynamicCategoryRule extends Model
{
    protected $table = 'dynamic_category_rules';

    protected $fillable = [
        'dynamic_category_id',
        'group_no',
        'position',
        'field',
        'operator',
        'value',
        'boolean',
        'label',
    ];

    protected $casts = [
        'group_no' => 'integer',
        'position' => 'integer',
    ];

    public function dynamicCategory(): BelongsTo
    {
        return $this->belongsTo(DynamicCategory::class);
    }
}

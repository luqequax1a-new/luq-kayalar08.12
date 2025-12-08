<?php

namespace Modules\DynamicCategory\Entities;

use Modules\Support\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Tag\Entities\Tag;

class DynamicCategoryTag extends Model
{
    protected $table = 'dynamic_category_tag';

    protected $fillable = [
        'dynamic_category_id',
        'tag_id',
        'type',
    ];

    public function dynamicCategory(): BelongsTo
    {
        return $this->belongsTo(DynamicCategory::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}

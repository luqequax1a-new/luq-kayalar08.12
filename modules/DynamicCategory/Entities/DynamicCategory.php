<?php

namespace Modules\DynamicCategory\Entities;

use Modules\Support\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Media\Entities\File;
use Modules\DynamicCategory\Admin\DynamicCategoryTable;
use Illuminate\Http\Request;

class DynamicCategory extends Model
{
    protected $table = 'dynamic_categories';

    protected $fillable = [
        'name',
        'description',
        'image_id',
        'slug',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $category): void {
            $attributes = request()->all();

            if (!empty($attributes)) {
                $include = (array) ($attributes['include_tags'] ?? []);

                $category->tags()->delete();

                foreach ($include as $tagId) {
                    if (!$tagId) {
                        continue;
                    }
                    $category->tags()->create([
                        'tag_id' => (int) $tagId,
                        'type' => 'include',
                    ]);
                }
            }
        });
    }

    public function tags(): HasMany
    {
        return $this->hasMany(DynamicCategoryTag::class);
    }

    public function includeTags(): HasMany
    {
        return $this->tags()->where('type', 'include');
    }

    public function excludeTags(): HasMany
    {
        return $this->tags()->where('type', 'exclude');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(File::class, 'image_id');
    }

    public function table(Request $request): DynamicCategoryTable
    {
        return new DynamicCategoryTable();
    }
}

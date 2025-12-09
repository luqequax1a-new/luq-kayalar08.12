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
        'rules_mode',
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
                $rulesMode = strtolower((string) ($attributes['rules_mode'] ?? 'all')) === 'any' ? 'any' : 'all';
                $category->rules_mode = $rulesMode;
                $category->saveQuietly();

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

                $rules = (array) ($attributes['rules'] ?? []);

                $category->rules()->delete();

                $position = 0;

                foreach ($rules as $rule) {
                    $field = trim($rule['field'] ?? '');

                    if ($field === '') {
                        continue;
                    }

                    $value = $rule['value'] ?? null;

                    if (is_array($value)) {
                        $value = json_encode($value);
                    }

                    $category->rules()->create([
                        'group_no' => 1,
                        'position' => $position++,
                        'field' => $field,
                        'operator' => (string) ($rule['operator'] ?? '='),
                        'value' => $value,
                        'boolean' => strtoupper($rulesMode === 'any' ? 'OR' : 'AND'),
                        'label' => $rule['label'] ?? null,
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

    public function rules(): HasMany
    {
        return $this->hasMany(DynamicCategoryRule::class);
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

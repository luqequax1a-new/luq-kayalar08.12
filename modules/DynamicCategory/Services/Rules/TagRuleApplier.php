<?php

namespace Modules\DynamicCategory\Services\Rules;

use Illuminate\Database\Eloquent\Builder;
use Modules\DynamicCategory\Entities\DynamicCategoryRule;

class TagRuleApplier implements RuleApplierInterface
{
    public function supports(string $field): bool
    {
        return in_array($field, ['tag_id', 'tag_slug'], true);
    }

    public function apply(Builder $query, DynamicCategoryRule $rule): void
    {
        $value = json_decode($rule->value, true) ?? $rule->value;

        $tagIds = [];
        $tagSlugs = [];

        if ($rule->field === 'tag_id') {
            $tagIds = is_array($value) ? $value : [$value];
        } elseif ($rule->field === 'tag_slug') {
            $tagSlugs = is_array($value) ? $value : [$value];
        }

        $method = $rule->operator === 'NOT_IN' ? 'whereDoesntHave' : 'whereHas';

        $query->{$method}('tags', function (Builder $q) use ($tagIds, $tagSlugs) {
            if (!empty($tagIds)) {
                $q->whereIn('tags.id', $tagIds);
            }

            if (!empty($tagSlugs)) {
                $q->whereIn('tags.slug', $tagSlugs);
            }
        });
    }
}

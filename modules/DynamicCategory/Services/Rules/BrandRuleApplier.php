<?php

namespace Modules\DynamicCategory\Services\Rules;

use Illuminate\Database\Eloquent\Builder;
use Modules\DynamicCategory\Entities\DynamicCategoryRule;

class BrandRuleApplier implements RuleApplierInterface
{
    public function supports(string $field): bool
    {
        return $field === 'brand_id';
    }

    public function apply(Builder $query, DynamicCategoryRule $rule): void
    {
        $value = json_decode($rule->value, true) ?? $rule->value;

        $brandIds = is_array($value) ? $value : [$value];
        $brandIds = array_filter($brandIds, static fn ($id) => $id !== null && $id !== '');

        if (empty($brandIds)) {
            return;
        }

        if ($rule->operator === 'NOT_IN') {
            $query->whereNotIn('products.brand_id', $brandIds);
        } else {
            $query->whereIn('products.brand_id', $brandIds);
        }
    }
}

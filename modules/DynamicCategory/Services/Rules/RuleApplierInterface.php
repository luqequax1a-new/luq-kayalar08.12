<?php

namespace Modules\DynamicCategory\Services\Rules;

use Illuminate\Database\Eloquent\Builder;
use Modules\DynamicCategory\Entities\DynamicCategoryRule;

interface RuleApplierInterface
{
    public function supports(string $field): bool;

    public function apply(Builder $query, DynamicCategoryRule $rule): void;
}

<?php

namespace Modules\DynamicCategory\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\DynamicCategory\Entities\DynamicCategory;
use Modules\DynamicCategory\Entities\DynamicCategoryRule;
use Modules\DynamicCategory\Services\Rules\RuleApplierInterface;

class DynamicCategoryRuleQueryBuilder
{
    /** @var RuleApplierInterface[] */
    private array $appliers;

    /**
     * @param iterable<RuleApplierInterface> $appliers
     */
    public function __construct(iterable $appliers)
    {
        $this->appliers = is_array($appliers) ? $appliers : iterator_to_array($appliers, false);
    }

    public function applyRules(Builder $query, DynamicCategory $category): void
    {
        $rules = $category->rules()->orderBy('group_no')->get();

        if ($rules->isEmpty()) {
            return;
        }

        /** @var array<int, \Illuminate\Support\Collection<int, DynamicCategoryRule>> $grouped */
        $grouped = $rules->groupBy('group_no');

        $query->where(function (Builder $outer) use ($grouped): void {
            foreach ($grouped as $groupRules) {
                $outer->where(function (Builder $inner) use ($groupRules): void {
                    $first = true;

                    foreach ($groupRules as $rule) {
                        $method = $first || strtoupper((string) $rule->boolean) === 'AND' ? 'where' : 'orWhere';
                        $first = false;

                        $inner->{$method}(function (Builder $sub) use ($rule): void {
                            $this->applySingleRule($sub, $rule);
                        });
                    }
                });
            }
        });
    }

    private function applySingleRule(Builder $query, DynamicCategoryRule $rule): void
    {
        foreach ($this->appliers as $applier) {
            if ($applier->supports($rule->field)) {
                $applier->apply($query, $rule);
                break;
            }
        }
    }
}

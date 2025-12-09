<?php

namespace Modules\DynamicCategory\Services\Rules;

use Illuminate\Database\Eloquent\Builder;
use Modules\DynamicCategory\Entities\DynamicCategoryRule;

class CreatedAtRuleApplier implements RuleApplierInterface
{
    public function supports(string $field): bool
    {
        return $field === 'created_at';
    }

    public function apply(Builder $query, DynamicCategoryRule $rule): void
    {
        $operator = strtoupper((string) $rule->operator);
        $value = (string) $rule->value;

        if ($value === '') {
            return;
        }

        $from = null;
        $to = null;

        // Beklenen format: "YYYY-MM-DD|YYYY-MM-DD" veya "from|to" tarzı basit bir ayraç.
        if (strpos($value, '|') !== false) {
            [$from, $to] = array_pad(explode('|', $value, 2), 2, null);
        }

        $from = $from ? trim($from) : null;
        $to = $to ? trim($to) : null;

        if (!$from && !$to) {
            return;
        }

        if ($operator === 'NOT_IN') {
            // Tarih aralığının DIŞINDA kalan ürünler
            $query->where(function (Builder $q) use ($from, $to) {
                if ($from) {
                    $q->where('products.created_at', '<', $from);
                }

                if ($to) {
                    $method = $from ? 'orWhere' : 'where';
                    $q->{$method}('products.created_at', '>', $to);
                }
            });
        } else {
            // Varsayılan: aralık içinde
            if ($from && $to) {
                $query->whereBetween('products.created_at', [$from, $to]);
            } elseif ($from) {
                $query->where('products.created_at', '>=', $from);
            } elseif ($to) {
                $query->where('products.created_at', '<=', $to);
            }
        }
    }
}

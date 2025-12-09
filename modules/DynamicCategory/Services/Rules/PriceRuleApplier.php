<?php

namespace Modules\DynamicCategory\Services\Rules;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\DynamicCategory\Entities\DynamicCategoryRule;

class PriceRuleApplier implements RuleApplierInterface
{
    public function supports(string $field): bool
    {
        return in_array($field, ['price', 'special_price', 'final_price'], true);
    }

    public function apply(Builder $query, DynamicCategoryRule $rule): void
    {
        $value = json_decode($rule->value, true) ?? $rule->value;

        // Hangi kolonun kullanılacağını alan adına göre belirle
        switch ($rule->field) {
            case 'special_price':
                $column = 'products.special_price';
                break;
            case 'final_price':
                // Özel fiyat varsa onu, yoksa normal price'ı kullan
                $column = DB::raw('COALESCE(products.special_price, products.price)');
                break;
            case 'price':
            default:
                $column = 'products.price';
                break;
        }

        $operator = strtoupper((string) $rule->operator);

        // Değer: [min,max] veya "min-max" stringi olabilir.
        $range = $value;
        if (!is_array($range)) {
            if (is_string($range) && strpos($range, '-') !== false) {
                $parts = array_map('trim', explode('-', $range, 2));
                if (count($parts) === 2) {
                    $range = $parts;
                }
            }
        }

        if (!is_array($range) || count($range) !== 2) {
            return;
        }

        [$min, $max] = $range;

        if ($operator === 'NOT_IN') {
            // Ikas: "içermeyen" fiyat aralığının dışında kalan ürünler
            $query->where(function (Builder $q) use ($column, $min, $max) {
                $q->where($column, '<', $min)
                  ->orWhere($column, '>', $max);
            });
        } else {
            // IN veya BETWEEN: aralık içinde
            $query->whereBetween($column, [$min, $max]);
        }
    }
}

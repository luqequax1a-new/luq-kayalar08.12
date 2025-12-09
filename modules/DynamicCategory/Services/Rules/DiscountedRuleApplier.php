<?php

namespace Modules\DynamicCategory\Services\Rules;

use Illuminate\Database\Eloquent\Builder;
use Modules\DynamicCategory\Entities\DynamicCategoryRule;

class DiscountedRuleApplier implements RuleApplierInterface
{
    public function supports(string $field): bool
    {
        return $field === 'discounted';
    }

    public function apply(Builder $query, DynamicCategoryRule $rule): void
    {
        $operator = strtoupper((string) $rule->operator);

        // Varsayılan: "IN" indirimli ürünleri, "NOT_IN" indirimli olmayanları temsil etsin.
        if ($operator === 'NOT_IN') {
            // Sadece indirim uygulanmamış veya geçersiz olan ürünler
            $query->where(function (Builder $q) {
                $q->whereNull('products.special_price')
                  ->orWhere(function (Builder $q2) {
                      $q2->whereNotNull('products.special_price')
                         ->where(function (Builder $dates) {
                             $dates->whereNotNull('products.special_price_start')
                                   ->where('products.special_price_start', '>', now())
                                   ->orWhere(function (Builder $inner) {
                                       $inner->whereNotNull('products.special_price_end')
                                             ->where('products.special_price_end', '<', now());
                                   });
                         });
                  });
            });
        } else {
            // IN veya diğer tüm operatörler: aktif indirimli ürünler
            $query->whereNotNull('products.special_price');
        }
    }
}

<?php

namespace Modules\Product\Services\Csv;

use Modules\Category\Entities\Category;

class CsvCategoryResolver
{
    public function findMissingCategoryIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $existing = Category::query()->whereIn('id', $ids)->pluck('id')->all();

        return array_values(array_diff($ids, $existing));
    }
}

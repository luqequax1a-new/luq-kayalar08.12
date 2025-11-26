<?php

namespace Modules\Product\Http\ViewComposers;

use Illuminate\View\View;
use Modules\Tag\Entities\Tag;
use Modules\Brand\Entities\Brand;
use Modules\Tax\Entities\TaxClass;
use Modules\Option\Entities\Option;
use Modules\Category\Entities\Category;
use Modules\Variation\Entities\Variation;
use Modules\Attribute\Entities\AttributeSet;
use Modules\Unit\Entities\Unit;

class ProductCreatePageComposer
{
    /**
     * Bind data to the view.
     *
     * @param View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $view->with([
            'permissions' => auth()->user()->permissions,
            'globalVariations' => Variation::globals()->latest()->get(),
            'globalOptions' => Option::globals()->latest()->get(),
            'brands' => Brand::keyValuedList(),
            'categories' => Category::keyValuedtreeList(),
            'taxClasses' => TaxClass::list(),
            'units' => Unit::all()->sortBy('name')->map(function (Unit $u) {
                return [
                    'name' => $u->name,
                    'value' => $u->id,
                    'step' => (float) $u->step,
                    'min' => (float) $u->min,
                    'is_decimal_stock' => (bool) $u->isDecimalStock(),
                    'short_suffix' => (string) $u->getDisplaySuffix(),
                ];
            })->values(),
            'tags' => Tag::keyValuedList(),
            'attributeSets' => AttributeSet::with('attributes.values')->get()->sortBy('name'),
        ]);
    }
}

<?php

namespace Modules\DynamicCategory\Http\Controllers\Admin;

use Modules\Admin\Traits\HasCrudActions;
use Modules\DynamicCategory\Entities\DynamicCategory;
use Modules\DynamicCategory\Http\Requests\SaveDynamicCategoryRequest;
use Modules\DynamicCategory\Services\DynamicCategoryProductService;
use Modules\Product\Entities\Product;

class DynamicCategoryController
{
    use HasCrudActions;

    protected $model = DynamicCategory::class;

    protected $label = 'dynamic_category::dynamic_categories.dynamic_category';

    protected $viewPath = 'dynamic_category::admin.dynamic_categories';

    protected $validation = SaveDynamicCategoryRequest::class;

    // No extra form data is required; dynamic categories are configured via
    // basic information and tag rules only.
}

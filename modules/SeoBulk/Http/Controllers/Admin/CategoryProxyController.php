<?php

namespace Modules\SeoBulk\Http\Controllers\Admin;

use Modules\Category\Entities\Category;
use Modules\Category\Http\Responses\CategoryTreeResponse;

class CategoryProxyController
{
    public function index()
    {
        $categories = Category::withoutGlobalScope('active')
            ->orderByRaw('-position DESC')
            ->get();

        return new CategoryTreeResponse($categories);
    }
}


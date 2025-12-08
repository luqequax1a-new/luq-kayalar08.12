<?php

namespace Modules\DynamicCategory\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\DynamicCategory\Entities\DynamicCategory;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Exceptions\Exception;

class DynamicCategoryTable extends AdminTable
{
    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function make()
    {
        $query = DynamicCategory::query()
            ->withCount([
                'includeTags as include_tags_count',
                'excludeTags as exclude_tags_count',
            ]);

        if (request()->filled('status')) {
            $status = request('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $this->source = $query;

        return $this->newTable()
            ->addColumn('name', function (DynamicCategory $category) {
                return $category->name;
            })
            ->addColumn('slug', function (DynamicCategory $category) {
                return $category->slug;
            })
            ->addColumn('include_tags_count', function (DynamicCategory $category) {
                return $category->include_tags_count;
            })
            ->addColumn('exclude_tags_count', function (DynamicCategory $category) {
                return $category->exclude_tags_count;
            });
    }
}

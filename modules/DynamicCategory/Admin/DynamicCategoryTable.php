<?php

namespace Modules\DynamicCategory\Admin;

use Illuminate\Http\JsonResponse;
use Modules\Admin\Ui\AdminTable;
use Modules\DynamicCategory\Entities\DynamicCategory;
use Modules\DynamicCategory\Services\DynamicCategoryProductService;
use Yajra\DataTables\Exceptions\Exception;

class DynamicCategoryTable extends AdminTable
{
    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected array $rawColumns = ['status', 'created', 'actions', 'name'];

    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function make()
    {
        $query = DynamicCategory::query();

        if (request()->filled('status')) {
            $status = request('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $this->source = $query;

        /** @var DynamicCategoryProductService $productService */
        $productService = app(DynamicCategoryProductService::class);

        return $this->newTable()
            ->addColumn('name', function (DynamicCategory $category) {
                $url = route('admin.dynamic_categories.edit', $category->id);

                return "<a href='" . e($url) . "' class='name-link' title='Edit'>" . e($category->name) . '</a>';
            })
            ->addColumn('slug', function (DynamicCategory $category) {
                return $category->slug;
            })
            ->addColumn('products_count', function (DynamicCategory $category) use ($productService) {
                try {
                    return $productService->buildQuery($category)->count();
                } catch (\Throwable $e) {
                    return 0;
                }
            })
            ->addColumn('actions', function (DynamicCategory $category) {
                $editUrl = route('admin.dynamic_categories.edit', $category->id);
                $viewUrl = route('categories.products.index', ['category' => $category->slug]);

                return "<div class='actions-grid' style='display:inline-flex;align-items:center;justify-content:center;gap:6px;'>
                    <a href='" . e($editUrl) . "' class='action-edit' title='Edit' data-toggle='tooltip' onclick='event.stopPropagation();'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'>
                            <path d='M4 20H20' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                            <path d='M16.44 3.56006L20.44 7.56006' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                            <path d='M14.02 5.98999L6.91 13.1C6.52 13.49 6.15 14.25 6.07 14.81L5.64 17.83C5.45 19.08 6.42 20.04 7.67 19.86L10.69 19.43C11.25 19.35 12.01 18.98 12.41 18.59L19.52 11.48' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                        </svg>
                    </a>
                    <a href='" . e($viewUrl) . "' target='_blank' class='action-view' title='View' data-toggle='tooltip' onclick='event.stopPropagation();'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'>
                            <path d='M2 12C2 12 5.99997 4 12 4C18 4 22 12 22 12C22 12 18 20 12 20C5.99997 20 2 12 2 12Z' stroke='#292D32' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/>
                            <path d='M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z' stroke='#292D32' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/>
                        </svg>
                    </a>
                </div>";
            });
    }
}

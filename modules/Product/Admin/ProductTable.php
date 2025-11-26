<?php

namespace Modules\Product\Admin;

use Modules\Admin\Ui\AdminTable;
use Illuminate\Http\JsonResponse;
use Modules\Product\Entities\Product;

class ProductTable extends AdminTable
{
    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected array $rawColumns = ['price', 'in_stock', 'status', 'actions', 'name'];


    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     */
    public function make()
    {
        return $this->newTable()
            ->editColumn('thumbnail', function ($product) {
                return view('admin::partials.table.image', [
                    'file' => ($product->variant && $product->variant->base_image->id) ? $product->variant->base_image : $product->base_image,
                ]);
            })
            ->editColumn('price', function (Product $product) {
                return product_price_formatted($product->variant ?? $product, function ($price, $specialPrice) use ($product) {
                    if ($product->variant ? $product->variant->hasSpecialPrice() : $product->hasSpecialPrice()) {
                        return "<span class='m-r-5'>{$specialPrice}</span>
                            <del class='text-red'>{$price}</del>";
                    }

                    return "<span class='m-r-5'>{$price}</span>";
                });
            })
            ->editColumn('in_stock', function (Product $product) {
                return e($product->getFormattedStock());
            })
            ->editColumn('name', function (Product $product) {
                $url = route('admin.products.edit', $product->id);
                return "<a href='{$url}' class='name-link' title='Edit'>" . e($product->name) . "</a>";
            })
            ->editColumn('status', function (Product $product) {
                $checked = $product->is_active ? 'checked' : '';

                return "<div class='switch'>
                    <input type='checkbox' class='product-status-switch' id='product-{$product->id}-status' data-id='{$product->id}' {$checked} />
                    <label for='product-{$product->id}-status'></label>
                </div>";
            })
            ->addColumn('actions', function (Product $product) {
                $editUrl = route('admin.products.edit', $product->id);
                $viewUrl = route('products.show', $product->slug);
                $deleteId = $product->id;

                return "<div class='d-flex align-items-center'>
                    <a href='{$editUrl}' class='action-edit' title='Edit' data-toggle='tooltip'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'>
                            <path d='M4 20H20' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                            <path d='M16.44 3.56006L20.44 7.56006' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                            <path d='M14.02 5.98999L6.91 13.1C6.52 13.49 6.15 14.25 6.07 14.81L5.64 17.83C5.45 19.08 6.42 20.04 7.67 19.86L10.69 19.43C11.25 19.35 12.01 18.98 12.41 18.59L19.52 11.48' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                        </svg>
                    </a>
                    <a href='{$viewUrl}' target='_blank' class='action-view m-l-10' title='View' data-toggle='tooltip'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'>
                            <path d='M2 12C2 12 5.99997 4 12 4C18 4 22 12 22 12C22 12 18 20 12 20C5.99997 20 2 12 2 12Z' stroke='#292D32' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/>
                            <path d='M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z' stroke='#292D32' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/>
                        </svg>
                    </a>
                    <a href='#' class='action-delete m-l-10' data-id='{$deleteId}' title='Delete' data-toggle='tooltip' data-confirm>
                        <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'>
                            <path d='M9 3H15' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                            <path d='M4 7H20' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                            <path d='M7 7L7.5 19C7.5 20.1046 8.39543 21 9.5 21H14.5C15.6046 21 16.5 20.1046 16.5 19L17 7' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                        </svg>
                    </a>
                </div>";
            });
    }
}

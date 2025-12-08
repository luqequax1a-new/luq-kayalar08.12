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
    protected array $rawColumns = ['price', 'in_stock', 'status', 'actions', 'name', 'brand'];


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
                    'file' => ($product->base_image && $product->base_image->id)
                        ? $product->base_image
                        : (($product->variant && $product->variant->base_image && $product->variant->base_image->id)
                            ? $product->variant->base_image
                            : $product->base_image),
                ]);
            })
            ->editColumn('price', function (Product $product) {
                if ($product->variants && $product->variants->count() > 0) {
                    $variants = $product->variants;

                    $originalMin = $variants->min(function ($v) { return $v->price->amount(); });
                    $originalMax = $variants->max(function ($v) { return $v->price->amount(); });

                    $promoMin = $variants->min(function ($v) { return $v->getSellingPrice()->amount(); });
                    $promoMax = $variants->max(function ($v) { return $v->getSellingPrice()->amount(); });

                    $originalMinFmt = \Modules\Support\Money::inDefaultCurrency($originalMin)->convertToCurrentCurrency()->format();
                    $originalMaxFmt = \Modules\Support\Money::inDefaultCurrency($originalMax)->convertToCurrentCurrency()->format();
                    $promoMinFmt = \Modules\Support\Money::inDefaultCurrency($promoMin)->convertToCurrentCurrency()->format();
                    $promoMaxFmt = \Modules\Support\Money::inDefaultCurrency($promoMax)->convertToCurrentCurrency()->format();

                    $originalRange = $originalMinFmt === $originalMaxFmt ? $originalMinFmt : "$originalMinFmt - $originalMaxFmt";
                    $promoRange = $promoMinFmt === $promoMaxFmt ? $promoMinFmt : "$promoMinFmt - $promoMaxFmt";

                    if ($originalRange !== $promoRange) {
                        return "<div class='price-cell' data-id='{$product->id}'><div class='price-top'><del class='text-red'>{$originalRange}</del></div><div class='price-bottom'>{$promoRange}</div></div>";
                    }

                    return "<div class='price-cell' data-id='{$product->id}'><div class='price-bottom'>{$originalRange}</div></div>";
                }

                // single/variant default
                $priceHtml = product_price_formatted($product->variant ?? $product, function ($price, $specialPrice) use ($product) {
                    if ($product->variant ? $product->variant->hasSpecialPrice() : $product->hasSpecialPrice()) {
                        return "<div class='price-cell' data-id='{$product->id}'><div class='price-top'><del class='text-red'>{$price}</del></div><div class='price-bottom'>{$specialPrice}</div></div>";
                    }
                    return "<div class='price-cell' data-id='{$product->id}'><div class='price-bottom'>{$price}</div></div>";
                });

                return $priceHtml;
            })
            ->addColumn('brand', function (Product $product) {
                $rawName = optional($product->brand)->name;
                $name = $rawName !== null && $rawName !== '' ? e($rawName) : '&mdash;';
                $id = (int) $product->id;
                $brandId = $product->brand_id ? (int) $product->brand_id : '';

                return "<span class='brand-cell' data-id='{$id}' data-brand-id='{$brandId}'>{$name}</span>";
            })
            ->addColumn('default_category', function (Product $product) {
                // Default category is always and only primaryCategory.
                // If current locale translation is empty, fall back to any available translation.
                $category = $product->primaryCategory;

                if (!$category) {
                    return '';
                }

                $name = $category->name;

                if ($name === null || $name === '') {
                    try {
                        $translation = $category->translations()
                            ->withoutGlobalScope('locale')
                            ->first();
                        if ($translation && isset($translation->name)) {
                            $name = $translation->name;
                        }
                    } catch (\Throwable $e) {
                        // ignore translation fallback errors
                    }
                }

                return e($name ?: '');
            })
            ->editColumn('in_stock', function (Product $product) {
                $clickable = (bool) $product->manage_stock || ($product->variants && $product->variants->where('manage_stock', true)->count() > 0);

                if ($product->variants && $product->variants->count() > 0) {
                    $activeVariants = $product->variants->where('is_active', true);
                    $count = $activeVariants->count();

                    $noManage = !$product->manage_stock && ($product->variants->where('manage_stock', true)->count() === 0);

                    if ($noManage) {
                        $inner = "<div class='stock-total'>" . e('Stokta') . "</div>"
                            . "<div class='stock-count'>" . e("{$count} varyant") . "</div>";

                        if ($clickable) {
                            return "<a href='#' class='inventory-click' data-id='{$product->id}'>" . $inner . "</a>";
                        }
                        return "<div class='stock-cell-disabled'>" . $inner . "</div>";
                    }

                    $sumQty = (float) $activeVariants->sum(function ($v) { return (float) $v->qty; });

                    $suffix = $product->saleUnit ? trim($product->saleUnit->getDisplaySuffix()) : '';
                    $value = fmod($sumQty, 1) === 0.0
                        ? (string) (int) $sumQty
                        : rtrim(rtrim(number_format($sumQty, 2, '.', ''), '0'), '.');
                    $stockText = $suffix !== '' ? "$value $suffix" : $value;

                    $inner = "<div class='stock-total'>" . e($stockText) . "</div>"
                        . "<div class='stock-count'>" . e("{$count} varyant") . "</div>";

                    if ($clickable) {
                        return "<a href='#' class='inventory-click' data-id='{$product->id}'>" . $inner . "</a>";
                    }
                    return "<div class='stock-cell-disabled'>" . $inner . "</div>";
                }

                $text = !$product->manage_stock ? e('Stokta') : e($product->getFormattedStock());
                if ($clickable) {
                    return "<a href='#' class='inventory-click' data-id='{$product->id}'>" . $text . "</a>";
                }
                return $text;
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

                $duplicateForm = "<form method='POST' action='" . e(route('admin.products.duplicate', $product->id)) . "' style='display:inline'>" . csrf_field() . "
                        <button type='submit' class='action-duplicate' title='Copy' aria-label='Copy product' data-toggle='tooltip' style='background:none;border:none;padding:0;'>
                            <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'>
                                <rect x='9' y='9' width='10' height='10' rx='2' stroke='#292D32' stroke-width='1.5'/>
                                <rect x='5' y='5' width='10' height='10' rx='2' stroke='#292D32' stroke-width='1.5'/>
                            </svg>
                        </button>
                    </form>";

                return "<div class='actions-grid'>
                    <a href='{$editUrl}' class='action-edit' title='Edit' data-toggle='tooltip'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'>
                            <path d='M4 20H20' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                            <path d='M16.44 3.56006L20.44 7.56006' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                            <path d='M14.02 5.98999L6.91 13.1C6.52 13.49 6.15 14.25 6.07 14.81L5.64 17.83C5.45 19.08 6.42 20.04 7.67 19.86L10.69 19.43C11.25 19.35 12.01 18.98 12.41 18.59L19.52 11.48' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                        </svg>
                    </a>
                    <a href='{$viewUrl}' target='_blank' class='action-view' title='View' data-toggle='tooltip'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'>
                            <path d='M2 12C2 12 5.99997 4 12 4C18 4 22 12 22 12C22 12 18 20 12 20C5.99997 20 2 12 2 12Z' stroke='#292D32' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/>
                            <path d='M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z' stroke='#292D32' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/>
                        </svg>
                    </a>
                    {$duplicateForm}
                    <a href='#' class='action-delete' data-id='{$deleteId}' title='Delete' data-toggle='tooltip' data-confirm>
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

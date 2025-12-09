<?php

namespace Modules\Product\Services\Csv;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Entities\Product;

class CsvExportService
{
    public function exportProducts(Builder $query, $request): string
    {
        $query->withoutGlobalScope('active')
            ->with(['translations', 'categories', 'brand', 'tags', 'saleUnit', 'productMedia']);

        $rows = [];

        $columns = $this->buildColumns();

        foreach ($query->cursor() as $product) {
            $rows[] = $this->mapProductToRow($product, $columns);
        }

        $tempId = uniqid('products_export_', true);
        $path = "exports/{$tempId}.csv";
        $absolute = Storage::disk('local')->path($path);

        if (!is_dir(dirname($absolute))) {
            mkdir(dirname($absolute), 0777, true);
        }

        $handle = fopen($absolute, 'w');
        fputcsv($handle, array_keys($columns));
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        return $absolute;
    }

    protected function buildColumns(): array
    {
        $columns = [
            'id' => 'id',
            'slug' => 'slug',
            'sku' => 'sku',
            'brand_id' => 'brand_id',
            'tax_class_id' => 'tax_class_id',
            'sale_unit_id' => 'sale_unit_id',
            'primary_category_id' => 'primary_category_id',
            'google_product_category_id' => 'google_product_category_id',
            'google_product_category_path' => 'google_product_category_path',
            'price' => 'price',
            'special_price' => 'special_price',
            'special_price_type' => 'special_price_type',
            'special_price_start' => 'special_price_start',
            'special_price_end' => 'special_price_end',
            'selling_price' => 'selling_price',
            'manage_stock' => 'manage_stock',
            'qty' => 'qty',
            'in_stock' => 'in_stock',
            'is_virtual' => 'is_virtual',
            'is_active' => 'is_active',
            'new_from' => 'new_from',
            'new_to' => 'new_to',
            'list_variants_separately' => 'list_variants_separately',
            'name' => 'name',
            'description' => 'description',
            'short_description' => 'short_description',
            'category_ids' => 'category_ids',
            'category_names' => 'category_names',
            'brand_name' => 'brand_name',
            'tag_ids' => 'tag_ids',
            'images' => 'images',
        ];

        return $columns;
    }

    protected function mapProductToRow(Product $product, array $columns): array
    {
        $defaultTranslation = $product->translations->first();

        $categoryIds = $product->categories->pluck('id')->implode(',');
        $categoryNames = $product->categories->pluck('name')->implode(',');
        $tagIds = method_exists($product, 'tags') ? $product->tags->pluck('id')->implode(',') : '';

        $images = '';
        if ($product->productMedia && $product->productMedia->count() > 0) {
            $images = $product->productMedia->pluck('path')->implode(',');
        }

        $row = [];
        foreach ($columns as $key => $field) {
            switch ($key) {
                case 'name':
                    $row[] = optional($defaultTranslation)->name;
                    break;
                case 'description':
                    $row[] = optional($defaultTranslation)->description;
                    break;
                case 'short_description':
                    $row[] = optional($defaultTranslation)->short_description;
                    break;
                case 'category_ids':
                    $row[] = $categoryIds;
                    break;
                case 'category_names':
                    $row[] = $categoryNames;
                    break;
                case 'brand_name':
                    $row[] = optional($product->brand)->name;
                    break;
                case 'tag_ids':
                    $row[] = $tagIds;
                    break;
                case 'images':
                    $row[] = $images;
                    break;
                default:
                    $value = $product->{$field} ?? null;
                    if ($value instanceof \Modules\Support\Money) {
                        $value = $value->amount();
                    }
                    $row[] = $value;
                    break;
            }
        }

        return $row;
    }
}

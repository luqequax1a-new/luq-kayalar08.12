<?php

namespace Modules\Product\Services\Csv;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Entities\ProductVariant;
use Modules\Variation\Entities\VariationValue;

class CsvVariantExportService
{
    public function exportVariants(Builder $query): string
    {
        $query->with(['product' => function ($q) {
            $q->withoutGlobalScope('active')->with(['translations', 'categories', 'brand']);
        }])->withoutGlobalScope('active');

        $columns = $this->buildColumns();
        $rows = [];

        foreach ($query->cursor() as $variant) {
            $rows[] = $this->mapVariantToRow($variant, $columns);
        }

        $tempId = uniqid('product_variants_export_', true);
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
        return [
            'variant_id' => 'id',
            'product_id' => 'product_id',
            'product_sku' => 'product_sku',
            'product_name' => 'product_name',
            'product_slug' => 'product_slug',
            'sku' => 'sku',
            'uid' => 'uid',
            'uids' => 'uids',
            'name' => 'name',
            'price' => 'price',
            'special_price' => 'special_price',
            'special_price_type' => 'special_price_type',
            'special_price_start' => 'special_price_start',
            'special_price_end' => 'special_price_end',
            'selling_price' => 'selling_price',
            'manage_stock' => 'manage_stock',
            'qty' => 'qty',
            'in_stock' => 'in_stock',
            'is_default' => 'is_default',
            'is_active' => 'is_active',
            'position' => 'position',
            'attributes' => 'attributes',
        ];
    }

    protected function mapVariantToRow(ProductVariant $variant, array $columns): array
    {
        $product = $variant->product;
        $translation = $product?->translations?->first();

        $row = [];
        foreach ($columns as $key => $field) {
            switch ($key) {
                case 'variant_id':
                    $row[] = $variant->id;
                    break;
                case 'product_id':
                    $row[] = $variant->product_id;
                    break;
                case 'product_sku':
                    $row[] = $product?->sku;
                    break;
                case 'product_name':
                    $row[] = $translation?->name ?? $product?->name;
                    break;
                case 'product_slug':
                    $row[] = $product?->slug;
                    break;
                case 'attributes':
                    $row[] = $this->buildAttributesString($variant);
                    break;
                default:
                    $value = $variant->{$field} ?? null;
                    if ($value instanceof \Modules\Support\Money) {
                        $value = $value->amount();
                    }
                    $row[] = $value;
                    break;
            }
        }

        return $row;
    }

    protected function buildAttributesString(ProductVariant $variant): string
    {
        $uids = (string) $variant->uids;
        if ($uids === '') {
            return '';
        }

        $valueUids = array_filter(explode('.', $uids));
        if (empty($valueUids)) {
            return '';
        }

        $pairs = [];

        $values = VariationValue::query()
            ->with(['variation'])
            ->whereIn('uid', $valueUids)
            ->get();

        foreach ($values as $value) {
            $variationName = $value->variation?->name ?? '';
            $label = $value->label ?? '';
            if ($variationName === '' || $label === '') {
                continue;
            }
            $pairs[] = $variationName . '=' . $label;
        }

        return implode('|', $pairs);
    }
}

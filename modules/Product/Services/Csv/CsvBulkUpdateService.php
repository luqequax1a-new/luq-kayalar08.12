<?php

namespace Modules\Product\Services\Csv;

use Modules\Product\Entities\Product;

class CsvBulkUpdateService
{
    public function handleRow(array $data, string $mode, string $identifier): void
    {
        if ($mode === 'create') {
            $this->createProduct($data);
            return;
        }

        $product = $this->findProduct($data, $identifier);
        if (!$product) {
            return;
        }

        $this->updateProduct($product, $data);
    }

    protected function findProduct(array $data, string $identifier): ?Product
    {
        if ($identifier === 'id' && !empty($data['id'])) {
            return Product::query()->withoutGlobalScope('active')->find((int) $data['id']);
        }

        if ($identifier === 'sku' && !empty($data['sku'])) {
            return Product::query()->withoutGlobalScope('active')->where('sku', $data['sku'])->first();
        }

        return null;
    }

    protected function createProduct(array $data): void
    {
        $trans = [
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'short_description' => $data['short_description'] ?? null,
        ];

        unset($data['name'], $data['description'], $data['short_description']);

        $product = Product::create($this->onlyProductFields($data));

        $product->translations()->create(array_merge($trans, [
            'locale' => app()->getLocale(),
        ]));
    }

    protected function updateProduct(Product $product, array $data): void
    {
        $transData = [];
        if (array_key_exists('name', $data) && $data['name'] !== '') {
            $transData['name'] = $data['name'];
        }
        if (array_key_exists('description', $data) && $data['description'] !== '') {
            $transData['description'] = $data['description'];
        }
        if (array_key_exists('short_description', $data) && $data['short_description'] !== '') {
            $transData['short_description'] = $data['short_description'];
        }

        unset($data['name'], $data['description'], $data['short_description']);

        $product->update($this->onlyProductFields($data));

        if (!empty($transData)) {
            $product->translations()->updateOrCreate(
                ['locale' => app()->getLocale()],
                $transData,
            );
        }
    }

    protected function onlyProductFields(array $data): array
    {
        $fillable = [
            'brand_id',
            'tax_class_id',
            'sale_unit_id',
            'primary_category_id',
            'google_product_category_id',
            'google_product_category_path',
            'slug',
            'sku',
            'price',
            'special_price',
            'special_price_type',
            'special_price_start',
            'special_price_end',
            'selling_price',
            'manage_stock',
            'qty',
            'in_stock',
            'is_virtual',
            'is_active',
            'new_from',
            'new_to',
            'list_variants_separately',
        ];

        $productData = [];
        foreach ($fillable as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== '') {
                $productData[$field] = $data[$field];
            }
        }

        return $productData;
    }
}

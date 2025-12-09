<?php

namespace Modules\Product\Services\Csv;

use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Variation\Entities\Variation;
use Modules\Variation\Entities\VariationValue;

class CsvVariantBulkUpdateService
{
    public function handleRow(array $data, string $mode, string $identifier): void
    {
        if ($mode === 'update') {
            $variant = $this->findVariant($data, $identifier);
            if (!$variant) {
                return;
            }
            $this->updateVariant($variant, $data);
            return;
        }

        $product = $this->findProductForCreate($data);
        if (!$product) {
            return;
        }

        $this->createVariant($product, $data);
    }

    protected function findVariant(array $data, string $identifier): ?ProductVariant
    {
        if ($identifier === 'id' && !empty($data['id'])) {
            return ProductVariant::query()->withoutGlobalScope('active')->find((int) $data['id']);
        }

        if ($identifier === 'sku' && !empty($data['sku'])) {
            $query = ProductVariant::query()->withoutGlobalScope('active')->where('sku', $data['sku']);
            if (!empty($data['product_id'])) {
                $query->where('product_id', (int) $data['product_id']);
            }
            return $query->first();
        }

        return null;
    }

    protected function findProductForCreate(array $data): ?Product
    {
        if (!empty($data['product_id'])) {
            return Product::query()->withoutGlobalScope('active')->find((int) $data['product_id']);
        }

        if (!empty($data['product_sku'])) {
            return Product::query()->withoutGlobalScope('active')->where('sku', $data['product_sku'])->first();
        }

        return null;
    }

    protected function createVariant(Product $product, array $data): void
    {
        $payload = $this->prepareVariantPayload($data);
        $payload['product_id'] = $product->id;
        ProductVariant::query()->withoutGlobalScope('active')->create($payload);
    }

    protected function updateVariant(ProductVariant $variant, array $data): void
    {
        $payload = $this->prepareVariantPayload($data);
        if (!empty($payload)) {
            $variant->update($payload);
        }
    }

    protected function prepareVariantPayload(array $data): array
    {
        if (!empty($data['attributes'])) {
            [$uids, $name] = $this->attributesToVariantMeta($data['attributes']);
            if ($uids !== null) {
                $data['uids'] = $uids;
            }
            if ($name !== null) {
                $data['name'] = $name;
            }
        }

        return $this->onlyVariantFields($data);
    }

    protected function onlyVariantFields(array $data): array
    {
        $fillable = [
            'uid', 'uids', 'name', 'sku', 'price', 'special_price', 'special_price_type',
            'special_price_start', 'special_price_end', 'selling_price', 'manage_stock', 'qty',
            'in_stock', 'is_default', 'is_active', 'position',
        ];

        $variantData = [];
        foreach ($fillable as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== '') {
                $variantData[$field] = $data[$field];
            }
        }

        return $variantData;
    }

    protected function attributesToVariantMeta(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            return [null, null];
        }

        $pairs = array_filter(explode('|', $value));
        $valueUids = [];
        $labels = [];

        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if ($pair === '' || !str_contains($pair, '=')) {
                continue;
            }
            [$varName, $valLabel] = array_map('trim', explode('=', $pair, 2));
            if ($varName === '' || $valLabel === '') {
                continue;
            }

            $variation = Variation::query()
                ->whereHas('translations', function ($q) use ($varName) {
                    $q->where('name', $varName);
                })
                ->first();

            if (!$variation) {
                continue;
            }

            $valueModel = VariationValue::query()
                ->where('variation_id', $variation->id)
                ->whereHas('translations', function ($q) use ($valLabel) {
                    $q->where('label', $valLabel);
                })
                ->first();

            if (!$valueModel) {
                continue;
            }

            $valueUids[] = $valueModel->uid;
            $labels[] = $valLabel;
        }

        if (empty($valueUids)) {
            return [null, null];
        }

        sort($valueUids);
        $uidsString = implode('.', $valueUids);
        $name = implode(' / ', $labels);

        return [$uidsString, $name];
    }
}

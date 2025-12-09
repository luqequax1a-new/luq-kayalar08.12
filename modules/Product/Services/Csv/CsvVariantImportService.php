<?php

namespace Modules\Product\Services\Csv;

use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Variation\Entities\Variation;
use Modules\Variation\Entities\VariationValue;

class CsvVariantImportService
{
    public function __construct(protected CsvReaderService $reader)
    {
    }

    public function preview(string $tempId, array $mapping, string $mode, string $identifier): array
    {
        $rows = [];
        $valid = 0;
        $invalid = 0;
        $index = 0;
        $limit = 50;

        foreach ($this->reader->readRows($tempId) as $row) {
            $index++;
            $mapped = $this->applyMapping($row, $mapping);
            [$action, $errors] = $this->analyzeRow($mapped, $mode, $identifier);

            if (!empty($errors)) {
                $invalid++;
            } else {
                $valid++;
            }

            if (count($rows) < $limit) {
                $rows[] = [
                    'index' => $index,
                    'action' => $action,
                    'errors' => $errors,
                ];
            }
        }

        return [
            'total' => $index,
            'valid' => $valid,
            'invalid' => $invalid,
            'rows' => $rows,
        ];
    }

    protected function applyMapping(array $row, array $mapping): array
    {
        $result = [];
        foreach ($mapping as $csvColumn => $field) {
            if (!$field) continue;
            $result[$field] = $row[$csvColumn] ?? null;
        }
        return $result;
    }

    protected function analyzeRow(array $data, string $mode, string $identifier): array
    {
        $errors = [];
        $action = 'skip';

        if ($mode === 'update') {
            $variant = $this->findVariant($data, $identifier);
            if (!$variant) {
                $errors[] = 'Varyant bulunamadı.';
            } else {
                $action = 'update';
            }
        } else {
            $product = $this->findProductForCreate($data);
            if (!$product) {
                $errors[] = 'Ürün bulunamadı.';
            } else {
                $action = 'create';
            }
        }

        if (isset($data['price']) && $data['price'] !== '' && !is_numeric(str_replace(',', '.', $data['price']))) {
            $errors[] = 'Fiyat alanı geçersiz.';
        }

        if (empty($data['sku']) && $mode === 'create') {
            $errors[] = 'SKU zorunludur.';
        }

        if (!empty($data['attributes'])) {
            $attrErrors = $this->validateAttributes($data['attributes']);
            $errors = array_merge($errors, $attrErrors);
        }

        return [$action, $errors];
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

    protected function validateAttributes(string $value): array
    {
        $errors = [];
        $value = trim($value);
        if ($value === '') {
            return $errors;
        }

        $pairs = array_filter(explode('|', $value));
        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if ($pair === '') {
                continue;
            }
            if (!str_contains($pair, '=')) {
                $errors[] = "Geçersiz attributes formatı: {$pair}";
                continue;
            }
            [$varName, $valLabel] = array_map('trim', explode('=', $pair, 2));
            if ($varName === '' || $valLabel === '') {
                $errors[] = "Geçersiz attributes formatı: {$pair}";
                continue;
            }

            $variation = Variation::query()
                ->whereHas('translations', function ($q) use ($varName) {
                    $q->where('name', $varName);
                })
                ->first();

            if (!$variation) {
                $errors[] = "Varyasyon bulunamadı: {$varName}";
                continue;
            }

            $valueModel = VariationValue::query()
                ->where('variation_id', $variation->id)
                ->whereHas('translations', function ($q) use ($valLabel) {
                    $q->where('label', $valLabel);
                })
                ->first();

            if (!$valueModel) {
                $errors[] = "Seçenek değeri bulunamadı: {$varName}={$valLabel}";
            }
        }

        return $errors;
    }
}

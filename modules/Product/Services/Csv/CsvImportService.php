<?php

namespace Modules\Product\Services\Csv;

use Modules\Product\Entities\Product;
use Modules\Product\Services\Csv\CsvCategoryResolver;
use Modules\Product\Services\Csv\CsvImageImporter;

class CsvImportService
{
    public function __construct(
        protected CsvReaderService $reader,
        protected CsvCategoryResolver $categoryResolver,
        protected CsvImageImporter $imageImporter,
    ) {
    }

    public function preview(string $tempId, array $mapping, string $mode, string $identifier): array
    {
        $rows = [];
        $valid = 0;
        $invalid = 0;
        $limit = 50;
        $index = 0;

        foreach ($this->reader->readRows($tempId) as $row) {
            $index++;
            $mapped = $this->applyMapping($row, $mapping);
            $errors = $this->validateRow($mapped, $mode, $identifier);

            if (!empty($errors)) {
                $invalid++;
            } else {
                $valid++;
            }

            if (count($rows) < $limit) {
                $rows[] = [
                    'index' => $index,
                    'action' => $this->determineAction($mapped, $mode, $identifier),
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
            if ($field === null || $field === '') {
                continue;
            }
            $result[$field] = $row[$csvColumn] ?? null;
        }
        return $result;
    }

    protected function validateRow(array $data, string $mode, string $identifier): array
    {
        $errors = [];

        if ($mode === 'update') {
            if ($identifier === 'id') {
                if (empty($data['id']) || !Product::query()->where('id', (int) $data['id'])->exists()) {
                    $errors[] = 'Ürün ID bulunamadı.';
                }
            } elseif ($identifier === 'sku') {
                if (empty($data['sku']) || !Product::query()->where('sku', $data['sku'])->exists()) {
                    $errors[] = 'Ürün SKU bulunamadı.';
                }
            }
        }

        if ($mode === 'create') {
            if (!isset($data['name']) || $data['name'] === '') {
                $errors[] = 'İsim zorunludur.';
            }
        }

        if (isset($data['price']) && $data['price'] !== '' && !is_numeric(str_replace(',', '.', $data['price']))) {
            $errors[] = 'Fiyat alanı geçersiz.';
        }

        if (isset($data['category_ids']) && $data['category_ids'] !== '') {
            $ids = array_filter(array_map('intval', explode(',', $data['category_ids'])));
            $missing = $this->categoryResolver->findMissingCategoryIds($ids);
            if (!empty($missing)) {
                $errors[] = 'Kategori bulunamadı: ' . implode(',', $missing);
            }
        }

        return $errors;
    }

    protected function determineAction(array $data, string $mode, string $identifier): string
    {
        if ($mode === 'create') {
            return 'create';
        }

        if ($identifier === 'id') {
            return 'update';
        }

        if ($identifier === 'sku') {
            return 'update';
        }

        return 'skip';
    }
}

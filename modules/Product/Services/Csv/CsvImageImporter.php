<?php

namespace Modules\Product\Services\Csv;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Media\Entities\File;
use Modules\Product\Entities\Product;

class CsvImageImporter
{
    public function attachImages(Product $product, ?string $value): void
    {
        if ($value === null || trim($value) === '') {
            return;
        }

        $paths = array_filter(array_map('trim', explode(',', $value)));
        if (empty($paths)) {
            return;
        }

        $files = [];

        foreach ($paths as $path) {
            if (Str::startsWith($path, ['http://', 'https://'])) {
                continue;
            }

            $files[] = [
                'path' => $path,
            ];
        }

        if (empty($files)) {
            return;
        }

        $product->productMedia()->delete();

        $position = 0;
        foreach ($files as $file) {
            $product->productMedia()->create([
                'type' => 'image',
                'path' => $file['path'],
                'position' => $position++,
                'is_active' => 1,
            ]);
        }
    }
}

<?php

namespace Modules\Product\Services\Csv;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CsvReaderService
{
    public function storeUploadedFile(UploadedFile $file, string $delimiter): string
    {
        $tempId = uniqid('products_csv_', true);
        $path = "tmp/{$tempId}.csv";
        Storage::disk('local')->putFileAs('tmp', $file, "{$tempId}.csv");

        Storage::disk('local')->put("tmp/{$tempId}.meta.json", json_encode([
            'delimiter' => $delimiter,
        ]));

        return $tempId;
    }

    protected function getMeta(string $tempId): array
    {
        $metaPath = "tmp/{$tempId}.meta.json";
        if (!Storage::disk('local')->exists($metaPath)) {
            return ['delimiter' => ','];
        }

        return json_decode(Storage::disk('local')->get($metaPath), true) ?: ['delimiter' => ','];
    }

    protected function getFilePath(string $tempId): string
    {
        return Storage::disk('local')->path("tmp/{$tempId}.csv");
    }

    public function getHeaders(string $tempId): array
    {
        $meta = $this->getMeta($tempId);
        $delimiter = $meta['delimiter'] ?? ',';
        $path = $this->getFilePath($tempId);

        if (!is_readable($path)) {
            return [];
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $headers = fgetcsv($handle, 0, $delimiter);
        fclose($handle);

        if (!is_array($headers)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', $headers), fn ($h) => $h !== ''));
    }

    public function readRows(string $tempId): \Generator
    {
        $meta = $this->getMeta($tempId);
        $delimiter = $meta['delimiter'] ?? ',';
        $path = $this->getFilePath($tempId);

        if (!is_readable($path)) {
            return;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return;
        }

        $headers = fgetcsv($handle, 0, $delimiter) ?: [];
        $headers = array_values(array_map('trim', $headers));

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $assoc = [];
            foreach ($headers as $index => $name) {
                $assoc[$name] = $row[$index] ?? null;
            }
            yield $assoc;
        }

        fclose($handle);
    }
}

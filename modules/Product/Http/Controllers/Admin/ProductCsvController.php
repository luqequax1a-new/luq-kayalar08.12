<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Illuminate\Support\Facades\Log;
use Modules\Product\Services\Csv\CsvReaderService;
use Modules\Product\Services\Csv\CsvExportService;
use Modules\Product\Services\Csv\CsvImportService;
use Modules\Product\Services\Csv\CsvBulkUpdateService;
use Modules\Product\Jobs\ImportProductsFromCsv;
use Modules\Product\Services\Csv\CsvVariantExportService;
use Modules\Product\Services\Csv\CsvVariantImportService;
use Modules\Product\Jobs\ImportProductVariantsFromCsv;

class ProductCsvController
{
    public function export(Request $request, CsvExportService $exportService): BinaryFileResponse
    {
        $query = Product::query()
            ->withoutGlobalScope('active')
            ->with(['translations', 'categories', 'brand', 'tags', 'saleUnit', 'productMedia'])
            ->when($request->has('brand_id') && $request->brand_id !== null && $request->brand_id !== '', function ($q) use ($request) {
                $q->where('brand_id', (int) $request->brand_id);
            })
            ->when($request->has('category_id') && $request->category_id !== null && $request->category_id !== '', function ($q) use ($request) {
                $categoryId = (int) $request->category_id;
                $q->where(function ($sub) use ($categoryId) {
                    $sub->where('primary_category_id', $categoryId)
                        ->orWhereHas('categories', function ($cat) use ($categoryId) {
                            $cat->where('categories.id', $categoryId);
                        });
                });
            })
            ->when($request->has('except'), function ($q) use ($request) {
                $q->whereNotIn('id', explode(',', $request->except));
            });

        if ($search = $request->input('search')) {
            $search = trim($search);
            if ($search !== '') {
                $query->whereHas('translations', function ($t) use ($search) {
                    $t->where('name', 'like', '%' . $search . '%');
                });
            }
        }

        $filePath = $exportService->exportProducts($query, $request);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function upload(Request $request, CsvReaderService $reader): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
            'mode' => ['required', 'in:create,update'],
            'delimiter' => ['nullable', 'in:comma,semicolon'],
        ]);

        $delimiter = $request->input('delimiter') === 'semicolon' ? ';' : ',';
        $tempId = $reader->storeUploadedFile($request->file('file'), $delimiter);

        $headers = $reader->getHeaders($tempId);

        return response()->json([
            'success' => true,
            'temp_id' => $tempId,
            'headers' => $headers,
        ]);
    }

    public function preview(Request $request, CsvReaderService $reader, CsvImportService $importService): JsonResponse
    {
        $data = $request->validate([
            'temp_id' => ['required', 'string'],
            'mode' => ['required', 'in:create,update'],
            'mapping' => ['required', 'array'],
            'identifier' => ['required', 'in:id,sku'],
        ]);

        $preview = $importService->preview($data['temp_id'], $data['mapping'], $data['mode'], $data['identifier']);

        return response()->json([
            'success' => true,
            'preview' => $preview,
        ]);
    }

    public function process(Request $request): JsonResponse
    {
        $data = $request->validate([
            'temp_id' => ['required', 'string'],
            'mode' => ['required', 'in:create,update'],
            'mapping' => ['required', 'array'],
            'identifier' => ['required', 'in:id,sku'],
        ]);

        Log::info('Product CSV process called', [
            'temp_id' => $data['temp_id'],
            'mode' => $data['mode'],
            'identifier' => $data['identifier'],
            'mapping_keys' => array_keys($data['mapping'] ?? []),
        ]);

        ImportProductsFromCsv::dispatch(
            $data['temp_id'],
            $data['mapping'],
            $data['mode'],
            $data['identifier'],
        );

        return response()->json([
            'success' => true,
        ]);
    }

    public function simpleImportForm(): BinaryFileResponse|\Illuminate\View\View
    {
        return view('product::admin.products.simple_csv_import');
    }

    public function simpleImport(Request $request, CsvReaderService $reader, CsvBulkUpdateService $bulkUpdate): \Illuminate\View\View
    {
        $request->validate([
            'file' => ['required', 'file'],
            'mode' => ['required', 'in:create,update'],
            'identifier' => ['required', 'in:id,sku'],
        ]);

        $delimiter = $request->input('delimiter') === 'semicolon' ? ';' : ',';
        $tempId = $reader->storeUploadedFile($request->file('file'), $delimiter);

        $headers = $reader->getHeaders($tempId);

        $total = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $rows = [];

        $mapping = [];
        foreach ($headers as $header) {
            $key = strtolower(trim($header));
            if ($key === 'id') $mapping[$header] = 'id';
            elseif ($key === 'sku') $mapping[$header] = 'sku';
            elseif ($key === 'slug') $mapping[$header] = 'slug';
            elseif ($key === 'name') $mapping[$header] = 'name';
            elseif ($key === 'description') $mapping[$header] = 'description';
            elseif ($key === 'short_description') $mapping[$header] = 'short_description';
            elseif ($key === 'price') $mapping[$header] = 'price';
            elseif ($key === 'sale_price' || $key === 'special_price') $mapping[$header] = 'special_price';
            elseif ($key === 'quantity' || $key === 'qty') $mapping[$header] = 'qty';
            elseif ($key === 'manage_stock') $mapping[$header] = 'manage_stock';
            elseif ($key === 'is_active' || $key === 'status') $mapping[$header] = 'is_active';
            elseif ($key === 'brand' || $key === 'brand_id') $mapping[$header] = 'brand_id';
            elseif ($key === 'categories' || $key === 'category_ids') $mapping[$header] = 'category_ids';
            elseif ($key === 'images') $mapping[$header] = 'images';
        }

        foreach ($reader->readRows($tempId) as $row) {
            $total++;
            $data = [];
            foreach ($mapping as $csvColumn => $field) {
                $data[$field] = $row[$csvColumn] ?? null;
            }

            try {
                if ($request->mode === 'create') {
                    $bulkUpdate->handleRow($data, 'create', $request->identifier);
                    $created++;
                    $rows[] = [
                        'row' => $total,
                        'action' => 'create',
                        'identifier' => $data['sku'] ?? ($data['id'] ?? ''),
                        'message' => null,
                    ];
                    continue;
                }

                // update modu
                $identifier = $request->identifier;
                $product = null;
                if ($identifier === 'id' && !empty($data['id'])) {
                    $product = Product::query()->withoutGlobalScope('active')->find((int) $data['id']);
                } elseif ($identifier === 'sku' && !empty($data['sku'])) {
                    $product = Product::query()->withoutGlobalScope('active')->where('sku', $data['sku'])->first();
                }

                if (!$product) {
                    $skipped++;
                    $msg = $identifier === 'id'
                        ? 'Güncelleme için ürün bulunamadı (id: ' . ($data['id'] ?? '') . ')' 
                        : 'Güncelleme için ürün bulunamadı (sku: ' . ($data['sku'] ?? '') . ')';
                    $errors[] = [
                        'row' => $total,
                        'message' => $msg,
                    ];
                    $rows[] = [
                        'row' => $total,
                        'action' => 'skipped',
                        'identifier' => $data[$identifier] ?? '',
                        'message' => $msg,
                    ];
                    continue;
                }

                $bulkUpdate->handleRow($data, 'update', $identifier);
                $updated++;
                $rows[] = [
                    'row' => $total,
                    'action' => 'update',
                    'identifier' => $data[$identifier] ?? '',
                    'message' => null,
                ];
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = [
                    'row' => $total,
                    'message' => $e->getMessage(),
                ];
                $rows[] = [
                    'row' => $total,
                    'action' => 'skipped',
                    'identifier' => $data['sku'] ?? ($data['id'] ?? ''),
                    'message' => $e->getMessage(),
                ];
            }
        }

        return view('product::admin.products.simple_csv_import_result', [
            'total' => $total,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
            'rows' => $rows,
        ]);
    }

    public function exportVariants(Request $request, CsvVariantExportService $exportService): BinaryFileResponse
    {
        $query = ProductVariant::query()
            ->withoutGlobalScope('active')
            ->with(['product' => function ($q) use ($request) {
                $q->withoutGlobalScope('active')
                    ->when($request->has('brand_id') && $request->brand_id !== null && $request->brand_id !== '', function ($sub) use ($request) {
                        $sub->where('brand_id', (int) $request->brand_id);
                    })
                    ->when($request->has('category_id') && $request->category_id !== null && $request->category_id !== '', function ($sub) use ($request) {
                        $categoryId = (int) $request->category_id;
                        $sub->where(function ($inner) use ($categoryId) {
                            $inner->where('primary_category_id', $categoryId)
                                ->orWhereHas('categories', function ($cat) use ($categoryId) {
                                    $cat->where('categories.id', $categoryId);
                                });
                        });
                    });

                if ($search = $request->input('search')) {
                    $search = trim($search);
                    if ($search !== '') {
                        $q->whereHas('translations', function ($t) use ($search) {
                            $t->where('name', 'like', '%' . $search . '%');
                        });
                    }
                }
            }]);

        $filePath = $exportService->exportVariants($query);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function uploadVariantCsv(Request $request, CsvReaderService $reader): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
            'mode' => ['required', 'in:create,update'],
            'delimiter' => ['nullable', 'in:comma,semicolon'],
        ]);

        $delimiter = $request->input('delimiter') === 'semicolon' ? ';' : ',';
        $tempId = $reader->storeUploadedFile($request->file('file'), $delimiter);

        $headers = $reader->getHeaders($tempId);

        return response()->json([
            'success' => true,
            'temp_id' => $tempId,
            'headers' => $headers,
        ]);
    }

    public function previewVariantCsv(Request $request, CsvVariantImportService $importService): JsonResponse
    {
        $data = $request->validate([
            'temp_id' => ['required', 'string'],
            'mode' => ['required', 'in:create,update'],
            'mapping' => ['required', 'array'],
            'identifier' => ['required', 'in:id,sku'],
        ]);

        Log::info('Product Variant CSV preview called', [
            'temp_id' => $data['temp_id'],
            'mode' => $data['mode'],
            'identifier' => $data['identifier'],
            'mapping_keys' => array_keys($data['mapping'] ?? []),
        ]);

        $preview = $importService->preview($data['temp_id'], $data['mapping'], $data['mode'], $data['identifier']);

        return response()->json([
            'success' => true,
            'preview' => $preview,
        ]);
    }

    public function processVariantCsv(Request $request): JsonResponse
    {
        $data = $request->validate([
            'temp_id' => ['required', 'string'],
            'mode' => ['required', 'in:create,update'],
            'mapping' => ['required', 'array'],
            'identifier' => ['required', 'in:id,sku'],
        ]);

        ImportProductVariantsFromCsv::dispatch(
            $data['temp_id'],
            $data['mapping'],
            $data['mode'],
            $data['identifier'],
        );

        return response()->json([
            'success' => true,
        ]);
    }
}

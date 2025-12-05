<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class TaxonomyController
{
    public function index(): JsonResponse
    {
        $q = request('q');
        $results = [];

        try {
            if (!Storage::disk('local')->exists('google_product_taxonomy.txt')) {
                return response()->json(['results' => []]);
            }

            $content = Storage::disk('local')->get('google_product_taxonomy.txt');
            $lines = preg_split('/\r?\n/', $content);

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if ($q) {
                    if (stripos($line, $q) === false) {
                        continue;
                    }
                }
                $results[] = [
                    'id' => $line,
                    'text' => $line,
                ];
                if (!$q && count($results) >= 5000) {
                    break;
                }
            }
        } catch (\Throwable $e) {
            return response()->json(['results' => []]);
        }

        return response()->json(['results' => $results]);
    }
}

<?php

namespace Modules\Product\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Product\Services\Csv\CsvReaderService;
use Modules\Product\Services\Csv\CsvBulkUpdateService;

class ImportProductsFromCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $tempId,
        protected array $mapping,
        protected string $mode,
        protected string $identifier,
    ) {
    }

    public function handle(CsvReaderService $reader, CsvBulkUpdateService $bulkUpdate): void
    {
        foreach ($reader->readRows($this->tempId) as $row) {
            $mapped = [];
            foreach ($this->mapping as $csvColumn => $field) {
                if (!$field) continue;
                $mapped[$field] = $row[$csvColumn] ?? null;
            }

            $bulkUpdate->handleRow($mapped, $this->mode, $this->identifier);
        }
    }
}

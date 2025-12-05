<?php

namespace Modules\Media\Console;

use Illuminate\Console\Command;
use Modules\Media\Entities\File as MediaFile;
use Modules\Media\Jobs\GenerateResponsiveImagesForMedia;

class GenerateResponsiveVariants extends Command
{
    protected $signature = 'media:generate-variants {--chunk=500} {--disk=} {--queue}';
    protected $description = 'Generate grid/card/thumb responsive image variants (JPEG/WebP/AVIF) for existing media images.';

    public function handle(): int
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '4096M');
        $chunk = (int) ($this->option('chunk') ?: 500);
        $disk = $this->option('disk');
        $useQueue = (bool) $this->option('queue');

        $query = MediaFile::query()
            ->where('mime', 'LIKE', 'image/%');

        if (!empty($disk)) {
            $query->where('disk', $disk);
        }

        $count = 0;

        $query->orderBy('id')->chunk($chunk, function ($files) use (&$count, $useQueue) {
            foreach ($files as $file) {
                $count++;
                if ($useQueue) {
                    dispatch(new GenerateResponsiveImagesForMedia($file->id));
                } else {
                    // Run inline using the job’s handle
                    try {
                        (new GenerateResponsiveImagesForMedia($file->id))->handle(app(\Modules\Media\Services\ResponsiveImageGenerator::class));
                    } catch (\Throwable $e) {
                        $this->error("Failed for media ID {$file->id}: " . $e->getMessage());
                    }
                }
            }
        });

        $this->info("Processed {$count} images.");
        return 0;
    }
}

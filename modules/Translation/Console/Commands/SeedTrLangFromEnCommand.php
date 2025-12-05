<?php

namespace Modules\Translation\Console\Commands;

use Illuminate\Console\Command;

class SeedTrLangFromEnCommand extends Command
{
    protected $signature = 'translation:seed-tr-from-en {--dry-run : Only report missing tr files}';

    protected $description = 'Create missing tr language files by copying en files across modules.';

    public function handle(): int
    {
        $modulesDir = base_path('modules');
        $modules = @scandir($modulesDir) ?: [];
        $dryRun = (bool) $this->option('dry-run');

        $totalMissing = 0;
        $totalCreated = 0;

        foreach ($modules as $module) {
            if ($module === '.' || $module === '..') { continue; }
            $langBase = $modulesDir . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'lang';
            $enDir = $langBase . DIRECTORY_SEPARATOR . 'en';
            $trDir = $langBase . DIRECTORY_SEPARATOR . 'tr';

            if (!is_dir($enDir)) { continue; }

            if (!is_dir($trDir)) {
                if ($dryRun) {
                    $this->line(sprintf('- %s: tr directory missing, will create', $module));
                } else {
                    @mkdir($trDir, 0775, true);
                    $this->line(sprintf('- %s: created tr directory', $module));
                }
            }

            $files = @glob($enDir . DIRECTORY_SEPARATOR . '*.php') ?: [];
            foreach ($files as $enFile) {
                $name = basename($enFile);
                $trFile = $trDir . DIRECTORY_SEPARATOR . $name;
                if (!file_exists($trFile)) {
                    $totalMissing++;
                    if ($dryRun) {
                        $this->line(sprintf('  -> missing: %s/%s', $module, $name));
                    } else {
                        // Copy en -> tr as a starting point
                        @copy($enFile, $trFile);
                        $totalCreated++;
                        $this->line(sprintf('  -> created: %s/%s', $module, $name));
                    }
                }
            }
        }

        $this->newLine();
        $this->info(sprintf('Missing tr files: %d', $totalMissing));
        if (!$dryRun) {
            $this->info(sprintf('Created tr files: %d', $totalCreated));
            $this->line('Next: php artisan cache:clear && php artisan view:clear');
        }

        return self::SUCCESS;
    }
}


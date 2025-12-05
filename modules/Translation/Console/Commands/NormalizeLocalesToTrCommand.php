<?php

namespace Modules\Translation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeLocalesToTrCommand extends Command
{
    protected $signature = 'translation:normalize-locales-to-tr {--dry-run : Only log, do not update}';

    protected $description = 'List DISTINCT locales in translation tables and normalize en/tr-TR/tr_TR to tr safely.';

    public function handle(): int
    {
        $tables = [
            // table => parent id column
            'product_translations' => 'product_id',
            'category_translations' => 'category_id',
            'page_translations' => 'page_id',
            'attribute_translations' => 'attribute_id',
            'brand_translations' => 'brand_id',
            'tag_translations' => 'tag_id',
            'menu_translations' => 'menu_id',
            'menu_item_translations' => 'menu_item_id',
            'option_translations' => 'option_id',
            'option_value_translations' => 'option_value_id',
            'slider_translations' => 'slider_id',
            'slider_slide_translations' => 'slider_slide_id',
            'meta_data_translations' => 'meta_data_id',
            'blog_post_translations' => 'blog_post_id',
            'blog_category_translations' => 'blog_category_id',
            'blog_tag_translations' => 'blog_tag_id',
            'flash_sale_translations' => 'flash_sale_id',
            'setting_translations' => 'setting_id',
            'variation_translations' => 'variation_id',
            'variation_value_translations' => 'variation_value_id',
        ];

        $dryRun = (bool) $this->option('dry-run');

        $this->info('Listing DISTINCT locales before normalization:');
        foreach ($tables as $table => $parent) {
            if (!$this->tableExists($table)) {
                $this->warn("- {$table}: table not found, skipping");
                continue;
            }

            $locales = DB::table($table)->select('locale')->distinct()->pluck('locale')->all();
            $this->line(sprintf('- %s: [%s]', $table, implode(', ', $locales)) ?: '- none');

            // Report parents missing tr translation
            $missingCount = DB::table($table . ' as t')
                ->whereNotExists(function ($q) use ($table, $parent) {
                    $q->select(DB::raw(1))
                        ->from($table . ' as tx')
                        ->whereColumn('tx.' . $parent, 't.' . $parent)
                        ->where('tx.locale', 'tr');
                })
                ->distinct()
                ->count('t.' . $parent);
            $this->line(sprintf('  -> parents without tr: %d', $missingCount));
        }

        if ($dryRun) {
            $this->info('Dry run complete. No updates performed.');
            return self::SUCCESS;
        }

        $this->info('Normalizing locales to tr ...');
        foreach ($tables as $table => $parent) {
            if (!$this->tableExists($table)) {
                continue;
            }

            // Normalize tr-TR / tr_TR to tr, avoid duplicates
            $sqlTrVariants = sprintf(
                "UPDATE %s t LEFT JOIN %s t2 ON t2.%s = t.%s AND t2.locale = 'tr' SET t.locale = 'tr' WHERE t.locale IN ('tr-TR','tr_TR') AND t2.id IS NULL",
                $table,
                $table,
                $parent,
                $parent
            );
            DB::statement($sqlTrVariants);

            // Normalize en to tr, avoid duplicates
            $sqlEnToTr = sprintf(
                "UPDATE %s t LEFT JOIN %s t2 ON t2.%s = t.%s AND t2.locale = 'tr' SET t.locale = 'tr' WHERE t.locale = 'en' AND t2.id IS NULL",
                $table,
                $table,
                $parent,
                $parent
            );
            DB::statement($sqlEnToTr);
        }

        // Settings: single-locale Turkish
        if ($this->tableExists('settings')) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'supported_locales'],
                ['plain_value' => serialize(['tr'])]
            );
            DB::table('settings')->updateOrInsert(
                ['key' => 'default_locale'],
                ['plain_value' => serialize('tr')]
            );
        }

        $this->info('Listing DISTINCT locales after normalization:');
        foreach ($tables as $table => $parent) {
            if (!$this->tableExists($table)) {
                continue;
            }
            $locales = DB::table($table)->select('locale')->distinct()->pluck('locale')->all();
            $this->line(sprintf('- %s: [%s]', $table, implode(', ', $locales)) ?: '- none');

            // Report parents missing tr translation after normalization
            $missingCount = DB::table($table . ' as t')
                ->whereNotExists(function ($q) use ($table, $parent) {
                    $q->select(DB::raw(1))
                        ->from($table . ' as tx')
                        ->whereColumn('tx.' . $parent, 't.' . $parent)
                        ->where('tx.locale', 'tr');
                })
                ->distinct()
                ->count('t.' . $parent);
            $this->line(sprintf('  -> parents without tr: %d', $missingCount));
        }

        // Scan module lang files: compare en vs tr keys
        $this->newLine();
        $this->info('Scanning module PHP lang files for missing tr keys relative to en:');
        $modulesDir = base_path('modules');
        $modules = @scandir($modulesDir) ?: [];
        foreach ($modules as $module) {
            if ($module === '.' || $module === '..') { continue; }
            $langBase = $modulesDir . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'lang';
            $enDir = $langBase . DIRECTORY_SEPARATOR . 'en';
            $trDir = $langBase . DIRECTORY_SEPARATOR . 'tr';
            if (!is_dir($enDir) || !is_dir($trDir)) { continue; }

            $files = @glob($enDir . DIRECTORY_SEPARATOR . '*.php') ?: [];
            foreach ($files as $enFile) {
                $name = basename($enFile);
                $trFile = $trDir . DIRECTORY_SEPARATOR . $name;
                $enArr = @include $enFile;
                $trArr = @include $trFile;
                if (!is_array($enArr)) { $enArr = []; }
                if (!is_array($trArr)) { $trArr = []; }
                $missing = array_diff_key($enArr, $trArr);
                if (!empty($missing)) {
                    $this->line(sprintf('- %s/%s missing keys: %s', $module, $name, implode(', ', array_keys($missing))));
                }
            }
        }

        $this->newLine();
        $this->info('Next steps:');
        $this->line('- Run: php artisan cache:clear');
        $this->line('- Run: php artisan config:clear');
        $this->line('- Run: php artisan view:clear');
        $this->line('- Ensure Language default is set to code "tr" in Admin');

        return self::SUCCESS;
    }

    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }
}

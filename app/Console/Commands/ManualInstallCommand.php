<?php

namespace FleetCart\Console\Commands;

use Illuminate\Console\Command;
use FleetCart\Install\App;
use FleetCart\Install\Store;
use FleetCart\Install\Database;
use FleetCart\Install\AdminAccount;
use Illuminate\Support\Facades\Artisan;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

class ManualInstallCommand extends Command
{
    protected $signature = 'app:install '
        . '{--db_host=}'
        . ' {--db_port=}'
        . ' {--db_username=}'
        . ' {--db_password=}'
        . ' {--db_database=}'
        . ' {--admin_first_name=}'
        . ' {--admin_last_name=}'
        . ' {--admin_email=}'
        . ' {--admin_phone=}'
        . ' {--admin_password=}'
        . ' {--store_name=}'
        . ' {--store_email=}'
        . ' {--store_phone=}'
        . ' {--store_search_engine=mysql}'
        . ' {--algolia_app_id=}'
        . ' {--algolia_secret=}'
        . ' {--meilisearch_host=}'
        . ' {--meilisearch_key=}';

    protected $description = 'Manual installation without UI';

    public function handle(): int
    {
        @set_time_limit(0);

        Artisan::call('optimize:clear');

        $data = [
            'db_host' => $this->option('db_host'),
            'db_port' => $this->option('db_port'),
            'db_username' => $this->option('db_username'),
            'db_password' => $this->option('db_password'),
            'db_database' => $this->option('db_database'),
            'admin_first_name' => $this->option('admin_first_name'),
            'admin_last_name' => $this->option('admin_last_name'),
            'admin_email' => $this->option('admin_email'),
            'admin_phone' => $this->option('admin_phone'),
            'admin_password' => $this->option('admin_password'),
            'store_name' => $this->option('store_name'),
            'store_email' => $this->option('store_email'),
            'store_phone' => $this->option('store_phone'),
            'store_search_engine' => $this->option('store_search_engine') ?? 'mysql',
            'algolia_app_id' => $this->option('algolia_app_id'),
            'algolia_secret' => $this->option('algolia_secret'),
            'meilisearch_host' => $this->option('meilisearch_host'),
            'meilisearch_key' => $this->option('meilisearch_key'),
        ];

        $database = app(Database::class);
        $admin = app(AdminAccount::class);
        $store = app(Store::class);
        $appInstall = app(App::class);

        $database->setup($data);
        $admin->setup($data);
        $store->setup($data);
        $appInstall->setup();

        DotenvEditor::setKey('APP_INSTALLED', 'true')->save();

        Artisan::call('key:generate', ['--force' => true]);

        $this->info('Congratulations! FleetCart installed successfully');

        return self::SUCCESS;
    }
}


<?php

namespace Modules\SeoBulk\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\Category;
use Modules\SeoBulk\Services\PlaceholderRenderer;

class BulkSeoMetaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $jobId;
    public array $config;
    public array $productIds;
    public array $categoryIds;
    public ?int $userId;

    public function __construct(string $jobId, array $config, array $productIds, array $categoryIds, ?int $userId)
    {
        $this->jobId = $jobId;
        $this->config = $config;
        $this->productIds = $productIds;
        $this->categoryIds = $categoryIds;
        $this->userId = $userId;
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $renderer = new PlaceholderRenderer($this->config);
        $locale = $this->config['locale'];
        $overwrite = (bool) ($this->config['seo_filters']['overwrite'] ?? false);
        $dry = (bool) ($this->config['dry_run'] ?? false);

        foreach (array_chunk($this->productIds, 400) as $chunk) {
            $products = Product::query()->withoutGlobalScope('active')
                ->with(['variants.files','brand','categories','meta'])
                ->whereIn('id',$chunk)->get();
            foreach ($products as $p) {
                $title = $renderer->renderProductTitle($p);
                $desc = $renderer->renderProductDescription($p);
                if (!$title && !$desc) continue;
                $curTitle = optional($p->meta)->getAttribute('meta_title');
                $curDesc = optional($p->meta)->getAttribute('meta_description');
                $applyTitle = $title && ($overwrite || !$curTitle);
                $applyDesc = $desc && ($overwrite || !$curDesc);
                if ($dry) {
                    $this->log('product',$p->id,'meta_title',$curTitle,$title);
                    $this->log('product',$p->id,'meta_description',$curDesc,$desc);
                } else {
                    $data = [];
                    if ($applyTitle) $data['meta_title'] = $title;
                    if ($applyDesc) $data['meta_description'] = $desc;
                    if (!empty($data)) {
                        $p->meta()->firstOrNew([])->fill([$locale=>$data])->save();
                    }
                }
                
            }
        }

        foreach (array_chunk($this->categoryIds, 400) as $chunk) {
            $cats = Category::query()->withoutGlobalScope('active')->with('files')->whereIn('id',$chunk)->get();
            foreach ($cats as $c) {
                $title = $renderer->renderCategoryTitle($c);
                $desc = $renderer->renderCategoryDescription($c);
                if (!$title && !$desc) continue;
                $curTitle = $c->meta_title;
                $curDesc = $c->meta_description;
                $applyTitle = $title && ($overwrite || !$curTitle);
                $applyDesc = $desc && ($overwrite || !$curDesc);
                if ($dry) {
                    $this->log('category',$c->id,'meta_title',$curTitle,$title);
                    $this->log('category',$c->id,'meta_description',$curDesc,$desc);
                } else {
                    $data = [];
                    if ($applyTitle) $data['meta_title'] = $title;
                    if ($applyDesc) $data['meta_description'] = $desc;
                    if (!empty($data)) {
                        $c->withoutEvents(function() use ($c,$data){ $c->update($data); });
                    }
                }
                
            }
        }
    }

    private function log(string $type, int $id, string $field, $old, $new): void
    {
        DB::table('seo_bulk_logs')->insert([
            'job_id' => $this->jobId,
            'entity_type' => $type,
            'entity_id' => $id,
            'field' => $field,
            'old_value' => is_null($old) ? null : (string) $old,
            'new_value' => is_null($new) ? null : (string) $new,
            'user_id' => $this->userId,
            'dry_run' => (bool) ($this->config['dry_run'] ?? false),
            'created_at' => now(),
        ]);
    }
}

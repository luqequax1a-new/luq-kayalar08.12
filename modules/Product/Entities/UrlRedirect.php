<?php

namespace Modules\Product\Entities;

use Modules\Support\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Product\Admin\RedirectTable;

class UrlRedirect extends Model
{
    protected $table = 'url_redirects';

    protected $fillable = [
        'source_path',
        'target_type',
        'target_id',
        'target_url',
        'status_code',
        'is_active',
    ];

    public function table(Request $request): RedirectTable
    {
        $query = $this->newQuery()
            ->withoutGlobalScope('active')
            ->select(['id','source_path','target_type','target_id','target_url','status_code','is_active','created_at']);

        return new RedirectTable($query);
    }
}

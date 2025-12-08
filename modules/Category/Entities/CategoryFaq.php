<?php

namespace Modules\Category\Entities;

use Modules\Support\Eloquent\Model;

class CategoryFaq extends Model
{
    protected $table = 'category_faqs';

    protected $fillable = [
        'category_id',
        'question',
        'answer',
        'position',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

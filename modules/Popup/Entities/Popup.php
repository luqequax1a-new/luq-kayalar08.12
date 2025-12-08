<?php

namespace Modules\Popup\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Media\Entities\File;

class Popup extends Model
{
    protected $table = 'popups';

    protected $fillable = [
        'name',
        'status',
        'device',
        'trigger_type',
        'trigger_value',
        'frequency_type',
        'frequency_value',
        'target_scope',
        'targeting',
        'headline',
        'subheadline',
        'body',
        'cta_label',
        'cta_url',
        'close_label',
        'image_path',
    ];

    protected $casts = [
        'status' => 'boolean',
        'trigger_value' => 'integer',
        'frequency_value' => 'integer',
        'targeting' => 'array',
        'image_path' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function image()
    {
        return $this->belongsTo(File::class, 'image_path');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image?->path;
    }
}

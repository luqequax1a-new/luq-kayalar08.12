<?php

namespace Modules\Media\Entities;

use Modules\Media\IconResolver;
use Modules\User\Entities\User;
use Illuminate\Http\JsonResponse;
use Modules\Media\Admin\MediaTable;
use Modules\Support\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'filename',
        'path',
        'url',
        'grid_webp_url',
        'grid_avif_url',
        'grid_jpeg_url',
        'thumb_webp_url',
        'thumb_avif_url',
        'thumb_jpeg_url',
        'detail_webp_url',
        'detail_avif_url',
        'detail_jpeg_url',
    ];

    protected $appends = [
        'url',
        'grid_webp_url',
        'grid_avif_url',
        'grid_jpeg_url',
        'thumb_webp_url',
        'thumb_avif_url',
        'thumb_jpeg_url',
        'detail_webp_url',
        'detail_avif_url',
        'detail_jpeg_url',
    ];


    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleting(function ($file) {
            Storage::disk($file->disk)->delete($file->getRawOriginal('path'));
        });
    }


    /**
     * Get the user that uploaded the file.
     *
     * @return void
     */
    public function uploader()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Get the file's path.
     *
     * @param string $path
     *
     * @return string|null
     */
    public function getPathAttribute($path)
    {
        if (is_null($path)) return null;
        $raw = str_replace('\\', '/', $path);
        if (Str::startsWith($raw, ['http://', 'https://', '//'])) {
            return $raw;
        }
        $clean = Str::startsWith($raw, 'public/') ? Str::replaceFirst('public/', '', $raw) : $raw;
        return Storage::disk($this->disk ?: 'public')->url($clean);
    }


    public function getUrlAttribute(): ?string
    {
        $raw = $this->getRawOriginal('path');
        if (is_null($raw)) return null;
        $raw = str_replace('\\', '/', $raw);
        if (Str::startsWith($raw, ['http://', 'https://', '//'])) {
            return $raw;
        }
        $clean = Str::startsWith($raw, 'public/') ? Str::replaceFirst('public/', '', $raw) : $raw;
        return Storage::disk($this->disk ?: 'public')->url($clean);
    }


    /**
     * Get file's real path.
     *
     * @return void
     */
    public function realPath()
    {
        if (!is_null($this->attributes['path'])) {
            return Storage::disk($this->disk)->path($this->attributes['path']);
        }
    }


    /**
     * Determine if the file type is image.
     *
     * @return bool
     */
    public function isImage()
    {
        return strtok($this->mime, '/') === 'image';
    }


    /**
     * Get the file's icon.
     *
     * @return string
     */
    public function icon()
    {
        return IconResolver::resolve($this->mime);
    }

    public function getGridWebpUrlAttribute(): ?string
    {
        return media_variant_url($this, 400, 'webp');
    }

    public function getGridAvifUrlAttribute(): ?string
    {
        return media_variant_url($this, 400, 'avif');
    }

    public function getGridJpegUrlAttribute(): ?string
    {
        return media_variant_url($this, 400, null);
    }

    public function getThumbWebpUrlAttribute(): ?string
    {
        return media_variant_url($this, 80, 'webp');
    }

    public function getThumbAvifUrlAttribute(): ?string
    {
        return media_variant_url($this, 80, 'avif');
    }

    public function getThumbJpegUrlAttribute(): ?string
    {
        return media_variant_url($this, 80, null);
    }

    public function getDetailWebpUrlAttribute(): ?string
    {
        return media_variant_url($this, 1000, 'webp');
    }

    public function getDetailAvifUrlAttribute(): ?string
    {
        return media_variant_url($this, 1000, 'avif');
    }

    public function getDetailJpegUrlAttribute(): ?string
    {
        return media_variant_url($this, 1000, null);
    }


    /**
     * Get table data for the resource
     *
     * @return JsonResponse
     */
    public function table($request)
    {
        $query = $this->newQuery()
            ->when(!is_null($request->type) && $request->type !== 'null', function ($query) use ($request) {
                $query->where('mime', 'LIKE', "{$request->type}/%");
            });

        return new MediaTable($query);
    }
}

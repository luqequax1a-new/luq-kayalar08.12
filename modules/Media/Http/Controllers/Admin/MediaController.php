<?php

namespace Modules\Media\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Modules\Media\Entities\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Media\Http\Requests\UploadMediaRequest;
use Modules\Media\Services\ImageOptimizationService;

class MediaController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = File::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'media::media.media';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'media::admin.media';

    /**
     * @var ImageOptimizationService
     */
    protected $imageOptimizer;


    public function __construct(ImageOptimizationService $imageOptimizer)
    {
        $this->imageOptimizer = $imageOptimizer;
    }


    /**
     * Store a newly created media in storage.
     *
     * @param UploadMediaRequest $request
     *
     * @return Response
     */
    public function store(UploadMediaRequest $request)
    {
        $file = $request->file('file');

        $optimized = $this->imageOptimizer->optimizeUploadedFile($file);

        if ($optimized && isset($optimized['file'])) {
            $optimizedFile = $optimized['file'];

            $extension = $optimized['extension'] ?? $optimizedFile->guessExtension() ?? '';
            $extension = $extension ?: 'jpg';
            $storedName = Str::random(40) . '.' . $extension;

            $path = Storage::putFileAs('media', $optimizedFile, $storedName);

            $mime = $optimized['mime'] ?? $optimizedFile->getMimeType();
            $size = $optimized['size'] ?? $optimizedFile->getSize();
        } else {
            $path = Storage::putFile('media', $file);

            $extension = $file->guessClientExtension() ?? '';
            $mime = $file->getClientMimeType();
            $size = $file->getSize();
        }

        $record = File::create([
            'user_id' => auth()->id(),
            'disk' => config('filesystems.default'),
            'filename' => substr($file->getClientOriginalName(), 0, 255),
            'path' => $path,
            'extension' => $extension,
            'mime' => $mime,
            'size' => $size,
        ]);

        dispatch(new \Modules\Media\Jobs\GenerateResponsiveImagesForMedia($record->id));
        $mime = strtolower((string) $record->mime);
        if (substr($mime, 0, 6) === 'video/') {
            dispatch(new \Modules\Media\Jobs\OptimizeVideoForMedia($record->id));
        }

        return $record;
    }


    /**
     * Remove the specified resources from storage.
     *
     * @param string $ids
     *
     * @return Response
     */
    public function destroy(string $ids)
    {
        File::find(explode(',', $ids))->each->delete();
    }
}

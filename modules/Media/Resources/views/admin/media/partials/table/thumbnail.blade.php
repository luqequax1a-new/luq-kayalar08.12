<div class="thumbnail-holder">
    @php($isVideo = strtok($file->mime, '/') === 'video')
    @if ($file->isImage())
        <img src="{{ $file->path }}" alt="thumbnail">
    @elseif ($isVideo)
        <img src="{{ media_variant_url($file, 400, 'webp') ?? media_variant_url($file, 400, 'jpg') ?? '' }}" alt="thumbnail">
    @else
        <i class="file-icon fa {{ $file->icon() }}"></i>
    @endif
</div>

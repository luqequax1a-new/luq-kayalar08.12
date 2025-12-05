<div class="product-gallery position-relative align-self-start"> 
    <div class="product-gallery-wrapper" style="position: relative;">
    </div>
    <div class="product-gallery-preview-wrap position-relative overflow-hidden">
        @include('storefront::public.partials.products.tag_badges', [
            'product' => $product,
            'context' => 'detail',
        ])
        <div class="product-gallery-preview swiper">
            <div class="swiper-wrapper">
                @php($videoMedia = $product->productMedia()->videos()->active()->orderBy('position')->get())

                @if ($product->media->isNotEmpty())
                    @foreach ($product->media as $media)
                        <div class="swiper-slide">
                            <div class="gallery-preview-slide">
                                <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox($event)">
                                    <img src="{{ $media->path }}" data-zoom="{{ $media->path }}" alt="{{ $product->name }}">
                                </div>

                                <a href="{{ $media->path }}" data-gallery="product-gallery-preview" class="gallery-view-icon glightbox">
                                    <i class="las la-search-plus"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach

                    @foreach ($videoMedia as $video)
                        @php(
                            $poster = $video->poster
                                ?? $product->base_image->path
                                ?? optional($product->images->first())->path
                                ?? asset('build/assets/image-placeholder.png')
                        )
                        <div class="swiper-slide">
                            <div class="gallery-preview-slide">
                                <div class="gallery-preview-item gallery-preview-item--video">
                                    <video
                                        class="product-main-media product-main-media--video"
                                        controls
                                        playsinline
                                        preload="metadata"
                                        poster="{{ $poster }}"
                                        style="width: 100%; height: 100%; object-fit: cover;"
                                    >
                                        <source src="{{ $video->path }}" type="video/mp4">
                                    </video>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="swiper-slide">
                        <div class="gallery-preview-slide">
                            <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox($event)">
                                <img src="{{ asset('build/assets/image-placeholder.png') }}" data-zoom="{{ asset('build/assets/image-placeholder.png') }}" alt="{{ $product->name }}" class="image-placeholder">
                            </div>

                            <a href="{{ asset('build/assets/image-placeholder.png') }}" data-gallery="product-gallery-preview" class="gallery-view-icon glightbox">
                                <i class="las la-search-plus"></i>
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>

    <div class="product-gallery-thumbnail swiper"> 
        <div class="swiper-wrapper">
            @if ($product->media->isNotEmpty())
                @foreach ($product->media as $media)
                    <div class="swiper-slide">
                        <div class="gallery-thumbnail-slide">
                            <div class="gallery-thumbnail-item">
                                <img src="{{ $media->path }}" alt="{{ $product->name }}">
                            </div>
                        </div>
                    </div>
                @endforeach

                @foreach ($videoMedia as $video)
                    @php(
                        $poster = $video->poster
                            ?? $product->base_image->path
                            ?? optional($product->images->first())->path
                            ?? asset('build/assets/image-placeholder.png')
                    )
                    <div class="swiper-slide">
                        <div class="gallery-thumbnail-slide">
                            <div class="gallery-thumbnail-item gallery-thumbnail-item--video">
                                <img src="{{ $poster }}" alt="{{ $product->name }}">
                                <span class="fc-video-play-icon">▶</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="swiper-slide">
                    <div class="gallery-thumbnail-slide">
                        <div class="gallery-thumbnail-item">
                            <img src="{{ asset('build/assets/image-placeholder.png') }}" alt="{{ $product->name }}" class="image-placeholder">
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div x-cloak class="swiper-button-next"></div>
        <div x-cloak class="swiper-button-prev"></div>
    </div>
</div>
 

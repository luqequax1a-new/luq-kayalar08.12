<div class="product-gallery position-relative align-self-start"> 
    <div class="product-gallery-wrapper" style="position: relative;">
    </div>

    <div
        class="product-gallery-preview-wrap position-relative overflow-hidden"
        :class="{ 'visible-variation-image': hasAnyVariationImage }"
    >
        @include('storefront::public.partials.products.tag_badges', [
            'product' => $product,
            'context' => 'detail',
        ])

        <template x-if="hasAnyVariationImage">
            <img :src="variationImagePath" class="variation-image" :alt="productName">
        </template>

        <div class="product-gallery-preview swiper">
            <div class="swiper-wrapper">
                @php($videoMedia = $product->productMedia()->videos()->active()->orderBy('position')->get())
                @php($hasAnyMedia = $product->variant->media->isNotEmpty() || $product->media->isNotEmpty())

                @if (!$hasAnyMedia)
                    <div class="swiper-slide">
                        <div class="gallery-preview-slide">
                            <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox($event)">
                                <img
                                    src="{{ asset('build/assets/image-placeholder.png') }}"
                                    data-zoom="{{ asset('build/assets/image-placeholder.png') }}"
                                    alt="{{ $product->name }}"
                                    class="image-placeholder"
                                >
                            </div>

                            <a href="{{ asset('build/assets/image-placeholder.png') }}" data-gallery="product-gallery-preview" class="gallery-view-icon glightbox">
                                <i class="las la-search-plus"></i>
                            </a>
                        </div>
                    </div>
                @else
                    @foreach ($product->variant->media as $media)
                        @php(
                            $detailAvif = $media->detail_avif_url ?? $media->grid_avif_url ?? null
                        )
                        @php(
                            $detailWebp = $media->detail_webp_url ?? $media->grid_webp_url ?? null
                        )
                        @php(
                            $detailJpeg = $media->detail_jpeg_url
                                ?? $media->grid_jpeg_url
                                ?? $media->path
                                ?? asset('build/assets/image-placeholder.png')
                        )
                        <div class="swiper-slide">
                            <div class="gallery-preview-slide">
                                <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox($event)">
                                    <picture>
                                        @if ($detailAvif)
                                            <source srcset="{{ $detailAvif }}" type="image/avif">
                                        @endif

                                        @if ($detailWebp)
                                            <source srcset="{{ $detailWebp }}" type="image/webp">
                                        @endif

                                        <img
                                            src="{{ $detailJpeg }}"
                                            data-zoom="{{ $detailJpeg }}"
                                            alt="{{ $product->name }}"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    </picture>
                                </div>

                                <a href="{{ $detailJpeg }}" data-gallery="product-gallery-preview" class="gallery-view-icon glightbox">
                                    <i class="las la-search-plus"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach

                    @foreach ($product->media as $media)
                        @php(
                            $detailAvif = $media->detail_avif_url ?? $media->grid_avif_url ?? null
                        )
                        @php(
                            $detailWebp = $media->detail_webp_url ?? $media->grid_webp_url ?? null
                        )
                        @php(
                            $detailJpeg = $media->detail_jpeg_url
                                ?? $media->grid_jpeg_url
                                ?? $media->path
                                ?? asset('build/assets/image-placeholder.png')
                        )
                        <div class="swiper-slide">
                            <div class="gallery-preview-slide">
                                <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox($event)">
                                    <picture>
                                        @if ($detailAvif)
                                            <source srcset="{{ $detailAvif }}" type="image/avif">
                                        @endif

                                        @if ($detailWebp)
                                            <source srcset="{{ $detailWebp }}" type="image/webp">
                                        @endif

                                        <img
                                            src="{{ $detailJpeg }}"
                                            data-zoom="{{ $detailJpeg }}"
                                            alt="{{ $product->name }}"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    </picture>
                                </div>

                                <a href="{{ $detailJpeg }}" data-gallery="product-gallery-preview" class="gallery-view-icon glightbox">
                                    <i class="las la-search-plus"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach

                    @foreach ($videoMedia as $video)
                        @php(
                            $poster = $video->poster
                                ?? optional($product->variant)->base_image?->path
                                ?? $product->base_image?->path
                                ?? asset('build/assets/image-placeholder.png')
                        )
                        <div class="swiper-slide">
                            <div class="gallery-preview-slide">
                                <div class="gallery-preview-item gallery-preview-item--video">
                                    <video
                                        class="product-main-media product-main-media--video"
                                        controls
                                        controlslist="nofullscreen"
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
                @endif
            </div>

            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>

    <div class="product-gallery-thumbnail swiper"> 
        <div class="swiper-wrapper">
            @if (!$hasAnyMedia)
                <div class="swiper-slide">
                    <div class="gallery-thumbnail-slide">
                        <div class="gallery-thumbnail-item">
                            <img src="{{ asset('build/assets/image-placeholder.png') }}" alt="{{ $product->name }}" class="image-placeholder">
                        </div>
                    </div>
                </div>
            @else
                @foreach ($product->variant->media as $media)
                    @php(
                        $thumbAvif = $media->thumb_avif_url ?? $media->grid_avif_url ?? null
                    )
                    @php(
                        $thumbWebp = $media->thumb_webp_url ?? $media->grid_webp_url ?? null
                    )
                    @php(
                        $thumbJpeg = $media->thumb_jpeg_url
                            ?? $media->grid_jpeg_url
                            ?? $media->path
                            ?? asset('build/assets/image-placeholder.png')
                    )
                    <div class="swiper-slide">
                        <div class="gallery-thumbnail-slide">
                            <div class="gallery-thumbnail-item">
                                <picture>
                                    @if ($thumbAvif)
                                        <source srcset="{{ $thumbAvif }}" type="image/avif">
                                    @endif

                                    @if ($thumbWebp)
                                        <source srcset="{{ $thumbWebp }}" type="image/webp">
                                    @endif

                                    <img
                                        src="{{ $thumbJpeg }}"
                                        alt="{{ $product->name }}"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </picture>
                            </div>
                        </div>
                    </div>
                @endforeach

                @foreach ($product->media as $media)
                    @php(
                        $thumbAvif = $media->thumb_avif_url ?? $media->grid_avif_url ?? null
                    )
                    @php(
                        $thumbWebp = $media->thumb_webp_url ?? $media->grid_webp_url ?? null
                    )
                    @php(
                        $thumbJpeg = $media->thumb_jpeg_url
                            ?? $media->grid_jpeg_url
                            ?? $media->path
                            ?? asset('build/assets/image-placeholder.png')
                    )
                    <div class="swiper-slide">
                        <div class="gallery-thumbnail-slide">
                            <div class="gallery-thumbnail-item">
                                <picture>
                                    @if ($thumbAvif)
                                        <source srcset="{{ $thumbAvif }}" type="image/avif">
                                    @endif

                                    @if ($thumbWebp)
                                        <source srcset="{{ $thumbWebp }}" type="image/webp">
                                    @endif

                                    <img
                                        src="{{ $thumbJpeg }}"
                                        alt="{{ $product->name }}"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </picture>
                            </div>
                        </div>
                    </div>
                @endforeach

                @foreach ($videoMedia as $video)
                    @php(
                        $poster = $video->poster
                            ?? optional($product->variant)->base_image?->path
                            ?? $product->base_image?->path
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
            @endif
        </div>

        <div x-cloak class="swiper-button-next"></div>
        <div x-cloak class="swiper-button-prev"></div>
    </div>
</div>

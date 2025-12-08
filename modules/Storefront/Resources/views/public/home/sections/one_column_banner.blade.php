<section class="banner-wrap one-column-banner">
    <div class="container">
        <a
            href="{{ $oneColumnBanner->call_to_action_url }}"
            class="banner"
            target="{{ $oneColumnBanner->open_in_new_window ? '_blank' : '_self' }}"
        >
            @php(
                $bannerAvif = $oneColumnBanner->image->detail_avif_url
                    ?? $oneColumnBanner->image->grid_avif_url
                    ?? null
            )
            @php(
                $bannerWebp = $oneColumnBanner->image->detail_webp_url
                    ?? $oneColumnBanner->image->grid_webp_url
                    ?? null
            )
            @php(
                $bannerJpeg = $oneColumnBanner->image->detail_jpeg_url
                    ?? $oneColumnBanner->image->grid_jpeg_url
                    ?? $oneColumnBanner->image->path
            )

            <picture>
                @if ($bannerAvif)
                    <source srcset="{{ $bannerAvif }}" type="image/avif">
                @endif

                @if ($bannerWebp)
                    <source srcset="{{ $bannerWebp }}" type="image/webp">
                @endif

                <img src="{{ $bannerJpeg }}" alt="Banner" loading="lazy" />
            </picture>
        </a>
    </div>
</section>

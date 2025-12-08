<section class="banner-wrap two-column-banner">
    <div class="container">
        <div class="row">
            <div class="col-md-9">
                <a
                    href="{{ $twoColumnBanners['banner_1']->call_to_action_url }}"
                    class="banner"
                    target="{{ $twoColumnBanners['banner_1']->open_in_new_window ? '_blank' : '_self' }}"
                >
                    @php(
                        $banner1Avif = $twoColumnBanners['banner_1']->image->detail_avif_url
                            ?? $twoColumnBanners['banner_1']->image->grid_avif_url
                            ?? null
                    )
                    @php(
                        $banner1Webp = $twoColumnBanners['banner_1']->image->detail_webp_url
                            ?? $twoColumnBanners['banner_1']->image->grid_webp_url
                            ?? null
                    )
                    @php(
                        $banner1Jpeg = $twoColumnBanners['banner_1']->image->detail_jpeg_url
                            ?? $twoColumnBanners['banner_1']->image->grid_jpeg_url
                            ?? $twoColumnBanners['banner_1']->image->path
                    )

                    <picture>
                        @if ($banner1Avif)
                            <source srcset="{{ $banner1Avif }}" type="image/avif">
                        @endif

                        @if ($banner1Webp)
                            <source srcset="{{ $banner1Webp }}" type="image/webp">
                        @endif

                        <img src="{{ $banner1Jpeg }}" alt="Banner" loading="lazy" />
                    </picture>
                </a>
            </div>

            <div class="col-md-9">
                <a
                    href="{{ $twoColumnBanners['banner_2']->call_to_action_url }}"
                    class="banner"
                    target="{{ $twoColumnBanners['banner_2']->open_in_new_window ? '_blank' : '_self' }}"
                >
                    @php(
                        $banner2Avif = $twoColumnBanners['banner_2']->image->detail_avif_url
                            ?? $twoColumnBanners['banner_2']->image->grid_avif_url
                            ?? null
                    )
                    @php(
                        $banner2Webp = $twoColumnBanners['banner_2']->image->detail_webp_url
                            ?? $twoColumnBanners['banner_2']->image->grid_webp_url
                            ?? null
                    )
                    @php(
                        $banner2Jpeg = $twoColumnBanners['banner_2']->image->detail_jpeg_url
                            ?? $twoColumnBanners['banner_2']->image->grid_jpeg_url
                            ?? $twoColumnBanners['banner_2']->image->path
                    )

                    <picture>
                        @if ($banner2Avif)
                            <source srcset="{{ $banner2Avif }}" type="image/avif">
                        @endif

                        @if ($banner2Webp)
                            <source srcset="{{ $banner2Webp }}" type="image/webp">
                        @endif

                        <img src="{{ $banner2Jpeg }}" alt="Banner" loading="lazy" />
                    </picture>
                </a>
            </div>
        </div>
    </div>
</section>
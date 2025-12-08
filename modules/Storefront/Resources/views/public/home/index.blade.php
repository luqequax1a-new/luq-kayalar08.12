@extends('storefront::public.layout')

@section('title', setting('store_tagline'))

@section('canonical')
    <link rel="canonical" href="{{ \Illuminate\Support\Str::before(route('home'), '?') }}">
@endsection

@section('content')
    @includeUnless(is_null($slider), 'storefront::public.home.sections.hero')

    @if (setting('storefront_features_section_enabled'))
        @include('storefront::public.home.sections.home_features')
    @endif

    @if (setting('storefront_featured_categories_section_enabled'))
        @include('storefront::public.home.sections.featured_categories')
    @endif

    @if (setting('storefront_three_column_full_width_banners_enabled'))
        @include('storefront::public.home.sections.three_column_full_width_banner')
    @endif

    @if (setting('storefront_product_tabs_1_section_enabled'))
        @include('storefront::public.home.sections.product_tabs_one')
    @endif

    @if (setting('storefront_top_brands_section_enabled') && $topBrands->isNotEmpty())
        @include('storefront::public.home.sections.top_brands')
    @endif

    @if (setting('storefront_flash_sale_and_vertical_products_section_enabled'))
        @include('storefront::public.home.sections.flash_sale', [
            'flashSaleEnabled' => setting('storefront_active_flash_sale_campaign')
        ])
    @endif

    @if (setting('storefront_two_column_banners_enabled'))
        @include('storefront::public.home.sections.two_column_banner')
    @endif

    @if (setting('storefront_product_grid_section_enabled'))
        @include('storefront::public.home.sections.grid_products')
    @endif

    @if (setting('storefront_three_column_banners_enabled'))
        @include('storefront::public.home.sections.three_column_banner')
    @endif

    @if (setting('storefront_product_tabs_2_section_enabled'))
        @include('storefront::public.home.sections.product_tabs_two')
    @endif

    @if (setting('storefront_one_column_banner_enabled'))
        @include('storefront::public.home.sections.one_column_banner')
    @endif

    @if (setting('storefront_blogs_section_enabled'))
        @include('storefront::public.home.sections.blog')
    @endif
@endsection

@push('meta')
    @php(
        $homeDescription = setting('store_description')
            ?: (setting('store_tagline') ?: setting('store_name'))
    )

    @php(
        $homeTitle = setting('store_tagline')
            ?: setting('store_name')
    )

    @php(
        $homeLogo = ($logo ?? null)
            ?: setting('store_logo')
    )

    <meta name="description" content="{{ $homeDescription }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $homeTitle }}">
    <meta property="og:description" content="{{ $homeDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if (! empty($homeLogo))
        <meta property="og:image" content="{{ $homeLogo }}">
    @endif
    <meta property="og:locale" content="{{ locale() }}">

    @foreach (supported_locale_keys() as $code)
        <meta property="og:locale:alternate" content="{{ $code }}">
    @endforeach

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $homeTitle }}">
    <meta name="twitter:description" content="{{ $homeDescription }}">
    @if (! empty($homeLogo))
        <meta name="twitter:image" content="{{ $homeLogo }}">
    @endif
@endpush

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/home/main.scss',
        'modules/Storefront/Resources/assets/public/js/pages/home/main.js',
    ])
@endpush

@extends('storefront::public.layout')

@section('title', $page->name)

@push('meta')
    <meta name="title" content="{{ $page->meta->meta_title ?: $page->name }}">
    <meta name="description" content="{{ $page->meta->meta_description }}">
    @php(
        $pageLogo = ($logo ?? null)
            ?: setting('store_logo')
            ?: asset('build/assets/image-placeholder.png')
    )
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $page->meta->meta_title ?: $page->name }}">
    <meta name="twitter:description" content="{{ $page->meta->meta_description }}">
    <meta name="twitter:image" content="{{ $pageLogo }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $page->meta->meta_title ?: $page->name }}">
    <meta property="og:description" content="{{ $page->meta->meta_description }}">
    <meta property="og:image" content="{{ $pageLogo }}">
    <meta property="og:locale" content="{{ locale() }}">

    @foreach (supported_locale_keys() as $code)
        <meta property="og:locale:alternate" content="{{ $code }}">
    @endforeach
@endpush

@section('canonical')
    <link rel="canonical" href="{{ \Illuminate\Support\Str::before($page->url(), '?') }}">
@endsection

@section('content') 
    <section class="custom-page-wrap clearfix">
        <div class="container">
            <div class="custom-page-content clearfix">
                {!! $page->body !!}
            </div>
        </div>
    </section>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/custom-page/main.scss',
    ])
@endpush

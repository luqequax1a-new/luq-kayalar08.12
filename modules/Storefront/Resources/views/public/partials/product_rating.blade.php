@php
    $serverReviewCount = null;
    if (isset($data)) {
        if (is_object($data)) {
            $serverReviewCount = $data->reviews_count ?? ((isset($data->reviews) && is_countable($data->reviews)) ? $data->reviews->count() : 0);
        } elseif (is_array($data)) {
            $serverReviewCount = $data['reviews_count'] ?? ((isset($data['reviews']) && is_countable($data['reviews'])) ? count($data['reviews']) : 0);
        }
    }
    $serverHasReviews = is_int($serverReviewCount) ? ($serverReviewCount > 0) : null;
@endphp

<template x-data="ProductRating({{ isset($data) && is_string($data) ? $data : 'product' }})">
    <a
        class="product-rating {{ $serverHasReviews === null ? '' : ($serverHasReviews ? 'has-reviews' : 'no-reviews') }}"
        :href="(typeof productUrl !== 'undefined' ? productUrl : window.location.pathname) + '#reviews'"
        x-on:click.prevent="typeof openReviewsTab==='function' && openReviewsTab()"
    >
        @if ($serverHasReviews)
            <div class="back-stars">
                <i class="las la-star"></i>
                <i class="las la-star"></i>
                <i class="las la-star"></i>
                <i class="las la-star"></i>
                <i class="las la-star"></i>

                <div x-cloak class="front-stars" :style="{ width: ratingPercent + '%' }">
                    <i class="las la-star"></i>
                    <i class="las la-star"></i>
                    <i class="las la-star"></i>
                    <i class="las la-star"></i>
                    <i class="las la-star"></i>
                </div>
            </div>
        @endif

        @if ($serverHasReviews)
            <template x-if="reviewCount > 0">
                <span class="rating-count" x-text="reviewCount"></span>
            </template>
        @endif

        @if ($serverHasReviews)
            <template x-if="reviewCount > 0">
                <div
                    class="reviews"
                    x-text="
                        reviewCount > 1 ?
                        '{{ trans('storefront::product_card.reviews') }}' :
                        '{{ trans('storefront::product_card.review') }}'
                    "
                >
                </div>
            </template>
        @endif
    </a>
</template>

<div x-data="ProductCard(product)" class="vertical-product-card">
    <a :href="productUrl" class="product-image">
        <picture>
            <template x-if="imageSources.avif">
                <source :srcset="imageSources.avif" type="image/avif">
            </template>
            <template x-if="imageSources.webp">
                <source :srcset="imageSources.webp" type="image/webp">
            </template>
            <img
                :src="imageSources.fallback"
                :alt="productName"
                loading="lazy"
                width="400"
                height="400"
            />
        </picture>

        <div class="product-image-layer"></div>
    </a>

    <div class="product-info">
        <a :href="productUrl" class="product-name">
            <span x-text="productName"></span>
        </a>

        @include('storefront::public.partials.product_rating')
        
        <div class="product-price" x-html="productPrice"></div>
    </div>
</div>

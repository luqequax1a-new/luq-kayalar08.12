import ProductMixin from "../mixins/ProductMixin";
import "./ProductRating";

Alpine.data("ProductCard", (product) => ({
    ...ProductMixin(product),
    previewImagePath: null,
    selectedVariantUid: null,
    showAllVariants: false,

    get currentImage() {
        const p = this.previewImagePath;
        const sel = Array.isArray(this.product?.variants)
            ? this.product.variants.find((v) => v.uid === this.selectedVariantUid)
            : null;
        const selPath = sel?.base_image?.path;
        const v = this.product?.variant?.base_image?.path;
        const b = this.product?.base_image?.path;
        if (p) return p;
        if (selPath) return selPath;
        if (v) return v;
        if (b) return b;
        return this.baseImage;
    },

    get productUrl() {
        let url = `/products/${this.product.slug}`;
        const uid = this.selectedVariantUid || (this.hasAnyVariant && this.item?.uid ? this.item.uid : null);
        if (uid) {
            url += `?variant=${uid}`;
        }
        return url;
    },

    get inWishlist() {
        return this.$store.wishlist.inWishlist(this.product.id);
    },

    get inCompareList() {
        return this.$store.compare.inCompareList(this.product.id);
    },

    get hasVisibleRating() {
        const reviewsCount = Number(
            this.product.reviews_count ?? (Array.isArray(this.product.reviews) ? this.product.reviews.length : 0)
        );
        return reviewsCount > 0;
    },

    previewVariant(variant) {
        this.previewImagePath = variant?.base_image?.path || null;
    },

    clearPreview() {
        this.previewImagePath = null;
    },

    selectVariant(variant) {
        this.selectedVariantUid = variant?.uid || null;
        this.previewVariant(variant);
    },

    urlForVariant(variant) {
        return `/products/${this.product.slug}?variant=${variant.uid}`;
    },

    toggleAllVariants() {
        this.showAllVariants = !this.showAllVariants;
    },

    closeAllVariants() {
        this.showAllVariants = false;
    },
    get currentSourceFile() {
        const p = this.previewImagePath;
        if (p && Array.isArray(this.product?.variants)) {
            const hovered = this.product.variants.find((v) => v?.base_image?.path === p);
            if (hovered?.base_image) return hovered.base_image;
        }
        const sel = Array.isArray(this.product?.variants)
            ? this.product.variants.find((v) => v.uid === this.selectedVariantUid)
            : null;
        if (sel?.base_image) return sel.base_image;
        if (this.product?.variant?.base_image) return this.product.variant.base_image;
        if (this.product?.base_image) return this.product.base_image;
        return null;
    },

    get imageSources() {
        const f = this.currentSourceFile;
        const avif = f?.grid_avif_url || null;
        const webp = f?.grid_webp_url || null;
        const jpeg = f?.grid_jpeg_url || f?.path || this.baseImageThumb || this.baseImage;
        return {
            avif,
            webp,
            fallback: jpeg,
        };
    },
}));

import ProductMixin from "../mixins/ProductMixin";
import "./ProductRating";

Alpine.data("ProductCard", (product) => ({
    ...ProductMixin(product),

    get inWishlist() {
        return this.$store.wishlist.inWishlist(this.product.id);
    },

    get inCompareList() {
        return this.$store.compare.inCompareList(this.product.id);
    },

    get hasVisibleRating() {
        const ratingPercent = this.product.rating_percent ?? 0;
        const reviews = Array.isArray(this.product.reviews) ? this.product.reviews.length : 0;
        return reviews > 0 || ratingPercent > 0;
    },
}));

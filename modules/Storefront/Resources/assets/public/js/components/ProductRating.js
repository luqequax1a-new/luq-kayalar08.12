Alpine.data("ProductRating", (data) => ({
    ratingPercent:
        (data && data.rating_percent != null)
            ? data.rating_percent
            : (data && data.avg_rating != null)
                ? Number(data.avg_rating) * 20
                : (data && data.rating != null)
                    ? Number(data.rating) * 20
                    : 0,

    reviewCount:
        (data && data.reviews_count != null)
            ? Number(data.reviews_count)
            : (data && Array.isArray(data.reviews))
                ? data.reviews.length
                : 0,

    get hasReviewCount() {
        return this.reviewCount !== undefined;
    },

    get hasVisibleRating() {
        return this.reviewCount > 0 || this.ratingPercent > 0;
    },
}));

Alpine.data("ProductRating", ({ rating_percent, reviews, reviews_count }) => ({
    ratingPercent: Number(rating_percent) || 0,
    reviewCount: Array.isArray(reviews) ? reviews.length : Number(reviews_count ?? 0),

    get hasReviewCount() {
        return Number(this.reviewCount ?? 0) > 0;
    },
}));

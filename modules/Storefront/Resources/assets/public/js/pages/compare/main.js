import "../../components/ProductCard";
import "../../components/ProductRating";
import "../../components/LandscapeProducts";

Alpine.data("Compare", () => ({
    get hasAnyProduct() {
        return Object.keys(this.$store.compare.products).length !== 0;
    },

    init() {
        this.$store.compare.fetchCompareProducts();

        Alpine.watch(
            () => Alpine.store("compare").fetchedCompareProducts,
            (newValue) => {
                if (newValue) {
                    this.hideSkeleton();
                }
            }
        );
    },

    hideSkeleton() {
        const el = document.querySelector(".compare-skeleton");

        if (!el) {
            return;
        }

        if (typeof el.remove === "function") {
            el.remove();
        } else if (el.parentNode) {
            el.parentNode.removeChild(el);
        }
    },

    badgeClass(product) {
        if (product.is_in_stock) {
            return "badge-success";
        }

        return "badge-danger";
    },

    hasAttribute(product, attribute) {
        for (let productAttribute of product.attributes) {
            if (productAttribute.name === attribute.name) {
                return true;
            }
        }
    },

    attributeValues(product, attribute) {
        for (let productAttribute of product.attributes) {
            if (productAttribute.name === attribute.name) {
                return productAttribute.values
                    .map((productAttributeValue) => {
                        return productAttributeValue.value;
                    })
                    .join(", ");
            }
        }
    },

    removeItem() {
        this.$store.compare.removeFromCompareList(this.product.id);
    },
}));

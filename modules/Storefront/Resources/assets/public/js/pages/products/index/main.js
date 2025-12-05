import { generateUid } from "../../../functions";
import { Navigation } from "swiper/modules";
import Swiper from "swiper";
import noUiSlider from "nouislider";
import "./components/CustomFilterSelect";
import "./components/CustomPageSelect";
import "../../../components/ProductCard";
import "../../../components/Pagination";

function updatePageTitle(selectedCategory) {
    const baseTitle = (window.FleetcartSEO && window.FleetcartSEO.baseTitle) || document.title;
    if (!selectedCategory) {
        document.title = baseTitle;
        return;
    }
    const categoryTitle = selectedCategory.meta_title || `${selectedCategory.name} | ${baseTitle}`;
    document.title = categoryTitle;
}

const {
    initialQuery,
    initialBrandName,
    initialBrandBanner,
    initialBrandSlug,
    initialCategoryName,
    initialCategoryBanner,
    initialCategorySlug,
    initialTagName,
    initialTagSlug,
    initialAttribute,
    minPrice,
    maxPrice,
    initialSort,
    initialPage,
    initialPerPage,
    initialViewMode,
} = FleetCart.data;

Alpine.data("ProductIndex", () => ({
    fetchingProducts: false,
    products: { data: [] },
    attributeFilters: [],
    initialBrandName,
    initialTagName,
    brandBanner: initialBrandBanner,
    categoryName: initialCategoryName,
    categoryBanner: initialCategoryBanner,
    viewMode: initialViewMode,
    currentPage: initialPage,
    queryParams: {
        query: initialQuery,
        brand: initialBrandSlug,
        category: initialCategorySlug,
        tag: initialTagSlug,
        attribute: initialAttribute,
        fromPrice: 0,
        toPrice: maxPrice,
        sort: initialSort,
        perPage: initialPerPage,
        page: initialPage,
    },

    get emptyProducts() {
        return this.products.data.length === 0;
    },

    get totalPage() {
        return Math.ceil(this.products.total / this.queryParams.perPage);
    },

    get showingResults() {
        if (this.emptyProducts) {
            return;
        }

        return trans("storefront::products.showing_results", {
            from: this.products.from,
            to: this.products.to,
            total: this.products.total,
        });
    },

    init() {
        if (this.queryParams.query && this.queryParams.category) {
            const url = new URL(window.location.href);
            url.pathname = "/products";
            url.searchParams.set("query", this.queryParams.query);
            url.searchParams.delete("category");
            window.history.replaceState({}, "", url.toString());

            this.queryParams.category = null;
            this.queryParams.attribute = {};
            this.queryParams.fromPrice = 0;
            this.queryParams.toPrice = maxPrice;
            this.categoryName = "";
            this.categoryBanner = null;

            if (this.$refs?.priceRange?.noUiSlider) {
                this.$refs.priceRange.noUiSlider.set([minPrice, maxPrice]);
            }
        } else if (this.queryParams.query) {
            this.currentPage = 1;
            this.queryParams.page = 1;
            this.queryParams.attribute = {};
            this.queryParams.fromPrice = 0;
            this.queryParams.toPrice = maxPrice;

            if (this.$refs?.priceRange?.noUiSlider) {
                this.$refs.priceRange.noUiSlider.set([minPrice, maxPrice]);
            }
        }
        this.initPriceFilter();
        this.fetchProducts();
        this.initLatestProductsSlider();
    },

    uid() {
        return generateUid();
    },

    changeSort(value) {
        this.queryParams.sort = value;

        this.fetchProducts();
    },

    changePerPage(value) {
        this.currentPage = 1;
        this.queryParams.perPage = value;
        this.queryParams.page = 1;

        this.fetchProducts();
    },

    initPriceFilter() {
        noUiSlider.create(this.$refs.priceRange, {
            connect: true,
            direction: window.FleetCart.rtl ? "rtl" : "ltr",
            start: [minPrice, maxPrice],
            range: {
                min: [minPrice],
                max: [maxPrice],
            },
        });

        this.$refs.priceRange.noUiSlider.on("update", (values, handle) => {
            const value = Number(values[handle]);

            if (handle === 0) {
                this.queryParams.fromPrice = value;
            } else {
                this.queryParams.toPrice = value;
            }
        });

        this.$refs.priceRange.noUiSlider.on("change", () => {
            this.fetchProducts();
        });
    },

    updatePriceRange(fromPrice, toPrice) {
        this.$refs.priceRange.noUiSlider.set([fromPrice, toPrice]);

        this.fetchProducts();
    },

    toggleAttributeFilter(slug, value) {
        if (!this.queryParams.attribute.hasOwnProperty(slug)) {
            this.queryParams.attribute[slug] = [];
        }

        if (this.queryParams.attribute[slug].includes(value)) {
            this.queryParams.attribute[slug].splice(
                this.queryParams.attribute[slug].indexOf(value),
                1
            );
        } else {
            this.queryParams.attribute[slug].push(value);
        }

        this.fetchProducts({ updateAttributeFilters: false });
    },

    isFilteredByAttribute(slug, value) {
        if (!this.queryParams.attribute.hasOwnProperty(slug)) {
            return false;
        }

        return this.queryParams.attribute[slug].includes(value);
    },

    changeCategory(category) {
        const url = new URL(window.location.href);
        const prevCategorySlug = this.queryParams.category;

        this.categoryName = category.name;
        this.categoryBanner = category.banner.path;
        this.currentPage = 1;
        this.queryParams.query = null;
        this.queryParams.category = category.slug;
        this.queryParams.attribute = {};
        this.queryParams.page = 1;
        this.queryParams.fromPrice = 0;
        this.queryParams.toPrice = maxPrice;

        if (this.$refs?.priceRange?.noUiSlider) {
            this.$refs.priceRange.noUiSlider.set([minPrice, maxPrice]);
        }

        this.fetchProducts();

        if (url.pathname.includes(`/categories/${prevCategorySlug}/products`)) {
            url.pathname = url.pathname.replace(
                prevCategorySlug,
                category.slug
            );

            window.history.replaceState(null, "", url.toString());
            updatePageTitle(category);
            return;
        }

        url.searchParams.set("category", category.slug);
        window.history.replaceState({}, "", url);
        updatePageTitle(category);
    },

    changePage(page) {
        this.currentPage = page;
        this.queryParams.page = page;

        this.fetchProducts();
    },

    async fetchProducts(options = { updateAttributeFilters: true }) {
        this.fetchingProducts = true;

        try {
            const response = await axios.get(`/products`, {
                params: {
                    ...this.queryParams,
                },
            });

            this.products = response.data.products;
            this.preloadFirstProductImage();

            if (options.updateAttributeFilters) {
                this.attributeFilters = response.data.attributes;
            }
        } catch (error) {
            notify(error.response.data.message);
        } finally {
            this.fetchingProducts = false;
        }
    },

    preloadFirstProductImage() {
        const first = Array.isArray(this.products?.data) ? this.products.data[0] : null;
        const base = first?.base_image || null;
        if (!base) return;
        const href = base.grid_avif_url || base.grid_webp_url || base.grid_jpeg_url || base.path;
        if (!href) return;
        if (document.querySelector(`link[rel="preload"][as="image"][href="${href}"]`)) return;
        const link = document.createElement("link");
        link.rel = "preload";
        link.as = "image";
        link.href = href;
        link.fetchPriority = "high";
        link.setAttribute("imagesrcset", href);
        link.setAttribute("imagesizes", "(min-width: 768px) 400px, 50vw");
        document.head.appendChild(link);
    },

    initLatestProductsSlider() {
        new Swiper(this.$refs.latestProducts, {
            modules: [Navigation],
            slidesPerView: 1,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });
    },
}));

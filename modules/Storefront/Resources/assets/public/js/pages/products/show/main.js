import { Manipulation, Pagination, Navigation, Thumbs } from "swiper/modules";
import md5 from "blueimp-md5";
import Swiper from "swiper";
import Drift from "drift-zoom";
import GLightbox from "glightbox";
import Errors from "../../../components/Errors";
import "../../../components/ProductRating";
import "../../../components/Pagination";
import "../../../components/ProductCard";

// Global helper: product header'daki rating'e tıklayınca yorumlar sekmesine git.
window.openReviewsTab = function openReviewsTab() {
    try {
        const tabLink = document.querySelector('.product-details-tab a[href="#reviews"]');
        if (!tabLink) return;

        // Bootstrap tab'i tetikle
        tabLink.click();

        // İçerik göründükten sonra yorumlar bölümüne kaydır
        const target = document.getElementById('reviews');
        if (!target) return;

        setTimeout(() => {
            try {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (_) {
                target.scrollIntoView(true);
            }
        }, 150);
    } catch (_) {}
};

function handleGalleryVideoTap(e) {
    const wrapper = e.target.closest('.gallery-preview-item--video');
    if (!wrapper) return;

    const video = wrapper.querySelector('.product-main-media--video');
    if (!video) return;

    e.preventDefault();
    e.stopPropagation();

    const isPlaying = !video.paused && !video.ended;

    try {
        if (isPlaying) {
            video.pause();
            wrapper.dataset.playing = 'false';
        } else {
            const p = video.play();
            wrapper.dataset.playing = 'true';
            if (p && typeof p.catch === 'function') p.catch(() => {});

            video.addEventListener('ended', () => {
                wrapper.dataset.playing = 'false';
            }, { once: true });
        }
    } catch (_) {}

    try {
        const wrap = document.querySelector('.product-gallery-preview-wrap');
        if (wrap && wrap.classList.contains('visible-variation-image')) {
            wrap.classList.remove('visible-variation-image');
        }
    } catch (_) {}
}

// Desktop + mobile: ekrana dokunma/tıklama ile play/pause toggle
document.addEventListener('click', handleGalleryVideoTap, false);
document.addEventListener('touchend', handleGalleryVideoTap, false);

let galleryPreviewSlider;
let galleryPreviewLightbox;
let galleryPreviewZoomInstances = [];

Alpine.data(
    "ProductShow",
    ({ product, variant, reviewCount, avgRating, ratingBreakdown, flashSalePrice }) => ({
        product: product,
        item: variant || product,
        optionPrices: {},
        addingToCart: false,
        oldMediaLength: null,
        activeVariationValues: {},
        variationImagePath: null,
        showDescriptionContent: false,
        showMore: false,
        fetchingReviews: false,
        reviews: {},
        reviewCount,
        avgRating,
        // Rating dağılımı (yorum istatistikleri)
        ratingBreakdown: ratingBreakdown || { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 },
        addingNewReview: false,
        reviewForm: {},
        currentPage: 1,

        // Review kuponu için: review request mailinden gelen order_id query parametresi
        orderIdFromQuery: null,
        cartItemForm: {
            product_id: product.id,
            // Qty başlangıcı tamamen unit modülüne bağlı: önce unit_default_qty, sonra unit_min; yoksa 1
            qty:
                (Number(product.unit_default_qty) > 0
                    ? Number(product.unit_default_qty)
                    : (Number(product.unit_min) > 0
                        ? Number(product.unit_min)
                        : 1)),
            variations: {},
            options: {},
        },
        errors: new Errors(),

        // ---- Qty input state (tamamen unit tabanlı) ----
        isEditingQty: false,
        qtyInput: "",
        // Min qty: sadece unit_min'den okunur; yoksa güvenli default 1
        minQty: (Number(product.unit_min) > 0 ? Number(product.unit_min) : 1),
        // Step qty: sadece unit_step'ten okunur; yoksa 1 (adet ürünler için)
        stepQty: (Number(product.unit_step) > 0 ? Number(product.unit_step) : 1),

        // ---- Review upload state ----
        isDraggingUpload: false,
        reviewImages: [],
        maxReviewPhotos: 4,

        get productName() {
            return this.product.name;
        },

        get isActiveItem() {
            return this.item.is_active === true;
        },

        get productUrl() {
            let url = `/products/${this.product.slug}`;

            if (this.hasAnyVariant) {
                url += `?variant=${this.item.uid}`;
            }

            return url;
        },

        get hasAnyMedia() {
            return this.item.media.length !== 0;
        },

        get productPrice() {
            return this.hasSpecialPrice
                ? this.item.selling_price.inCurrentCurrency.amount
                : this.item.price.inCurrentCurrency.amount;
        },

        get regularPrice() {
            let productPrice = this.item.price.inCurrentCurrency.amount;

            if (
                this.hasAnyOption &&
                !this.hasSpecialPrice &&
                this.hasAnyOptionPrice
            ) {
                return productPrice + this.optionsPrice;
            }

            return productPrice;
        },

        get hasSpecialPrice() {
            return (
                this.product.is_in_flash_sale ||
                this.item.special_price !== null
            );
        },

        get hasPercentageSpecialPrice() {
            return this.item.has_percentage_special_price;
        },

        get specialPrice() {
            let productPrice = this.item.selling_price.inCurrentCurrency.amount;

            if (flashSalePrice && !this.hasAnyVariant) {
                productPrice = flashSalePrice;
            }

            if (
                this.hasAnyOption &&
                this.hasSpecialPrice &&
                this.hasAnyOptionPrice
            ) {
                return productPrice + this.optionsPrice;
            }

            return productPrice;
        },

        get isInStock() {
            return this.item.is_in_stock;
        },

        get isOutOfStock() {
            return this.item.is_out_of_stock;
        },

        get doesManageStock() {
            return this.item.does_manage_stock;
        },

        get hasAnyVariationImage() {
            return this.variationImagePath !== null;
        },

        get inWishlist() {
            return this.$store.wishlist.inWishlist(this.product.id);
        },

        get inCompareList() {
            return this.$store.compare.inCompareList(this.product.id);
        },

        get hasAnyVariant() {
            return this.product.variant !== null;
        },

        get hasAnyOption() {
            return this.product.options.length > 0;
        },

        get hasAnyOptionPrice() {
            return Object.keys(this.optionPrices).length !== 0;
        },

        get optionsPrice() {
            return Object.values(this.optionPrices).reduce(
                (total, value) => total + value,
                0
            );
        },

        get isAddToCartDisabled() {
            return this.isActiveItem ? this.isOutOfStock : true;
        },

        get maxQuantity() {
            return this.isInStock && this.doesManageStock
                ? this.item.qty
                : null;
        },

        get isQtyIncreaseDisabled() {
            return (
                this.isOutOfStock ||
                (this.maxQuantity !== null &&
                    this.cartItemForm.qty >= this.item.qty) ||
                !this.isActiveItem
            );
        },

        get isQtyDecreaseDisabled() {
            return (
                this.isOutOfStock ||
                this.cartItemForm.qty <= this.minQty ||
                !this.isActiveItem
            );
        },

        get totalReviews() {
            if (!this.reviews.total) {
                return this.reviewCount;
            }

            return this.reviews.total;
        },

        get ratingPercent() {
            return (this.avgRating / 5) * 100;
        },

        get emptyReviews() {
            return this.totalReviews === 0;
        },

        get totalPage() {
            return Math.ceil(this.reviews.total / 5);
        },

        getRatingColor(rating) {
            const r = Number(rating) || 0;

            if (r >= 4.5) {
                return "#16a34a"; // çok iyi
            }

            if (r >= 3) {
                return "#facc15"; // orta
            }

            if (r > 0) {
                return "#ef4444"; // düşük
            }

            return "#9ca3af"; // rating yoksa gri
        },

        init() {
            // URL'den order_id query parametresini oku (yorum kuponu için gerekecek)
            try {
                const params = new URLSearchParams(window.location.search || "");
                const rawOrderId = params.get("order_id");
                const parsed = rawOrderId ? parseInt(rawOrderId, 10) : null;
                this.orderIdFromQuery = Number.isFinite(parsed) && parsed > 0 ? parsed : null;
            } catch (_) {
                this.orderIdFromQuery = null;
            }

            this.$watch("cartItemForm.options", () => {
                this.productPriceWithOptionsPrice();
            });

            galleryPreviewSlider = this.initGalleryPreviewSlider();
            galleryPreviewLightbox = this.initGalleryPreviewLightbox();

            this.fetchReviews();
            this.setOldMediaLength();
            this.initGalleryPreviewZoom();
            this.setActiveVariationsValue();
            this.setDescriptionContentHeight();
            this.initUpSellProductsSlider();
            this.initRelatedProductsSlider();

            // Yorum görselleri için lightbox başlat
            this.initReviewLightbox();
            this.updateBadgeVisibilityForActiveSlide();
        },

        syncWishlist() {
            this.$store.wishlist.syncWishlist(this.product.id);
        },

        syncCompareList() {
            this.$store.compare.syncCompareList(this.product.id);
        },

        setOldMediaLength() {
            if (this.hasAnyVariant) {
                this.oldMediaLength = this.item.media.length;
            }
        },

        initGalleryPreviewSlider() {
            const slider = new Swiper(".product-gallery-preview", {
                modules: [Manipulation, Navigation, Thumbs],
                slidesPerView: 1,
                // Masaüstünde oklarla, mobilde parmakla kaydırma
                allowTouchMove: this.isMobileDevice(),
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                thumbs: {
                    swiper: this.initGalleryThumbnailSlider(),
                },
            });
            slider.on("slideChange", () => {
                this.updateBadgeVisibilityForActiveSlide();
            });

            return slider;
        },

        initGalleryThumbnailSlider() {
            return new Swiper(".product-gallery-thumbnail", {
                modules: [Manipulation, Navigation],
                slidesPerView: 4,
                spaceBetween: 10,
                watchSlidesProgress: true,
                touchEventsTarget: "container",
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                breakpoints: {
                    450: {
                        slidesPerView: 6,
                    },
                    576: {
                        slidesPerView: 7,
                    },
                    992: {
                        slidesPerView: 6,
                    },
                    1600: {
                        slidesPerView: 7,
                    },
                },
            });
        },

        updateGallerySlider() {
            if (!galleryPreviewSlider || !galleryPreviewSlider.thumbs || !galleryPreviewSlider.thumbs.swiper) {
                return;
            }

            this.removeAllGallerySlides();

            // If product and variant has not media
            if (this.product.media.length === 0 && !this.hasAnyMedia) {
                this.addGalleryEmptySlide();
            } else {
                // 1) Sadece image slidelarını kur
                this.addGallerySlides();
                // 2) Videoları 3. kanal gibi en sona ekle
                this.appendVideoSlides();
            }

            this.addGalleryEventListeners();
            this.updateBadgeVisibilityForActiveSlide();
        },

        addGallerySlides() {
            // Swiper yoksa devam etme
            if (
                !galleryPreviewSlider ||
                !galleryPreviewSlider.thumbs ||
                !galleryPreviewSlider.thumbs.swiper
            ) {
                return;
            }

            const galleryPreviewSlides = [];
            const galleryThumbnailSlides = [];

            const variantMedia = Array.isArray(this.item.media) ? this.item.media : [];
            const productMedia = Array.isArray(this.product.media) ? this.product.media : [];

            // DEFAULT: sadece item.media + product.media (sadece IMAGES)
            const allMedia = [...variantMedia, ...productMedia];

            const seen = new Set();

            allMedia.forEach((m) => {
                if (!m) return;

                // Video'ları burada tamamen yok sayıyoruz
                if (m.type === "video") {
                    return;
                }

                const path = m.path;
                if (!path) return;

                if (seen.has(path)) return;
                seen.add(path);

                galleryPreviewSlides.unshift(
                    this.galleryPreviewSlide(path)
                );
                galleryThumbnailSlides.unshift(
                    this.galleryThumbnailSlide(path)
                );
            });

            galleryPreviewSlider.addSlide(0, galleryPreviewSlides);
            galleryPreviewSlider.thumbs.swiper.addSlide(0, galleryThumbnailSlides);

            galleryPreviewSlider.slideTo(0);
            galleryPreviewSlider.thumbs.swiper.slideTo(0);
        },

        appendVideoSlides() {
            if (
                !galleryPreviewSlider ||
                !galleryPreviewSlider.thumbs ||
                !galleryPreviewSlider.thumbs.swiper
            ) {
                return;
            }

            const rawVideos = Array.isArray(this.product.product_media)
                ? this.product.product_media
                : (Array.isArray(this.product.productMedia)
                    ? this.product.productMedia
                    : []);

            if (!rawVideos.length) {
                return;
            }

            rawVideos
                .filter((v) => v && v.type === "video" && v.path)
                .forEach((v) => {
                    const videoPath = v.path;

                    const poster =
                        v.poster ||
                        v.thumb ||
                        this.item?.base_image?.path ||
                        this.product?.base_image?.path ||
                        `${FleetCart.baseUrl}/build/assets/image-placeholder.png`;

                    const previewSlide = this.galleryPreviewVideoSlide(videoPath, poster);
                    const thumbSlide = this.galleryThumbnailVideoSlide(poster);

                    const lastIndex = galleryPreviewSlider.slides.length;

                    galleryPreviewSlider.addSlide(lastIndex, previewSlide);
                    galleryPreviewSlider.thumbs.swiper.addSlide(
                        galleryPreviewSlider.thumbs.swiper.slides.length,
                        thumbSlide
                    );
                });

            galleryPreviewSlider.update();
            galleryPreviewSlider.thumbs.swiper.update();
        },

        addGalleryEmptySlide() {
            const filePath = `${FleetCart.baseUrl}/build/assets/image-placeholder.png`;

            galleryPreviewSlider.addSlide(
                0,
                this.galleryPreviewEmptySlide(filePath)
            );
            galleryPreviewSlider.thumbs.swiper.addSlide(
                0,
                this.galleryThumbnailEmptySlide(filePath)
            );
        },

        removeAllGallerySlides() {
            if (!galleryPreviewSlider || !galleryPreviewSlider.thumbs || !galleryPreviewSlider.thumbs.swiper) {
                return;
            }

            galleryPreviewSlider.removeAllSlides();
            galleryPreviewSlider.thumbs.swiper.removeAllSlides();
        },

        addGalleryEventListeners() {
            this.$nextTick(() => {
                this.initGalleryPreviewZoom();
                galleryPreviewLightbox.reload();
            });
        },

        updateBadgeVisibilityForActiveSlide() {
            try {
                const wrap = document.querySelector(".product-gallery-preview-wrap");
                if (!wrap || !galleryPreviewSlider || !galleryPreviewSlider.slides) {
                    return;
                }

                const activeIndex = galleryPreviewSlider.activeIndex || 0;
                const activeSlide = galleryPreviewSlider.slides[activeIndex];
                if (!activeSlide) {
                    wrap.classList.remove("is-video-active");
                    return;
                }

                const isVideo = !!activeSlide.querySelector(".gallery-preview-item--video");

                if (isVideo) {
                    wrap.classList.add("is-video-active");
                } else {
                    wrap.classList.remove("is-video-active");
                }
            } catch (_) {}
        },

        initGalleryPreviewZoom() {
            // Mobilde Drift zoom'u tamamen devre dışı bırak;
            // kullanıcı sadece yana kaydırarak görsel/video değiştirebilsin.
            if (this.isMobileDevice()) {
                this.destroyGalleryPreviewZoomInstances();

                return;
            }

            this.initGalleryPreviewDesktopZoom();
        },

        initGalleryPreviewMobileZoom() {
            this.destroyGalleryPreviewZoomInstances();

            [
                ...document.querySelectorAll(".gallery-preview-item > img"),
            ].forEach((el) => {
                galleryPreviewZoomInstances.push(
                    new Drift(el, {
                        namespace: "mobile-drift",
                        inlinePane: true,
                        inlineOffsetY: -50,
                        passive: true,
                    })
                );
            });
        },

        initGalleryPreviewDesktopZoom() {
            this.destroyGalleryPreviewZoomInstances();

            [
                ...document.querySelectorAll(".gallery-preview-item > img"),
            ].forEach((el) => {
                galleryPreviewZoomInstances.push(
                    new Drift(el, {
                        inlinePane: false,
                        hoverBoundingBox: true,
                        boundingBoxContainer: document.body,
                        paneContainer:
                            document.querySelector(".product-gallery"),
                    })
                );
            });
        },

        destroyGalleryPreviewZoomInstances() {
            if (galleryPreviewZoomInstances.length !== 0) {
                galleryPreviewZoomInstances.forEach((instance) => {
                    instance.destroy();
                });
            }
        },

        initGalleryPreviewLightbox() {
            return GLightbox({
                zoomable: true,
                preload: false,
            });
        },

        triggerGalleryPreviewLightbox(event) {
            if (window.innerWidth > 990) {
                event.currentTarget.nextElementSibling.click();
            }
        },

        galleryPreviewSlide(filePath) {
            return `
                <div class="swiper-slide">
                    <div class="gallery-preview-slide">
                        <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox(event)">
                            <img src="${filePath}" data-zoom="${filePath}" alt="${this.productName}">
                        </div>

                        <a href="${filePath}" data-gallery="product-gallery-preview" class="gallery-view-icon glightbox">
                            <i class="las la-search-plus"></i>
                        </a>
                    </div>
                </div>
            `;
        },

        galleryThumbnailSlide(filePath) {
            return `
                <div class="swiper-slide">
                    <div class="gallery-thumbnail-slide">
                        <div class="gallery-thumbnail-item">
                            <img src="${filePath}" alt="${this.productName}">
                        </div>
                    </div>
                </div>
            `;
        },

        galleryPreviewVideoSlide(videoPath, posterPath) {
            return `
                <div class="swiper-slide">
                    <div class="gallery-preview-slide">
                        <div class="gallery-preview-item gallery-preview-item--video" data-media-type="video">
                            <video
                                class="product-main-media product-main-media--video"
                                controls
                                playsinline
                                preload="metadata"
                                poster="${posterPath}"
                            >
                                <source src="${videoPath}" type="video/mp4">
                            </video>
                            <span class="fc-video-play-icon">▶</span>
                        </div>
                    </div>
                </div>
            `;
        },

        galleryThumbnailVideoSlide(posterPath) {
            return `
                <div class="swiper-slide">
                    <div class="gallery-thumbnail-slide">
                        <div class="gallery-thumbnail-item gallery-thumbnail-item--video">
                            <img src="${posterPath}" alt="${this.productName}">
                            <span class="fc-video-play-icon">▶</span>
                        </div>
                    </div>
                </div>
            `;
        },

        galleryPreviewEmptySlide(filePath) {
            return `
                <div class="swiper-slide">
                    <div class="gallery-preview-slide">
                        <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox(event)">
                            <img src="${filePath}" data-zoom="${filePath}" alt="${this.productName}" class="image-placeholder">
                        </div>

                        <a href="${filePath}" data-gallery="product-gallery-preview" class="gallery-view-icon glightbox">
                            <i class="las la-search-plus"></i>
                        </a>
                    </div>
                </div>
            `;
        },

        galleryThumbnailEmptySlide(filePath) {
            return `
                <div class="swiper-slide">
                    <div class="gallery-thumbnail-slide">
                        <div class="gallery-thumbnail-item">
                            <img src="${filePath}" alt="${this.productName}" class="image-placeholder">
                        </div>
                    </div>
                </div>
            `;
        },

        productPriceWithOptionsPrice() {
            const cartItemoptions = Object.entries(this.cartItemForm.options);

            cartItemoptions.forEach(([key, value]) => {
                const option = this.product.options.find(
                    ({ id }) => id === Number(key)
                );

                // Single select with single value
                if (
                    ["field", "textarea", "date", "date_time", "time"].includes(
                        option.type
                    )
                ) {
                    if (!Boolean(this.cartItemForm.options[option.id])) {
                        delete this.optionPrices[option.id];

                        return;
                    }

                    const optionValue = option.values[0];
                    const price =
                        optionValue.price?.inCurrentCurrency?.amount ??
                        (+optionValue.price / 100) * this.productPrice;

                    this.optionPrices[key] = price;

                    return;
                }

                // Single select with multiple values
                if (
                    ["dropdown", "radio", "radio_custom"].includes(option.type)
                ) {
                    const optionValue = option.values.find(
                        ({ id }) => id === Number(value)
                    );

                    const price =
                        optionValue.price?.inCurrentCurrency?.amount ??
                        (+optionValue.price / 100) * this.productPrice;

                    this.optionPrices[key] = price;

                    return;
                }

                // Multiple select with multiple values
                if (
                    ["checkbox", "checkbox_custom", "multiple_select"].includes(
                        option.type
                    ) &&
                    value.length !== 0
                ) {
                    const values = this.product.options
                        .find(({ id }) => id === Number(key))
                        .values.filter((data) => value.includes(data.id));

                    const price = values.reduce(
                        (accumulator, value) =>
                            accumulator +
                            (value.price?.inCurrentCurrency?.amount ??
                                (+value.price / 100) * this.productPrice),
                        0
                    );

                    this.optionPrices[key] = price;
                }
            });
        },

        isVariationValueEnabled(variationUid, variationIndex, valueUid) {
            // Check if enabled first variation values
            if (variationIndex === 0) {
                return this.doesVariantExist(valueUid);
            }

            // Check if enabled variation values between first and last variation
            if (
                variationIndex > 0 &&
                variationIndex < this.product.variations.length - 1
            ) {
                return this.doesVariantExist(valueUid);
            }

            // Check if enabled last variation values
            if (variationIndex === this.product.variations.length - 1) {
                const variations = this.cartItemForm.variations;
                const valueUids = Object.values(variations).filter(
                    (uid) => uid !== variations[variationUid]
                );

                valueUids.push(valueUid);

                return this.doesVariantExist(valueUids.sort().join("."));
            }
        },

        setActiveVariationsValue() {
            if (!this.hasAnyVariant) return;

            this.item.uids.split(".").forEach((uid) => {
                this.product.variations.some((variation) => {
                    const value = variation.values.find(
                        (value) => value.uid === uid
                    );

                    if (value !== undefined) {
                        this.activeVariationValues[variation.uid] = value.label;
                        this.cartItemForm.variations[variation.uid] = uid;

                        return true;
                    }
                });
            });
        },

        setActiveVariationValueLabel(variationIndex) {
            this.variationImagePath = null;

            const variation = this.product.variations[variationIndex];
            const value = variation.values.find(
                (value) =>
                    value.uid === this.cartItemForm.variations[variation.uid]
            );

            this.activeVariationValues[variation.uid] = value.label;
        },

        setVariationValueLabel(variationIndex, valueIndex) {
            const variation = this.product.variations[variationIndex];
            const value = variation.values[valueIndex];

            if (!this.isMobileDevice() && variation.type === "image") {
                this.variationImagePath = value.image.path;
            }

            this.activeVariationValues[variation.uid] = value.label;
        },

        isActiveVariationValue(variationUid, valueUid) {
            if (!this.cartItemForm.variations.hasOwnProperty(variationUid)) {
                return false;
            }

            return this.cartItemForm.variations[variationUid] === valueUid;
        },

        syncVariationValue(variationUid, variationIndex, valueUid, valueIndex) {
            if (!this.isActiveVariationValue(variationUid, valueUid)) {
                this.cartItemForm.variations[variationUid] = valueUid;

                this.setVariationValueLabel(variationIndex, valueIndex);
                this.updateVariantDetails();
            }
        },

        doesVariantExist(uid) {
            return this.product.variants.some(({ uids }) => uids.includes(uid));
        },

        setVariant() {
            const selectedUids = Object.values(this.cartItemForm.variations)
                .sort()
                .join(".");

            const variant = this.product.variants.find(
                (variant) => variant.uids === selectedUids
            );

            if (variant !== undefined) {
                this.item = { ...variant };

                this.reduceToMaxQuantity();

                return;
            }

            // Set empty variant data if variant does not exist
            const uid = md5(
                Object.values(this.cartItemForm.variations).sort().join(".")
            );

            this.item = {
                uid,
                media: [],
                base_image: [],
            };

            this.cartItemForm.qty = 1;
        },

        setVariantSlug() {
            const url = `${FleetCart.baseUrl.replace(/\/$/, "")}/products/${
                this.product.slug
            }?variant=${this.item.uid}`;

            window.history.replaceState({}, "", url);
        },

        updateVariantDetails() {
            this.setOldMediaLength();
            this.setVariant();
            this.setVariantSlug();
            this.updateGallerySlider();
        },

        updateSelectTypeOptionValue(optionId, event) {
            this.cartItemForm.options = Object.assign(
                {},
                this.cartItemForm.options,
                {
                    [optionId]: event.target.value,
                }
            );

            this.errors.clear(`options.${optionId}`);
        },

        updateCheckboxTypeOptionValue(optionId, event) {
            let values = $(event.target)
                .parents(".variant-check")
                .find('input[type="checkbox"]:checked')
                .map((_, el) => {
                    return el.value;
                });

            this.cartItemForm.options = Object.assign(
                {},
                this.cartItemForm.options,
                {
                    [optionId]: values.get(),
                }
            );
        },

        customRadioTypeOptionValueIsActive(optionId, valueId) {
            if (!this.cartItemForm.options.hasOwnProperty(optionId)) {
                return false;
            }

            return this.cartItemForm.options[optionId] === valueId;
        },

        syncCustomRadioTypeOptionValue(optionId, valueId) {
            if (this.customRadioTypeOptionValueIsActive(optionId, valueId)) {
                delete this.cartItemForm.options[optionId];
            } else {
                this.cartItemForm.options = Object.assign(
                    {},
                    this.cartItemForm.options,
                    {
                        [optionId]: valueId,
                    }
                );

                this.errors.clear(`options.${optionId}`);
            }
        },

        customCheckboxTypeOptionValueIsActive(optionId, valueId) {
            if (!this.cartItemForm.options.hasOwnProperty(optionId)) {
                this.cartItemForm.options = Object.assign(
                    {},
                    this.cartItemForm.options,
                    {
                        [optionId]: [],
                    }
                );

                return false;
            }

            return this.cartItemForm.options[optionId].includes(valueId);
        },

        syncCustomCheckboxTypeOptionValue(optionId, valueId) {
            if (this.customCheckboxTypeOptionValueIsActive(optionId, valueId)) {
                this.cartItemForm.options[optionId].splice(
                    this.cartItemForm.options[optionId].indexOf(valueId),
                    1
                );
            } else {
                this.cartItemForm.options[optionId].push(valueId);

                // Reassign the existing data due to reactivity issue
                this.cartItemForm = Object.assign(
                    {},
                    this.cartItemForm,
                    this.cartItemForm.options
                );

                this.errors.clear(`options.${optionId}`);
            }
        },

        setDescriptionContentHeight() {
            this.$nextTick(() => {
                this.showMore =
                    this.$refs.descriptionContent.clientHeight >= 400
                        ? true
                        : false;
            });
        },

        setInactiveItemData() {
            this.item = {
                uid: this.item.uid,
                media: [],
                base_image: [],
            };
        },

        isMobileDevice() {
            return window.matchMedia("only screen and (max-width: 992px)")
                .matches;
        },

        // Unit tabanlı qty normalizasyonu
        normalizeQty(raw) {
            let v;

            if (typeof raw === "number") {
                v = raw;
            } else {
                const str = String(raw ?? "").trim().replace(",", ".");
                v = parseFloat(str);
            }

            if (!isFinite(v) || v <= 0) {
                v = this.minQty || 1;
            }

            if (typeof this.minQty === "number") {
                v = Math.max(v, this.minQty);
            }

            if (typeof this.maxQuantity === "number" && this.maxQuantity > 0) {
                v = Math.min(v, this.maxQuantity);
            }

            const step = this.stepQty || 0;
            if (step > 0) {
                v = Math.round(v / step) * step;
                v = Number(v.toFixed(3));
            }

            return v;
        },

        updateQuantity(nextQty) {
            const value = this.normalizeQty(nextQty);
            this.cartItemForm.qty = value;
        },

        exceedsMaxStock(qty) {
            return this.doesManageStock && this.item.qty < qty;
        },

        reduceToMaxQuantity() {
            if (this.doesManageStock && this.cartItemForm.qty > this.item.qty) {
                this.cartItemForm.qty = this.item.qty || 1;
            }
        },

        beginEditQty(event) {
            this.isEditingQty = true;
            // Input'a tıklayınca alan boşalsın, kullanıcı baştan yazsın
            this.qtyInput = "";
        },

        commitEditQty() {
            this.isEditingQty = false;

            const raw = (this.qtyInput || "").trim();
            if (raw === "") return;

            const val = Number(raw.replace(",", "."));
            if (Number.isNaN(val)) return;

            this.setQuantityManual(val);
        },

        setQuantityManual(val) {
            let v = Number(val);
            if (Number.isNaN(v)) return;

            const min = this.minQty || 1;
            if (v < min) v = min;

            if (this.exceedsMaxStock(v)) {
                this.cartItemForm.qty = this.item.qty;
                return;
            }

            if (this.product.unit_decimal) {
                this.cartItemForm.qty = Number(v.toFixed(2));
                return;
            }

            this.cartItemForm.qty = Math.round(v);
        },

        onQtyInput(event) {
            this.qtyInput = event.target.value;
        },

        addToCart() {
            if (this.isAddToCartDisabled) return;

            this.addingToCart = true;

            axios
                .post("/cart/items", {
                    ...this.cartItemForm,
                    ...(this.hasAnyVariant && { variant_id: this.item.id }),
                })
                .then((response) => {
                    this.$store.cart.updateCart(response.data);
                    this.$store.layout.openSidebarCart();
                })
                .catch(({ response }) => {
                    if (response.status === 422) {
                        this.errors.record(response.data.errors);
                    }

                    notify(response.data.message);
                })
                .finally(() => {
                    this.addingToCart = false;
                });
        },

        toggleDescriptionContent() {
            this.showDescriptionContent = !this.showDescriptionContent;
        },

        initReviewLightbox() {
            try {
                if (this.reviewLightbox && typeof this.reviewLightbox.destroy === "function") {
                    this.reviewLightbox.destroy();
                }
            } catch (_) {}

            this.$nextTick(() => {
                try {
                    this.reviewLightbox = GLightbox({
                        selector: ".review-image-lightbox",
                        touchNavigation: true,
                        preload: false,
                        openEffect: "fade",
                        closeEffect: "fade",
                    });
                } catch (_) {}
            });
        },

        async fetchReviews() {
            this.fetchingReviews = true;

            try {
                const response = await axios.get(
                    `/products/${this.product.id}/reviews?page=${this.currentPage}`
                );

                this.reviews = response.data;
                this.initReviewLightbox();
            } catch (error) {
                notify(error.response.data.message);
            } finally {
                this.fetchingReviews = false;
            }
        },

        addNewReview(event) {
            this.addingNewReview = true;

            const formEl = event?.target || null;
            const formData = new FormData();

            formData.append("rating", this.reviewForm.rating || "");
            formData.append("reviewer_name", this.reviewForm.reviewer_name || "");
            formData.append("comment", this.reviewForm.comment || "");

            // Review kuponu için order_id bilgisini de ilet
            if (this.orderIdFromQuery) {
                formData.append("order_id", this.orderIdFromQuery);
            }

            this.reviewImages.forEach(({ file }) => {
                if (file) {
                    formData.append("images[]", file);
                }
            });

            if (window.grecaptcha) {
                formData.append("g-recaptcha-response", grecaptcha.getResponse());
            }

            axios
                .post(`/products/${this.product.id}/reviews`, formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                })
                .then((response) => {
                    const newReview = response.data;

                    this.reviews.total = (this.reviews.total || 0) + 1;
                    this.reviews.data = Array.isArray(this.reviews.data)
                        ? [newReview, ...this.reviews.data]
                        : [newReview];

                    notify(trans("storefront::product.review_submitted"));

                    this.errors.reset();
                    this.reviewForm.rating = null;
                    this.reviewForm.reviewer_name = "";
                    this.reviewForm.comment = "";

                    if (formEl && typeof formEl.reset === "function") {
                        formEl.reset();
                    }

                    // URL.revokeObjectURL ile oluşturulan tüm preview'leri temizle
                    this.reviewImages.forEach((item) => {
                        if (item.preview) {
                            try { URL.revokeObjectURL(item.preview); } catch (_) {}
                        }
                    });

                    this.reviewImages = [];
                })
                .catch((error) => {
                    const response = error.response;

                    if (response && response.status === 422) {
                        this.errors.record(response.data.errors);

                        return;
                    }

                    if (response && response.data && response.data.message) {
                        notify(response.data.message);
                    }
                })
                .finally(() => {
                    this.addingNewReview = false;

                    if (window.grecaptcha) {
                        try { grecaptcha.reset(); } catch (_) {}
                    }
                });
        },

        openReviewFilePicker() {
            if (this.$refs.reviewFileInput) {
                this.$refs.reviewFileInput.click();
            }
        },

        onSelectReviewImages(event) {
            const input = event.target;
            const files = Array.from(input.files || []);

            if (!files.length) {
                return;
            }

            const allowed = new Set(["image/jpeg", "image/png", "image/webp", "image/avif"]);
            const existing = this.reviewImages.slice();
            const remainingSlots = Math.max(0, this.maxReviewPhotos - existing.length);

            const selected = files
                .filter((f) => allowed.has(f.type))
                .slice(0, remainingSlots)
                .map((file) => ({
                    file,
                    preview: URL.createObjectURL(file),
                }));

            this.reviewImages = existing.concat(selected);

            if (input) {
                input.value = "";
            }
        },

        onDropReviewImages(event) {
            this.isDraggingUpload = false;

            const dt = event.dataTransfer;
            if (!dt || !dt.files) return;

            const pseudoEvent = { target: { files: dt.files } };
            this.onSelectReviewImages(pseudoEvent);
        },

        removeReviewImage(index) {
            const item = this.reviewImages[index];

            if (item && item.preview) {
                try { URL.revokeObjectURL(item.preview); } catch (_) {}
            }

            this.reviewImages.splice(index, 1);
        },

        changePage(page) {
            this.currentPage = page;

            this.fetchReviews();
        },

        hideRelatedProductsSkeleton() {
            const skeletons = document.querySelectorAll(
                ".landscape-products .swiper-slide-skeleton"
            );

            skeletons.forEach((skeleton) => skeleton.remove());
        },

        initUpSellProductsSlider() {
            new Swiper(this.$refs.upSellProducts, {
                modules: [Navigation],
                slidesPerView: 1,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
            });
        },

        initRelatedProductsSlider() {
            this.hideRelatedProductsSkeleton();

            new Swiper(this.$refs.landscapeProducts, {
                modules: [Navigation, Pagination],
                slidesPerView: 2,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                breakpoints: {
                    640: {
                        slidesPerView: 3,
                    },
                    880: {
                        slidesPerView: 4,
                    },
                    992: {
                        slidesPerView: 3,
                    },
                    1100: {
                        slidesPerView: 4,
                    },
                    1300: {
                        slidesPerView: 5,
                    },
                    1600: {
                        slidesPerView: 6,
                    },
                },
            });
        },
    })
);

import _ from "lodash";
import { useVariants } from "../composables/useVariants";

const { variantPosition, hasAnyVariant } = useVariants();

export default class {
    constructor() {
        this.data = {};
    }

    transformMedia() {
        this.data.media = (this.data.media || [])
            .filter((m) => !!m && !!m.id && !this.isVideoPath(m.path))
            .map((data) => data.id);
    }

    transformAttributes() {
        this.data.attributes = this.data.attributes
            .filter(({ attribute_id }) => attribute_id !== "")
            .reduce((accumulator, { attribute_id, uid, values }) => {
                return { ...accumulator, [uid]: { attribute_id, values } };
            }, {});
    }

    transformDownloads() {
        this.data.downloads = this.data.downloads
            .filter(({ id }) => id !== null)
            .map(({ id }) => id);
    }

    transformGalleryItems() {
        if (!Array.isArray(this.data.gallery_items)) return;
        const images = [];
        const videos = [];
        this.data.gallery_items.forEach((item, index) => {
            if (item && item.kind === 'video') {
                videos.push({
                    id: item.id || null,
                    product_id: this.data.id || null,
                    variant_id: item.variant_id || null,
                    type: 'video',
                    path: item.path || '',
                    poster: item.poster || '',
                    position: Number.isFinite(item.position) ? item.position : index,
                    // is_active dropped; always show
                });
            } else if (item && item.kind === 'image') {
                images.push({ id: item.id || null, path: item.path || '' });
            }
        });
        this.data.media = images;
        this.data.product_media = videos;
    }

    isVideoPath(path) {
        try {
            const raw = String(path || '').split('?')[0];
            const ext = raw.split('.').pop().toLowerCase();
            return ['mp4','webm','ogg','ogv'].includes(ext);
        } catch (_) {
            return false;
        }
    }

    collectProductMediaFromForm() {
        const videos = [];
        const images = [];
        (this.data.media || []).forEach((m, index) => {
            if (!m || !m.path) return;
            if (this.isVideoPath(m.path)) {
                videos.push({
                    id: m.id || null,
                    product_id: this.data.id || null,
                    variant_id: m.variant_id || null,
                    type: 'video',
                    path: m.path || '',
                    poster: (typeof m.poster === 'string' ? m.poster : (m.poster?.path || '')),
                    position: Number.isFinite(m.position) ? m.position : index,
                    is_active: 1,
                });
            } else {
                images.push(m);
            }
        });
        this.data.media = images;
        this.data.product_media = videos;
    }

    transformProductMedia() {
        const sanitize = (item, index) => {
            const out = {
                id: item.id || null,
                product_id: item.product_id || null,
                variant_id: item.variant_id || null,
                type: item.type === 'video' ? 'video' : 'image',
                path: item.path || '',
                poster: item.poster || (item.poster?.path || ''),
                position: Number.isFinite(item.position) ? item.position : index,
                is_active: item.is_active === false ? 0 : 1,
            };
            return out;
        };

        if (Array.isArray(this.data.product_media)) {
            this.data.product_media = this.data.product_media
                .filter((m) => !!m && !!m.path)
                .map((m, i) => sanitize(m, i));
        } else {
            this.data.product_media = [];
        }
    }

    transformVariations() {
        const PATHS = {
            text: ["id", "uid", "label"],
            color: ["id", "uid", "label", "color"],
            image: ["id", "uid", "label", "image"],
        };

        this.data.variations = this.data.variations
            .filter(({ name, type }) => Boolean(name) || type !== "")
            .reduce((accumulator, variation) => {
                if (variation.type === "") {
                    variation.values = [];
                } else {
                    variation.values = variation.values.reduce(
                        (valueAccumulator, value) => {
                            value = _.pick(value, PATHS[variation.type]);

                            if (variation.type === "image" && value.image?.id) {
                                value.image = value.image.id;
                            }

                            return { ...valueAccumulator, [value.uid]: value };
                        },
                        {},
                    );
                }

                return { ...accumulator, [variation.uid]: variation };
            }, {});
    }

    transformVariants() {
        variantPosition.value = 0;

        this.data.variants = this.data.variants.reduce(
            (accumulator, variant) => {
                variant.position = variantPosition.value++;
                variant.media = variant.media.map(({ id }) => id);

                return { ...accumulator, [variant.uid]: variant };
            },
            {},
        );
    }

    getOptionType(option) {
        const TYPES = {
            text: ["field", "textarea"],
            select: [
                "dropdown",
                "checkbox",
                "checkbox_custom",
                "radio",
                "radio_custom",
                "multiple_select",
            ],
            date: ["date", "date_time", "time"],
        };

        for (const [type, values] of Object.entries(TYPES)) {
            if (values.includes(option.type)) {
                return type;
            }
        }
    }

    transformOptions() {
        const PATHS = {
            text: ["id", "uid", "price", "price_type"],
            select: ["id", "uid", "label", "price", "price_type"],
            date: ['id', 'uid', 'price', 'price_type']
        };

        this.data.options = this.data.options
            .filter(({ name, type }) => Boolean(name) || type !== "")
            .reduce((accumulator, option) => {
                if (option.type === "") {
                    option.values = [];
                } else {
                    option.values = option.values.reduce(
                        (valueAccumulator, value) => {
                            value = _.pick(value, PATHS[this.getOptionType(option)]);
                            
                            return { ...valueAccumulator, [value.uid]: value };
                        },
                        {},
                    );
                }

                return { ...accumulator, [option.uid]: option };
            }, {});
    }

    transform(data) {
        this.data = JSON.parse(JSON.stringify(data));

        if (hasAnyVariant.value) {
            this.data = {
                ...this.data,
                price: null,
                special_price: null,
                special_price_type: "fixed",
                special_price_start: null,
                special_price_end: null,
                sku: null,
                manage_stock: 0,
                qty: null,
                in_stock: 1,
            };
        }

        this.collectProductMediaFromForm();
        this.transformMedia();
        this.transformProductMedia();
        this.transformAttributes();
        this.transformDownloads();
        this.transformVariations();
        this.transformVariants();
        this.transformOptions();

        if (typeof this.data.list_variants_separately !== 'undefined') {
            this.data.list_variants_separately = this.data.list_variants_separately ? 1 : 0;
        }

        return this.data;
    }
}

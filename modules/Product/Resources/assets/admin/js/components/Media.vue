<template>
    <div class="box-header">
        <h5>
            {{ trans("product::products.group.media") }}
        </h5>

        <div class="drag-handle">
            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
        </div>
    </div>

    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <draggable
                    class="product-media-grid"
                    animation="200"
                    item-key="index"
                    handle=".handle"
                    :list="form.media"
                >
                    <template #item="{ element: media, index }">
                        <div class="media-grid-item">
                            <div class="image-holder">
                                <span class="handle" style="position:absolute;top:6px;right:6px;width:16px;height:16px;display:block;cursor:grab;"></span>
                                <template v-if="isVideo(media.path)">
                                    <div class="admin-video-thumb" style="position: relative;">
                                        <img :src="getVideoThumb(media)" alt="product video" />
                                        <span class="admin-video-play"></span>
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-secondary pick-poster-btn"
                                            @click.stop="pickPoster(index)"
                                            :title="media.poster ? 'Kapak görselini değiştir' : 'Kapak görseli seç'"
                                            style="position:absolute;left:6px;top:6px;padding:1px 6px;font-size:10px;line-height:1;border-radius:10px;opacity:0.9;"
                                        >
                                            {{ media.poster ? 'Kapak' : 'Kapak Seç' }}
                                        </button>
                                    </div>
                                </template>
                                <template v-else>
                                    <img :src="media.path" alt="product media" />
                                </template>
                                <button type="button" class="btn remove-image" @click="removeMedia(index)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M6.00098 17.9995L17.9999 6.00053" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M17.9999 17.9995L6.00098 6.00055" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                    <template #footer>
                        <div class="media-grid-item media-picker disabled" @click="addMedia">
                            <div class="image-holder">
                                <img src="@admin/images/placeholder_image.png" class="placeholder-image" alt="Placeholder Image" />
                            </div>
                        </div>
                    </template>
                </draggable>
            </div>
        </div>

        
    </div>
</template>

<script setup>
import { useForm } from "../composables/useForm";
import draggable from "vuedraggable";
import placeholderImage from "@admin/images/placeholder_image.png";

const { form } = useForm();

function isVideo(path) {
    try {
        const raw = (path || '').split('?')[0];
        const ext = raw.split('.').pop().toLowerCase();
        return ['mp4','webm','ogg'].includes(ext);
    } catch (_) {
        return false;
    }
}

function getVideoThumb(media) {
    const poster = media.poster || '';
    if (poster && typeof poster === 'string') return poster;
    return placeholderImage;
}

function addMedia() {
    const picker = new MediaPicker({ type: null, multiple: true });
    picker.on("select", ({ id, path, poster }) => {
        const ext = String(path || '').split('?')[0].split('.').pop().toLowerCase();
        const isVideo = ['mp4','webm','ogg'].includes(ext);
        const payload = isVideo ? { id: +id, path, poster: poster || '' } : { id: +id, path };
        form.media.push(payload);
    });
}

function removeMedia(index) {
    form.media.splice(index, 1);
}

function pickPoster(index) {
    try {
        const picker = new MediaPicker({ type: 'image', multiple: false });
        picker.on('select', ({ path }) => {
            const item = form.media[index];
            if (!item) return;
            item.poster = String(path || '');
        });
    } catch (_) {}
}
</script>

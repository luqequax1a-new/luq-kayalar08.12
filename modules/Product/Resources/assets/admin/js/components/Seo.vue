<template>
    <div class="box-header">
        <h5>
            {{ trans("product::products.group.seo") }}
        </h5>

        <div class="drag-handle">
            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
        </div>
    </div>

    <div class="box-body">
        <div class="form-group row">
            <label for="slug" class="col-sm-12 control-label text-left">
                {{ trans("product::attributes.slug") }}

                <span
                    v-if="window.location.pathname.endsWith('/edit')"
                    class="text-red"
                    >*</span
                >
            </label>

            <div class="col-sm-12">
                <div class="input-group">
                    <input
                        type="text"
                        name="slug"
                        id="slug"
                        class="form-control"
                        @change="setProductSlug($event.target.value)"
                        v-model="form.slug"
                    />
                    <span class="input-group-btn">
                        <button
                            type="button"
                            class="btn btn-default"
                            title="Slug oluştur"
                            aria-label="Slug oluştur"
                            @click="setProductSlug(form.name || '')"
                        >
                            <i class="fa fa-magic" aria-hidden="true"></i>
                        </button>
                    </span>
                </div>

                <div v-if="slugChanged" style="display:flex;align-items:center;gap:10px;margin-top:6px;">
                    <small class="text-muted">
                        Eski: <code>{{ oldUrl }}</code>
                    </small>
                    <small>→</small>
                    <small class="text-muted">
                        Yeni: <code>{{ newUrl }}</code>
                    </small>
                    <div class="switch" style="margin-left:auto;">
                        <input type="checkbox" id="redirect-on-slug-change" name="redirect_on_slug_change" v-model="form.redirect_on_slug_change" />
                        <label for="redirect-on-slug-change">301 yönlendirme</label>
                    </div>
                    <input type="hidden" name="original_slug" :value="form.original_slug" />
                </div>

                <span
                    class="help-block text-red"
                    v-if="errors.has('slug')"
                    v-text="errors.get('slug')"
                ></span>
            </div>
        </div>

        <div class="form-group row">
            <label for="meta-title" class="col-sm-12 control-label text-left">
                {{ trans("meta::attributes.meta_title") }}
            </label>

            <div class="col-sm-12">
                <input
                    type="text"
                    name="meta.meta_title"
                    id="meta-title"
                    class="form-control"
                    v-model="form.meta.meta_title"
                />

                <span
                    class="help-block text-red"
                    v-if="errors.has('meta.meta_title')"
                    v-text="errors.get('meta.meta_title')"
                ></span>
            </div>
        </div>

        <div class="form-group row">
            <label
                for="meta-description"
                class="col-sm-12 control-label text-left"
            >
                {{ trans("meta::attributes.meta_description") }}
            </label>

            <div class="col-sm-12">
                <textarea
                    name="meta.meta_description"
                    rows="6"
                    cols="10"
                    id="meta-description"
                    class="form-control"
                    v-model="form.meta.meta_description"
                ></textarea>

                <span
                    class="help-block text-red"
                    v-if="errors.has('slug')"
                    v-text="errors.get('slug')"
                ></span>
            </div>
        </div>

        
    </div>
</template>

<script setup>
import { computed } from "vue";
import { useForm } from "../composables/useForm";
import { useProductMethods } from "../composables/useProductMethods";

const { form, errors } = useForm();
const { setProductSlug } = useProductMethods();

const slugChanged = computed(() => !!form.original_slug && form.slug !== form.original_slug);
const oldUrl = computed(() => `/products/${(form.original_slug || '').replace(/^\//,'')}`);
const newUrl = computed(() => `/products/${(form.slug || '').replace(/^\//,'')}`);
</script>

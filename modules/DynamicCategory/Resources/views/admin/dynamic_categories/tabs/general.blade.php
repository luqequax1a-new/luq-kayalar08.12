@php
    // Ensure $errors is available in tab views, similar to other modules
    if (!isset($errors)) {
        $errors = app('view')->shared('errors') ?? new \Illuminate\Support\ViewErrorBag;
    }
@endphp

<div class="tab-pane active" id="general">
    {{ Form::text('name', trans('dynamic_category::attributes.name'), $errors, $dynamicCategory, ['required' => true]) }}

    {{ Form::textarea('description', trans('dynamic_category::attributes.description'), $errors, $dynamicCategory, ['class' => 'wysiwyg']) }}

    @if (auth()->user()->hasAccess('admin.media.index'))
        <div class="form-group">
            <label class="col-md-3 control-label">{{ trans('dynamic_category::attributes.image') }}</label>
            <div class="col-md-9">
                @include('media::admin.image_picker.single', [
                    'title' => trans('dynamic_category::attributes.image'),
                    'inputName' => 'image_id',
                    'file' => optional($dynamicCategory->image ?? null),
                ])
            </div>
        </div>
    @endif

    {{ Form::text('slug', trans('dynamic_category::attributes.slug'), $errors, $dynamicCategory, ['required' => true]) }}

    {{ Form::checkbox('is_active', trans('dynamic_category::attributes.is_active'), trans('dynamic_category::dynamic_categories.form.enable_dynamic_category'), $errors, $dynamicCategory) }}

    {{ Form::text('meta_title', trans('dynamic_category::attributes.meta_title'), $errors, $dynamicCategory) }}
    {{ Form::textarea('meta_description', trans('dynamic_category::attributes.meta_description'), $errors, $dynamicCategory, ['rows' => 3]) }}

    <div class="form-group">
        <label class="col-md-3 control-label">{{ trans('dynamic_category::dynamic_categories.serp_preview_title') }}</label>
        <div class="col-md-9">
            <div id="serp-preview" class="serp-preview">
                <div class="serp-title"></div>
                <div class="serp-url"></div>
                <div class="serp-description"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script type="module">
        (function () {
            const baseUrl = '{{ url('/') }}';

            const nameInput = document.querySelector('input[name="name"]');
            const slugInput = document.querySelector('input[name="slug"]');
            const metaTitleInput = document.querySelector('input[name="meta_title"]');
            const metaDescriptionInput = document.querySelector('textarea[name="meta_description"]');

            const serpTitle = document.querySelector('#serp-preview .serp-title');
            const serpUrl = document.querySelector('#serp-preview .serp-url');
            const serpDescription = document.querySelector('#serp-preview .serp-description');

            function getTitle() {
                const metaTitle = metaTitleInput?.value?.trim();
                if (metaTitle) {
                    return metaTitle;
                }

                return nameInput?.value?.trim() || '';
            }

            function getUrl() {
                const slug = slugInput?.value?.trim() || '';

                if (!slug) {
                    return baseUrl + '/kategori/';
                }

                return baseUrl + '/kategori/' + slug;
            }

            function getDescription() {
                return metaDescriptionInput?.value?.trim() || '';
            }

            function updateSerp() {
                if (!serpTitle || !serpUrl || !serpDescription) {
                    return;
                }

                serpTitle.textContent = getTitle();
                serpUrl.textContent = getUrl();
                serpDescription.textContent = getDescription();
            }

            [nameInput, slugInput, metaTitleInput, metaDescriptionInput].forEach((input) => {
                if (!input) return;

                input.addEventListener('input', updateSerp);
                input.addEventListener('change', updateSerp);
            });

            updateSerp();
        })();
    </script>
@endpush

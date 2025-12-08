@php
    if (!isset($errors)) {
        $errors = app('view')->shared('errors') ?? new \Illuminate\Support\ViewErrorBag;
    }
@endphp

<div class="tab-pane" id="tag_rules">
    <div class="form-group">
        <label class="col-md-3 control-label">{{ trans('dynamic_category::dynamic_categories.include_label') }}</label>
        <div class="col-md-9">
            <select name="include_tags[]" class="form-control select2" multiple required>
                @foreach (Modules\Tag\Entities\Tag::list() as $id => $name)
                    <option value="{{ $id }}" {{ isset($dynamicCategory) && $dynamicCategory->includeTags->pluck('tag_id')->contains($id) ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
            <span class="help-block">{{ trans('dynamic_category::dynamic_categories.include_help') }}</span>
            @if ($errors->has('include_tags'))
                <span class="help-block text-red">{{ $errors->first('include_tags') }}</span>
            @endif
        </div>
    </div>
</div>

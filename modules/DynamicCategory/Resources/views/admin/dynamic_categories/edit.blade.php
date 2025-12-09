@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('dynamic_category::dynamic_categories.dynamic_category')]))
    @slot('subtitle', $dynamicCategory->name)

    <li><a href="{{ route('admin.dynamic_categories.index') }}">{{ trans('dynamic_category::dynamic_categories.dynamic_categories') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('dynamic_category::dynamic_categories.dynamic_category')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.dynamic_categories.update', $dynamicCategory) }}" class="form-horizontal" id="dynamic-category-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}

        @include('dynamic_category::admin.dynamic_categories.tabs.general', ['dynamicCategory' => $dynamicCategory])
        @include('dynamic_category::admin.dynamic_categories.tabs.rules', ['dynamicCategory' => $dynamicCategory])

        <div class="form-group mb-0">
            <div class="col-md-12 col-md-offset-3">
                <button type="submit" class="btn btn-primary" data-loading>
                    {{ trans('admin::admin.buttons.save') }}
                </button>
            </div>
        </div>
    </form>
@endsection

@push('globals')
    @vite([
        'modules/DynamicCategory/Resources/assets/admin/js/main.js',
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
    ])
@endpush

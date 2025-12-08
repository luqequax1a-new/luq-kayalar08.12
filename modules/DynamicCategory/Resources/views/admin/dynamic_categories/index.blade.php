@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('dynamic_category::dynamic_categories.dynamic_categories'))

    <li class="active">{{ trans('dynamic_category::dynamic_categories.dynamic_categories') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'dynamic_categories')
    @slot('name', trans('dynamic_category::dynamic_categories.dynamic_category'))

    @component('admin::components.table')
        @slot('thead')
            <tr>
                @include('admin::partials.table.select_all')

                <th>{{ trans('admin::admin.table.id') }}</th>
                <th>{{ trans('dynamic_category::dynamic_categories.table.name') }}</th>
                <th>{{ trans('dynamic_category::dynamic_categories.table.slug') }}</th>
                <th>{{ trans('dynamic_category::dynamic_categories.table.status') }}</th>
                <th class="text-center">{{ trans('dynamic_category::dynamic_categories.table.include_tags_count') }}</th>
                <th class="text-center">{{ trans('dynamic_category::dynamic_categories.table.exclude_tags_count') }}</th>
                <th data-sort>{{ trans('admin::admin.table.created') }}</th>
            </tr>
        @endslot
    @endcomponent
@endcomponent

@push('scripts')
    <script type="module">
        new DataTable('#dynamic_categories-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'name', name: 'name', defaultContent: '' },
                { data: 'slug', name: 'slug', defaultContent: '' },
                { data: 'status', name: 'is_active', searchable: false },
                { data: 'include_tags_count', searchable: false, className: 'text-center' },
                { data: 'exclude_tags_count', searchable: false, className: 'text-center' },
                { data: 'created', name: 'created_at' },
            ],
        });
    </script>
@endpush

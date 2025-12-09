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
                <th>{{ trans('dynamic_category::dynamic_categories.table.products_count') }}</th>
                <th data-sort>{{ trans('admin::admin.table.created') }}</th>
                <th>{{ trans('admin::admin.table.actions') }}</th>
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
                { data: 'products_count', searchable: false, className: 'text-center align-middle' },
                { data: 'created', name: 'created_at', className: 'text-center align-middle' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center align-middle' },
            ],
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Dynamic categories table alignment: keep headers default (left), center only body cells */
        #dynamic_categories-table .table td.text-center {
            text-align: center !important;
        }

        #dynamic_categories-table .table td.align-middle {
            vertical-align: middle !important;
        }

        /* Explicitly center Product Count, Created and Actions body cells
           Columns (including checkbox):
           1: checkbox, 2: ID, 3: Name, 4: Slug, 5: Status,
           6: Product Count, 7: Created, 8: Actions
        */
        #dynamic_categories-table .table td:nth-child(6),
        #dynamic_categories-table .table td:nth-child(7),
        #dynamic_categories-table .table td:nth-child(8) {
            text-align: center !important;
            vertical-align: middle !important;
        }
    </style>
@endpush

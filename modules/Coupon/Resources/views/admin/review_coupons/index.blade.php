@extends('admin::layout')

@section('title', 'Yorum Kuponları')

@component('admin::components.page.index_table')
    @slot('resource', 'review_coupons')
    @slot('name', 'Yorum Kuponları')

    @slot('thead')
        <tr>
            @include('admin::partials.table.select_all')
            <th>{{ 'Kod' }}</th>
            <th>{{ 'Müşteri' }}</th>
            <th>{{ 'Sipariş' }}</th>
            <th>{{ 'İndirim' }}</th>
            <th data-sort>{{ trans('admin::admin.table.created') }}</th>
            <th>{{ 'Durum' }}</th>
            <th data-sort>{{ trans('admin::admin.table.actions') }}</th>
        </tr>
    @endslot
@endcomponent

@push('scripts')
    <script type="module">
        new DataTable('#review_coupons-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'code' },
                { data: 'customer' },
                { data: 'order_id' },
                { data: 'discount' },
                { data: 'created', name: 'created_at' },
                { data: 'status' },
                { data: 'actions', orderable: false, searchable: false },
            ],
        });
    </script>
@endpush

@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('ticket::ticket.tickets'))
    <li class="active">{{ trans('ticket::ticket.tickets') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('resource', 'tickets')
    @slot('name', trans('ticket::ticket.ticket'))

    @slot('thead')
        <tr>
            @include('admin::partials.table.select_all')

            <th>{{ trans('admin::admin.table.id') }}</th>
            <th>{{ trans('ticket::ticket.subject') }}</th>
            <th>{{ trans('ticket::ticket.status') }}</th>
            <th>User</th>
            <th data-sort>{{ trans('ticket::ticket.last_message') }}</th>
            <th></th>
        </tr>
    @endslot
@endcomponent

@push('scripts')
    <script type="module">
        DataTable.set('#tickets-table .table', {
            routePrefix: 'tickets',
            routes: { table: 'table', show: 'show' }
        });
        new DataTable('#tickets-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'subject' },
                { data: 'status' },
                { data: 'user', orderable: false, searchable: true },
                { data: 'updated', name: 'updated_at' },
                { data: 'actions', orderable: false, searchable: false, width: '8%' },
            ],
        });
    </script>
@endpush

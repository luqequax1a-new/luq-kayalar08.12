<tr>
    @include('admin::partials.table.select_all')

    <th>{{ trans('admin::admin.table.id') }}</th>
    <th>{{ trans('product::products.table.thumbnail') }}</th>
    <th>{{ trans('product::products.table.name') }}</th>
    <th>{{ trans('product::products.table.brand') }}</th>
    <th>Default Category</th>
    <th>{{ trans('product::products.table.price') }}</th>
    <th>{{ trans('product::products.table.stock') }}</th>
    <th>{{ trans('admin::admin.table.status') }}</th>
    <th>Actions</th>
</tr>

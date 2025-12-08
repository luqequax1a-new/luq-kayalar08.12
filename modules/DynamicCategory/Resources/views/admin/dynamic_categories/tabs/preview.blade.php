<div class="tab-pane" id="preview">
    <div class="box box-default">
        <div class="box-body">
            <p class="text-muted">{{ trans('dynamic_category::dynamic_categories.preview_help') }}</p>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ trans('admin::admin.table.image') }}</th>
                        <th>{{ trans('admin::admin.table.name') }}</th>
                        <th>{{ trans('admin::admin.table.price') }}</th>
                        <th>{{ trans('tag::tags.tags') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($previewProducts as $product)
                        <tr>
                            <td>
                                @if ($product->base_image)
                                    <img src="{{ $product->base_image->path }}" alt="{{ $product->name }}" width="50">
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->formatted_price }}</td>
                            <td>
                                @if ($product->relationLoaded('tags'))
                                    {{ $product->tags->pluck('name')->implode(', ') }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                {{ trans('dynamic_category::dynamic_categories.preview_empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

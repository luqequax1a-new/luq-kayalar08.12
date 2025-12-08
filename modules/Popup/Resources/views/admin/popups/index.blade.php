@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('popup::popup.admin_title'))

    <li class="active">{{ trans('popup::popup.admin_title') }}</li>
@endcomponent

@section('content')
    <div class="box box-primary">
        <div class="box-header with-border clearfix">
            <h3 class="box-title">{{ trans('popup::popup.admin_title') }}</h3>

            <div class="box-tools pull-right">
                <a href="{{ route('admin.popups.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i>
                    {{ trans('admin::resource.create', ['resource' => trans('popup::popup.admin_title')]) }}
                </a>
            </div>
        </div>

        <div class="box-body">
            <form method="GET" action="{{ route('admin.popups.index') }}" class="form-inline" style="margin-bottom: 15px;">
                <div class="form-group" style="margin-right:10px;">
                    <label for="status" style="margin-right:5px;">{{ trans('popup::popup.fields.status') }}</label>
                    <select name="status" id="status" class="form-control input-sm">
                        <option value="">Hepsi</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>{{ trans('popup::popup.status.active') }}</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>{{ trans('popup::popup.status.inactive') }}</option>
                    </select>
                </div>

                <div class="form-group" style="margin-right:10px;">
                    <label for="target_scope" style="margin-right:5px;">{{ trans('popup::popup.fields.target_scope') }}</label>
                    <select name="target_scope" id="target_scope" class="form-control input-sm">
                        <option value="">Hepsi</option>
                        @foreach(trans('popup::popup.target_scopes') as $key => $label)
                            <option value="{{ $key }}" {{ request('target_scope') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-default btn-sm">Filtrele</button>
            </form>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ trans('popup::popup.table.name') }}</th>
                        <th>{{ trans('popup::popup.table.status') }}</th>
                        <th>{{ trans('popup::popup.table.target_scope') }}</th>
                        <th>{{ trans('popup::popup.table.device') }}</th>
                        <th>{{ trans('popup::popup.table.trigger') }}</th>
                        <th>{{ trans('popup::popup.table.frequency') }}</th>
                        <th>{{ trans('popup::popup.table.created_at') }}</th>
                        <th class="text-right">{{ trans('admin::admin.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($popups as $popup)
                        <tr>
                            <td>{{ $popup->name }}</td>
                            <td>
                                @if($popup->status)
                                    <span class="label label-success">{{ trans('popup::popup.status.active') }}</span>
                                @else
                                    <span class="label label-default">{{ trans('popup::popup.status.inactive') }}</span>
                                @endif
                            </td>
                            <td>{{ trans('popup::popup.target_scopes.' . $popup->target_scope) }}</td>
                            <td>{{ trans('popup::popup.device.' . $popup->device) }}</td>
                            <td>{{ trans('popup::popup.trigger_types.' . $popup->trigger_type) }}</td>
                            <td>{{ trans('popup::popup.frequency_types.' . $popup->frequency_type) }}</td>
                            <td>{{ $popup->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('admin.popups.destroy', $popup->id) }}" style="display:inline-block" onsubmit="return confirm('{{ trans('admin::messages.confirm_delete') }}');">
                                    {{ csrf_field() }}
                                    {{ method_field('delete') }}

                                    <a href="{{ route('admin.popups.edit', $popup->id) }}" class="btn btn-default btn-sm">
                                        <i class="fa fa-pencil"></i>
                                    </a>

                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.popups.duplicate', $popup->id) }}" style="display:inline-block; margin-left:3px;">
                                    {{ csrf_field() }}

                                    <button type="submit" class="btn btn-info btn-sm" title="Kopyala">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">{{ trans('admin::admin.table.no_data_available_table') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="box-footer clearfix">
            {{ $popups->links() }}
        </div>
    </div>
@endsection

@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('support::robots.title'))

    <li class="active">{{ trans('support::robots.title') }}</li>
@endcomponent

@section('content')
    <div class="alert alert-info">
        {{ trans('support::robots.info', ['url' => $robotsUrl]) }}
    </div>

    <form method="POST" action="{{ route('admin.robots.update') }}" class="form-horizontal">
        @csrf

        <div class="form-group{{ $errors->has('robots') ? ' has-error' : '' }}">
            <label class="col-md-2 control-label" for="robots">{{ trans('support::robots.edit_label') }}</label>

            <div class="col-md-10">
                <textarea name="robots" id="robots" rows="20" class="form-control">{{ old('robots', $robotsContent) }}</textarea>

                @if ($errors->has('robots'))
                    <span class="help-block">{{ $errors->first('robots') }}</span>
                @endif

                <span class="help-block">{{ trans('support::robots.hint') }}</span>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-10 col-md-offset-2">
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group" role="group">
                        <button type="submit" class="btn btn-primary" data-loading>
                            {{ trans('support::robots.save') }}
                        </button>
                    </div>

                    <div class="btn-group" role="group" style="margin-left: 10px;">
                        <button type="submit" form="robots-reset-form" class="btn btn-default" data-loading>
                            {{ trans('support::robots.reset') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form id="robots-reset-form" method="POST" action="{{ route('admin.robots.reset') }}" style="display:none;">
        @csrf
    </form>
@endsection

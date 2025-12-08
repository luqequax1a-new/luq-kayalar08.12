@extends('admin::layout')

@section('title', trans('popup::popup.admin_title'))

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jstree@3.3.12/dist/themes/default/style.min.css">
@endpush

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('popup::popup.admin_title')]))

    <li><a href="{{ route('admin.popups.index') }}">{{ trans('popup::popup.admin_title') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('popup::popup.admin_title')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.popups.store') }}" class="form-horizontal" novalidate>
        {{ csrf_field() }}

        @include('popup::admin.popups.form')
    </form>
@endsection

@push('globals')
    @vite([
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
    ])
@endpush

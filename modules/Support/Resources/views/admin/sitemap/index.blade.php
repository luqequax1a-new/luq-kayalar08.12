@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('support::sitemap.sitemap'))

    <li class="active">{{ trans('support::sitemap.sitemap') }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.sitemaps.store') }}" enctype="multipart/form-data"
          class="form-horizontal">
        @csrf

        <div class="accordion-content">
            <div class="accordion-box-content clearfix">
                <div class="col-md-12">
                    <div class="accordion-box-content">
                        <div class="tab-content clearfix">
                            <div class="tab-pane fade in active">
                                <h4 class="tab-content-title">Priority &amp; Change Frequency</h4>

                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th style="width: 160px;">Include</th>
                                                <th style="width: 160px;">Priority (0.1 - 1.0)</th>
                                                <th style="width: 200px;">Changefreq</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $freqOptions = [
                                                    \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_DAILY => 'daily',
                                                    \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY => 'weekly',
                                                    \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_MONTHLY => 'monthly',
                                                    \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_YEARLY => 'yearly',
                                                ];
                                            @endphp

                                            @php
                                                $types = [
                                                    'products' => ['label' => 'Products', 'includeDefault' => true, 'priorityDefault' => 0.7, 'freqDefault' => \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY],
                                                    'categories' => ['label' => 'Categories', 'includeDefault' => true, 'priorityDefault' => 0.8, 'freqDefault' => \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_DAILY],
                                                    'pages' => ['label' => 'CMS Pages', 'includeDefault' => true, 'priorityDefault' => 0.5, 'freqDefault' => \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY],
                                                    'brands' => ['label' => 'Brands', 'includeDefault' => true, 'priorityDefault' => 0.5, 'freqDefault' => \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY],
                                                    'blog_posts' => ['label' => 'Blog Posts', 'includeDefault' => true, 'priorityDefault' => 0.6, 'freqDefault' => \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY],
                                                    'blog_categories' => ['label' => 'Blog Categories', 'includeDefault' => true, 'priorityDefault' => 0.5, 'freqDefault' => \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY],
                                                    'other_pages' => ['label' => 'Other Pages', 'includeDefault' => false, 'priorityDefault' => 0.5, 'freqDefault' => \Spatie\Sitemap\Tags\Url::CHANGE_FREQUENCY_WEEKLY],
                                                ];
                                            @endphp

                                            @foreach ($types as $key => $meta)
                                                @php
                                                    $includeName = 'include_' . $key;
                                                    $priorityName = $key . '_priority';
                                                    $freqName = $key . '_changefreq';

                                                    $includeVal = (bool) setting('support.sitemap.' . $includeName, $meta['includeDefault']);
                                                    $priorityVal = (float) setting('support.sitemap.' . $priorityName, $meta['priorityDefault']);
                                                    $freqVal = (string) setting('support.sitemap.' . $freqName, $meta['freqDefault']);
                                                @endphp
                                                <tr>
                                                    <td>{{ $meta['label'] }}</td>
                                                    <td class="text-center">
                                                        <input type="hidden" name="{{ $includeName }}" value="0">
                                                        <input type="checkbox" name="{{ $includeName }}" value="1" {{ $includeVal ? 'checked' : '' }}>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="{{ $priorityName }}" min="0.1" max="1.0" step="0.1" class="form-control" value="{{ $priorityVal }}">
                                                    </td>
                                                    <td>
                                                        <select name="{{ $freqName }}" class="form-control">
                                                            @foreach ($freqOptions as $optVal => $optLabel)
                                                                <option value="{{ $optVal }}" {{ $freqVal === $optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">Products per sitemap file</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label" for="products_per_sitemap">Number of products per sitemap file</label>

                                    <div class="col-md-5">
                                        @php
                                            $productsPer = (int) setting('support.sitemap.products_per_sitemap', 10000);
                                        @endphp
                                        <input type="number" name="products_per_sitemap" id="products_per_sitemap" class="form-control" min="100" max="50000" value="{{ $productsPer }}">
                                        <span class="help-block">Leave empty to use default (10000). Min 100, max 50000.</span>
                                    </div>
                                </div>

                                <hr>

                                <h4 class="tab-content-title">Cronjob settings</h4>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Artisan command</label>
                                    <div class="col-md-9">
                                        <code>php {{ base_path('artisan') }} sitemap:generate</code>
                                        <p class="help-block">You can add this to your server cron to regenerate sitemaps automatically.</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Cron example</label>
                                    <div class="col-md-9">
                                        <code>0 3 * * * php {{ base_path('artisan') }} sitemap:generate &gt; /dev/null 2&gt;&amp;1</code>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label" for="cron_token">HTTP cron token (optional)</label>
                                    <div class="col-md-5">
                                        @php
                                            $cronToken = (string) setting('support.sitemap.cron_token', '');
                                        @endphp
                                        <input type="text" name="cron_token" id="cron_token" class="form-control" value="{{ $cronToken }}">
                                        <span class="help-block">If set, you can trigger sitemap generation via a secure URL.</span>
                                    </div>
                                </div>

                                @if (!empty($cronToken))
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">HTTP cron URL</label>
                                        <div class="col-md-9">
                                            <code>{{ url('sitemap/cron/' . $cronToken) }}</code>
                                        </div>
                                    </div>
                                @else
                                    <div class="form-group">
                                        <div class="col-md-9 col-md-offset-3">
                                            <p class="help-block">Set a cron token and save to enable HTTP-based sitemap generation.</p>
                                        </div>
                                    </div>
                                @endif

                                <hr>

                                <h4 class="tab-content-title">{{ trans('support::sitemap.generate_sitemap') }}</h4>

                                <div class="row btn-generate-sitemap">
                                    <div class="form-group">
                                        <div class="col-md-9 col-md-offset-3">
                                            <div class="btn-toolbar" role="toolbar">
                                                <div class="btn-group" role="group">
                                                    <button type="submit" class="btn btn-primary" data-loading>
                                                        {{ trans('support::sitemap.generate') }}
                                                    </button>
                                                </div>

                                                <div class="btn-group" role="group" style="margin-left: 10px;">
                                                    <a href="{{ url('sitemap.xml') }}" target="_blank" class="btn btn-default">
                                                        Sitemap'i Aç
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

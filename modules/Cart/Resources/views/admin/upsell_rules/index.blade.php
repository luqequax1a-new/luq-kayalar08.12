@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', '')

    <li class="active">{{ trans('cart::upsell.admin_title') }}</li>
@endcomponent

@section('content')
    <div class="box box-primary">
        <div class="box-header with-border d-flex justify-content-between align-items-center">
            <h3 class="box-title">{{ trans('cart::upsell.admin_title') }}</h3>

            <a href="{{ route('admin.cart_upsell_rules.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i>
                {{ trans('admin::resource.create', ['resource' => trans('cart::upsell.admin_title')]) }}
            </a>
        </div>

        <div class="box-body no-padding">
            <table class="table table-striped table-hover upsell-rules-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th style="width: 70px;">Küçük Görsel</th>
                        <th>{{ trans('cart::upsell.table.upsell_product') }}</th>
                        <th style="width: 140px;">{{ trans('cart::upsell.table.discount') }}</th>
                        <th style="width: 180px;">Tarih Aralığı</th>
                        <th style="width: 90px;">{{ trans('cart::upsell.table.status') }}</th>
                        <th style="width: 80px;">{{ trans('cart::upsell.table.sort_order') }}</th>
                        <th class="text-right" style="width: 90px;">{{ trans('admin::admin.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rules as $rule)
                        <tr>
                            <td>{{ $rule->id }}</td>
                            @php
                                $product = $rule->upsellProduct;
                                $thumbUrl = null;
                                if ($product && $product->base_image) {
                                    $baseImage = $product->base_image;
                                    if (is_array($baseImage)) {
                                        $thumbUrl = $baseImage['thumb'] ?? $baseImage['path'] ?? null;
                                    } else {
                                        $thumbUrl = $baseImage->thumb ?? $baseImage->path ?? null;
                                    }
                                }
                            @endphp
                            <td class="upsell-thumb-cell">
                                @if ($thumbUrl)
                                    <div style="display:flex; align-items:center; justify-content:center;">
                                        <img src="{{ $thumbUrl }}" alt="" style="width:56px; height:56px; object-fit:cover; border-radius:8px;">
                                    </div>
                                @endif
                            </td>
                            <td class="upsell-name-cell">
                                <div class="upsell-name-main">{{ optional($product)->name ?? '—' }}</div>
                                @if ($rule->mainProduct)
                                    <div class="upsell-name-sub">Ana Ürün: {{ $rule->mainProduct->name }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($rule->discount_type === 'percent')
                                    {{ (float) $rule->discount_value }}%
                                @elseif ($rule->discount_type === 'fixed')
                                    {{ format_price((float) $rule->discount_value) }}
                                @else
                                    İndirim yok
                                @endif
                            </td>
                            <td>
                                @php
                                    $start = $rule->starts_at ? $rule->starts_at->format('Y-m-d') : null;
                                    $end = $rule->ends_at ? $rule->ends_at->format('Y-m-d') : null;
                                @endphp
                                @if (!$start && !$end)
                                    <span class="text-muted">Süresiz</span>
                                @else
                                    <div>
                                        {{ $start ?? '—' }} &rarr; {{ $end ?? '—' }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if ($rule->status)
                                    <span class="label label-success">{{ trans('admin::admin.table.enabled') }}</span>
                                @else
                                    <span class="label label-default">{{ trans('admin::admin.table.disabled') }}</span>
                                @endif
                            </td>
                            <td>{{ $rule->sort_order }}</td>
                            <td class="text-right upsell-actions-cell">
                                <div class="actions-grid" aria-label="İşlemler">
                                    <a href="{{ route('admin.cart_upsell_rules.edit', $rule) }}"
                                       class="action-edit"
                                       title="Düzenle"
                                       data-toggle="tooltip">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                                            <path d="M4 20H20" stroke="#292D32" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M16.44 3.56006L20.44 7.56006" stroke="#292D32" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M14.02 5.98999L6.91 13.1C6.52 13.49 6.15 14.25 6.07 14.81L5.64 17.83C5.45 19.08 6.42 20.04 7.67 19.86L10.69 19.43C11.25 19.35 12.01 18.98 12.41 18.59L19.52 11.48" stroke="#292D32" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </a>

                                    <form method="POST" action="{{ route('admin.cart_upsell_rules.destroy', $rule) }}" class="inline-block" onsubmit="return confirm('{{ trans('admin::messages.confirm_delete') }}')">
                                        {{ csrf_field() }}
                                        {{ method_field('delete') }}

                                        <button type="submit" class="action-delete" title="Sil" data-toggle="tooltip">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                                                <path d="M9 3H15" stroke="#292D32" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M4 7H20" stroke="#292D32" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M7 7L7.5 19C7.5 20.1046 8.39543 21 9.5 21H14.5C15.6046 21 16.5 20.1046 16.5 19L17 7" stroke="#292D32" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">{{ trans('admin::admin.table.no_data_available_table') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="box-footer clearfix">
            {{ $rules->links() }}
        </div>
    </div>
@endsection

@push('styles')
<style>
    .upsell-rules-table > tbody > tr > td {
        vertical-align: middle;
        padding-top: 10px;
        padding-bottom: 10px;
    }

    .upsell-thumb-cell {
        width: 96px;
    }

    .upsell-name-cell {
        padding-left: 35px;
    }

    .upsell-name-main {
        font-weight: 500;
        line-height: 1.3;
    }

    .upsell-name-sub {
        font-size: 11px;
        color: #777;
        line-height: 1.3;
        margin-top: 2px;
    }

    .upsell-actions-cell {
        width: 90px;
    }

    .actions-grid {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .actions-grid a,
    .actions-grid button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: transparent;
        padding: 0;
        cursor: pointer;
        color: #6b7280;
    }

    .actions-grid a:hover,
    .actions-grid button:hover {
        color: #111827;
    }
</style>
@endpush

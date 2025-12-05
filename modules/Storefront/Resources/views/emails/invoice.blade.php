<!DOCTYPE html>
<html lang="en"
      style="-ms-text-size-adjust: 100%;
             -webkit-text-size-adjust: 100%;
             -webkit-print-color-adjust: exact;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', Arial, sans-serif;
            font-size: 15px;
            color: #4b5563;
            background: #f3f4f6;
        }

        td {
            vertical-align: top;
        }

        .main-wrapper {
            border-collapse: collapse;
            min-width: 320px;
            width: 100%;
            margin: 0;
        }

        .main-container {
            border-collapse: collapse;
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px 12px 0 0;
            overflow: hidden;
            border-bottom: 2px solid {{ mail_theme_color() }};
        }

        .section {
            padding: 20px 18px;
        }

        .section + .section {
            border-top: 1px solid #f1f5f9;
        }

        .section-title {
            font-family: 'Poppins', Arial, sans-serif;
            font-weight: 700;
            font-size: 18px;
            line-height: 22px;
            margin: 0 0 8px;
            color: #111827;
        }

        .address-column {
            width: 50%;
        }

        .address-block {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 10px 12px;
            background: #ffffff;
        }

        .address-block span {
            display: block;
            padding: 2px 0;
        }

        .summary-table {
            border-collapse: collapse;
            width: 100%;
        }

        .summary-table td {
            font-size: 15px;
            padding: 5px 0;
        }

        .summary-total-row td {
            border-top: 1px solid #e5e7eb;
            font-weight: 700;
            padding-top: 8px;
        }

        @media screen and (max-width: 767px) {
            .address-column {
                width: 100% !important;
                display: block;
            }

            .address-column + .address-column {
                margin-top: 12px;
            }

            .section {
                padding: 16px 14px;
            }

            .summary-table td {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
<table class="main-wrapper">
    <tbody>
    <tr>
        <td style="padding: 16px 8px;">
            <span style="display:none;color:transparent;visibility:hidden;opacity:0;height:0;width:0;">
                Siparişiniz başarıyla oluşturuldu – Toplam: {{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}
            </span>

            <!-- ANA KART -->
            <table class="main-container">
                <tbody>

                <!-- HEADER -->
                <tr>
                    <td style="padding: 0;">
                        <table style="border-collapse: collapse;width: 100%;background: {{ mail_theme_color() }};">
                            <tbody>
                            <tr>
                                <td style="padding: 20px 15px 12px; text-align: center;">
                                    @if (is_null($logo))
                                        <h1
                                            style="font-family: 'Poppins', Arial, sans-serif;
                                                   font-weight: 700;
                                                   font-size: 26px;
                                                   line-height: 32px;
                                                   display: inline-block;
                                                   color: #fafafa;
                                                   margin: 0;">
                                            {{ setting('store_name') }}
                                        </h1>
                                    @else
                                        <div
                                            style="display: flex;
                                                   align-items: center;
                                                   justify-content: center;
                                                   height: 64px;
                                                   width: 200px;
                                                   margin: auto;">
                                            <img src="{{ $logo }}" style="max-height: 100%; max-width: 100%;" alt="Logo">
                                        </div>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 0 15px 18px; text-align: center;">
                                    <span
                                        style="font-family: 'Poppins', Arial, sans-serif;
                                               font-size: 22px;
                                               line-height: 30px;
                                               font-weight: 700;
                                               display: inline-block;
                                               color: #fafafa;
                                               margin: 0;">
                                        Siparişiniz Başarıyla Oluşturuldu 🎉
                                    </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <!-- KARŞILAMA -->
                <tr>
                    <td class="section" style="text-align:center;">
                        @php
                            $greetName = trim(($order->customer_first_name ?? '') . ' ' . ($order->customer_last_name ?? ''));
                        @endphp
                        @if ($greetName !== '')
                            <div style="font-size:17px;color:#ef4444;">
                                Merhaba {{ $greetName }},
                            </div>
                        @endif
                        <div style="font-size:15px;color:#ef4444;margin-top:4px;">
                            🎁 Siparişiniz başarıyla oluşturuldu! Hazırlıklara hemen başlıyoruz.
                        </div>
                        <div style="font-size:14px;color:#6b7280;margin-top:8px;">
                            Aşağıda siparişinize ait detayları bulabilirsiniz. Herhangi bir sorunuz olursa bizimle iletişime geçmekten çekinmeyin.
                        </div>
                    </td>
                </tr>

                <!-- SİPARİŞ ÖZETİ (MINIMAL) -->
                <tr>
                    <td class="section">
                        <h5 class="section-title">Sipariş Özeti</h5>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                               style="border:1px solid #e5e7eb;border-radius:12px;background:#ffffff;">
                            <tr>
                                <td style="padding:12px 14px;">
                                    <p style="margin:2px 0;font-size:14px;color:#111827;">
                                        <strong>{{ trans('storefront::invoice.order_id') }}:</strong>
                                        &nbsp;#{{ $order->id }}
                                    </p>
                                    <p style="margin:2px 0;font-size:14px;color:#111827;">
                                        <strong>{{ trans('storefront::invoice.date') }}:</strong>
                                        &nbsp;{{ $order->created_at->toFormattedDateString() }}
                                    </p>
                                    <p style="margin:2px 0;font-size:14px;color:#111827;">
                                        <strong>{{ trans('storefront::invoice.total') }}:</strong>
                                        &nbsp;{{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                                    </p>
                                    <p style="margin:2px 0;font-size:14px;color:#111827;word-break:break-all;">
                                        <strong>{{ trans('storefront::invoice.email') }}:</strong>
                                        &nbsp;{{ $order->customer_email }}
                                    </p>
                                    <p style="margin:2px 0;font-size:14px;color:#111827;word-break:break-all;">
                                        <strong>{{ trans('storefront::invoice.phone') }}:</strong>
                                        &nbsp;{{ $order->customer_phone }}
                                    </p>
                                    <p style="margin:4px 0 0;font-size:14px;color:#111827;">
                                        <strong>{{ trans('storefront::invoice.payment_method') }}:</strong>
                                        &nbsp;{{ $order->payment_method }}
                                    </p>

                                    @if ($order->payment_method === 'Bank Transfer')
                                        <span style="color:#9ca3af;font-size:12px;margin-top:4px;display:block;">
                                            {!! setting('bank_transfer_instructions') !!}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- ADRESLER -->
                <tr>
                    <td class="section">
                        <table style="border-collapse: collapse; width: 100%;">
                            <tbody>
                            <tr>
                                <!-- KARGO -->
                                <td class="address-column" style="padding-right:10px;">
                                    <h5 class="section-title" style="margin-bottom:8px; text-align:center;">🚚 {{ trans('storefront::invoice.shipping_address') }}</h5>
                                    <div class="address-block" style="font-size:13px; text-align:center;">
                                        @if ($order->shippingAddress)
                                            <span>{{ $order->shippingAddress->first_name }} {{ $order->shippingAddress->last_name }}</span>
                                            <span>{{ $order->shippingAddress->phone }}</span>
                                            <span>{{ $order->shippingAddress->address_line ?? $order->shippingAddress->address_1 }}</span>

                                            @if ($order->shippingAddress && ($order->shippingAddress->district_title || $order->shippingAddress->city_title))
                                                <span>
                                                    {{ $order->shippingAddress->district_title }}
                                                    @if ($order->shippingAddress->district_title && $order->shippingAddress->city_title)
                                                        ,
                                                    @endif
                                                    {{ $order->shippingAddress->city_title }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>

                                <!-- FATURA -->
                                <td class="address-column" style="padding-left:10px;">
                                    <h5 class="section-title" style="margin-bottom:8px; text-align:center;">📄 {{ trans('storefront::invoice.billing_address') }}</h5>
                                    <div class="address-block" style="font-size:13px; text-align:center;">
                                        @if ($order->billingAddress && $order->billing_address_id !== $order->shipping_address_id)
                                            @if ($order->billingAddress->company_name)
                                                <span>{{ $order->billingAddress->company_name }}</span>
                                            @endif

                                            @if ($order->billingAddress->tax_office || $order->billingAddress->tax_number)
                                                <span>Vergi Dairesi: {{ $order->billingAddress->tax_office }}</span>
                                                <span>Vergi No: {{ $order->billingAddress->tax_number }}</span>
                                            @endif

                                            <span>{{ $order->billingAddress->phone }}</span>
                                            <span>{{ $order->billingAddress->address_line ?? $order->billingAddress->address_1 }}</span>

                                            @if ($order->billingAddress && ($order->billingAddress->district_title || $order->billingAddress->city_title))
                                                <span>
                                                    {{ $order->billingAddress->district_title }}
                                                    @if ($order->billingAddress->district_title && $order->billingAddress->city_title)
                                                        ,
                                                    @endif
                                                    {{ $order->billingAddress->city_title }}
                                                </span>
                                            @endif
                                        @elseif ($order->shippingAddress)
                                            <span>{{ $order->shippingAddress->first_name }} {{ $order->shippingAddress->last_name }}</span>
                                            <span>{{ $order->shippingAddress->phone }}</span>
                                            <span>{{ $order->shippingAddress->address_line ?? $order->shippingAddress->address_1 }}</span>

                                            @if ($order->shippingAddress && ($order->shippingAddress->district_title || $order->shippingAddress->city_title))
                                                <span>
                                                    {{ $order->shippingAddress->district_title }}
                                                    @if ($order->shippingAddress->district_title && $order->shippingAddress->city_title)
                                                        ,
                                                    @endif
                                                    {{ $order->shippingAddress->city_title }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <!-- ÜRÜNLER -->
                <tr>
                    <td class="section">
                        <h5 class="section-title" style="text-align:center;">🛒 Sipariş Verilen Ürünler</h5>

                        @foreach ($order->products as $product)
                            @php
                                $imagePath = $product->product_variant?->base_image?->path
                                    ?? $product->product?->base_image?->path
                                    ?? $product->product_image_path;

                                $attributes = [];

                                if ($product->hasAnyVariation()) {
                                    foreach ($product->variations as $variation) {
                                        $label = $variation->values()->first()?->label;
                                        if ($label) {
                                            $attributes[] = $variation->name . ':' . $label;
                                        }
                                    }
                                }

                                if ($product->hasAnyOption()) {
                                    foreach ($product->options as $option) {
                                        if ($option->option->isFieldType()) {
                                            $val = $option->value;
                                        } else {
                                            $val = $option->values->implode('label', ', ');
                                        }
                                        if ($val) {
                                            $attributes[] = $option->name . ':' . $val;
                                        }
                                    }
                                }

                                $attributesText = implode(' • ', $attributes);
                            @endphp

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                   style="border:1px solid #e5e7eb;border-radius:12px;margin:8px 0;background:#ffffff;">
                                <tr>
                                    <td width="110" valign="top" style="padding:10px;">
                                        @if ($imagePath)
                                            <img src="{{ $imagePath }}" width="90" height="90" alt="{{ $product->name }}"
                                                 style="display:block;border-radius:10px;border:1px solid #e5e7eb;object-fit:cover;">
                                        @endif
                                    </td>

                                    <td valign="top" style="padding:10px 10px 10px 0; width:100%;">
                                        <div style="font-size:14px;font-weight:700;color:#0f172a;line-height:1.35;">
                                            {{ $product->name }}
                                        </div>

                                        @if ($attributesText)
                                            <div style="font-size:13px;color:#4b5563;margin-top:4px;">
                                                {{ $attributesText }}
                                            </div>
                                        @endif

                                        @if ($product->sku)
                                            <div style="font-size:13px;color:#4b5563;margin-top:2px;">
                                                <strong>Stok Kodu:</strong> {{ $product->sku }}
                                            </div>
                                        @endif

                                        @if ($product->unit_price)
                                            <div style="font-size:13px;color:#0f172a;margin-top:6px;">
                                                {{ $product->unit_price->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                                                × {{ $product->getFormattedQuantityWithUnit() }}
                                                = <span style="font-weight:700;color:#16a34a;">
                                                    {{ $product->line_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                                                  </span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        @endforeach
                    </td>
                </tr>

                <!-- ÖDEME ÖZETİ -->
                @php
                    $isCodOrder = $order->isCodPayment();
                    $codFeeForOrder = null;

                    if ($isCodOrder) {
                        $codFee = \Modules\Shipping\SmartShippingCod::codFeeForSubtotal($order->sub_total);

                        if (!$codFee->isZero()) {
                            $codFeeForOrder = $codFee->convert($order->currency, $order->currency_rate);
                        }
                    }
                @endphp

                <tr>
                    <td class="section">
                        <h3 style="font-size:18px;font-weight:700;color:#0f172a;margin:0 0 10px;text-align:center;">
                            💳 Ödeme Özeti
                        </h3>
                        <table class="summary-table" style="border:1px solid #e5e7eb;border-radius:12px;width:100%;background:#ffffff;table-layout:fixed;">
                            <tbody>
                            <tr>
                                <td style="padding:8px 12px;font-size:15px;color:#334155;">Ürün Toplamı</td>
                                <td style="padding:8px 12px;font-size:15px;color:#334155;text-align:right;word-break:break-word;">
                                    {{ $order->sub_total->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 12px;font-size:15px;color:#334155;">Kargo</td>
                                <td style="padding:8px 12px;font-size:15px;color:#334155;text-align:right;word-break:break-word;">
                                    @if ($order->shipping_cost->amount() == 0)
                                        {{ trans('storefront::checkout.free') }}
                                    @else
                                        {{ $order->shipping_cost->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                                    @endif
                                </td>
                            </tr>
                            @if ($codFeeForOrder)
                            <tr>
                                <td style="padding:8px 12px;font-size:15px;color:#334155;">{{ trans('storefront::checkout.cod_fee') }}</td>
                                <td style="padding:8px 12px;font-size:15px;color:#334155;text-align:right;word-break:break-word;">
                                    {{ $codFeeForOrder->format($order->currency) }}
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td style="padding:8px 12px;font-size:15px;color:#ef4444;">İndirim</td>
                                <td style="padding:8px 12px;font-size:15px;color:#ef4444;text-align:right;word-break:break-word;">
                                    -{{ $order->discount->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:10px 12px;font-size:16px;font-weight:700;color:#0f172a;border-top:1px solid #e5e7eb;">
                                    Genel Toplam
                                </td>
                                <td style="padding:10px 12px;font-size:16px;font-weight:800;color:#16a34a;text-align:right;word-break:break-word;border-top:1px solid #e5e7eb;">
                                    {{ $order->total->convert($order->currency, $order->currency_rate)->format($order->currency) }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

                </tbody>
            </table>

            <!-- FOOTER -->
            <table style="border-collapse: collapse; max-width: 600px; width: 100%; margin: 0 auto;">
                <tbody>
                <tr>
                    <td style="padding: 20px 0; background: {{ mail_theme_color() }}; text-align: center; border-radius: 0 0 12px 12px; color:#ffffff;">
                        <div style="font-family: 'Poppins', Arial, sans-serif; font-weight: 700; font-size: 18px; line-height: 22px; color: #ffffff; padding: 0 15px;">
                            {{ setting('store_name') }}
                        </div>
                        <div style="font-family: 'Poppins', Arial, sans-serif; font-weight: 400; font-size: 14px; line-height: 20px; color: #e5e7eb; padding: 6px 15px;">
                            @if (setting('store_phone') && ! setting('store_phone_hide'))
                                <a href="tel:{{ setting('store_phone') }}" style="text-decoration: none; color: #ffffff;">{{ setting('store_phone') }}</a>
                            @endif
                            @if (setting('store_email') && ! setting('store_email_hide'))
                                <span style="margin: 0 6px; color:#cbd5e1;">•</span>
                                <a href="mailto:{{ setting('store_email') }}" style="text-decoration: none; color: #ffffff;">{{ setting('store_email') }}</a>
                            @endif
                            @if (setting('storefront_address'))
                                <span style="margin: 0 6px; color:#cbd5e1;">•</span>
                                <span style="color:#ffffff;">{{ setting('storefront_address') }}</span>
                            @endif
                        </div>
                        <div style="font-family: 'Poppins', Arial, sans-serif; font-weight: 500; font-size: 14px; line-height: 20px; color: #ffffff; padding: 6px 15px;">
                            <a href="{{ route('categories.index') }}" style="text-decoration: none; color: #ffffff;">{{ trans('storefront::layouts.categories') }}</a>
                            <span style="margin: 0 10px; color:#cbd5e1;">•</span>
                            <a href="{{ route('home') }}#flash-sale" style="text-decoration: none; color: #ffffff;">İndirimli Ürünler</a>
                            <span style="margin: 0 10px; color:#cbd5e1;">•</span>
                            <a href="{{ route('account.dashboard.index') }}" style="text-decoration: none; color: #ffffff;">{{ trans('storefront::layouts.my_account') }}</a>
                            <span style="margin: 0 10px; color:#cbd5e1;">•</span>
                            <a href="{{ route('register') }}" style="text-decoration: none; color: #ffffff;">{{ trans('storefront::layouts.login_register') }}</a>
                        </div>
                        <div style="font-family: 'Poppins', Arial, sans-serif; font-weight: 400; font-size: 13px; line-height: 18px; color: #e5e7eb; padding: 0 15px;">
                            &copy; {{ date('Y') }}
                            <a target="_blank" href="{{ route('home') }}" style="text-decoration: none; color: #ffffff;">{{ setting('store_name') }}</a>
                            {{ trans('storefront::mail.all_rights_reserved') }}
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>

        </td>
    </tr>
    </tbody>
</table>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('store_name') }}</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">
</head>
<body style="margin:0;padding:0;background:#f5f7fb;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fb;">
    <tr>
        <td align="center" style="padding:20px;">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;">
                <tr>
                    <td style="padding:20px;text-align:center;background:#0f172a;">
                        @if (! empty($logo))
                            <img src="{{ $logo }}" alt="{{ setting('store_name') }}" style="height:40px;">
                        @endif
                    </td>
                </tr>

                <tr>
                    <td style="padding:20px;">
                        <h2 style="font-family:'Open Sans',sans-serif;font-size:18px;color:#0f172a;margin:0 0 10px;">
                            {{ $title }}
                        </h2>

                        <p style="font-family:'Open Sans',sans-serif;font-size:14px;color:#334155;margin:0 0 10px;">
                            Merhaba {{ $order->customer_first_name }} {{ $order->customer_last_name }},
                        </p>

                        <p style="font-family:'Open Sans',sans-serif;font-size:14px;color:#334155;margin:0 0 10px;">
                            Bir süre önce siparişinizle ilgili bir değerlendirme daveti göndermiştik. Yoğunlukta gözünüzden kaçmış olabilir diye nazikçe tekrar hatırlatmak istedik. 😊
                        </p>

                        <p style="font-family:'Open Sans',sans-serif;font-size:14px;color:#0f172a;margin:0 0 15px;font-weight:600;">
                            Deneyiminiz hem bizim için hem de diğer müşterilerimiz için çok değerli.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:0 20px 20px;">
                        @php $firstProduct = $order->products->first(); @endphp
                        @if ($firstProduct && $firstProduct->product)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;border:1px solid #e2e8f0;border-radius:8px;">
                                <tr>
                                    <td width="90" style="padding:10px;">
                                        @if ($firstProduct->product->base_image)
                                            <img src="{{ $firstProduct->product->base_image->path }}" alt="{{ $firstProduct->product->name }}" style="width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #e2e8f0;display:block;">
                                        @endif
                                    </td>
                                    <td style="padding:10px 10px 10px 0;">
                                        <div style="font-family:'Open Sans',sans-serif;font-size:14px;color:#0f172a;font-weight:600;margin-bottom:8px;">
                                            {{ $firstProduct->product->name }}
                                        </div>

                                        <a href="{{ $firstProduct->product->url() }}#reviews?order_id={{ $order->id }}"
                                           style="display:inline-block;padding:8px 14px;border-radius:4px;background:{{ mail_theme_color() }};color:#ffffff;text-decoration:none;font-family:'Open Sans',sans-serif;font-size:13px;">
                                            Ürünü değerlendirin
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </td>
                </tr>

                <tr>
                    <td style="padding:20px;text-align:center;background:#0f172a;color:#ffffff;font-family:'Open Sans',sans-serif;font-size:12px;">
                        {{ setting('store_phone_hide') ? '' : setting('store_phone') }}
                        @if (! setting('store_phone_hide') && ! setting('store_email_hide')) · @endif
                        {{ setting('store_email_hide') ? '' : setting('store_email') }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>


 <!DOCTYPE html>
<html lang="{{ locale() }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400" rel="stylesheet">
        <style>
            @media screen and (max-width: 600px) {
                .product-list .product-item { display: block !important; }
                .product-list .product-item img { margin-bottom: 8px; }
            }
        </style>
    </head>

    <body style="font-family: 'Open Sans', sans-serif; font-size: 15px; min-width: 320px; margin: 0;">
        <table style="border-collapse: collapse; width: 100%;">
            <tbody>
                <tr>
                    <td style="padding: 0;">
                        <table style="border-collapse: collapse; width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="background: {{ mail_theme_color() }}; text-align: center;">
                                        @if (is_null($logo))
                                            <h5 style="font-size: 30px; line-height: 36px; margin: 0; padding: 30px 15px; text-align: center;">
                                                <a href="{{ route('home') }}" style="font-family: 'Open Sans', sans-serif; font-weight: 400; color: #ffffff; text-decoration: none;">
                                                    {{ setting('store_name') }}
                                                </a>
                                            </h5>
                                        @else
                                            <div style="display: flex; height: 64px; width: 200px; align-items: center; justify-content: center; margin: auto; padding: 16px 15px;">
                                                <img src="{{ $logo }}" style="max-height: 100%; max-width: 100%;" alt="Logo">
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 40px 15px;">
                        <table style="border-collapse: collapse; min-width: 320px; max-width: 700px; width: 100%; margin: auto;">
                            <tr>
                                <td style="padding: 0;">
                                    <h4 style="font-family: 'Open Sans', sans-serif; font-weight: 400; font-size: 21px; line-height: 26px; margin: 0 0 10px; color: #555555;">
                                        {{ trans('storefront::mail.hello', ['name' => $order->customer_first_name]) }}
                                    </h4>
                                    <h5 style="font-family: 'Open Sans', sans-serif; font-weight: 600; font-size: 18px; line-height: 24px; margin: 0 0 10px; color: #0f172a;">
                                        {{ $title ?? trans('storefront::product.add_a_review') }}
                                    </h5>
                                    <p style="font-family: 'Open Sans', sans-serif; font-size: 15px; line-height: 24px; color: #334155; margin: 0 0 8px;">
                                        {{ $intro ?? 'Siparişiniz elinize ulaştı. Deneyiminizi bizimle ve diğer müşterilerimizle paylaşır mısınız?' }}
                                    </p>
                                    <p style="font-family: 'Open Sans', sans-serif; font-size: 15px; line-height: 24px; color: #0f172a; margin: 0 0 12px; font-weight: 600;">
                                        {{ $promo ?? 'Siparişiniz hakkında değerlendirme yapmayı unutmayın. Yorum bırakan müşterilerimize bir sonraki alışverişlerinde kullanmaları için özel indirim kuponu tanımlıyoruz.' }}
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 18px 0;">
                                    <table class="product-list" role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%;">
                                        @foreach ($order->products as $product)
                                            @php
                                                $imagePath = $product->product_variant?->base_image?->path
                                                    ?? $product->product?->base_image?->path
                                                    ?? $product->product_image_path;
                                            @endphp
                                            <tr>
                                                <td class="product-item" style="padding: 10px; border:1px solid #e5e7eb; border-radius:12px; background:#ffffff;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td width="84" valign="top" style="padding-right: 10px;">
                                                                @if ($imagePath)
                                                                    <img src="{{ $imagePath }}" width="64" height="64" alt="{{ $product->name }}" style="display:block;border-radius:10px;border:1px solid #e5e7eb;object-fit:cover;">
                                                                @endif
                                                            </td>
                                                            <td valign="top" style="padding-right: 10px;">
                                                                <div style="font-size:14px;font-weight:800;color:#0f172a;line-height:1.3;">{{ $product->name }}</div>
                                                                @if ($product->sku)
                                                                    <div style="font-size:12px;color:#64748b;margin-top:2px;">Kod: {{ $product->sku }}</div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 0;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-top: 10px;">
                                        <tr>
                                            <td style="padding: 14px; border: 1px solid #e5e7eb; border-radius: 12px; background: #f9fafb;">
                                                <div style="font-size: 14px; line-height: 22px; color: #374151;">
                                                    <div style="margin-bottom:12px;color:#111827;font-weight:600;">Yorumunuz neden önemli?</div>
                                                    <div style="margin:6px 0;color:#334155;">• Ürünlerimizin gelişimine katkı sağlarsınız.</div>
                                                    <div style="margin:6px 0;color:#334155;">• Diğer müşterilere karar verirken rehberlik edersiniz.</div>
                                                    <div style="margin:6px 0;color:#334155;">• Topluluğumuzun güvenilirliğine katkıda bulunursunuz.</div>
                                                    <div style="margin-top:12px;color:#111827;font-weight:600;">Kupon avantajınız</div>
                                                    <div style="margin:6px 0;color:#334155;">• Yorum bırakan müşterilerimize %{{ setting('review_coupon_discount_percent', 10) }} indirim tanımlanır.</div>
                                                    <div style="margin:6px 0;color:#334155;">• Kupon süresi {{ setting('review_coupon_valid_days', 30) }} gün geçerlidir.</div>
                                                    <div style="margin-top:12px;color:#111827;font-weight:600;">Nasıl yorum yaparım?</div>
                                                    <div style="margin:6px 0;color:#334155;">• Ürün sayfasındaki “Yorumlar” bölümünden değerlendirme bırakabilirsiniz.</div>
                                                    <div style="margin:6px 0;color:#334155;">• Aşağıdaki butona tıklayarak hesabınızdaki yorumlar sayfasına gidebilirsiniz.</div>

                                                    @php
                                                        $firstProduct = $order->products->first();
                                                        $productUrl = $firstProduct ? $firstProduct->url() : null;
                                                        $targetReviewUrl = ($productUrl && $productUrl !== '#')
                                                            ? ($productUrl . '?order_id=' . $order->id . '#reviews')
                                                            : route('home');
                                                    @endphp
                                                    <div style="margin:14px 0 20px;">
                                                        <a href="{{ $targetReviewUrl }}" style="font-family: 'Open Sans', sans-serif; font-weight: 600; text-decoration: none; display: inline-block; background: {{ mail_theme_color() }}; color: #fafafa; padding: 10px 18px; border: none; border-radius: 4px; outline: 0;">
                                                            Ürünlerimi Değerlendir
                                                        </a>
                                                    </div>
                                                    
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 20px 15px; background: #0f172a; text-align: center;">
                        <div style="font-family: 'Open Sans', sans-serif; font-size: 14px; color: #ffffff;">
                            <div style="margin-bottom:8px;">
                                <a target="_blank" href="{{ route('home') }}" style="text-decoration: none; color: #ffffff;">{{ setting('store_name') }}</a>
                            </div>
                            @if (setting('store_phone') && ! setting('store_phone_hide'))
                                <div><a href="tel:{{ setting('store_phone') }}" style="text-decoration: none; color: #ffffff;">{{ setting('store_phone') }}</a></div>
                            @endif
                            @if (setting('store_email') && ! setting('store_email_hide'))
                                <div><a href="mailto:{{ setting('store_email') }}" style="text-decoration: none; color: #ffffff;">{{ setting('store_email') }}</a></div>
                            @endif
                            <div style="margin-top:10px; opacity:0.8;">
                                &copy; {{ date('Y') }} {{ trans('storefront::mail.all_rights_reserved') }}
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ setting('store_name') }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;">
        <tr>
            <td align="center" style="padding:24px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background: {{ setting('storefront_mail_theme_color', '#111827') }}; padding: 20px; text-align:center;">
                            @if ($logo)
                                <img src="{{ $logo }}" alt="{{ setting('store_name') }}" style="height:40px;display:inline-block;vertical-align:middle;" />
                            @endif
                            <div style="font-family:'Open Sans',sans-serif;font-size:24px;line-height:32px;font-weight:800;color:#fafafa;margin-top:10px;">İadeniz Tamamlandı 💸</div>
                            
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 20px 8px;">
                            <div style="font-family:'Open Sans',sans-serif;font-size:15px;line-height:20px;color:#374151;text-align:center;">
                                Siparişiniz (#{{ $order->id }}) için iade işlemi tamamlandı.<br/>
                                Ödeme yöntemi: <strong>{{ $order->payment_method }}</strong>. İade, bu yöntem üzerinden gerçekleştirildi.
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 20px 12px;">
                            <div style="font-family:'Open Sans',sans-serif;font-size:14px;line-height:20px;color:#111827;text-align:center;">
                                <div style="margin:4px 0;">Sipariş No: #{{ $order->id }}</div>
                                <div style="margin:4px 0;">Tarih: {{ $order->created_at->toFormattedDateString() }}</div>
                                <div style="margin:4px 0;">Ödeme: {{ $order->payment_method }}</div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:12px 20px 20px;">
                            <div style="font-family:'Open Sans',sans-serif;font-size:14px;line-height:20px;color:#374151;text-align:center;">
                                Sizi tekrar görmek isteriz. Keyifli alışverişler!
                            </div>
                        </td>
                    </tr>
                </table>
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;margin-top:12px;background:#f9fafb;border-radius:12px;">
                    <tr>
                        <td style="padding:14px 20px;text-align:center;font-family:'Open Sans',sans-serif;font-size:13px;line-height:20px;color:#6b7280;">
                            Teşekkürler, {{ setting('store_name') }}
                            • <a href="{{ route('home') }}" style="color:#6b7280;text-decoration:underline;">Mağazayı ziyaret et</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

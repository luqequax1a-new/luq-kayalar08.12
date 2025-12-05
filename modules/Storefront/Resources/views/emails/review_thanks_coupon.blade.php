<!DOCTYPE html>
<html lang="{{ locale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('store_name') }}</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">
</head>
<body style="margin:0;padding:0;background:#0f172a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0f172a;">
    <tr>
        <td align="center" style="padding:0;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;background:#ffffff;">
                <tr>
                    <td style="padding:40px 15px;text-align:center;background:linear-gradient(135deg, {{ mail_theme_color() }}, #0ea5e9); color:#ffffff;">
                        <div style="font-family:'Open Sans',sans-serif;font-size:24px;line-height:32px;font-weight:700;">
                            ❤️ Yorumunuz için teşekkürler
                        </div>
                        <div style="font-family:'Open Sans',sans-serif;font-size:14px;opacity:.9;margin-top:6px;">
                            {{ setting('store_name') }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px 15px;background:#ffffff;text-align:center;">
                        <h2 style="font-family:'Open Sans',sans-serif;font-size:22px;color:#0f172a;margin:0 0 10px;">{{ $order->customer_first_name }} {{ $order->customer_last_name }}, indirim kuponunuz hazır!</h2>
                        <p style="font-family:'Open Sans',sans-serif;font-size:15px;color:#334155;margin:0 0 18px;">
                            Geri bildiriminiz bizim için çok değerli. Yorumlarınız ürün ve hizmetlerimizi geliştirmemize yardımcı oluyor.
                        </p>

                        <div style="padding:18px;border:1px dashed #cbd5e1;border-radius:12px;background:#f8fafc;margin-bottom:20px;max-width:560px;width:100%;margin-left:auto;margin-right:auto;text-align:center;">
                            <div style="display:inline-block;font-family:'Open Sans',sans-serif;font-size:12px;color:#075985;background:#e0f2fe;padding:8px 12px;border-radius:8px;margin-bottom:12px;font-weight:700;">Not: Kupon tek kullanımlıktır.</div>
                            <div style="font-family:'Open Sans',sans-serif;font-size:20px;color:#0f172a;font-weight:800;">Kupon</div>
                            <div style="margin-top:12px;padding:14px;border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;text-align:center;width:100%;max-width:520px;margin-left:auto;margin-right:auto;">
                                <div style="font-family:'Open Sans',sans-serif;font-size:28px;letter-spacing:2px;color:#0f172a;font-weight:800;">{{ $coupon->code }}</div>
                            </div>
                            <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin-top:12px;">
                                <tr>
                                    <td style="font-family:'Open Sans',sans-serif;font-size:14px;color:#0f172a;white-space:nowrap;">
                                        🔖 İndirim: <strong>%{{ (int) ($coupon->value) }}</strong>
                                    </td>
                                    <td style="width:16px;"></td>
                                    <td style="font-family:'Open Sans',sans-serif;font-size:14px;color:#0f172a;white-space:nowrap;">
                                        🗓️ Geçerlilik: {{ optional($coupon->end_date)->format('Y-m-d') }}
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <a href="{{ route('home') }}" style="display:inline-block;background:{{ mail_theme_color() }};color:#fff;text-decoration:none;padding:12px 22px;border-radius:6px;font-family:'Open Sans',sans-serif;font-weight:700;">
                            Hemen Kullan
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px;text-align:center;background:#0f172a;color:#fff;font-family:'Open Sans',sans-serif;font-size:12px;">
                        {{ setting('store_phone_hide') ? '' : setting('store_phone') }} · {{ setting('store_email_hide') ? '' : setting('store_email') }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

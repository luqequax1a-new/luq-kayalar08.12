<!DOCTYPE html>
<html lang="en" style="-ms-text-size-adjust: 100%;
                    -webkit-text-size-adjust: 100%;
                    -webkit-print-color-adjust: exact;"
>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body { margin:0; padding:0; font-family:'Poppins', Arial, sans-serif; font-size:16px; background:#f3f4f6; color:#374151; }
            .wrapper { width:100%; max-width:720px; margin:auto; background:#ffffff; border-radius:12px; overflow:hidden; }
            .section { padding:24px 16px; }
            .title { font-size:22px; font-weight:800; color:#111827; text-align:center; margin:0 0 8px; }
            .subtitle { font-size:14px; color:#6b7280; text-align:center; }
            .btn { display:inline-block; background:{{ mail_theme_color() }}; color:#fafafa; text-decoration:none; padding:12px 28px; border-radius:8px; font-weight:700; }
            .footer { background:#0f172a; color:#ffffff; text-align:center; padding:18px 16px; }
            .card { border:1px solid #e5e7eb; border-radius:12px; background:#ffffff; }
        </style>
    </head>

    <body dir="rtl">
        <table style="border-collapse: collapse; width: 100%;">
            <tbody>
                <tr>
                    <td style="padding: 0;">
                        <table style="border-collapse: collapse; width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="background: {{ mail_theme_color() }}; text-align: center;">
                                        @if (is_null($logo))
                                            <h5 style="font-size: 30px; line-height: 36px; margin: 0; padding: 24px 15px; text-align: center;">
                                                <a href="{{ route('home') }}" style="font-family:'Poppins', Arial, sans-serif; font-weight:700; color:#ffffff; text-decoration:none;">{{ setting('store_name') }}</a>
                                            </h5>
                                        @else
                                            <div style="display:flex; height:64px; width:200px; align-items:center; justify-content:center; margin:auto; padding:16px 15px;">
                                                <img src="{{ $logo }}" style="max-height:100%; max-width:100%;" alt="Logo">
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 24px 8px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" class="wrapper">
                            <tr>
                                <td class="section" style="padding-bottom:8px;">
                                    <div class="title">Şifrenizi Sıfırlayın</div>
                                    <div class="subtitle">Merhaba {{ $user->first_name }}, şifre sıfırlama talebiniz alındı.</div>
                                </td>
                            </tr>

                            <tr>
                                <td class="section" style="padding-top:0;">
                                    <span style="display:block; text-align:center; font-size:15px; color:#4b5563;">Butona tıklayarak yeni şifrenizi belirleyebilirsiniz.</span>
                                </td>
                            </tr>

                            <tr>
                                <td class="section" style="text-align:center;">
                                    <a href="{{ $url }}" class="btn">Şifremi Sıfırla</a>
                                </td>
                            </tr>

                            <tr>
                                <td class="section" style="padding-top:8px;">
                                    <div class="card" style="padding:14px;">
                                        <div style="font-size:14px; color:#374151; text-align:center;">Bağlantı çalışmıyorsa aşağıdaki URL’yi kopyalayıp tarayıcınıza yapıştırın.</div>
                                        <div style="text-align:center; margin-top:8px;">
                                            <a href="{{ $url }}" style="font-size:14px; color:#31629f; word-break:break-all;">{{ $url }}</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="section" style="padding-top:8px;">
                                    <div class="card" style="padding:14px; text-align:center;">
                                        <div style="font-size:14px; color:#374151; font-weight:700; margin-bottom:6px;">Yardım mı lazım?</div>
                                        @if (setting('store_phone') && ! setting('store_phone_hide'))
                                            <div style="margin:4px 0;"><a href="tel:{{ setting('store_phone') }}" style="text-decoration:none; color:#31629f;">{{ setting('store_phone') }}</a></div>
                                        @endif
                                        @if (setting('store_email') && ! setting('store_email_hide'))
                                            <div style="margin:4px 0;"><a href="mailto:{{ setting('store_email') }}" style="text-decoration:none; color:#31629f;">{{ setting('store_email') }}</a></div>
                                        @endif
                                        <div style="margin-top:6px;"><a href="{{ route('contact.create') }}" style="text-decoration:none; color:#31629f;">İletişim</a></div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 16px 0 0;">
                                    <span style="font-family: 'Open Sans', sans-serif;
                                                font-weight: 400;
                                                font-size: 15px;
                                                line-height: 24px;
                                                display: block;
                                                padding: 5px 0 10px;
                                                color: #666666;
                                                border-top: 1px solid #e9e9e9;"
                                    >
                                        {{ trans('user::mail.if_you\’re_having_trouble') }}
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 0;">
                                    <a href="{{ $url }}" style="font-family: 'Open Sans', sans-serif;
                                                                font-weight: 400;
                                                                font-size: 16px;
                                                                line-height: 26px;
                                                                text-decoration: underline;
                                                                color: #31629f;
                                                                word-break: break-all;"
                                    >
                                        {{ $url }}
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td class="footer">
                        <div style="font-family:'Poppins', Arial, sans-serif; font-size:16px; font-weight:800;">{{ setting('store_name') }}</div>
                        @if (setting('store_phone') && ! setting('store_phone_hide'))
                            <div><a href="tel:{{ setting('store_phone') }}" style="text-decoration:none; color:#ffffff;">{{ setting('store_phone') }}</a></div>
                        @endif
                        @if (setting('store_email') && ! setting('store_email_hide'))
                            <div><a href="mailto:{{ setting('store_email') }}" style="text-decoration:none; color:#ffffff;">{{ setting('store_email') }}</a></div>
                        @endif
                        <div style="margin-top:8px; opacity:0.85; font-size:13px;">&copy; {{ date('Y') }} Tüm hakları saklıdır.</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
</html>

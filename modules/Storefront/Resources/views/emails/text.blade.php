<!DOCTYPE html>
<html lang="{{ locale() }}" style="-ms-text-size-adjust: 100%;
                    -webkit-text-size-adjust: 100%;
                    -webkit-print-color-adjust: exact;"
>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400" rel="stylesheet">
    </head>

    <body style="font-family: 'Open Sans', sans-serif;
                font-size: 15px;
                min-width: 320px;
                margin: 0;"
    >
        <table style="border-collapse: collapse; width: 100%;">
            <tbody>
                <tr>
                    <td style="padding: 0;">
                        <table style="border-collapse: collapse; width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="background: {{ mail_theme_color() }}; text-align: center;">
                                        @if (is_null($logo))
                                            <h5 style="font-size: 30px;
                                                    line-height: 36px;
                                                    margin: 0;
                                                    padding: 30px 15px;
                                                    text-align: center;"
                                            >
                                                <a href="{{ route('home') }}" style="font-family: 'Open Sans', sans-serif;
                                                                                    font-weight: 400;
                                                                                    color: #ffffff;
                                                                                    text-decoration: none;"
                                                >
                                                    {{ setting('store_name') }}
                                                </a>
                                            </h5>
                                        @else
                                            <div style="display: flex;
                                                        height: 64px;
                                                        width: 200px;
                                                        align-items: center;
                                                        justify-content: center;
                                                        margin: auto;
                                                        padding: 16px 15px;"
                                            >
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
                        <table style="border-collapse: collapse;
                                    min-width: 320px;
                                    max-width: 600px;
                                    width: 100%;
                                    margin: auto;"
                        >
                            @isset($heading)
                                <tr>
                                    <td style="padding: 0;">
                                        <h4 style="font-family: 'Open Sans', sans-serif; font-weight: 800; font-size: 22px; line-height: 28px; margin: 0 0 12px; color: #0f172a;">
                                            {{ $heading }}
                                        </h4>
                                    </td>
                                </tr>
                            @endisset

                            <tr>
                                <td style="padding: 0;">
                                    <span style="font-family: 'Open Sans', sans-serif;
                                                font-weight: 400;
                                                font-size: 16px;
                                                line-height: 26px;
                                                color: #666666;
                                                display: block;"
                                    >
                                        {{ $text }}
                                    </span>
                                </td>
                            </tr>

                            @isset($action_url)
                                <tr>
                                    <td style="padding: 24px 0; text-align: center;">
                                        <a href="{{ $action_url }}" style="font-family: 'Open Sans', sans-serif; font-weight: 700; text-decoration: none; display: inline-block; background: {{ mail_theme_color() }}; color: #fafafa; padding: 11px 26px; border: none; border-radius: 8px; outline: 0;">
                                            {{ $action_text ?? 'View Details' }}
                                        </a>
                                    </td>
                                </tr>
                            @endisset

                            @if(isset($order) && $order->status === \Modules\Order\Entities\Order::SHIPPED)
                                <tr>
                                    <td style="padding: 0;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-top: 12px;">
                                            <tr>
                                                <td style="padding: 16px; border: 1px solid #e5e7eb; border-radius: 12px; background: #f9fafb;">
                                                    <div style="font-family: 'Open Sans', sans-serif; font-size: 16px; line-height: 24px; color: #0f172a; font-weight: 800; margin-bottom: 10px;">Sipariş kargoya verildi, en kısa sürede teslim edilecek.</div>
                                                    <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                                                        <div style="font-size: 14px; color: #374151;"><strong style="color:#111827;">Kargo Adı:</strong> {{ (string) ($order->shipping_carrier_name ?? '') }}</div>
                                                        <div style="font-size: 14px; color: #374151;"><strong style="color:#111827;">Takip Numarası:</strong> {{ (string) ($order->shipping_tracking_number ?? '') }}</div>
                                                        @php($trkUrl = (string) ($order->shipping_tracking_url ?? $order->tracking_reference ?? ''))
                                                        @if($trkUrl !== '' && filter_var($trkUrl, FILTER_VALIDATE_URL))
                                                            <div style="font-size: 14px; color: #374151;"><strong style="color:#111827;">Takip Linki:</strong> <a href="{{ $trkUrl }}" style="text-decoration: underline; color: #31629f;" target="_blank">{{ $trkUrl }}</a></div>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endif

                            @if(!(isset($order) && $order->status === \Modules\Order\Entities\Order::SHIPPED))
                                <tr>
                                    <td style="padding: 0;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-top: 10px;">
                                            <tr>
                                                <td style="padding: 14px; border: 1px solid #e5e7eb; border-radius: 12px; background: #f9fafb;">
                                                    <div style="font-size: 14px; line-height: 22px; color: #374151;">
                                                        <strong style="display:block; margin-bottom:6px; color:#111827;">{{ trans('storefront::layouts.contact') }}</strong>
                                                        @if (setting('store_phone') && ! setting('store_phone_hide'))
                                                            <div style="margin: 4px 0;">
                                                                <a href="tel:{{ setting('store_phone') }}" style="text-decoration: none; color: #31629f;">{{ setting('store_phone') }}</a>
                                                            </div>
                                                        @endif
                                                        @if (setting('store_email') && ! setting('store_email_hide'))
                                                            <div style="margin: 4px 0;">
                                                                <a href="mailto:{{ setting('store_email') }}" style="text-decoration: none; color: #31629f;">{{ setting('store_email') }}</a>
                                                            </div>
                                                        @endif
                                                        <div style="margin-top: 6px;">
                                                            <a href="{{ route('contact.create') }}" style="text-decoration: none; color: #31629f;">{{ trans('storefront::layouts.contact') }}</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endif
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

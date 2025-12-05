<?php

namespace Modules\Payment\Gateways;

use Illuminate\Http\Request;
use Modules\Order\Entities\Order;
use Modules\Payment\GatewayInterface;
use Modules\Payment\Responses\NullResponse;
use Modules\Shipping\SmartShippingCod;

class COD implements GatewayInterface
{
    public $label;
    public $description;


    public function __construct()
    {
        $label = setting('cod_label');
        $description = setting('cod_description');

        // Varsayılan İngilizce metinler hâlâ kullanılıyorsa ve aktif dil TR ise,
        // ödeme yöntemi adını "Kapıda Nakit Ödeme" olarak göster.
        if (app()->getLocale() === 'tr') {
            if ($label === 'Cash On Delivery' || empty($label)) {
                $label = 'Kapıda Nakit Ödeme';
            }

            if ($description === 'Pay with cash upon delivery.' || empty($description)) {
                $description = 'Teslimat sırasında nakit olarak ödeme yapın.';
            }
        }

        $this->label = $label;
        // Açıklama alanında sadece ayarlardan gelen metni göster.
        // COD ücreti zaten sipariş özetinde ayrı satır olarak gösteriliyor.
        $this->description = $description;
    }


    public function purchase(Order $order, Request $request)
    {
        return new NullResponse($order);
    }


    public function complete(Order $order)
    {
        return new NullResponse($order);
    }
}

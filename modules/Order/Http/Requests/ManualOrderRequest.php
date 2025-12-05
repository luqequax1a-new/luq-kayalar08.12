<?php

namespace Modules\Order\Http\Requests;

use Modules\Core\Http\Requests\Request;

class ManualOrderRequest extends Request
{
    protected $availableAttributes = 'order::attributes.orders';

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'shipping_method' => ['nullable', 'string'],
            'shipping_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'payment_mode' => ['required', 'in:manual_paid,manual_unpaid,payment_link'],
            'invoice.title' => ['nullable', 'string'],
            'invoice.tax_office' => ['nullable', 'string'],
            'invoice.tax_number' => ['nullable', 'string'],
            'payment_note' => ['nullable', 'string'],
        ];
    }
}


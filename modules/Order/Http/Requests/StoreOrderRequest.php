<?php

namespace Modules\Order\Http\Requests;

use Exception;
use Modules\Support\Country;
use Modules\Cart\Facades\Cart;
use Illuminate\Validation\Rule;
use Modules\Payment\Facades\Gateway;
use Modules\Core\Http\Requests\Request;
use Modules\Checkout\Exceptions\CheckoutException;

class StoreOrderRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'checkout::attributes';


    /**
     * Validate the class instance.
     *
     * @return void
     * @throws Exception
     */
    public function prepareForValidation()
    {
        if (!Cart::allItemsAreVirtual() && !$this->input('shipping_method')) {
            throw new CheckoutException(trans('checkout::messages.no_shipping_method'));
        }
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(
            [
                'customer_email' => ['required', 'email', $this->emailUniqueRule()],
                'customer_phone' => ['required'],
                'create_an_account' => 'boolean',
                'password' => 'required_if:create_an_account,1',
                'ship_to_a_different_address' => 'boolean',
                'payment_method' => ['required', Rule::in(Gateway::names())],
                'terms_and_conditions' => 'accepted',
                'shipping_method' => Cart::allItemsAreVirtual() ? 'nullable' : 'required',
                'billing.phone' => ['nullable'],
                'shipping.phone' => ['nullable'],
                'invoice.title' => ['nullable'],
                'invoice.tax_office' => ['nullable'],
                'invoice.tax_number' => ['nullable'],
            ],
            $this->billingAddressRules(),
            $this->shippingAddressRules()
        );
    }


    private function emailUniqueRule()
    {
        return $this->create_an_account ? Rule::unique('users', 'email') : null;
    }


    private function billingAddressRules()
    {
        return [
            'billing.first_name' => 'nullable',
            'billing.last_name' => 'nullable',
            'billing.address_1' => 'nullable',
            'billing.city' => 'nullable',
            'billing.zip' => ['nullable'],
            'billing.country' => ['nullable', Rule::in(Country::supportedCodes())],
            'billing.state' => 'nullable',
        ];
    }


    private function shippingAddressRules()
    {
        return [
            'shipping.first_name' => 'required',
            'shipping.last_name' => 'required',
            'shipping.address_1' => 'required',
            'shipping.city' => 'required',
            'shipping.zip' => ['nullable'],
            'shipping.country' => ['required', Rule::in(Country::supportedCodes())],
            'shipping.state' => 'required',
        ];
    }
}

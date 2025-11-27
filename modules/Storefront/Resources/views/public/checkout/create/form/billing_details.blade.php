<div class="billing-details" x-cloak x-show="form.ship_to_a_different_address">
    <h4 class="section-title">{{ trans('storefront::checkout.billing_details') }}</h4>

    <template x-if="hasAddress">
        <div x-cloak class="address-card-wrap">
            <div class="row">
                <template x-for="address in addresses" :key="address.id">
                    <div class="col d-flex">
                        <address
                            class="address-card"
                            :class="{
                                active: form.billingAddressId === address.id && !form.newBillingAddress,
                                'cursor-default': form.newBillingAddress
                            }"
                            @click="changeBillingAddress(address)"
                        >
                            <svg class="address-card-selected-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM16.78 9.7L11.11 15.37C10.97 15.51 10.78 15.59 10.58 15.59C10.38 15.59 10.19 15.51 10.05 15.37L7.22 12.54C6.93 12.25 6.93 11.77 7.22 11.48C7.51 11.19 7.99 11.19 8.28 11.48L10.58 13.78L15.72 8.64C16.01 8.35 16.49 8.35 16.78 8.64C17.07 8.93 17.07 9.4 16.78 9.7Z" fill="#292D32"/>
                            </svg>    

                            <template x-if="defaultAddress.address_id === address.id">
                                <span class="badge">
                                    {{ trans('storefront::checkout.default') }}
                                </span>
                            </template>
                            
                            <div class="address-card-data">
                                <span x-text="address.full_name"></span>
                                <span x-text="address.address_1"></span>

                                <template x-if="address.address_2">
                                    <span x-text="address.address_2"></span>
                                </template>

                                <span x-html="`${address.city}, ${address.state_name ?? address.state} ${address.zip}`"></span>
                                <span x-text="address.country_name"></span>
                            </div>
                        </address>
                    </div>
                </template>
            </div>

            <template x-if="!form.newBillingAddress && !form.billingAddressId">
                <span class="error-message">
                    {{ trans('storefront::checkout.you_must_select_an_address') }}
                </span>
            </template>
        </div>
    </template>

    <div x-cloak class="add-new-address-wrap">
        <template x-if="hasAddress">
            <button type="button" class="btn btn-add-new-address" @click="addNewBillingAddress">
                <span x-text="form.newBillingAddress ? '-' : '+'"></span>
                
                {{ trans('storefront::checkout.add_new_address') }}
            </button>
        </template>

        <div class="add-new-address-form" x-show="!hasAddress || form.newBillingAddress">
            <div class="row">
                
                

                <!-- Invoice block first -->
                <div class="col-md-9">
                    <div class="form-group">
                        <label for="invoice-title">Firma Adı</label>

                        <input
                            type="text"
                            id="invoice-title"
                            class="form-control"
                            x-model="form.invoice.title"
                        >
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="form-group">
                        <label for="invoice-tax-number">Vergi Numarası / TCKN</label>

                        <input
                            type="text"
                            id="invoice-tax-number"
                            class="form-control"
                            x-model="form.invoice.tax_number"
                        >
                    </div>
                </div>

                <div class="col-md-18">
                    <div class="form-group">
                        <label for="invoice-tax-office">Vergi Dairesi</label>

                        <input
                            type="text"
                            id="invoice-tax-office"
                            class="form-control"
                            x-model="form.invoice.tax_office"
                        >
                    </div>
                </div>

                <!-- Address line -->
                <div class="col-md-18">
                    <div class="form-group">
                        <label for="billing-address-1">
                            {{ trans('checkout::attributes.street_address') }}<span>*</span>
                        </label>

                        <input
                            type="text"
                            name="billing[address_1]"
                            id="billing-address-1"
                            class="form-control"
                            placeholder="{{ trans('checkout::attributes.billing.address_1') }}"
                            x-model="form.billing.address_1"
                        >

                        <template x-if="errors.has('billing.address_1')">
                            <span class="error-message" x-text="errors.get('billing.address_1')"></span>
                        </template>
                    </div>
                </div>

                <!-- State & City side-by-side -->
                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-state">
                            {{ trans('checkout::attributes.billing.state') }}<span>*</span>
                        </label>

                        <template x-if="!hasBillingStates">
                            <input
                                type="text"
                                name="billing[state]"
                                id="billing-state"
                                class="form-control"
                                x-model="form.billing.state"
                            >
                        </template>

                        <template x-if="hasBillingStates">
                            <select
                                name="billing[state]"
                                id="billing-state"
                                class="form-control arrow-black"
                                @change="changeBillingState($event.target.value)"
                            >
                                <option value="">{{ trans('storefront::checkout.please_select') }}</option>

                                <template x-for="(name, code) in states.billing" :key="code">
                                    <option :value="code" x-html="name"></option>
                                </template>
                            </select>
                        </template>

                        <template x-if="errors.has('billing.state')">
                            <span class="error-message" x-text="errors.get('billing.state')"></span>
                        </template>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="form-group">
                        <label for="billing-city">
                            {{ trans('checkout::attributes.billing.city') }}<span>*</span>
                        </label>

                        <template x-if="form.billing.country === 'TR' && districts.billing.length">
                            <select
                                name="billing[city]"
                                id="billing-city"
                                class="form-control arrow-black"
                                @change="changeBillingCity($event.target.value)"
                            >
                                <option value="">{{ trans('storefront::checkout.please_select') }}</option>

                                <template x-for="d in districts.billing" :key="d">
                                    <option :value="d" x-text="d"></option>
                                </template>
                            </select>
                        </template>

                        <template x-if="!(form.billing.country === 'TR' && districts.billing.length)">
                            <input
                                type="text"
                                name="billing[city]"
                                :value="form.billing.city"
                                id="billing-city"
                                class="form-control"
                                @change="changeBillingCity($event.target.value)"
                            >
                        </template>

                        <template x-if="errors.has('billing.city')">
                            <span class="error-message" x-text="errors.get('billing.city')"></span>
                        </template>
                    </div>
                </div>

                <!-- Country & Phone last row (pair); Phone not marked required) -->
                <div class="col-md-9" x-cloak x-show="!singleCountry">
                    <div class="form-group">
                        <label for="billing-country">
                            {{ trans('checkout::attributes.billing.country') }}<span>*</span>
                        </label>

                        <select
                            name="billing[country]"
                            id="billing-country"
                            class="form-control arrow-black"
                            @change="changeBillingCountry($event.target.value)"
                        >
                            <option value="">{{ trans('storefront::checkout.please_select') }}</option>
                            
                            <template x-for="(name, code) in countries" :key="code">
                                <option :value="code" x-text="name"></option>
                            </template>
                        </select>

                        <template x-if="errors.has('billing.country')">
                            <span class="error-message" x-text="errors.get('billing.country')"></span>
                        </template>
                    </div>
                </div>

                <div :class="singleCountry ? 'col-md-18' : 'col-md-9'">
                    <div class="form-group">
                        <label for="billing-phone">Telefon</label>

                        <input
                            type="text"
                            id="billing-phone"
                            class="form-control"
                            x-model="form.customer_phone"
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-borderless cart-table">
        <thead>
            <tr>
                <th>{{ trans('storefront::cart.table.image') }}</th>
                <th>{{ trans('storefront::cart.table.product_name') }}</th>
                <th>{{ trans('storefront::cart.table.unit_price') }}</th>
                <th>{{ trans('storefront::cart.table.quantity') }}</th>
                <th>{{ trans('storefront::cart.table.line_total') }}</th>
                <th>
                    <button class="btn-remove" @click="clearCart">
                        <i class="las la-times"></i>
                    </button>
                </th>
            </tr>
        </thead>

        <tbody>
            <template x-for="cartItem in $store.cart.items" :key="cartItem.id">
                <tr x-data="CartItem(cartItem)">
                    <td>
                        <a :href="productUrl" class="product-image">
                            <img
                                :src="baseImage"
                                :class="{ 'image-placeholder': !hasBaseImage }"
                                :alt="productName"
                            />
                        </a>
                    </td>
                    <td>
                        <a
                            :href="productUrl"
                            class="product-name"
                            x-text="productName"
                        >
                        </a>

                        <template x-cloak x-if="hasAnyVariation">
                            <ul class="list-inline product-options">
                                <template
                                    x-for="(variation, key) in cartItem.variations"
                                    :key="variation.id"
                                >
                                    <li>
                                        <label x-text="`${variation.name}:`"></label>
                                        
                                        <span x-text="`${variation.values[0].label}${variationsLength === Number(key) ? '' : ','}`"></span>
                                    </li>
                                </template>
                            </ul>
                        </template>

                        <template x-cloak x-if="hasAnyOption">
                            <ul class="list-inline product-options">
                                <template
                                    x-for="(option, key) in cartItem.options"
                                    :key="option.id"
                                >
                                    <li>
                                        <label x-text="`${option.name}:`"></label>

                                        <span x-text="`${optionValues(option)}${optionsLength === Number(key) ? '' : ','}`"></span>
                                    </li>
                                </template>
                            </ul>
                        </template>
                    </td>
                    <td>
                        <label>{{ trans('storefront::cart.table.unit_price') }}:</label>

                        <span class="product-price" x-text="formatCurrency(unitPrice)"></span>
                    </td>
                    <td>
                        <label>{{ trans('storefront::cart.table.quantity') }}:</label>

                        <span class="cart-qty" x-text="`${product.unit_decimal ? Number(cartItem.qty).toFixed(2).replace(/\\.00$/, '') : cartItem.qty}${product.unit_suffix ? ' ' + product.unit_suffix : ''}`"></span>
                    </td>
                    <td>
                        <label>{{ trans('storefront::cart.table.line_total') }}:</label>

                        <span class="product-price" x-text="formatCurrency(lineTotal(cartItem.qty))"></span>
                    </td>
                    <td>
                        <button class="btn-remove" @click="removeCartItem">
                            <i class="las la-times"></i>
                        </button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</div>

<template x-for="cartItem in $store.cart.items" :key="cartItem.id">
    <div x-data="CartItem(cartItem)" class="cart-item sidebar-cart-item">
        <a :href="productUrl" class="product-image">
            <img
                :src="baseImage"
                :class="{
                    'image-placeholder': !hasBaseImage,
                }"
                :alt="productName"
                loading="lazy"
            />
        </a>

        <div class="product-info">
            <a
                :href="productUrl"
                class="product-name"
                :title="productName"
                x-text="productName"
            >
            </a>
            
            <template x-if="hasAnyVariation">
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
            
            <template x-if="hasAnyOption">
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

            <div class="product-info-bottom d-flex flex-wrap align-items-center justify-content-between">
                <div class="number-picker">
                    <div class="input-group-quantity">
                        <button
                            type="button"
                            class="btn btn-number btn-minus"
                            :disabled="cartItem.qty <= cartItem.product.unit_min"
                            @click="updateQuantity(cartItem, cartItem.qty - cartItem.product.unit_step)"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                viewBox="0 0 16 16"
                                fill="none"
                            >
                                <path
                                    d="M13.3333 8H2.66663"
                                    stroke="#0E1E3E"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>

                        <input
                            type="text"
                            :value="cartItem.product.unit_decimal ? Number(cartItem.qty).toFixed(2).replace(/\.00$/, '') : cartItem.qty"
                            autocomplete="off"
                            :min="cartItem.product.unit_min || 1"
                            :max="maxQuantity(cartItem)"
                            class="form-control input-number input-quantity"
                            :id="`sidebar-cart-input-quantity-${cartItem.id}`"
                            @focus="$event.target.select()"
                            @input="changeQuantity(cartItem, Number($event.target.value))"
                            @keydown.up="updateQuantity(cartItem, cartItem.qty + cartItem.product.unit_step)"
                            @keydown.down="updateQuantity(cartItem, cartItem.qty - cartItem.product.unit_step)"
                        />

                        <button
                            type="button"
                            class="btn btn-number btn-plus"
                            :disabled="isQtyIncreaseDisabled(cartItem)"
                            @click="updateQuantity(cartItem, cartItem.qty + cartItem.product.unit_step)"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                viewBox="0 0 16 16"
                                fill="none"
                            >
                                <path
                                    d="M7.99996 2.66669V13.3334M13.3333 8.00002H2.66663"
                                    stroke="#0E1E3E"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="product-price" x-text="`x ${cartItem.product.unit_decimal ? Number(cartItem.qty).toFixed(2).replace(/\\.00$/, '') : cartItem.qty} · ${formatCurrency(lineTotal(cartItem.qty))}`"></div>
            </div>
        </div>

        <div class="remove-cart-item">
            <button class="btn-remove" @click="removeCartItem">
                <i class="las la-times"></i>
            </button>
        </div>
    </div>
</template>
